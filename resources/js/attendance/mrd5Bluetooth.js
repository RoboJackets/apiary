// WebBluetooth client for a Transact MRD5 card reader.
//
// The MRD5 exposes a Microchip MLDP (Low Energy Data Profile) service; card reads and battery status
// both arrive as ASCII text notifications on the same data characteristic. This module connects to
// the reader, buffers incoming chunks into complete messages, and classifies each message as either
// a battery status line (`BATT:<percent>/<opaque>`) or a card read. Card reads are handed off
// verbatim — the same string a user would type/swipe into the text field — so they can flow through
// the shared credential parser (resources/js/attendance/parseCredential.js).
//
// Requires a secure context (https or localhost/127.0.0.1) and a Chromium-based browser (Chrome/Edge);
// the reader must have Bluetooth Security Mode disabled or it transmits nothing over the characteristic.

// Microchip MLDP service + data/command characteristics advertised by the MRD5. Data (RX) carries
// card reads, battery status, and command responses as notifications; command (TX) is where ASCII
// commands like `VER:` are written.
export const MLDP_SERVICE = '00035b03-58e6-07dd-021a-08123a000300';
export const MLDP_DATA_CHARACTERISTIC = '00035b03-58e6-07dd-021a-08123a000301';
export const MLDP_COMMAND_CHARACTERISTIC = '00035b03-58e6-07dd-021a-08123a0003ff';

// Standard Bluetooth SIG Device Information Service, read once after connecting so the UI can
// show firmware/hardware/etc. Serial Number String (0x2A25) is intentionally omitted: Chrome's
// Web Bluetooth GATT blocklist forbids reading it (SecurityError), so it never populates.
const DEVICE_INFO_SERVICE = 'device_information';
const DEVICE_INFO_CHARACTERISTICS = {
    manufacturer: 'manufacturer_name_string',
    model: 'model_number_string',
    firmware: 'firmware_revision_string',
    hardware: 'hardware_revision_string',
    software: 'software_revision_string',
};

// Battery status line, e.g. "BATT:99/136". The first number is the battery percentage directly;
// the number after the slash is of unknown meaning and is preserved only as `raw`.
const BATTERY_REGEX = /^BATT:(\d+)\/(\d+)$/;

// Wait this long after the last chunk before treating the buffer as a complete message.
const RX_DEBOUNCE_MS = 80;

// `VER:` returns free-form text across several notification bursts, e.g. a line like
// "Blackboard MRD5 / SN: 3155029 / Boot: V1.0" plus a separate "Application: V3.2" line. These
// are matched independently of line layout so reordering/wrapping in firmware doesn't break parsing.
const VER_SERIAL_REGEX = /SN:\s*(\d+)/;
const VER_BOOT_REGEX = /Boot:\s*(\S+)/;
const VER_APPLICATION_REGEX = /Application:\s*(\S+)/i;

// How long to wait, after the last byte of a command response, before treating it as complete.
const COMMAND_QUIET_MS = 350;
// How long to wait for the first byte of a command response before giving up.
const COMMAND_TIMEOUT_MS = 1500;
// Conservative default ATT payload size for chunking command writes.
const COMMAND_WRITE_CHUNK_SIZE = 20;

/**
 * @typedef {Object} Mrd5DeviceInfo
 * @property {string|null} manufacturer
 * @property {string|null} model
 * @property {string|null} firmware
 * @property {string|null} hardware
 * @property {string|null} software
 */

/**
 * @typedef {Object} Mrd5VerInfo
 * @property {string|null} serial_number
 * @property {string|null} bootloader_version
 * @property {string|null} application_version
 */

/**
 * Shape expected by the `reader` field of the attendance store request
 * (app/Http/Requests/StoreAttendanceRequest.php).
 *
 * @typedef {Object} Mrd5ReaderPayload
 * @property {string} serial_number
 * @property {string} manufacturer
 * @property {string} model
 * @property {string} hardware_version
 * @property {string} bluetooth_firmware_version
 * @property {string} bluetooth_software_version
 * @property {string} bootloader_version
 * @property {string} application_version
 * @property {number} battery_percentage
 */

/**
 * @typedef {Object} Mrd5Callbacks
 * @property {(status: 'disconnected'|'connecting'|'connected') => void} [onStatusChange]
 * @property {(card: string) => void} [onCardRead]
 * @property {(battery: { raw: string, percent: number }) => void} [onBattery]
 * @property {(info: Mrd5DeviceInfo) => void} [onDeviceInfo]
 * @property {(error: Error) => void} [onError]
 */

export class Mrd5Reader {
    /**
     * Whether WebBluetooth is available in this browser/context.
     *
     * @returns {boolean}
     */
    static isSupported() {
        return typeof navigator !== 'undefined' && navigator.bluetooth != null;
    }

    /**
     * @param {Mrd5Callbacks} [callbacks]
     */
    constructor(callbacks = {}) {
        this.callbacks = callbacks;

        this.device = null;
        this.characteristic = null;
        this.commandCharacteristic = null;
        this.rxBuffer = '';
        this.rxTimer = null;
        this.decoder = new TextDecoder('ascii');
        this.encoder = new TextEncoder();
        this.deviceInfo = null;
        this.verInfo = null;
        this.batteryPercent = null;
        // Set while a command (e.g. `VER:`) is awaiting its response; incoming notifications are
        // routed here instead of the card-read/battery classifier until it resolves.
        this.pendingCommand = null;

        // Bound so they can be added/removed as event listeners.
        this.handleRx = this.handleRx.bind(this);
        this.handleDisconnect = this.handleDisconnect.bind(this);
    }

    get deviceName() {
        return this.device ? this.device.name || 'MRD5 reader' : null;
    }

    /**
     * Assemble the `reader` payload for the attendance store request from data already cached on
     * this instance (Device Information Service fields read at connect time, `VER:` fields queried
     * at connect time, and the most recently seen `BATT:` percentage) — never queried fresh here.
     * Returns null until every required field has been observed at least once.
     *
     * @returns {Mrd5ReaderPayload|null}
     */
    get readerPayload() {
        if (this.deviceInfo === null || this.verInfo === null || this.batteryPercent === null) {
            return null;
        }

        const {
            manufacturer, model, firmware, hardware, software,
        } = this.deviceInfo;
        const {
            serial_number: serialNumber, bootloader_version: bootloaderVersion, application_version: applicationVersion,
        } = this.verInfo;

        if (
            manufacturer == null || model == null || firmware == null || hardware == null || software == null
            || serialNumber == null || bootloaderVersion == null || applicationVersion == null
        ) {
            return null;
        }

        return {
            serial_number: serialNumber,
            manufacturer,
            model,
            hardware_version: hardware,
            bluetooth_firmware_version: firmware,
            bluetooth_software_version: software,
            bootloader_version: bootloaderVersion,
            application_version: applicationVersion,
            battery_percentage: this.batteryPercent,
        };
    }

    /**
     * Prompt for and connect to an MRD5 reader, then start listening for card reads. Must be called
     * from a user gesture (e.g. a click handler) or the browser will reject requestDevice().
     */
    async connect() {
        if (!Mrd5Reader.isSupported()) {
            throw new Error('WebBluetooth is not supported in this browser.');
        }

        this.emitStatus('connecting');

        try {
            this.device = await navigator.bluetooth.requestDevice({
                filters: [{ services: [MLDP_SERVICE] }],
                optionalServices: [MLDP_SERVICE, DEVICE_INFO_SERVICE],
            });

            this.device.addEventListener('gattserverdisconnected', this.handleDisconnect);

            const server = await this.device.gatt.connect();
            const service = await server.getPrimaryService(MLDP_SERVICE);
            this.characteristic = await service.getCharacteristic(MLDP_DATA_CHARACTERISTIC);

            this.characteristic.addEventListener('characteristicvaluechanged', this.handleRx);
            await this.characteristic.startNotifications();

            // Query VER: before announcing "connected" so nothing can tap a card while its
            // response is still being collected — while pendingCommand is set, incoming
            // notifications are routed to it instead of the card-read classifier (see handleRx),
            // so a card read during that window would otherwise be silently swallowed.
            this.commandCharacteristic = await this.discoverCommandCharacteristic(service);
            if (this.commandCharacteristic !== null) {
                await this.queryVersion();
            }

            this.emitStatus('connected');

            await this.readDeviceInfo(server);
        } catch (error) {
            // Reset partial state, then surface. A user cancelling the chooser throws NotFoundError;
            // callers may choose to ignore that quietly.
            this.cleanup();
            this.emitStatus('disconnected');
            throw error;
        }
    }

    /**
     * Disconnect from the reader and release the device. Notifications are stopped explicitly
     * before dropping the link: forcing a GATT disconnect while the data characteristic is still
     * subscribed can leave the reader's BLE module (the RN4020, in the MRD5) treating the
     * connection as still live until it separately times out, rather than registering a clean
     * disconnect. Bounded so an unresponsive reader can't hang this call indefinitely.
     */
    async disconnect() {
        const device = this.device;
        const characteristic = this.characteristic;
        this.cleanup();

        if (characteristic !== null) {
            try {
                await Promise.race([
                    characteristic.stopNotifications(),
                    new Promise(resolve => setTimeout(resolve, 1000)),
                ]);
            } catch {
                // Best-effort; fall through and drop the link regardless.
            }
        }

        if (device && device.gatt && device.gatt.connected) {
            device.gatt.disconnect();
        }
        this.emitStatus('disconnected');
    }

    /**
     * Best-effort read of the standard Device Information Service, once after connecting. Each
     * characteristic is read independently so a reader missing one (or blocked by Chrome's GATT
     * blocklist) still populates the rest.
     *
     * @param {BluetoothRemoteGATTServer} server
     */
    async readDeviceInfo(server) {
        let service;
        try {
            service = await server.getPrimaryService(DEVICE_INFO_SERVICE);
        } catch {
            return;
        }

        const info = {};
        for (const [key, uuid] of Object.entries(DEVICE_INFO_CHARACTERISTICS)) {
            try {
                const characteristic = await service.getCharacteristic(uuid);
                const value = await characteristic.readValue();
                info[key] = this.decoder.decode(value).replace(/\0+$/, '').trim() || null;
            } catch {
                info[key] = null;
            }
        }

        this.deviceInfo = info;
        this.emit('onDeviceInfo', info);
    }

    /**
     * Handle an incoming notification chunk. While a command response is being collected (see
     * {@link queryVersion}), bytes are routed there instead of the card-read/battery buffer.
     * Otherwise: decode ASCII, append to the buffer, and (re)start the debounce timer so a full
     * message is assembled before it is classified.
     *
     * @param {Event} event
     */
    handleRx(event) {
        const value = event.target.value;
        if (value == null) {
            return;
        }

        if (this.pendingCommand !== null) {
            const bytes = new Uint8Array(value.buffer, value.byteOffset, value.byteLength);
            for (const byte of bytes) {
                this.pendingCommand.bytes.push(byte);
            }
            this.pendingCommand.arm(this.pendingCommand.quietMs);
            return;
        }

        this.rxBuffer += this.decoder.decode(value);

        if (this.rxTimer !== null) {
            clearTimeout(this.rxTimer);
        }
        this.rxTimer = setTimeout(() => this.flush(), RX_DEBOUNCE_MS);
    }

    /**
     * Find the MLDP command (TX) characteristic used to send ASCII commands like `VER:`.
     * Best-effort: returns null (rather than throwing) if it isn't present, since it's only
     * needed for supplementary reader info, not for card reads.
     *
     * @param {BluetoothRemoteGATTService} service
     * @returns {Promise<BluetoothRemoteGATTCharacteristic|null>}
     */
    async discoverCommandCharacteristic(service) {
        try {
            return await service.getCharacteristic(MLDP_COMMAND_CHARACTERISTIC);
        } catch {
            return null;
        }
    }

    /**
     * Write an ASCII command to the MLDP command characteristic, chunked to a conservative ATT
     * payload size.
     *
     * @param {string} text
     */
    async sendCommand(text) {
        if (this.commandCharacteristic === null) {
            throw new Error('No MLDP command characteristic available.');
        }

        const bytes = this.encoder.encode(text);
        for (let i = 0; i < bytes.length; i += COMMAND_WRITE_CHUNK_SIZE) {
            const slice = bytes.slice(i, i + COMMAND_WRITE_CHUNK_SIZE);
            if (this.commandCharacteristic.properties.write) {
                await this.commandCharacteristic.writeValueWithResponse(slice);
            } else if (this.commandCharacteristic.properties.writeWithoutResponse) {
                await this.commandCharacteristic.writeValueWithoutResponse(slice);
                await new Promise(resolve => setTimeout(resolve, 12));
            } else {
                await this.commandCharacteristic.writeValue(slice);
            }
        }
    }

    /**
     * Send an ASCII command and collect its response. Responses can arrive as several separate
     * notification bursts, so completion is idle-based: resolve once `quietMs` elapses with no
     * new bytes after the first one arrives, bounded by `timeoutMs` for the very first byte.
     *
     * @param {string} text
     * @param {{quietMs?: number, timeoutMs?: number}} [options]
     * @returns {Promise<string>}
     */
    collectCommandResponse(text, { quietMs = COMMAND_QUIET_MS, timeoutMs = COMMAND_TIMEOUT_MS } = {}) {
        return new Promise((resolve, reject) => {
            const pending = { bytes: [], timer: null, quietMs };
            pending.finish = () => {
                clearTimeout(pending.timer);
                this.pendingCommand = null;
                resolve(this.decoder.decode(new Uint8Array(pending.bytes)));
            };
            pending.arm = ms => {
                clearTimeout(pending.timer);
                pending.timer = setTimeout(pending.finish, ms);
            };

            this.pendingCommand = pending;
            this.sendCommand(text)
                .then(() => {
                    if (this.pendingCommand === pending) {
                        pending.arm(timeoutMs);
                    }
                })
                .catch(error => {
                    clearTimeout(pending.timer);
                    if (this.pendingCommand === pending) {
                        this.pendingCommand = null;
                    }
                    reject(error);
                });
        });
    }

    /**
     * Query the reader's `VER:` info (serial number, bootloader version, application version) and
     * cache it on the instance. Best-effort: a reader that doesn't respond simply leaves
     * `verInfo` null, and `readerPayload` reflects that by returning null.
     */
    async queryVersion() {
        try {
            const text = await this.collectCommandResponse('VER:\n');
            const serial = VER_SERIAL_REGEX.exec(text);
            const boot = VER_BOOT_REGEX.exec(text);
            const application = VER_APPLICATION_REGEX.exec(text);

            this.verInfo = {
                serial_number: serial ? serial[1] : null,
                bootloader_version: boot ? boot[1] : null,
                application_version: application ? application[1] : null,
            };
        } catch {
            // Best-effort; card reads still work without VER info.
        }
    }

    /**
     * Classify the buffered message as battery status or a card read, then reset the buffer.
     */
    flush() {
        this.rxTimer = null;
        const message = this.rxBuffer.trim();
        this.rxBuffer = '';

        if (message === '') {
            return;
        }

        const battery = BATTERY_REGEX.exec(message);
        if (battery !== null) {
            this.batteryPercent = Number(battery[1]);
            this.emit('onBattery', { raw: message, percent: this.batteryPercent });
            return;
        }

        this.emit('onCardRead', message);
    }

    handleDisconnect() {
        this.cleanup();
        this.emitStatus('disconnected');
    }

    /**
     * Tear down listeners, timers and references without emitting status.
     */
    cleanup() {
        if (this.rxTimer !== null) {
            clearTimeout(this.rxTimer);
            this.rxTimer = null;
        }
        if (this.pendingCommand !== null) {
            clearTimeout(this.pendingCommand.timer);
            this.pendingCommand = null;
        }
        this.rxBuffer = '';
        this.deviceInfo = null;
        this.verInfo = null;
        this.batteryPercent = null;
        this.commandCharacteristic = null;

        if (this.characteristic !== null) {
            this.characteristic.removeEventListener('characteristicvaluechanged', this.handleRx);
            this.characteristic = null;
        }

        if (this.device !== null) {
            this.device.removeEventListener('gattserverdisconnected', this.handleDisconnect);
            this.device = null;
        }
    }

    emitStatus(status) {
        this.emit('onStatusChange', status);
    }

    emit(name, payload) {
        const handler = this.callbacks[name];
        if (typeof handler === 'function') {
            handler(payload);
        }
    }
}

export default Mrd5Reader;

// WebBluetooth client for a Transact MRD5 card reader.
//
// The MRD5 exposes a Microchip MLDP (Low Energy Data Profile) service; card reads, battery status,
// and command replies all arrive as ASCII text notifications on the same data characteristic. This
// module connects to the reader, buffers incoming chunks into complete messages, and classifies
// each message as a battery status line (`BATT:<percent>/<opaque>`), a stray command
// acknowledgment/reply not otherwise captured (see DEVICE_ACK_REGEX and
// VER_RESPONSE_FRAGMENT_REGEX), or a card read. Card reads are handed off verbatim — the same
// string a user would type/swipe into the text field — so they can flow through the shared
// credential parser (resources/js/attendance/parseCredential.js).
//
// Requires a secure context (https or localhost/127.0.0.1) and a Chromium-based browser (Chrome/Edge);
// the reader must have Bluetooth Security Mode disabled or it transmits nothing over the characteristic.

// Microchip MLDP service + data characteristic advertised by the MRD5. Card reads, battery status,
// and command responses (e.g. to `VER:`) all arrive as notifications on this one characteristic;
// commands are written to the same characteristic (it accepts WRITE / WRITE NO RESPONSE too) —
// this is a transparent bidirectional serial pipe, not two separate channels.
// It discovers "the write characteristic" by scanning for write permission,
// which would happily land on this data characteristic too.
export const MLDP_SERVICE = '00035b03-58e6-07dd-021a-08123a000300';
export const MLDP_DATA_CHARACTERISTIC = '00035b03-58e6-07dd-021a-08123a000301';

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

// VER: info is supplementary — it's only used for the `reader` field sent with attendance records,
// never for card reads themselves — so it's queried in the background after the connection is
// already usable rather than blocking "connected" on it, and retried a few times spread over the
// first several seconds in case the very first attempt lands before the link has fully settled.
const VER_INITIAL_DELAY_MS = 2500;
const VER_RETRY_DELAY_MS = 2500;
const VER_MAX_ATTEMPTS = 4;

// A correctly configured MRD5 pushes a BATT: notification every few seconds on its own, with no
// command needed to request it — so its absence is actually a faster/more direct dead-session
// signal than VER: (which requires a round trip we send ourselves). If none has arrived by this
// long after connecting, something's probably wrong; see startBatteryLivenessWatchdog.
const BATTERY_LIVENESS_TIMEOUT_MS = 12000;

// `LED:<color>,<durationMs>` — <color> is 3 ASCII chars, one per subpixel in R/G/B order; only the
// low 3 bits of each character matter, so decimal digit characters '0'-'7' give a predictable 0-7
// brightness dial per channel (e.g. "070" = green at max). Device replies "LED on". See
// mrd5-web-demo/detailed-findings.md §16.
const LED_MAX_DURATION_MS = 3000;

// `TONE:<letter>` triggers a beep pattern; device replies "Tone = <letter>". The letter is the key
// of the Mrd5Tone enum (apiary-mobile) — same set as the config protocol's Tones enum minus None:
// L=LowHighLow, H=HighLowHigh, W=Warble, K=Single, A=Ascending, D=Descending, B=FourLongBeeps,
// V=LongTone. See mrd5-web-demo/detailed-findings.md §16.

// Feedback played on a successful attendance record, matching apiary-mobile's doSuccessChirp().
const SUCCESS_CHIRP_TONE = 'A';
const SUCCESS_CHIRP_LED_COLOR = '070';
const SUCCESS_CHIRP_LED_DURATION_MS = 400;

// Feedback played on a card read that fails to parse (e.g. a malformed GTID), matching
// apiary-mobile's doErrorChirp().
const ERROR_CHIRP_TONE = 'W';
const ERROR_CHIRP_LED_COLOR = '700';
const ERROR_CHIRP_LED_DURATION_MS = 400;

// The MRD5's MLDP link is shared across every WebBluetooth client connected to it, not private to
// whichever one sent a given command — so a second tab connected to the same physical reader
// can see the reply to *our* VER: query (or a combined TONE:/LED: chirp), or we can see someone
// else's, with no pendingCommand active on the receiving side to capture it either way.
// Recognized by content (not the full expected shape) since it can arrive split across several
// flush() calls, same as a real VER: response can for its own sender.
const DEVICE_ACK_REGEX = /^(LED on|Tone = \S+)$/i;
const VER_RESPONSE_FRAGMENT_REGEX = /Blackboard MRD5|SN:\s*\d+|Boot:\s*\S+|Application:\s*\S+/i;

// A combined write (see playChirp) draws two separate one-line replies ("Tone = <letter>" and
// "LED on") back in a single notification burst, so a stray/observed ack can arrive as more than
// one line; DEVICE_ACK_REGEX alone only matches a single line. True when every non-blank line of
// the message independently looks like a device ack or a VER: fragment.
function isDeviceNoise(message) {
    return message.split(/\r?\n/).every(line => {
        const trimmed = line.trim();
        return trimmed === '' || DEVICE_ACK_REGEX.test(trimmed) || VER_RESPONSE_FRAGMENT_REGEX.test(trimmed);
    });
}

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
 * @property {() => void} [onSessionStuck] Fired when the connection appears to be one where the
 *   MRD5's MLDP session never came alive (see queryVersionInBackground) — nothing, including card
 *   reads, is likely to work until the user disconnects and reconnects.
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
        // See startBatteryLivenessWatchdog().
        this.batteryLivenessTimer = null;

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
        const missing = this.missingReaderFields();
        if (missing.length > 0) {
            // eslint-disable-next-line no-console
            console.warn(
                'Mrd5Reader: omitting `reader` from the attendance request — missing '
                + missing.join(', ') + '.'
            );
            return null;
        }

        const {
            manufacturer, model, firmware, hardware, software,
        } = this.deviceInfo;
        const {
            serial_number: serialNumber, bootloader_version: bootloaderVersion, application_version: applicationVersion,
        } = this.verInfo;

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
     * List which pieces of the `reader` payload haven't been observed yet, for diagnostics.
     *
     * @returns {string[]}
     */
    missingReaderFields() {
        const missing = [];

        if (this.deviceInfo === null) {
            missing.push('deviceInfo (Device Information Service was never read)');
        } else {
            for (const key of ['manufacturer', 'model', 'firmware', 'hardware', 'software']) {
                if (this.deviceInfo[key] == null) {
                    missing.push(`deviceInfo.${key}`);
                }
            }
        }

        if (this.verInfo === null) {
            missing.push('verInfo (VER: command never completed)');
        } else {
            for (const key of ['serial_number', 'bootloader_version', 'application_version']) {
                if (this.verInfo[key] == null) {
                    missing.push(`verInfo.${key}`);
                }
            }
        }

        if (this.batteryPercent === null) {
            missing.push('batteryPercent (no BATT: notification received yet)');
        }

        return missing;
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

            const server = await this.establishSession();

            this.emitStatus('connected');

            this.startBatteryLivenessWatchdog();

            await this.readDeviceInfo(server);

            // Kicked off last, unawaited: see the comment on VER_INITIAL_DELAY_MS for why this
            // can't block "connected", and the comment on queryVersion for the tradeoff this
            // implies (a real card tap landing in one of its collection windows is possible, if
            // unlikely, since it now runs after the reader is already usable).
            this.queryVersionInBackground();
        } catch (error) {
            // Reset partial state, then surface. A user cancelling the chooser throws NotFoundError;
            // callers may choose to ignore that quietly.
            this.cleanup();
            this.emitStatus('disconnected');
            throw error;
        }
    }

    /**
     * Connect the GATT link and subscribe to the data characteristic.
     *
     * @returns {Promise<BluetoothRemoteGATTServer>}
     */
    async establishSession() {
        const server = await this.device.gatt.connect();
        const service = await server.getPrimaryService(MLDP_SERVICE);
        this.characteristic = await service.getCharacteristic(MLDP_DATA_CHARACTERISTIC);

        this.characteristic.addEventListener('characteristicvaluechanged', this.handleRx);
        await this.characteristic.startNotifications();

        return server;
    }

    /**
     * Start a one-shot timer that fires onSessionStuck if no BATT: notification has arrived by
     * BATTERY_LIVENESS_TIMEOUT_MS after connecting. Cleared as soon as any battery update is
     * observed (see noteBatteryReceived), or on disconnect (see cleanup). This runs independently
     * of — and typically resolves faster than — the VER:-based stuck detection in
     * queryVersionInBackground, since it doesn't require us to send anything first.
     */
    startBatteryLivenessWatchdog() {
        this.batteryLivenessTimer = setTimeout(() => {
            this.batteryLivenessTimer = null;
            if (this.batteryPercent === null) {
                this.emit('onSessionStuck');
            }
        }, BATTERY_LIVENESS_TIMEOUT_MS);
    }

    /**
     * Record a freshly observed battery percentage and cancel the liveness watchdog, whether the
     * reading came from a normal BATT: push (see flush) or one harvested from inside a VER:
     * collection window (see queryVersion).
     *
     * @param {string} raw
     * @param {number} percent
     */
    noteBatteryReceived(raw, percent) {
        this.batteryPercent = percent;
        if (this.batteryLivenessTimer !== null) {
            clearTimeout(this.batteryLivenessTimer);
            this.batteryLivenessTimer = null;
        }
        this.emit('onBattery', { raw, percent });
    }

    /**
     * Fire-and-forget: wait for the link to settle, then query VER: (with its own internal
     * retries). Errors are surfaced via onError inside queryVersion() itself, not thrown here,
     * since there's no caller left to catch them by the time this resolves.
     * A completely empty response after every attempt fires onSessionStuck
     * so the UI can prompt the user to reconnect themselves.
     */
    queryVersionInBackground() {
        (async () => {
            await new Promise(resolve => setTimeout(resolve, VER_INITIAL_DELAY_MS));
            const outcome = await this.queryVersion();

            if (outcome === 'empty') {
                this.emit('onSessionStuck');
            }
        })();
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
     * Write an ASCII command to the data characteristic
     * chunked to a conservative ATT payload size.
     *
     * @param {string} text
     */
    async sendCommand(text) {
        if (this.characteristic === null) {
            throw new Error('Not connected to a reader.');
        }

        const bytes = this.encoder.encode(text);
        for (let i = 0; i < bytes.length; i += COMMAND_WRITE_CHUNK_SIZE) {
            const slice = bytes.slice(i, i + COMMAND_WRITE_CHUNK_SIZE);
            if (this.characteristic.properties.write) {
                await this.characteristic.writeValueWithResponse(slice);
            } else if (this.characteristic.properties.writeWithoutResponse) {
                await this.characteristic.writeValueWithoutResponse(slice);
                await new Promise(resolve => setTimeout(resolve, 12));
            } else {
                await this.characteristic.writeValue(slice);
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
     * cache it on the instance. Best-effort: card reads still work if this fails, but the failure
     * is surfaced via onError (rather than swallowed) since it silently blocks `readerPayload`. A
     * response that doesn't parse is retried (see VER_MAX_ATTEMPTS), in case it was sent before the
     * link had actually settled and was dropped. A response that turns out to just be a `BATT:` line
     * — a periodic push that happened to land inside this collection window instead of the real
     * reply — is harvested as a normal battery update rather than discarded.
     *
     * @param {number} [attempt]
     * @returns {Promise<'ok'|'empty'|'unparsed'|'not-connected'|'error'>}
     */
    async queryVersion(attempt = 1) {
        if (this.characteristic === null) {
            // Disconnected (or torn down) while a background retry was waiting; nothing to do.
            return 'not-connected';
        }

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

            if (serial !== null && boot !== null && application !== null) {
                return 'ok';
            }

            const trimmed = text.trim();

            const battery = BATTERY_REGEX.exec(trimmed);
            if (battery !== null) {
                this.noteBatteryReceived(trimmed, Number(battery[1]));
            }

            if (attempt < VER_MAX_ATTEMPTS) {
                await new Promise(resolve => setTimeout(resolve, VER_RETRY_DELAY_MS));
                return this.queryVersion(attempt + 1);
            }

            const message = 'MRD5: VER: response did not match the expected format: ' + JSON.stringify(text);
            // eslint-disable-next-line no-console
            console.warn(message);
            this.emit('onError', new Error(message));

            return trimmed === '' ? 'empty' : 'unparsed';
        } catch (error) {
            this.emit('onError', new Error('MRD5: VER: query failed: ' + error.message));
            return 'error';
        }
    }

    /**
     * Trigger a beep pattern + LED flash in a single write, joining `TONE:<letter>` and
     * `LED:<color>,<durationMs>` with `\n` the same way apiary-mobile's Mrd5Command.combined()
     * does, so both take effect from one command/response round trip instead of two back-to-back
     * ones. Best-effort and silent on failure (logged, not surfaced via onError/Sentry — this is
     * cosmetic feedback, not something worth alarming on). The device's combined text reply
     * ("Tone = <letter>\nLED on") is drained via collectCommandResponse rather than left to flow
     * through the normal card-read path, where it would otherwise show up as a bogus "Card format
     * not recognized" error right after a successful scan.
     *
     * @param {string} tone
     * @param {string} ledColor Three ASCII digit characters ('0'-'7'), one per R/G/B channel.
     * @param {number} ledDurationMs Clamped to LED_MAX_DURATION_MS.
     */
    async playChirp(tone, ledColor, ledDurationMs) {
        if (this.characteristic === null) {
            return;
        }
        const duration = Math.min(LED_MAX_DURATION_MS, Math.max(0, ledDurationMs));
        const command = `TONE:${tone}\nLED:${ledColor},${duration}\n`;
        try {
            await this.collectCommandResponse(command);
        } catch (error) {
            // eslint-disable-next-line no-console
            console.warn('MRD5: combined TONE:/LED: command failed: ' + error.message);
        }
    }

    /**
     * Play the reader's tone + LED feedback for a successful attendance record, so someone tapping
     * a card gets confirmation from the reader itself without needing to look at the screen —
     * matches apiary-mobile's doSuccessChirp(). Fire-and-forget from the caller's perspective: never
     * awaited by callers, so it never blocks the UI on the reader responding.
     */
    async playSuccessChirp() {
        await this.playChirp(SUCCESS_CHIRP_TONE, SUCCESS_CHIRP_LED_COLOR, SUCCESS_CHIRP_LED_DURATION_MS);
    }

    /**
     * Play the reader's tone + LED feedback for a card read that failed to parse (e.g. a malformed
     * GTID), matching apiary-mobile's doErrorChirp(). Same fire-and-forget contract as
     * {@link playSuccessChirp}.
     */
    async playErrorChirp() {
        await this.playChirp(ERROR_CHIRP_TONE, ERROR_CHIRP_LED_COLOR, ERROR_CHIRP_LED_DURATION_MS);
    }

    /**
     * Classify the buffered message as battery status, a stray command acknowledgment, or a card
     * read, then reset the buffer.
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
            this.noteBatteryReceived(message, Number(battery[1]));
            return;
        }

        if (isDeviceNoise(message)) {
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
        if (this.batteryLivenessTimer !== null) {
            clearTimeout(this.batteryLivenessTimer);
            this.batteryLivenessTimer = null;
        }
        this.rxBuffer = '';
        this.deviceInfo = null;
        this.verInfo = null;
        this.batteryPercent = null;

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

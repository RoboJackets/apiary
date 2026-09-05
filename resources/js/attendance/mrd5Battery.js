// Shared parser for the MRD5's periodic battery status line.
//
// A correctly configured MRD5 emits `BATT:<percent>/<current>` every few seconds in both output
// modes: as a notification on the MLDP data characteristic over Bluetooth (consumed in
// mrd5Bluetooth.js), and as typed keystrokes in keyboard-wedge mode, where the line lands in
// whatever text field has focus and is submitted like a card read. Callers check each incoming
// message/line with parseBatteryMessage so these pushes are consumed as telemetry rather than
// surfaced as unrecognized card data.
//
// The number after the slash is a current reading and can be negative (e.g. "BATT:100/-62").

const BATTERY_MESSAGE_REGEX = /^BATT:(\d+)\/(-?\d+)$/;

/**
 * Parse a battery status line emitted by the reader.
 *
 * @param {*} message
 * @returns {{raw: string, percent: number, current: number}|null}
 */
export function parseBatteryMessage(message) {
    if (message === null || message === undefined) {
        return null;
    }

    const match = BATTERY_MESSAGE_REGEX.exec(String(message).trim());

    if (match === null) {
        return null;
    }

    return {
        raw: match[0],
        percent: Number(match[1]),
        current: Number(match[2]),
    };
}

export default parseBatteryMessage;

// Shared BuzzCard/GTID credential parser.
//
// Given a raw string from a card reader (keyboard wedge) or manual entry, this detects the
// credential format and returns { gtid, access_card_number, cardType }. `gtid` and
// `access_card_number` are strings when present and `null` otherwise; `cardType` is `null` when
// the format does not imply a specific credential type (e.g. a bare GTID). Returns `null` when the
// input is empty or the format is not recognized.
//
// Used by both the attendance kiosk (resources/js/components/attendance/AttendanceKiosk.vue) and the
// Nova record-attendance modal (nova-components/RecordAttendanceModal) so the two entry points stay
// in sync. The Nova modal is built in an isolated stage; the Dockerfile copies this file in so its
// relative import resolves.

const tsCustomRegex = /\|?(\d*)\|(\d*)\|(\w+)\|?/;
const tsRawRegex = /^1570=(\d+)=\d+=(\d+)$/;

function isNumeric(n) {
    return !isNaN(parseFloat(n)) && isFinite(n);
}

export default function parseCredential(cardData) {
    if (cardData === null || cardData === undefined) {
        return null;
    }

    const value = String(cardData).trim();

    if (value === '') {
        return null;
    }

    if (isNumeric(value) && value.length === 9 && value[0] === '9') {
        // GTID only: no specific credential type discernible (e.g. 902900001)
        return { gtid: value, access_card_number: null, cardType: null };
    }

    if (isNumeric(value) && value.length === 16 && value.startsWith('601770')) {
        // Mobile credential: sixteen-digit number starting with 601770 (e.g. 6017700010000123)
        return { gtid: null, access_card_number: value, cardType: 'mobile' };
    }

    if (isNumeric(value) && (value.length === 6 || value.length === 7 ||
        (value.length === 9 && value.startsWith('0')))) {
        // Plastic credential: 6-digit, 7-digit or 9-digit zero-padded
        // e.g. 800001, 1000003, 000800001, 001000003
        return { gtid: null, access_card_number: String(parseInt(value, 10)), cardType: 'plastic' };
    }

    if (tsCustomRegex.test(value)) {
        // Transact reader custom format for RoboJackets: GTID|CardNumber|CardType
        // Mobile Credential (Card): |6017700010000123|MobileA
        // Mobile Credential (GTID): 902900001||MobileA
        // Plastic Credential: 902900001|800001|DESFire
        const [, gtid, cardNumber, cardType] = value.match(tsCustomRegex);

        return {
            gtid: gtid ? gtid : null,
            access_card_number: gtid ? null : cardNumber,
            cardType,
        };
    }

    if (tsRawRegex.test(value)) {
        // Transact Reader (MRD5, PS4101, maybe TWN4?) Raw (Non-Customized) Plastic Card Format
        // 1570=GTID=00=RawCardNumber (e.g. 1570=902900001=00=6017700008000010)
        const [, gtid, rawCardNumber] = value.match(tsRawRegex);
        const cardNumber = rawCardNumber.slice(6, 15); // drop '601770' prefix and trailing digit

        return {
            gtid,
            access_card_number: String(parseInt(cardNumber, 10)), // strip leading zeros
            cardType: 'plastic',
        };
    }

    return null;
}

export { parseCredential };

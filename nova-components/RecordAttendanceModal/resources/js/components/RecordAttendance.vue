<template>
    <modal
        data-testid="record-attendance-modal"
        tabindex="-1"
        role="dialog"
        @modal-close="handleClose"
    >
        <form
            autocomplete="off"
            @submit.prevent.stop="submit()"
            class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden space-y-6"
        >
            <h3
                class="uppercase tracking-wide font-bold text-xs text-gray-500 dark:text-gray-400 border-b border-gray-100 dark:border-gray-700 py-4 px-8 flex items-center justify-between"
            >
                <span>Record Attendance</span>
                <span
                    class="normal-case inline-flex items-center rounded-full bg-primary-500 text-white dark:text-gray-900 text-xs font-bold px-2 py-0.5"
                    dusk="attendance-counter"
                    title="Records created this session"
                >
                    {{ count }}
                </span>
            </h3>

            <div class="px-8">
                <div v-if="mrd5Supported" class="flex items-center flex-wrap mb-4" dusk="mrd5-status-row">
                    <button
                        type="button"
                        dusk="reader-connection-button"
                        :disabled="readerStatus === 'connecting'"
                        :title="paired ? `Disconnect ${readerDeviceName || 'the BuzzCard reader'}` : 'Connect a BuzzCard reader'"
                        @click.prevent="paired ? disconnectReader() : pairReader()"
                        class="inline-flex items-center justify-center h-7 px-3 mr-3 rounded-full text-xs font-bold border bg-transparent border-gray-400 dark:border-gray-500 text-gray-600 dark:text-gray-400 hover:[&:not(:disabled)]:bg-gray-700/5 dark:hover:[&:not(:disabled)]:bg-gray-950 cursor-pointer appearance-none disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <bluetooth-icon class="h-3 w-3 mr-2" aria-hidden="true" />
                        {{ readerButtonText }}
                    </button>

                    <span
                        v-if="paired && readerBattery !== null"
                        class="inline-flex items-center h-7 px-3 mr-3 rounded-full text-xs font-bold border"
                        :class="batteryLow ? 'border-red-500 text-red-500' : 'border-gray-400 dark:border-gray-500 text-gray-600 dark:text-gray-400'"
                        dusk="reader-battery-pill"
                    >
                        <battery-icon class="h-3 w-3 mr-2" aria-hidden="true" />
                        {{ readerBattery }}%
                    </span>

                    <a
                        href="/docs/officers/attendance/"
                        target="_blank"
                        rel="noopener noreferrer"
                        dusk="pair-reader-help-link"
                        title="Help with pairing a reader"
                        aria-label="Help with pairing a reader"
                        class="inline-flex items-center justify-center mr-3 text-gray-400 dark:text-gray-500 hover:text-primary-500 dark:hover:text-primary-400"
                    >
                        <icon name="question-mark-circle" />
                    </a>

                    <span v-if="paired && batteryLow" class="text-xs font-semibold text-red-500" dusk="reader-battery-warning">
                        ⚠ Reader battery low ({{ readerBattery }}%) - charge it soon.
                    </span>
                </div>

                <div
                    v-if="sessionStuck"
                    class="flex items-center justify-between mb-4 p-3 rounded border border-red-500 bg-red-50 dark:bg-transparent"
                    dusk="session-stuck-banner"
                >
                    <span class="text-xs font-semibold text-red-500 mr-3">
                        Reader isn't responding. Disconnect and reconnect to fix this.
                    </span>
                    <button
                        type="button"
                        dusk="reconnect-stuck-button"
                        @click.prevent="recoverStuckSession"
                        class="inline-flex items-center justify-center h-7 px-3 rounded-full text-xs font-bold border border-red-500 text-red-500 cursor-pointer appearance-none shrink-0"
                    >
                        Reconnect
                    </button>
                </div>

                <div v-if="paired && !manualEntryVisible" class="flex flex-col items-center text-center mb-4" dusk="mrd5-tap-prompt">
                    <img
                        src="/img/Universal_Contactless_Card_Symbol.svg"
                        alt=""
                        aria-hidden="true"
                        class="contactless-icon h-14 mb-4"
                    >
                    <p class="text-base font-semibold text-gray-800 dark:text-gray-100 mb-3">
                        Tap a BuzzCard
                    </p>
                    <button
                        type="button"
                        dusk="manual-entry-button"
                        @click.prevent="showManualEntry"
                        class="inline-flex items-center justify-center h-7 px-3 rounded-full text-xs font-bold border bg-transparent border-gray-400 dark:border-gray-500 text-gray-600 dark:text-gray-400 hover:bg-gray-700/5 dark:hover:bg-gray-950 cursor-pointer appearance-none"
                    >
                        Enter GTID manually
                    </button>
                </div>
                <p v-else class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                    Enter a GTID, then press Enter.
                </p>

<!-- autocomplete/data-*ignore attributes keep password managers from offering
                    credit-card autofill -->
                <input
                    v-if="!paired || manualEntryVisible"
                    ref="input"
                    v-model="identifier"
                    type="text"
                    name="attendance-identifier"
                    autocomplete="off"
                    autocapitalize="off"
                    autocorrect="off"
                    spellcheck="false"
                    data-1p-ignore="true"
                    data-lpignore="true"
                    data-bwignore="true"
                    data-form-type="other"
                    :disabled="submitting"
                    class="w-full form-control form-input form-input-bordered"
                    placeholder="GTID"
                />

                <button
                    v-if="paired && manualEntryVisible"
                    type="button"
                    dusk="use-reader-button"
                    @click.prevent="manualEntryVisible = false"
                    class="mt-2 text-xs font-bold text-primary-500 hover:underline cursor-pointer appearance-none bg-transparent border-0 p-0"
                >
                    Use reader instead
                </button>

                <div v-if="result !== null" class="mt-4" dusk="attendance-result">
                    <p
                        class="text-sm font-semibold"
                        :class="result.type === 'success'
                            ? 'text-green-500'
                            : result.type === 'error'
                                ? 'text-red-500'
                                : 'text-gray-500 dark:text-gray-400'"
                    >
                        {{ result.message }}
                    </p>
                    <ul v-if="result.tips" class="mt-2 space-y-1 text-xs text-gray-500 dark:text-gray-400">
                        <li v-for="tip in result.tips" :key="tip">
                            &bull; {{ tip }}
                        </li>
                    </ul>
                </div>
            </div>

            <div class="bg-gray-100 dark:bg-gray-700 px-6 py-3 flex">
                <div class="flex items-center ml-auto">
                    <button
                        type="button"
                        dusk="close-attendance-button"
                        @click.prevent="handleClose"
                        class="inline-flex items-center justify-center h-9 px-3 mr-3 rounded text-sm font-bold border bg-transparent border-transparent text-gray-600 dark:text-gray-400 hover:[&:not(:disabled)]:bg-gray-700/5 dark:hover:[&:not(:disabled)]:bg-gray-950 focus:outline-none focus:ring ring-primary-200 dark:ring-gray-600 cursor-pointer appearance-none"
                    >
                        Done
                    </button>

                    <button
                        dusk="record-attendance-button"
                        :disabled="submitting"
                        type="submit"
                        class="inline-flex items-center justify-center shadow h-9 px-3 rounded text-sm font-bold border bg-primary-500 border-primary-500 hover:[&:not(:disabled)]:bg-primary-400 hover:[&:not(:disabled)]:border-primary-400 text-white dark:text-gray-900 focus:outline-none focus:ring ring-primary-200 dark:ring-gray-600 cursor-pointer appearance-none disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        Record
                    </button>
                </div>
            </div>
        </form>
    </modal>
</template>

<script>
// The credential parser is shared with the attendance kiosk (resources/js/components/attendance/
// AttendanceKiosk.vue) so both entry points detect card formats identically. It is bundled from the
// app's resources during the build; the Dockerfile copies it into the nova-components build stage.
import parseCredential from '../../../../../resources/js/attendance/parseCredential';
import Mrd5Reader from '../../../../../resources/js/attendance/mrd5Bluetooth';
import { Icon } from 'laravel-nova-ui';
import BluetoothIcon from './icons/BluetoothIcon';
import BatteryIcon from './icons/BatteryIcon';

const LOW_BATTERY_THRESHOLD = 15;

const CONNECT_ERROR_TIPS = [
    "Press the reader's power button three times quickly.",
    "Forget all MRD5-XXX devices in your Bluetooth settings.",
    'Try connecting again.',
    'Make sure to accept any pairing prompts.'
];

export default {
    components: {
        Icon,
        BluetoothIcon,
        BatteryIcon,
    },

    props: {
        working: Boolean,
        resourceName: { type: String, required: true },
        action: { type: Object, required: true },
        selectedResources: { type: [Array, String], required: true },
        errors: { type: Object, required: true },
    },

    data() {
        return {
            identifier: '',
            count: 0,
            result: null,
            submitting: false,
            mrd5Supported: Mrd5Reader.isSupported(),
            readerStatus: 'disconnected',
            readerDeviceName: null,
            readerBattery: null,
            manualEntryVisible: false,
            sessionStuck: false,
        };
    },

    computed: {
        attendableType() {
            return this.resourceName.replace(/s$/, '');
        },
        attendableId() {
            return Array.isArray(this.selectedResources)
                ? this.selectedResources[0]
                : this.selectedResources;
        },
        paired() {
            return this.readerStatus === 'connected';
        },
        readerButtonText() {
            if (this.readerStatus === 'connecting') {
                return 'Connecting…';
            }
            return this.paired ? 'Reader connected' : 'Connect BuzzCard Reader';
        },
        batteryLow() {
            return this.readerBattery !== null && this.readerBattery < LOW_BATTERY_THRESHOLD;
        },
    },

    created() {
        this.reader = new Mrd5Reader({
            onStatusChange: status => {
                this.readerStatus = status;
                if (status === 'connected') {
                    this.readerDeviceName = this.reader.deviceName;
                    this.result = null;
                } else {
                    this.readerDeviceName = null;
                    this.readerBattery = null;
                    this.manualEntryVisible = false;
                    this.sessionStuck = false;
                }
            },
            onCardRead: card => this.submit(card),
            onBattery: battery => {
                this.readerBattery = battery.percent;
            },
            onSessionStuck: () => {
                this.sessionStuck = true;
            },
            onError: error => {
                Sentry.captureException(error);
            },
        });
    },

    mounted() {
        this.focusInput();
    },

    beforeUnmount() {
        this.reader.disconnect();
    },

    methods: {
        focusInput() {
            this.$nextTick(() => {
                if (this.$refs.input) {
                    this.$refs.input.focus();
                }
            });
        },

        showManualEntry() {
            this.manualEntryVisible = true;
            this.focusInput();
        },

        pairReader() {
            this.reader.connect().catch(error => {
                if (error.name !== 'NotFoundError') {
                    Sentry.captureException(error);
                    this.showResult('error', 'Unable to connect to the reader.', CONNECT_ERROR_TIPS);
                }
            });
        },

        disconnectReader() {
            if (window.confirm('Disconnect the BuzzCard reader?')) {
                this.reader.disconnect();
            }
        },

        async recoverStuckSession() {
            // No confirmation dialog here, unlike disconnectReader() — the connection is already
            // known to be broken (that's why this banner is showing), so there's nothing to protect
            // the user from by asking first.
            this.sessionStuck = false;
            await this.reader.disconnect();
            this.pairReader();
        },

        submit(rawFromReader) {
            if (this.submitting) {
                return;
            }

            const fromReader = rawFromReader !== undefined;
            const value = fromReader ? rawFromReader : this.identifier;
            const parsed = parseCredential(value);

            if (parsed === null) {
                const trimmedValue = String(value).trim();
                this.showResult(
                    'error',
                    trimmedValue === ''
                        ? 'Enter a GTID'
                        : 'Card format not recognized.'
                );
                if (fromReader) {
                    // Fire-and-forget, same as the success chirp — gives feedback from the reader
                    // itself without waiting on or blocking the UI.
                    this.reader.playErrorChirp();

                    if (trimmedValue !== '') {
                        // Only reader-sourced misses are logged: a manually typed value failing to
                        // parse is almost always a human typo, not a gap in parseCredential's format
                        // coverage, and would otherwise flood Sentry with noise.
                        Sentry.captureException(
                            new Error(`Card format not recognized: ${JSON.stringify(trimmedValue)}`)
                        );
                    }
                }
                this.identifier = '';
                this.focusInput();
                return;
            }

            let source = fromReader ? 'Nova - MRD5' : 'Nova';
            if (parsed.cardType !== null) {
                source += ` - ${parsed.cardType}`;
            }

            const payload = {
                attendable_type: this.attendableType,
                attendable_id: this.attendableId,
                source,
            };
            if (parsed.gtid !== null) {
                payload.gtid = parsed.gtid;
            }
            if (parsed.access_card_number !== null) {
                payload.access_card_number = parsed.access_card_number;
            }
            if (this.paired) {
                const reader = this.reader.readerPayload;
                if (reader !== null) {
                    payload.reader = reader;
                }
            }

            this.submitting = true;

            Nova.request()
                .post('/api/v1/attendance?include=attendee', payload)
                .then(response => {
                    const attendance = response.data.attendance;
                    const name = attendance.attendee != null ? attendance.attendee.name : 'Non-Member';

                    if (response.status === 201) {
                        this.count += 1;
                    }

                    this.showResult('success', `Recorded — ${name}`);

                    if (fromReader) {
                        // Fire-and-forget: gives feedback from the reader itself for someone who
                        // tapped a card without watching the screen. Never awaited/blocking.
                        this.reader.playSuccessChirp();
                    }
                })
                .catch(error => {
                    Sentry.captureException(error)
                    const status = error.response ? error.response.status : null;
                    if (status === 403) {
                        this.showResult('error', "You don't have permission to record attendance.");
                    } else if (status === 422) {
                        this.showResult('error', 'That does not look like a valid GTID or BuzzCard tap.');
                    } else {
                        this.showResult(
                            'error',
                            'Unable to record attendance. Check your connection and try again.'
                        );
                    }
                })
                .finally(() => {
                    this.submitting = false;
                    this.identifier = '';
                    this.focusInput();
                });
        },

        showResult(type, message, tips = null) {
            this.result = { type, message, tips };
        },

        handleClose() {
            this.reader.disconnect();
            this.$emit('close');
        },
    },
};
</script>

<style>
/* The source SVG is solid black with no currentColor support, so it disappears against Nova's
   dark-mode background — invert it there instead. */
.dark .contactless-icon {
    filter: invert(1);
}
</style>

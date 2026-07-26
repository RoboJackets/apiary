<template>
    <modal
        data-testid="record-attendance-modal"
        tabindex="-1"
        role="dialog"
        @modal-close="handleClose"
    >
        <form
            autocomplete="off"
            @submit.prevent.stop="submit"
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
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                    Swipe a BuzzCard or type a GTID or BuzzCard number, then press Enter.
                </p>

                <!-- autocomplete/data-*ignore attributes keep password managers from offering
                     credit-card autofill (the field is an ID, not a payment card). -->
                <input
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
                    placeholder="GTID or BuzzCard number"
                />

                <p
                    v-if="result !== null"
                    class="mt-4 text-sm font-semibold"
                    :class="result.type === 'success'
                        ? 'text-green-500'
                        : result.type === 'error'
                            ? 'text-red-500'
                            : 'text-gray-500 dark:text-gray-400'"
                    dusk="attendance-result"
                >
                    {{ result.message }}
                </p>
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

export default {
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
        };
    },

    computed: {
        // 'teams' => 'team', 'events' => 'event'
        attendableType() {
            return this.resourceName.replace(/s$/, '');
        },
        attendableId() {
            return Array.isArray(this.selectedResources)
                ? this.selectedResources[0]
                : this.selectedResources;
        },
    },

    mounted() {
        this.focusInput();
    },

    methods: {
        focusInput() {
            this.$nextTick(() => {
                if (this.$refs.input) {
                    this.$refs.input.focus();
                }
            });
        },

        submit() {
            if (this.submitting) {
                return;
            }

            const parsed = parseCredential(this.identifier);

            if (parsed === null) {
                this.showResult(
                    'error',
                    this.identifier.trim() === ''
                        ? 'Enter a GTID or BuzzCard number.'
                        : 'Card format not recognized.'
                );
                this.identifier = '';
                this.focusInput();
                return;
            }

            let source = 'nova';
            if (parsed.cardType !== null) {
                source += '-' + parsed.cardType;
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

            this.submitting = true;

            Nova.request()
                .post('/api/v1/attendance?include=attendee', payload)
                .then(response => {
                    const attendance = response.data.attendance;
                    const name = attendance.attendee != null ? attendance.attendee.name : 'Non-Member';

                    if (response.status === 201) {
                        this.count += 1;
                        this.showResult('success', 'Recorded — ' + name);
                    } else {
                        // 200: attendance already existed for today.
                        this.showResult('duplicate', 'Already recorded today — ' + name);
                    }
                })
                .catch(error => {
                    const status = error.response ? error.response.status : null;
                    if (status === 403) {
                        this.showResult('error', "You don't have permission to record attendance.");
                    } else if (status === 422) {
                        this.showResult('error', 'That does not look like a valid GTID or BuzzCard number.');
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

        showResult(type, message) {
            this.result = { type, message };
        },

        handleClose() {
            this.$emit('close');
        },
    },
};
</script>

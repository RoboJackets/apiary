<template>
    <div class="relative">
        <h1 class="mb-3 text-90 font-normal text-2xl">Collect Attendance</h1>
        <div class="card">
            <div class="relative">
                <div class="flex px-6 py-8">
                    <form id="collectAttendanceForm" @submit.prevent>
                        <div class="row">
                            <div class="col-12">
                                Swipe a BuzzCard or enter a GTID number
                            </div>
                        </div>
                        <div class="row pt-3">
                            <div class="col-sm-12 col-lg-9">
                                <input v-model="attendance.gtid"
                                       type="text"
                                       class="w-full form-control form-input form-input-bordered"
                                       :class="{ 'is-invalid': $v.attendance.gtid.$error }"
                                       ref="input">
                            </div>
                        </div>
                        <div class="row pt-3">
                            <div class="col-sm-3 pt-3">
                                <button type="submit"
                                        class="btn btn-primary inline-flex items-center relative"
                                        @click="submit">
                                    <span>Submit</span>
                                </button>
                            </div>
                        </div>
                        <div class="row pt-3">
                            <div class="col-12">
                                <em><span v-bind:class="{ 'text-danger': hasError}"> {{feedback}} </span></em>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    const {required, numeric} = require('vuelidate/lib/validators')
    import Swal from 'sweetalert2'

    export default {
        props: ['resourceName', 'resourceId', 'field'],

        mounted() {
        },
        computed: {
            attendableType: function() {
                return this.resourceName.toLowerCase().replace(/s$/, '');
            }
        },
        data() {
            return {
                attendance: {
                    gtid: '',
                    attendable_id: this.resourceId,
                    attendable_type: this.attendableType,
                    source: 'MyRoboJackets',
                    include: 'attendee',
                },
                feedback: '',
                baseUrl: '/api/v1/attendance',
                hasError: false,
            };
        },
        watch: {
            'attendance.gtid': function (val, oldVal) {
                this.debouncedGTID();
            },
        },
        created: function () {
            this.debouncedGTID = _.debounce(this.parseGTID, 500);
            this.attendance.attendable_type = this.attendableType;
        },
        methods: {
            parseGTID() {
                //Allows use of card readers that can't parse out data (#317)
                //Parses out the GTID using regex and updates the value accordingly
                let re = new RegExp('(9[0-9]{8})');
                if (!Number.isInteger(parseInt(this.attendance.gtid)) && this.attendance.gtid !== '') {
                    let matches = re.exec(this.attendance.gtid);
                    if (matches != null) {
                        this.attendance.gtid = matches[0];
                        //We have to manually submit it since the carriage return happens before the regex is run
                        this.submit();
                    }
                }
            },
            submit() {
                if (this.$v.$invalid) {
                    this.$v.$touch();
                    return;
                }

                axios
                    .post(this.baseUrl, this.attendance)
                    .then(response => {
                        this.hasError = false;
                        let responseContent = response.data.attendance;
                        let attendeeName = (responseContent.attendee != null) ? responseContent.attendee.name : "Non-Member";
                        this.feedback = 'Saved! (' + attendeeName + ')';
                        this.attendance.gtid = '';
                        this.$refs.input.focus();
                    })
                    .catch(error => {
                        this.hasError = true;
                        this.feedback = '';
                        if (error.response.status === 403) {
                            Swal.fire({
                                title: 'Whoops!',
                                text: "You don't have permission to perform that action.",
                                type: 'error',
                            });
                        } else {
                            Swal.fire(
                                'Error',
                                'Unable to process data. Check your internet connection or try refreshing the page.',
                                'error'
                            );
                        }
                    });
            },
        },
        validations: {
            attendance: {
                gtid: {required, numeric},
                attendable_type: {required},
                attendable_id: {required, numeric},
            },
        }
    }
</script>

<template>
    <div class="modal fade" :id="id" tabindex="-1" role="dialog" :aria-labelledby="id" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" :id="id">Swipe your BuzzCard</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="recordAttendanceForm" v-on:submit.prevent="submit">
                    <div class="row">
                        <div class="col-12">
                            <img src="https://rickert.yolasite.com/resources/card-swipe.gif" style="max-width: 400px;">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <em><span v-bind:class="{ 'text-danger': hasError}"> {{feedback}} </span></em>
                        </div>
                    </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {required, numeric} from 'vuelidate/lib/validators'
    export default {
        props: {
            id: "",
            attendable_id: {
                type: [Number, String],
            },
            attendable_type: {
                type: String,
            },
            source: {
                type: String,
                default: "MyRoboJackets"
            }
        },
        data() {
            return {
                attendance: {
                    gtid: "",
                    gtid_field: "",
                    attendable_id: this.attendable_id,
                    attendable_type: this.attendable_type,
                    source: this.source,
                    includeName: "true"
                },
                feedback: "",
                baseUrl: "/api/v1/attendance",
                hasError: false,
            }
        },
        mounted() {
            //Listen for keystrokes from card swipe (or keyboard)
            let buffer = "";
            window.addEventListener("keypress", function(e) {
                if (this.attendance.attendable_id == "" && e.key == "Enter") {
                    buffer = "";
                    sweetAlert("Whoops!", "Please select a team before swiping your BuzzCard", "warning");
                } else if (e.key != "Enter") {
                    buffer += e.key;
                } else {
                    console.log(buffer);
                    buffer = "";
                }
            }.bind(this));
        },
        methods: {
            submit() {
                if (this.$v.$invalid) {
                    this.$v.$touch();
                    return;
                }

                axios.post(this.baseUrl, this.attendance)
                    .then(response => {
                        this.hasError = false;
                        this.feedback = "Saved! (" + response.data.attendance.name + ")";
                        console.log("success");
                        this.attendance.gtid = "";
                        this.$refs.input.focus();
                    })
                    .catch(error => {
                        this.hasError = true;
                        this.feedback = "";
                        if (error.response.status == 403) {
                            swal({
                                title: "Whoops!",
                                text: "You don't have permission to perform that action.",
                                type: "error"
                            });
                        } else {
                            sweetAlert("Error", "Unable to process data. Check your internet connection or try refreshing the page.", "error");
                        }
                    })
            },
        },
        validations: {
            attendance: {
                gtid: {required, numeric},
                attendable_type: {required},
                attendable_id: {required, numeric}
            }
        },
    }
</script>
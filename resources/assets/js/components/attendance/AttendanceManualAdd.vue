<template>
    <div>
        <div class="row form-row">
            <label for="date" class="col-sm-2 col-form-label">Date<span style="color:red">*</span></label>
            <div class="col-sm-10 col-lg-4">
                <flat-pickr
                        id="date"
                        v-model="attendance.created_at"
                        placeholder="Select date"
                        :required="true"
                        :config="dateTimeConfig"
                        input-class="form-control">
                </flat-pickr>
                <em v-if="$v.attendance.created_at.$error" style="color:red">You must select a date.</em>
            </div>
        </div>
        <div class="row form-row">
            <label for="teams-buttons" class="col-sm-2 col-form-label">Team<span style="color:red">*</span></label>
            <div class="col-sm-10 col-lg-4">
                <custom-radio-buttons
                        v-model="attendance.attendable_id"
                        :options="teams"
                        id="teams-buttons"
                        :is-error="$v.attendance.attendable_id.$error"
                        @input="$v.attendance.attendable_id.$touch()">
                </custom-radio-buttons>
            </div>
        </div>
        <div class="row form-row">
            <label for="gtid" class="col-sm-2 col-form-label">GTID<span style="color:red">*</span></label>
            <div class="col-sm-12 col-lg-4">
                <input
                        type="number"
                        id="gtid"
                        class="form-control"
                        :class="{ 'is-invalid': $v.attendance.gtid.$error }"
                        ref="input"
                        v-model="attendance.gtid"
                        @keyup.enter="submit"
                        @input="$v.attendance.gtid.$touch()"
                        @blur="$v.attendance.gtid.$touch()">
            </div>
        </div>
        <div class="row form-row">
            <div class="col-12">
                <em><span v-bind:class="{ 'text-danger': hasError}"> {{feedback}} </span></em>
            </div>
        </div>
        <div class="row form-row">
            <button type="button" class="btn btn-primary" @click="submit">Submit</button>
        </div>
    </div>
</template>
<script>
    import { required, numeric, between, minLength, maxLength } from 'vuelidate/lib/validators'
    export default {
        name: "attendance-manual-add",
        data() {
            return {
                teams: [],
                feedback: "",
                hasError: false,
                attendance: {
                    created_at: "",
                    gtid: "",
                    attendable_type: "App\\Team",
                    attendable_id: "",
                    source: "manual",
                    includeName: true
                },
                dateTimeConfig: {
                    dateFormat: "Y-m-d",
                    enableTime:false,
                    altInput: true,
                    maxDate: "today",
                },
                attendanceBaseUrl: "/api/v1/attendance",
                teamsBaseUrl: "/api/v1/teams",
            }
        },
        methods: {
            loadTeams() {
                // Fetch teams from the API to populate buttons
                let self = this;
                axios.get(this.teamsBaseUrl)
                    .then(response => {
                        let rawTeams = response.data.teams;
                        if (rawTeams.length < 1) {
                            swal("Bueller...Bueller...", "No teams found.", "warning");
                        } else {
                            let alphaTeams = response.data.teams.sort(function(a,b) {return (a.name > b.name) ? 1 : ((b.name > a.name) ? -1 : 0);} );
                            alphaTeams.forEach( function(team) {
                                self.teams.push({"value": team.id, "text": team.name});
                            });
                        }
                    })
                    .catch(error => {
                        if (error.response.status === 403) {
                            swal({
                                title: "Whoops!",
                                text: "You don't have permission to perform that action.",
                                type: "error"
                            });
                        } else {
                            swal("Error", "Unable to process data. Check your internet connection or try refreshing the page.", "error");
                        }
                    });
            },
            submit() {
                // Submit attendance data

                if (this.$v.$invalid) {
                    this.$v.$touch();
                    return;
                }

                axios.post(this.attendanceBaseUrl, this.attendance)
                    .then(response => {
                        this.hasError = false;
                        this.feedback = "Saved! (" + response.data.attendance.name + ")";
                        console.log("success");
                        this.attendance.gtid = "";
                        this.$refs.input.focus();
                    })
                    .catch(error => {
                        console.log(error);
                        this.hasError = true;
                        this.feedback = "";
                        if (error.response.status == 403) {
                            swal({
                                title: "Whoops!",
                                text: "You don't have permission to perform that action.",
                                type: "error"
                            });
                        } else {
                            swal("Error", "Unable to process data. Check your internet connection or try refreshing the page.", "error");
                        }
                    });
            },
        },
        mounted() {
            this.loadTeams();
        },
        validations: {
            attendance: {
                attendable_id: {
                    required
                },
                created_at: {
                    required
                },
                gtid: {
                    required,
                    numeric,
                    minLength: minLength(9),
                    maxLength: maxLength(9),
                    between: between(900000000, 909999999)
                }
            }
        },
    }
</script>
<style scoped>
    .form-row {
        padding-bottom: 10px;
    }
</style>
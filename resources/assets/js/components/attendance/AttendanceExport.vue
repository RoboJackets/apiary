<template>
    <div>
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
            <label for="start-date" class="col-sm-2 col-form-label">Start Date<span style="color:red">*</span></label>
            <div class="col-sm-10 col-lg-4">
                <flat-pickr
                        id="start-date"
                        v-model="attendance.start_date"
                        placeholder="Select start date"
                        :required="true"
                        :config="dateTimeConfig"
                        input-class="form-control">
                </flat-pickr>
            </div>
        </div>
        <div class="row form-row">
            <label for="end-date" class="col-sm-2 col-form-label">End Date</label>
            <div class="col-sm-10 col-lg-4">
                <flat-pickr
                        id="end-date"
                        v-model="attendance.end_date"
                        placeholder="Defaults to current date"
                        :required="true"
                        :config="dateTimeConfig"
                        input-class="form-control">
                </flat-pickr>
            </div>
        </div>
    </div>
</template>

<script>
    import { required, numeric } from 'vuelidate/lib/validators'
    export default {
        name: "attendance-export",
        data() {
            return {
                teams: [],
                attendance: {
                    attendable_type: "App\\Team",
                    attendable_id: "",
                    start_date: "",
                    end_date: ""
                },
                attendanceBaseUrl: "/api/v1/attendance",
                teamsBaseUrl: "/api/v1/teams",
                dateTimeConfig: {
                    dateFormat: "Y-m-d",
                    enableTime:false,
                    altInput: true
                },
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
        },
        mounted() {
            this.loadTeams();
        },
        validations: {
            attendance: {
                attendable_id: {
                    required
                },
                start_date: {
                    required
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
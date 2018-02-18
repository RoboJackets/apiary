<template>
    <div class="row">
        <template v-for="team in teams">
        <div class="col-sm-12 col-md-4" style="padding-top:50px">
            <button
                    class="btn btn-kiosk btn-secondary"
                    type="button"
                    :id="team.id"
                    v-on:click="clicked"
                    data-toggle="modal" data-target="#attendanceModal">
                {{ team.name }}
            </button>
        </div>
        </template>
        <attendance-modal-single
                id="attendanceModal"
                v-bind="attendance">
        </attendance-modal-single>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                attendance: {
                    attendable_id: '',
                    attendable_type: "App\\Team",
                    source: "kiosk"
                },
                attendanceBaseUrl: "/api/v1/attendance",
                teamsBaseUrl: "/api/v1/teams",
                teams: []
            }
        },
        mounted() {
            //Fetch teams from the API to populate buttons
            axios.get(this.teamsBaseUrl)
                .then(response => {
                    this.teams = response.data.teams.sort(function(a,b) {return (a.name > b.name) ? 1 : ((b.name > a.name) ? -1 : 0);} );
                })
                .catch(response => {
                    console.log(response);
                    sweetAlert("Connection Error", "Unable to load data. Check your internet connection or try refreshing the page.", "error");
                });

            // Listen for bootstrap modal close to clear fields
            // TODO: Find a better way in Vue to do this w/o jQuery
            $("#attendanceModal").on("hidden.bs.modal", this.clearFields);
        },
        methods: {
            clicked: function(event) {
                this.attendance.attendable_id = event.target.id;
            },
            clearFields() {
                this.attendance.attendable_id = "";

            }
        }
    }
</script>

<style scoped>
    /* Combination of btn-lg and btn-block with  some customizations */
    .btn-kiosk {
        min-height: 175px;
        font-weight: bolder;
        font-size: 2rem;
        display: block;
        width: 100%;
        padding: 0.5rem 1rem;
        line-height: 1.5;
        border-radius: 0;
    }
</style>
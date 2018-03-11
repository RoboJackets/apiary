<template>
    <div>
        <div class="row">
            <div class="col-sm-4 col-md-3 col-lg-3">
                <button
                        type="button" class="btn btn-secondary"
                        v-if="memberOfTeam" v-on:click="changeMembership('leave')">
                    Leave {{ team.name }}
                </button>
                <button
                        type="button" class="btn btn-primary"
                        v-else v-on:click="changeMembership('join')">
                    Join {{ team.name }}!
                </button>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12 col-md-4 col-lg-4">
                <b>Since:</b> {{ team.founding_year }}
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12 col-md-6 col-lg-6">
                <b>About:</b> {{ team.long_description }}
            </div>
        </div>
    </div>
</template>
<script>
    export default {
        props: {
            team: {},
            user: {}
        },
        data() {
            return {
                baseUrl: "/api/v1/teams/",
                user_teams: this.user.teams
            }
        },
        computed: {
            memberOfTeam: function() {
                let self = this;
                return this.user_teams.some(function(val) {return val.id === self.team.id});
            }
        },
        methods: {
            changeMembership: function(action) {
                let data = {
                    user_id: this.user.id,
                    team_id: this.team.id,
                    action: action
                };
                axios.post(this.baseUrl + this.team.id + "/members", data)
                    .then(response => {
                        if (response.status === 201 && data.action === "join") {
                            swal({
                                type: 'success',
                                title: 'Sweet!',
                                text: 'You joined ' + this.team.name + '!',
                                timer: 1500
                            });
                            this.user_teams.push(response.data.team);
                        } else if (response.status === 201 && data.action === "leave") {
                            swal({
                                type: 'success',
                                title: 'See you soon!',
                                text: 'You left ' + this.team.name + '.',
                                timer: 1500
                            });
                            let index = this.user_teams.findIndex(t => t.id === this.team.id);
                            if (index > -1) {
                             this.user_teams.splice(index, 1);
                            }
                        } else {
                            console.log(response);
                            swal('Whoops!', 'Something went wrong. Please try again later.', 'error');
                        }
                    })
                    .catch(response => {
                        console.log(response);
                        swal("Connection Error", "Unable to modify team membership. Check your internet connection or try refreshing the page.", "error");
                    })
            },
        }
    }
</script>
<style scoped>
    .row {
        padding-bottom: 10px;
    }
</style>
<template>
    <div class="modal fade" :id="id" tabindex="-1" role="dialog" :aria-labelledby="id" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" :id="id">Invite Member</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="inviteMemberForm" v-on:submit.prevent="submit">
                        <div class="row">
                            <div class="col-12">
                                <em>Enter a first name to search...</em>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 col-lg-9">
                                <user-lookup v-model="member.user"></user-lookup>
                            </div>
                            <div class="col-sm-3">
                                <button type="submit" class="btn btn-primary" @click="submit">Submit</button>
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
            teamId: {
                type: [Number, String],
            },
        },
        data() {
            return {
                member: {
                    user: "",
                    user_id: "",
                    action: "join"
                },
                feedback: "",
                baseUrl: "/api/v1/teams/",
                hasError: false,
            }
        },
        methods: {
            submit() {
                if (this.$v.$invalid) {
                    this.$v.$touch();
                    return;
                }

                let membersUrl = this.baseUrl + this.teamId + "/members";
                let updatedMember = this.member;
                updatedMember.user_id = this.member.user.id;
                delete updatedMember.user;

                axios.post(membersUrl, updatedMember)
                    .then(response => {
                        this.hasError = false;
                        this.feedback = "Invited " + response.data.member.name + "!";
                        console.log("success");
                        this.member.user_id = "";
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
                            swal("Error", "Unable to process data. Check your internet connection or try refreshing the page.", "error");
                        }
                    })
            },
        },
        validations: {
            member: {
                user: {required},
                action: {required}
            }
        },
    }
</script>
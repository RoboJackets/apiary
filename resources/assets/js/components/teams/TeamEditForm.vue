<template>
    <div class="row">
        <div class="col-12">
            <form id="teamEditForm" v-on:submit.prevent="submit">

                <h3>Team Details</h3>

                <div class="form-group row">
                    <label for="name" class="col-sm-2 col-form-label">Name<span style="color:red">*</span></label>
                    <div class="col-sm-10 col-lg-4">
                        <input v-model="team.name" type="text" class="form-control"
                               :class="{ 'is-invalid': $v.team.name.$error }" id="name" @blur="$v.team.name.$touch()"
                               placeholder="None on record">
                    </div>

                    <label for="founding-year" class="col-sm-2 col-form-label">Founding Year<span
                            style="color:red">*</span></label>
                    <div class="col-sm-10 col-lg-4">
                        <input v-model="team.founding_year" type="number" class="form-control"
                               :class="{ 'is-invalid': $v.team.founding_year.$error }" id="founding-year"
                               @blur="$v.team.founding_year.$touch()" placeholder="None on record">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="description" class="col-sm-2 col-form-label">Description<span style="color:red">*</span></label>
                    <div class="col-sm-12 col-lg-6">
                        <textarea v-model="team.description" rows="5" class="form-control" id="description"></textarea>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="team-hidden-buttons" class="col-sm-2 col-form-label">Hidden<span
                            style="color:red">*</span></label>
                    <div class="col-sm-10 col-lg-4">
                        <custom-radio-buttons
                                v-model="team.hidden"
                                :options="yesNoOptions"
                                id="team-hidden-buttons"
                                :is-error="$v.team.hidden.$error"
                                @input="$v.team.hidden.$touch()">
                        </custom-radio-buttons>
                    </div>

                    <label for="team-attendable-buttons" class="col-sm-2 col-form-label">Attendable<span
                            style="color:red">*</span></label>
                    <div class="col-sm-10 col-lg-4">
                        <custom-radio-buttons
                                v-model="team.attendable"
                                :options="yesNoOptions"
                                id="team-attendable-buttons"
                                :is-error="$v.team.attendable.$error"
                                @input="$v.team.attendable.$touch()">
                        </custom-radio-buttons>
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <a class="btn btn-secondary" href="/admin/teams">Cancel</a>
                    <button type="button" class="btn btn-danger" @click="deletePrompt">Delete</button>
                    <em><span v-bind:class="{ 'text-danger': hasError}"> {{feedback}} </span></em>
                </div>

            </form>

            <ul class="nav nav-tabs">
                <li class="nav-item">
                    <a class="nav-link active" id="rsvp-tab" data-toggle="tab" href="#rsvps">Members</a>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane show active" id="members">
                    <h3>Members</h3>
                    <team-invite-modal
                            id="teamInviteModal"
                            :teamId="this.teamId">
                    </team-invite-modal>
                    <button type="button" class="btn btn-secondary btn-above-table" data-toggle="modal" data-target="#teamInviteModal">Invite</button>
                    <datatable id="team-members-table"
                               :data-object="team.members"
                               :columns="memberTableConfig">
                    </datatable>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {required, numeric} from 'vuelidate/lib/validators'

    export default {
        name: "teamEditForm",
        props: ['teamId'],
        mounted() {
            this.dataUrl = this.baseUrl + this.teamId;
            this.loadMembers();
            $("#teamInviteModal").on("hidden.bs.modal", this.loadMembers);
        },
        data() {
            return {
                team: {},
                feedback: '',
                hasError: false,
                baseUrl: "/api/v1/teams/",
                dataURL: "",
                dateTimeConfig: {
                    dateFormat: "Y-m-d H:i:S",
                    enableTime: true,
                    altInput: true
                },
                yesNoOptions: [
                    {value: "0", text: "No"},
                    {value: "1", text: "Yes"},
                ],
                memberTableConfig: [
                    {'title': 'Name', 'data': 'name'},
                    {'title': 'GTID', 'data': 'gtid'},
                    {'title': 'Action', 'data': ''}
                ],
            }
        },
        validations: {
            team: {
                name: {required},
                founding_year: {numeric},
                description: {required},
                hidden: {required},
                attendable: {required},
            }
        },
        methods: {
            submit() {
                if (this.$v.$invalid) {
                    this.$v.$touch();
                    return;
                }

                axios.put(this.dataUrl, this.team)
                    .then(response => {
                        this.hasError = false;
                        this.feedback = "Saved!";
                        console.log("success");
                        window.location.href = "/teams/" + response.data.team.slug;
                    })
                    .catch(response => {
                        this.hasError = true;
                        this.feedback = "";
                        console.log(response);
                        swal("Error", "Unable to save data. Check your internet connection or try refreshing the page.", "error");
                    })
            },
            loadMembers() {
                axios.get(this.dataUrl, {
                    params: {
                        include: 'members'
                    }
                }).then(response => {
                        this.team = response.data.team;
                }).catch(response => {
                    console.log(response);
                    swal("Connection Error", "Unable to load data. Check your internet connection or try refreshing the page.", "error");
                });
            },
            deletePrompt() {
                let self = this;
                swal({
                    title: "Are you sure?",
                    text: "Once deleted, you will not be able to recover this team!",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Yes, delete it!",
                    focusCancel: true,
                    confirmButtonColor: "#dc3545"
                }).then((result) => {
                    if (result.value) {
                        self.deleteTeam();
                    }
                });
            },
            deleteTeam() {
                axios.delete(this.dataUrl)
                    .then(response => {
                        this.hasError = false;
                        swal({
                            title: "Deleted!",
                            text: "The team has been deleted.",
                            type: "success",
                            timer: 3000
                        }).then((result) => {
                            if (result.value) {
                                window.location.href = "/admin/teams";
                            }
                        })
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
                    });
            }
        }
    }
</script>

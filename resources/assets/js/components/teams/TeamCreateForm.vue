<template>
    <div class="row">
        <div class="col-12">
            <form id="teamCreateForm" v-on:submit.prevent="submit">

                <h3>Team Details</h3>

                <div class="form-group row">
                    <label for="name" class="col-sm-2 col-form-label">Name<span style="color:red">*</span></label>
                    <div class="col-sm-10 col-lg-4">
                        <input v-model="team.name" type="text" class="form-control" :class="{ 'is-invalid': $v.team.name.$error }" id="name" @blur="$v.team.name.$touch()">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="description" class="col-sm-2 col-form-label">Description<span style="color:red">*</span></label>
                    <div class="col-sm-12 col-lg-6">
                        <textarea v-model="team.description" rows="5" class="form-control" id="description"></textarea>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="mailing_list_name" class="col-sm-2 col-form-label">Mailing List Name</label>
                    <div class="input-group col-sm-10 col-lg-4">
                        <input v-model="team.mailing_list_name" type="text" class="form-control"
                               :class="{ 'is-invalid': $v.team.mailing_list_name.$error }" id="mailing_list_name"
                               @blur="$v.team.mailing_list_name.$touch()">
                        <div class="input-group-append">
                            <span class="input-group-text">@lists.gatech.edu</span>
                        </div>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="slack_channel_id" class="col-sm-2 col-form-label">
                        <abbr title="Internal Slack Identifier">Slack Channel ID</abbr></label>
                    <div class="input-group col-sm-10 col-lg-4">
                        <input v-model="team.slack_channel_id" type="text" class="form-control"
                               :class="{ 'is-invalid': $v.team.slack_channel_id.$error }" id="slack_channel_id"
                               @blur="$v.team.slack_channel_id.$touch()">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="slack_channel_name" class="col-sm-2 col-form-label">
                        <abbr title="Public-Facing Name">Slack Channel Name</abbr></label>
                    <div class="input-group col-sm-10 col-lg-4">
                        <div class="input-group-prepend">
                            <span class="input-group-text">#</span>
                        </div>
                        <input v-model="team.slack_channel_name" type="text" class="form-control"
                               :class="{ 'is-invalid': $v.team.slack_channel_name.$error }" id="slack_channel_name"
                               @blur="$v.team.slack_channel_name.$touch()">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="team-visible-buttons" class="col-sm-2 col-form-label">
                        <abbr title="Displayed to users">Visible</abbr><span style="color:red">*</span>
                    </label>
                    <div class="col-sm-10 col-lg-2">
                        <custom-radio-buttons
                                v-model="team.visible"
                                :options="yesNoOptions"
                                id="team-visible-buttons"
                                :is-error="$v.team.visible.$error"
                                @input="$v.team.visible.$touch()">
                        </custom-radio-buttons>
                    </div>

                    <label for="team-attendable-buttons" class="col-sm-2 col-form-label">
                        <abbr title="Used for attendance tracking">Attendable</abbr><span style="color:red">*</span>
                    </label>
                    <div class="col-sm-10 col-lg-2">
                        <custom-radio-buttons
                                v-model="team.attendable"
                                :options="yesNoOptions"
                                id="team-attendable-buttons"
                                :is-error="$v.team.attendable.$error"
                                @input="$v.team.attendable.$touch()">
                        </custom-radio-buttons>
                    </div>

                    <label for="team-self-serviceable-buttons" class="col-sm-2 col-form-label">
                        <abbr title="Users can join/leave via self-service">Self-Serviceable</abbr><span style="color:red">*</span>
                    </label>
                    <div class="col-sm-10 col-lg-2">
                        <custom-radio-buttons
                                v-model="team.self_serviceable"
                                :options="yesNoOptions"
                                id="team-self-serviceable-buttons"
                                :is-error="$v.team.self_serviceable.$error"
                                @input="$v.team.self_serviceable.$touch()">
                        </custom-radio-buttons>
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Create</button>
                    <a class="btn btn-secondary" href="/admin/teams">Cancel</a>
                    <em><span v-bind:class="{ 'text-danger': hasError}"> {{feedback}} </span></em>
                </div>

            </form>
        </div>
    </div>
</template>

<script>
import { required, numeric, alphaNum } from 'vuelidate/lib/validators';
export default {
  name: 'createTeamForm',
  data() {
    return {
      team: {},
      feedback: '',
      hasError: false,
      baseUrl: '/api/v1/teams',
      dateTimeConfig: {
        dateFormat: 'Y-m-d H:i:S',
        enableTime: true,
        altInput: true,
      },
      yesNoOptions: [{ value: '0', text: 'No' }, { value: '1', text: 'Yes' }],
    };
  },
  validations: {
    team: {
      name: { required },
      description: { required },
      visible: { required },
      attendable: { required },
      self_serviceable: { required },
      mailing_list_name: {},
      slack_channel_id: { alphaNum },
      slack_channel_name: { alphaNum },
    },
  },
  methods: {
    submit() {
      if (this.$v.$invalid) {
        this.$v.$touch();
        return;
      }

      axios
        .post(this.baseUrl, this.team)
        .then(response => {
          this.hasError = false;
          this.feedback = 'Saved!';
          console.log('success');
          window.location.href = '/admin/teams/' + response.data.team.slug;
        })
        .catch(response => {
          this.hasError = true;
          this.feedback = '';
          console.log(response);
          swal('Error', 'Unable to save data. Check your internet connection or try refreshing the page.', 'error');
        });
    },
  },
};
</script>

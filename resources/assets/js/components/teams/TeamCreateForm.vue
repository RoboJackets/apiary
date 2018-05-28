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

                    <label for="founding-year" class="col-sm-2 col-form-label">Founding Year<span style="color:red">*</span></label>
                    <div class="col-sm-10 col-lg-4">
                        <input v-model="team.founding_year" type="number" class="form-control" :class="{ 'is-invalid': $v.team.founding_year.$error }" id="founding-year" @blur="$v.team.founding_year.$touch()">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="description" class="col-sm-2 col-form-label">Description<span style="color:red">*</span></label>
                    <div class="col-sm-12 col-lg-6">
                        <textarea v-model="team.description" rows="5" class="form-control" id="description"></textarea>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="team-hidden-buttons" class="col-sm-2 col-form-label">Hidden<span style="color:red">*</span></label>
                    <div class="col-sm-10 col-lg-4">
                        <custom-radio-buttons
                                v-model="team.hidden"
                                :options="yesNoOptions"
                                id="team-hidden-buttons"
                                :is-error="$v.team.hidden.$error"
                                @input="$v.team.hidden.$touch()">
                        </custom-radio-buttons>
                    </div>

                    <label for="team-attendable-buttons" class="col-sm-2 col-form-label">Attendable<span style="color:red">*</span></label>
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
                    <button type="submit" class="btn btn-primary">Create</button>
                    <a class="btn btn-secondary" href="/admin/teams">Cancel</a>
                    <em><span v-bind:class="{ 'text-danger': hasError}"> {{feedback}} </span></em>
                </div>

            </form>
        </div>
    </div>
</template>

<script>
import { required, numeric } from 'vuelidate/lib/validators';
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
      founding_year: { numeric },
      description: { required },
      hidden: { required },
      attendable: { required },
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
          window.location.href = '/teams/' + response.data.team.slug;
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

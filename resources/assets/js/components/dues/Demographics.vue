<template>
  <div class="row">
    <div class="col-12">
      <form id="DuesRequiredInfoForm" v-on:submit.prevent="submit">
        <h3>Demographics</h3>
        <p>RoboJackets collects demographics data from members in order to supply aggregate statistics to Georgia Tech and corporate sponsors. Identifiable data is never provided.</p>

        <fieldset class="form-group">
          <label class ="lead" for="gender">What is your gender?</label>
          <div v-for="option in genderOptions" class="custom-control custom-radio">
            <input v-model="localUser.gender" type="radio" class="custom-control-input" :value="option.value" name="gender" :id="'gender-'+option.value">
            <label class="custom-control-label" :for="'gender-'+option.value">{{option.text}}</label>
          </div>
        </fieldset>

        <fieldset class="form-group">
          <label class="lead" for="ethnicity">What is your ethnicity/race? (Check all that Apply)</label>
          <div v-for="option in ethnicityOptions" class="custom-control custom-checkbox">
            <input v-model="localUser.ethnicity" type="checkbox" class="custom-control-input" :value="option.value" :id="'ethnicity-'+option.value" name="ethnicity">
            <label class="custom-control-label" :for="'ethnicity-'+option.value">{{option.text}}</label>
          </div>
        </fieldset>

        <div class="row">
          <div class="col-lg-6 col-12">
            <button type="submit" class="btn btn-primary float-right">Continue</button>
            <button type="submit" class="btn btn-secondary float-right mx-2">Skip</button>
          </div>
        </div>

      </form>
    </div>
  </div>
</template>

<script>
export default {
  props: ['user'],
  data() {
    return {
      ethnicityOptions: [
        { value: 'white', text: 'White/Caucasian' },
        { value: 'asian', text: 'Asian' },
        { value: 'hispanic', text: 'Hispanic' },
        { value: 'black', text: 'Black or African American' },
        { value: 'native', text: 'Native American' },
        { value: 'islander', text: 'Native Hawaiian and Other Pacific Islander' },
        { value: 'none', text: 'Prefer not to respond' },
      ],
      genderOptions: [
        { value: 'male', text: 'Male' },
        { value: 'female', text: 'Female' },
        { value: 'nonbinary', text: 'Non-binary or gender-queer' },
        { value: 'none', text: 'Prefer not to respond' },
      ],
    };
  },
  mounted() {
    if (!this.localUser.ethnicity) {
      this.localUser.ethnicity = [];
    } else {
      this.localUser.ethnicity = this.localUser.ethnicity.split(',');
    }
  },
  methods: {
    submit() {
      var baseUrl = '/api/v1/users/';
      var dataUrl = baseUrl + this.localUser.uid;

      delete this.localUser.dues;

      this.localUser.ethnicity = this.localUser.ethnicity.toString();

      axios
        .put(dataUrl, this.localUser)
        .then(response => {
          this.$emit('next');
        })
        .catch(response => {
          console.log(response);
          swal(
            'Connection Error',
            'Unable to save data. Check your internet connection or try refreshing the page.',
            'error'
          );
        });
    },
  },
  computed: {
    localUser: function() {
      return this.user;
    },
  },
};
</script>

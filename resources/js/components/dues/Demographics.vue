<template>
  <div class="row">
    <div class="col-12">
      <form id="DuesRequiredInfoForm" v-on:submit.prevent="submit">
        <h3>Demographics</h3>
        <p>RoboJackets collects demographics data from members in order to supply aggregate statistics to Georgia Tech and corporate sponsors. Identifiable data is never provided.</p>

        <fieldset class="mb-3">
          <label class ="lead" for="gender">What is your gender?</label>
          <div v-for="option in genderOptions" class="form-check custom-radio">
            <input v-model="gender" type="radio" class="form-check-input" :value="option.value" name="gender" :id="'gender-'+option.value">
            <label class="form-check-label" :for="'gender-'+option.value">{{option.text}}</label>
          </div>
        </fieldset>

        <fieldset class="mb-3">
          <label class="lead" for="ethnicity">What is your ethnicity/race? (Check all that Apply)</label>
          <div v-for="option in ethnicityOptions" class="form-check custom-checkbox">
            <input v-model="ethnicity" type="checkbox" class="form-check-input" :value="option.value" :id="'ethnicity-'+option.value" name="ethnicity">
            <label class="form-check-label" :for="'ethnicity-'+option.value">{{option.text}}</label>
          </div>
        </fieldset>

        <div class="row">
          <div class="col-lg-3 col-6">
            <button @click.prevent="$emit('back')" class="btn btn-secondary float-start">Back</button>
          </div>
          <div class="col-lg-3 col-6">
            <button type="submit" class="btn btn-primary float-end">Continue</button>
            <button @click.prevent="$emit('next')" class="btn btn-secondary float-end mx-2">Skip</button>
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
      ethnicity: [],
      gender: '',
    };
  },
  methods: {
    submit() {
      const baseUrl = '/api/v1/users/';
      const dataUrl = baseUrl + this.user.uid;

      const userRequest = {
        ethnicity: this.ethnicity.toString(),
        gender: this.gender
      }
      axios
        .put(dataUrl, userRequest)
        .then(response => {
          this.$emit('next');
        })
        .catch(response => {
          console.log(response);
          Swal.fire(
            'Connection Error',
            'Unable to save data. Check your internet connection or try refreshing the page.',
            'error'
          );
        });
    },
  },
  mounted() {
    if (this.user.ethnicity) {
      this.ethnicity = this.user.ethnicity.split(',');
    } else {
      this.ethnicity = [];
    }
  },
};
</script>

<template>
  <div class="row">
    <div class="col-12">
      <form id="DuesRequiredInfoForm" v-on:submit.prevent="submit">
        <h4>Additional Information</h4>
        <p>Providing RoboJackets with this optional information enables the RoboJackets leadership to better serve you.</p>

        <div class="form-group row">
          <label for="user-preferredname" class="col-sm-2 col-form-label">Preferred Name</label>
          <div class="col-sm-10 col-lg-4">
            <input
              v-model="localUser.preferred_first_name"
              type="text"
              class="form-control"
              id="user-preferredname"
              :class="{ 'is-invalid': $v.localUser.preferred_first_name.$error }"
              @input="$v.localUser.preferred_first_name.$touch()">
            <div class="invalid-feedback">
              Must be letters, with optional spaces between words only
            </div>
          </div>
        </div>

        <div class="form-group row">
          <label for="user-uid" class="col-sm-2 col-form-label">Phone Number</label>
          <div class="col-sm-10 col-lg-4">
            <input
              v-model="localUser.phone"
              type="tel"
              class="form-control"
              id="user-uid"
              maxlength="15"
              :class="{ 'is-invalid': $v.localUser.phone.$error }"
              @input="$v.localUser.phone.$touch()">
            <div class="invalid-feedback">
              Must be a valid phone number with no punctuation
            </div>
          </div>
        </div>

        <h4>Emergency Contact Information</h4>
        <p>You may optionally provide information on who to contact in the event of an emergency. This information is required should you go on any RoboJackets trips.</p>

        <div class="form-group row">
          <label for="user-emergencycontactname" class="col-sm-2 col-form-label">Contact Name</label>
          <div class="col-sm-10 col-lg-4">
            <input
              v-model="localUser.emergency_contact_name"
              type="text"
              class="form-control"
              id="user-emergencycontactname"
              :class="{ 'is-invalid': $v.localUser.emergency_contact_name.$error }"
              @input="$v.localUser.emergency_contact_name.$touch()"
              required>
          </div>
        </div>
        <div class="form-group row">
          <label for="user-emergencycontactphone" class="col-sm-2 col-form-label">Contact Phone Number</label>
          <div class="col-sm-10 col-lg-4">
            <input
              v-model="localUser.emergency_contact_phone"
              type="tel"
              class="form-control"
              id="user-emergencycontactphone"
              maxlength="15"
              :class="{ 'is-invalid': $v.localUser.emergency_contact_phone.$error }"
              @input="$v.localUser.emergency_contact_phone.$touch()"
              required>
              <div class="invalid-feedback">
                Must be a valid phone number with no punctuation, different from your phone number provided above
              </div>
          </div>
        </div>

        <div class="row">
          <div class="col-lg-3 col-6">
            <button @click.prevent="$emit('back')" class="btn btn-secondary float-left">Back</button>
          </div>
          <div class="col-lg-3 col-6">
            <button type="submit" class="btn btn-primary float-right">Continue</button>
            <button @click.prevent="$emit('next')" class="btn btn-secondary float-right mx-2">Skip</button>
          </div>
        </div>

      </form>
    </div>
  </div>
</template>

<script>
import { email, minLength, maxLength, required, helpers } from 'vuelidate/lib/validators';
import notGTEmail from '../../customValidators/notGTEmail';

const alphaSpace = helpers.regex('alphaSpace', /^([a-zA-Z]+\s)*[a-zA-Z]+$/);

export default {
  props: ['user'],
  methods: {
    submit() {
      if (this.$v.$invalid) {
        this.$v.$touch();
        return;
      }

      const baseUrl = '/api/v1/users/';
      const dataUrl = baseUrl + this.localUser.uid;

      const userRequest = {
        phone: this.localUser.phone,
        preferred_first_name: this.localUser.preferred_first_name,
        emergency_contact_name: this.localUser.emergency_contact_name,
        emergency_contact_phone: this.localUser.emergency_contact_phone,
      }
      axios
        .put(dataUrl, userRequest)
        .then(response => {
          this.$emit('next');
        })
        .catch(error => {
          if (
            error &&
            error.response &&
            error.response.status === 422 &&
            error.response.data &&
            error.response.data.errors &&
            typeof error.response.data.errors === 'object' && Object.keys(error.response.data.errors).length > 0 &&
            typeof error.response.data.errors[Object.keys(error.response.data.errors)[0]] === 'object' &&
            error.response.data.errors[Object.keys(error.response.data.errors)[0]].length > 0
          ) {
            const message = error.response.data.message;
            const errors = error.response.data.errors;
            const validation_messages = []
            Object.entries(errors).forEach(([prop, val]) => validation_messages.push(val));
            Swal.fire({
                title: 'Validation Error',
                html: `<b>${message}</b><br/>${validation_messages.join('<br/>')}`,
                icon: 'warning',
              }
            );
            return;
          }
          console.log(error);
          Swal.fire(
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
  validations: {
    localUser: {
      phone: { maxLength: maxLength(15) },
      preferred_first_name: { alphaSpace },
      emergency_contact_name: { required },
      emergency_contact_phone: { required, maxLength: maxLength(15) },
    },
  },
};
</script>

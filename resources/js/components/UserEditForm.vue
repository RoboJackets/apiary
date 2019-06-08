<template>
  <div class="row">
    <div class="col-12">
      <form id="userEditForm" v-on:submit.prevent="submit">
        <h3>GT Directory Info</h3>
        <p>Information obtained via GT Single Sign-On. Update at <a href="https://passport.gatech.edu">Passport</a>.</p>

        <div class="form-group row">
          <label for="user-name" class="col-sm-2 col-form-label">Name</label>
          <div class="col-sm-10 col-lg-4">
            <input v-model="user.full_name" type="text" readonly class="form-control" id="user-name">
          </div>

          <label for="user-uid" class="col-sm-2 col-form-label">GT Username</label>
          <div class="col-sm-10 col-lg-4">
            <input v-model="user.uid" type="text" readonly class="form-control" id="user-uid">
          </div>
        </div>

        <div class="form-group row">
          <label for="user-gtemail" class="col-sm-2 col-form-label">GT Email</label>
          <div class="col-sm-10 col-lg-4">
            <input v-model="user.gt_email" type="text" readonly class="form-control" id="user-gtemail">
          </div>
        </div>

        <h3>Additional Information</h3>

        <div class="form-group row">
          <label for="user-preferredname" class="col-sm-2 col-form-label">Preferred First Name</label>
          <div class="col-sm-10 col-lg-4">
            <input
                v-model="user.preferred_first_name"
                type="text"
                class="form-control"
                id="user-preferredname"
                :class="{ 'is-invalid': $v.user.preferred_first_name.$error }"
                @input="$v.user.preferred_first_name.$touch()">
          </div>
        </div>

        <div class="form-group row">
          <label for="user-personalemail" class="col-sm-2 col-form-label">Personal Email</label>
          <div class="col-sm-10 col-lg-4">
            <input
                v-model="user.personal_email"
                type="email"
                class="form-control"
                id="user-personalemail"
                placeholder="None on record"
                :class="{ 'is-invalid': $v.user.personal_email.$error }"
                @input="$v.user.personal_email.$touch()">
            <div class="invalid-feedback" v-if="!$v.user.personal_email.notGTEmail">
              Personal email cannot be a GT email address
            </div>
            <div class="invalid-feedback" v-if="!$v.user.personal_email.email">
              Must be a valid email address
            </div>
          </div>

        </div>

        <div class="form-group row">
          <label for="user-phone" class="col-sm-2 col-form-label">Phone Number</label>
          <div class="col-sm-10 col-lg-4">
            <input
                v-model="user.phone"
                type="tel"
                class="form-control"
                id="user-phone"
                placeholder="None on record"
                :class="{ 'is-invalid': $v.user.phone.$error }"
                @input="$v.user.phone.$touch()">
            <div class="invalid-feedback">
              Must be a valid phone number with no punctuation
            </div>
          </div>
        </div>

        <div class="form-group row">
          <label for="user-shirtsize" class="col-sm-2 col-form-label">Shirt Size</label>
          <div class="col-sm-10 col-lg-4">
            <custom-radio-buttons
                v-model="user.shirt_size"
                :options="shirtSizeOptions"
                id="user-shirtsize"
                @input="$v.user.shirt_size.$touch()">
            </custom-radio-buttons>
          </div>
        </div>

        <div class="form-group row">
          <label for="user-polosize" class="col-sm-2 col-form-label">Polo Size</label>
          <div class="col-sm-10 col-lg-4">
            <custom-radio-buttons
                v-model="user.polo_size"
                :options="shirtSizeOptions"
                id="user-polosize"
                @input="$v.user.polo_size.$touch()">
            </custom-radio-buttons>
          </div>
        </div>

        <h3>Emergency Contacts</h3>

        <div class="form-group row">
          <label for="user-emergencyname" class="col-sm-2 col-form-label">Contact Name</label>
          <div class="col-sm-10 col-lg-4">
            <input
                v-model="user.emergency_contact_name"
                type="text"
                class="form-control"
                id="user-emergencyname"
                placeholder="None on record"
                :class="{ 'is-invalid': $v.user.emergency_contact_name.$error }"
                @input="$v.user.emergency_contact_name.$touch()">
          </div>
        </div>

        <div class="form-group row">
          <label for="user-emergencyphone" class="col-sm-2 col-form-label">Contact Phone Number</label>
          <div class="col-sm-10 col-lg-4">
            <input
                v-model="user.emergency_contact_phone"
                type="tel"
                class="form-control"
                id="user-emergencyphone"
                placeholder="None on record"
                :class="{ 'is-invalid': $v.user.emergency_contact_phone.$error }"
                @input="$v.user.emergency_contact_phone.$touch()">
            <div class="invalid-feedback">
              Must be a valid phone number with no punctuation
            </div>
          </div>
        </div>

        <div class="form-group">
          <button type="submit" class="btn btn-primary">Save Changes</button>
          <em><span v-bind:class="{ 'text-danger': hasError}"> {{feedback}} </span></em>
        </div>

      </form>
    </div>
  </div>
</template>

<script>
import { alpha, email, maxLength, required } from 'vuelidate/lib/validators';
import notGTEmail from '../customValidators/notGTEmail';

export default {
  props: ['userUid'],
  data() {
    return {
      user: {},
      feedback: '',
      hasError: false,
      dataUrl: '',
      baseUrl: '/api/v1/users/',
      shirtSizeOptions: [
        { value: 's', text: 'S' },
        { value: 'm', text: 'M' },
        { value: 'l', text: 'L' },
        { value: 'xl', text: 'XL' },
        { value: 'xxl', text: 'XXL' },
        { value: 'xxxl', text: 'XXXL' },
      ],
    };
  },
  mounted() {
    this.dataUrl = this.baseUrl + this.userUid;
    axios
      .get(this.dataUrl)
      .then(response => {
        this.user = response.data.user;
      })
      .catch(response => {
        console.log(response);
        swal(
          'Connection Error',
          'Unable to load data. Check your internet connection or try refreshing the page.',
          'error'
        );
      });
  },
  methods: {
    submit() {
      if (this.$v.$invalid) {
        this.$v.$touch();
        return;
      }

      delete this.user.dues;

      axios
        .put(this.dataUrl, this.user)
        .then(response => {
          this.hasError = false;
          this.feedback = 'Saved!';
          console.log('success');
        })
        .catch(response => {
          this.hasError = true;
          this.feedback = '';
          console.log(response);
          swal(
            'Connection Error',
            'Unable to save data. Check your internet connection or try refreshing the page.',
            'error'
          );
        });
    },
  },
  validations: {
    user: {
      personal_email: { email, notGTEmail },
      phone: { maxLength: maxLength(15) },
      preferred_first_name: { alpha },
      shirt_size: {},
      polo_size: {},
      emergency_contact_name: {},
      emergency_contact_phone: { maxLength: maxLength(15) },
    },
  },
};
</script>

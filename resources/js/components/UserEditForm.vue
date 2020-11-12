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

        <h3>Linked Accounts</h3>

        <div class="form-group row">
          <label for="user-github" class="col-sm-2 col-form-label">GitHub</label>
          <div class="col-sm-10 col-lg-4">
            <div class="input-group">
              <input v-model="user.github_username" type="text" readonly class="form-control" id="user-github" placeholder="No account linked">
              <div class="input-group-append" v-if="!user.github_username">
                <a href="/github" class="btn btn-secondary">Link Account</a>
              </div>
            </div>
          </div>
        </div>

        <div class="form-group row">
          <label for="user-google" class="col-sm-2 col-form-label">Google</label>
          <div class="col-sm-10 col-lg-4">
            <div class="input-group">
              <input v-model="user.gmail_address" type="text" readonly class="form-control" id="user-google" placeholder="No account linked">
              <div class="input-group-append" v-if="!user.gmail_address">
                <a href="/google" class="btn btn-secondary">Link Account</a>
              </div>
            </div>
          </div>
        </div>

        <div class="form-group row">
          <label for="user-sums" class="col-sm-2 col-form-label">SUMS</label>
          <div class="col-sm-10 col-lg-4">
            <div class="input-group">
            <template v-if="user.exists_in_sums">
              <input v-model="user.uid" type="text" readonly class="form-control" id="user-sums">
            </template>
            <template v-else>
              <input type="text" readonly class="form-control" id="user-sums" placeholder="No account linked">
              <div class="input-group-append">
                <a href="/sums" class="btn btn-secondary">Link Account</a>
              </div>
            </template>
            </div>
          </div>
        </div>

        <div class="form-group row">
          <label for="user-clickup" class="col-sm-2 col-form-label">ClickUp</label>
          <div class="col-sm-10 col-lg-4">
            <div class="input-group">
            <template v-if="user.clickup_email && user.clickup_email === clickUpEmailInDatabase">
              <input v-model="user.clickup_email" type="text" readonly class="form-control" id="user-clickup">
              <div class="input-group-append" v-if="user.clickup_invite_pending === 1">
                <a href="/clickup" class="btn btn-secondary">Resend Invitation</a>
              </div>
            </template>
            <template v-if="!user.clickup_email || user.clickup_email !== clickUpEmailInDatabase">
              <select class="form-control" id="user-clickup" v-model="user.clickup_email">
                <option v-for="option in clickUpEmailOptions" :value="option">{{ option }}</option>
              </select>
              <div class="input-group-append">
                <button type="submit" class="btn btn-secondary">Send Invitation</button>
              </div>
            </template>
            </div>
          </div>
        </div>

        <div class="form-group row">
          <label for="user-autodesk" class="col-sm-2 col-form-label">Autodesk</label>
          <div class="col-sm-10 col-lg-4">
            <div class="input-group">
            <template v-if="user.autodesk_email && user.autodesk_email === autodeskEmailInDatabase">
              <input v-model="user.autodesk_email" type="text" readonly class="form-control" id="user-autodesk">
              <div class="input-group-append" v-if="user.autodesk_invite_pending === 1">
                <a href="/autodesk" class="btn btn-secondary">Resend Invitation</a>
              </div>
            </template>
            <template v-if="!user.autodesk_email || user.autodesk_email !== autodeskEmailInDatabase">
              <select class="form-control" id="user-autodesk" v-model="user.autodesk_email">
                <option v-for="option in autodeskEmailOptions" :value="option">{{ option }}</option>
              </select>
              <div class="input-group-append">
                <button type="submit" class="btn btn-secondary">Send Invitation</button>
              </div>
            </template>
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
      clickUpEmailOptions: [],
      clickUpEmailInDatabase: null,
      autodeskEmailOptions: [],
      autodeskEmailInDatabase: null,
    };
  },
  mounted() {
    this.dataUrl = this.baseUrl + this.userUid;
    axios
      .get(this.dataUrl)
      .then(response => {
        this.user = response.data.user;
        this.clickUpEmailOptions = [
          ...new Set([this.user.gt_email.toLowerCase(), this.user.uid.toLowerCase() + '@gatech.edu', this.user.gmail_address || this.user.gt_email.toLowerCase()])
        ];
        this.clickUpEmailInDatabase = this.user.clickup_email;

        this.autodeskEmailOptions = [
          ...new Set([this.user.gt_email.toLowerCase(), this.user.uid.toLowerCase() + '@gatech.edu', this.user.gmail_address || this.user.gt_email.toLowerCase()])
        ];
        this.autodeskEmailInDatabase = this.user.autodesk_email;

      })
      .catch(response => {
        console.log(response);
        Swal.fire(
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
          if ((this.user.clickup_email !== this.clickUpEmailInDatabase && this.user.clickup_email && this.user.clickup_email.length > 0) 
          && (this.user.autodesk_email !== this.autodeskEmailInDatabase && this.user.autodesk_email && this.user.autodesk_email.length > 0)) {
            this.clickUpEmailInDatabase = this.user.clickup_email;
            this.autodeskEmailInDatabase = this.user.autodesk_email;
            this.feedback = 'Saved! Look out for an email from ClickUp and Autodesk in the next few minutes.'
          } else if (this.user.clickup_email !== this.clickUpEmailInDatabase && this.user.clickup_email && this.user.clickup_email.length > 0) {
            this.clickUpEmailInDatabase = this.user.clickup_email;
            this.feedback = 'Saved! Look out for an email from ClickUp in the next few minutes.'
          } else if (this.user.autodesk_email !== this.autodeskEmailInDatabase && this.user.autodesk_email && this.user.autodesk_email.length > 0) {
            this.autodeskEmailInDatabase = this.user.autodesk_email;
            this.feedback = 'Saved! Look out for an email from Autodesk in the next few minutes.'
          } else {
            this.feedback = 'Saved!';
          }
          
          console.log('success');
        })
        .catch(response => {
          this.hasError = true;
          this.feedback = '';
          console.log(response);
          Swal.fire(
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

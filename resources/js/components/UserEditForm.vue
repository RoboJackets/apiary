<template>
    <div class="row">
      <div class="col-12">
        <form id="userEditForm" v-on:submit.prevent="submit">
          <h3>GT Directory Info</h3>
          <p>Information obtained via GT Single Sign-On. Update at <a href="https://passport.gatech.edu">Passport</a>.</p>

          <div class="mb-3 row">
            <label for="user-name" class="col-sm-2 col-form-label">Name</label>
            <div class="col-sm-10 col-lg-4">
              <input v-model="user.full_name" type="text" readonly class="form-control" id="user-name">
            </div>

            <label for="user-uid" class="col-sm-2 col-form-label">GT Username</label>
            <div class="col-sm-10 col-lg-4">
              <input v-model="user.uid" type="text" readonly class="form-control" id="user-uid">
            </div>
          </div>

          <div class="mb-3 row">
            <label for="user-gtemail" class="col-sm-2 col-form-label">GT Email</label>
            <div class="col-sm-10 col-lg-4">
              <input v-model="user.gt_email" type="text" readonly class="form-control" id="user-gtemail">
            </div>
          </div>

          <h3>Additional Information</h3>

          <div class="mb-3 row">
            <label for="user-preferredname" class="col-sm-2 col-form-label">Preferred First Name</label>
            <div class="col-sm-10 col-lg-4">
              <input
                  v-model="user.preferred_first_name"
                  type="text"
                  class="form-control"
                  id="user-preferredname"
                  :class="{ 'is-invalid': $v.user.preferred_first_name.$error }"
                  @input="$v.user.preferred_first_name.$touch(); onFormChange();">
            </div>
          </div>

          <div class="mb-3 row">
            <label for="user-phone" class="col-sm-2 col-form-label">Phone Number</label>
            <div class="col-sm-10 col-lg-4">
              <input
                  v-model="user.phone"
                  type="tel"
                  class="form-control"
                  id="user-phone"
                  placeholder="None on record"
                  :class="{ 'is-invalid': $v.user.phone.$error }"
                  @input="$v.user.phone.$touch(); onFormChange();">
              <div class="invalid-feedback">
                Must be a valid phone number with no punctuation
              </div>
            </div>
          </div>

          <div class="mb-3 row">
            <label for="user-shirtsize" class="col-sm-2 col-form-label">Shirt Size</label>
            <div class="col-sm-10 col-lg-4">
              <custom-radio-buttons
                  v-model="user.shirt_size"
                  :options="shirtSizeOptions"
                  id="user-shirtsize"
                  @input="$v.user.shirt_size.$touch(); onFormChange();">
              </custom-radio-buttons>
            </div>
          </div>

          <div class="mb-3 row">
            <label for="user-polosize" class="col-sm-2 col-form-label">Polo Size</label>
            <div class="col-sm-10 col-lg-4">
              <custom-radio-buttons
                  v-model="user.polo_size"
                  :options="shirtSizeOptions"
                  id="user-polosize"
                  @input="$v.user.polo_size.$touch(); onFormChange();">
              </custom-radio-buttons>
            </div>
          </div>

          <div v-if="graduationInfoRequired">
            <h3>Graduation Information</h3>

            <div class="mb-3 row">
              <label for="graduationInformation" class="col-sm-2 col-form-label">Graduation Date</label>
              <div class="col-sm-10 col-lg-4">
                <term-input
                  v-model="user.graduation_semester"
                  id="user-graduationsemester"
                  :is-error="$v.user.graduation_semester.$error"
                  @input="onFormChange();"
                  @touch="$v.user.graduation_semester.$touch()">
                </term-input>
                <div class="invalid-feedback">
                  Select a valid graduation date.
                </div>
              </div>
            </div>
          </div>

          <h3>Emergency Contact</h3>
          <p>Emergency contact information is required for all trips off campus.</p>

          <div class="mb-3 row">
            <label for="user-emergencyname" class="col-sm-2 col-form-label">Contact Name</label>
            <div class="col-sm-10 col-lg-4">
              <input
                  v-model="user.emergency_contact_name"
                  type="text"
                  class="form-control"
                  id="user-emergencyname"
                  placeholder="None on record"
                  :class="{ 'is-invalid': $v.user.emergency_contact_name.$error }"
                  @input="$v.user.emergency_contact_name.$touch(); onFormChange();"
                  required>
            </div>
          </div>

          <div class="mb-3 row">
            <label for="user-emergencyphone" class="col-sm-2 col-form-label">Contact Phone Number</label>
            <div class="col-sm-10 col-lg-4">
              <input
                  v-model="user.emergency_contact_phone"
                  type="tel"
                  class="form-control"
                  id="user-emergencyphone"
                  placeholder="None on record"
                  :class="{ 'is-invalid': $v.user.emergency_contact_phone.$error }"
                  @input="$v.user.emergency_contact_phone.$touch(); onFormChange();"
                  required>
              <div class="invalid-feedback">
                Must be a valid phone number with no punctuation
              </div>
            </div>
          </div>

          <h3>Air Travel Information</h3>
          <p>Legal name, legal gender, and date of birth are required for booking air travel for you to attend competitions and will not be used for any other purpose. This information must exactly match your government-issued identification to comply with TSA Secure Flight requirements. Please see <a href="https://pro.delta.com/content/agency/us/en/news/news-archive/2022/october-2022/non-binary-gender-identifiers-now-available.html">Delta Air Lines guidance on gender identifiers</a> if needed.</p>

          <div class="mb-3 row">
            <label for="legal-first-name" class="col-sm-2 col-form-label">Legal First Name</label>
            <div class="col-sm-10 col-lg-4">
              <input id="legal-first-name" type="text" v-model="user.first_name" class="form-control" readonly>
            </div>
          </div>

          <div class="mb-3 row">
            <label for="legal-middle-name" class="col-sm-2 col-form-label">Legal Middle Name</label>
            <div class="col-sm-10 col-lg-4">
              <input id="legal-middle-name" type="text" v-model="user.legal_middle_name" class="form-control" @input="onFormChange();">
            </div>
          </div>

          <div class="mb-3 row">
            <label for="legal-last-name" class="col-sm-2 col-form-label">Legal Last Name</label>
            <div class="col-sm-10 col-lg-4">
              <input id="legal-last-name" type="text" v-model="user.last_name" class="form-control" readonly>
            </div>
          </div>

          <div class="mb-3 row">
            <label for="legal-gender" class="col-sm-2 col-form-label">Legal Gender</label>
            <div class="col-sm-10 col-lg-4">
              <select id="legal-gender" v-model="user.legal_gender" class="form-select" @input="onFormChange();">
                <option value="M">Male (M)</option>
                <option value="F">Female (F)</option>
                <option value="X">Unspecified (X)</option>
                <option value="U">Undisclosed (U)</option>
              </select>
            </div>
          </div>

          <div class="mb-3 row">
            <label for="date-of-birth" class="col-sm-2 col-form-label">Date of Birth</label>
            <div id="date-of-birth" class="col-sm-10 col-lg-4">
              <input id="date-of-birth" type="date" v-model="user.date_of_birth" class="form-control" @input="onFormChange();">
            </div>
          </div>

          <div class="mb-3 row">
            <label for="delta-skymiles-number" class="col-sm-2 col-form-label">Delta SkyMiles Number</label>
            <div id="delta-skymiles-number" class="col-sm-10 col-lg-4">
              <input id="delta-skymiles-number" type="text" v-model="user.delta_skymiles_number" class="form-control" @input="onFormChange();">
            </div>
          </div>

          <h3>Linked Accounts</h3>

          <div class="mb-3 row">
            <label for="user-github" class="col-sm-2 col-form-label">GitHub</label>
            <div class="col-sm-10 col-lg-4">
              <div class="input-group">
                <input v-model="user.github_username" type="text" readonly class="form-control" id="user-github" placeholder="No account linked">
                <div  v-if="!user.github_username">
                  <a href="/github" class="btn btn-secondary">Link Account</a>
                </div>
              </div>
            </div>
          </div>

          <div class="mb-3 row">
            <label for="user-google" class="col-sm-2 col-form-label">Google</label>
            <div class="col-sm-10 col-lg-4">
              <div class="input-group">
                <input v-model="user.gmail_address" type="text" readonly class="form-control" id="user-google" placeholder="No account linked">
                <div  v-if="!user.gmail_address">
                  <a href="/google" class="btn btn-secondary">Link Account</a>
                </div>
              </div>
            </div>
          </div>

          <div class="mb-3 row">
            <label for="user-sums" class="col-sm-2 col-form-label">SUMS</label>
            <div class="col-sm-10 col-lg-4">
              <div class="input-group">
              <template v-if="user.exists_in_sums">
                <input v-model="user.uid" type="text" readonly class="form-control" id="user-sums">
              </template>
              <template v-else>
                <input type="text" readonly class="form-control" id="user-sums" placeholder="No account linked">
                <div>
                  <a href="/sums" class="btn btn-secondary">Link Account</a>
                </div>
              </template>
              </div>
            </div>
          </div>

          <div class="mb-3 row">
            <label for="user-clickup" class="col-sm-2 col-form-label">ClickUp</label>
            <div class="col-sm-10 col-lg-4">
              <div class="input-group">
              <template v-if="user.clickup_email && user.clickup_email === clickUpEmailInDatabase">
                <input v-model="user.clickup_email" type="text" readonly class="form-control" id="user-clickup">
                <div v-if="user.clickup_invite_pending === true">
                  <a href="/clickup" class="btn btn-secondary">Resend Invitation</a>
                </div>
              </template>
              <template v-if="!user.clickup_email || user.clickup_email !== clickUpEmailInDatabase">
                <select class="form-control" id="user-clickup" v-model="user.clickup_email" @input="onFormChange();">
                  <option v-for="option in clickUpEmailOptions" :value="option">{{ option }}</option>
                </select>
                <div>
                  <button type="submit" class="btn btn-secondary">Send Invitation</button>
                </div>
              </template>
              </div>
            </div>
          </div>

          <div class="mb-3">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <em><span v-bind:class="{ 'text-danger': hasError}"> {{feedback}} </span></em>
          </div>

          <h3>Payment History</h3>
          <div class="row">
            <div class="col-12">
              <payment-history :user-uid="userUid" />
            </div>
          </div>

          <h3>Authorized Applications</h3>
          <div class="row">
            <div class="col-12">
              <oauth2-authorizations />
            </div>
          </div>

          <h3>Personal Access Tokens</h3>
          <div class="row">
            <div class="col-12">
              <personal-access-tokens />
            </div>
          </div>
        </form>
      </div>
    </div>
</template>

<script>
import { maxLength, minLength, required, helpers } from 'vuelidate/lib/validators';

const alphaSpace = helpers.regex('alphaSpace', /^([a-zA-Z]+\s)*[a-zA-Z]+$/);

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
      formChanged: false,
    };
  },
  mounted() {
    console.log("in mounted");
    // This event listener is vanilla JS and is meant to activate if you leave the page
    // without saving your changes. If we update to Vue 3 and use Vue Router
    // (which I believe is the plan), we will need to update this code to use
    // navigation guards instead. (Navigation guards should improve the repeatability
    // of this code anyway.)
    window.addEventListener('beforeunload', (event) => {this.onLeaveAttempt(event)});
    this.dataUrl = this.baseUrl + this.userUid;
    axios
      .get(this.dataUrl)
      .then(response => {
        this.user = response.data.user;
        this.clickUpEmailOptions = [
          ...new Set([this.user.gt_email.toLowerCase(), this.user.uid.toLowerCase() + '@gatech.edu', this.user.gmail_address || this.user.gt_email.toLowerCase()])
        ];
        this.clickUpEmailInDatabase = this.user.clickup_email;
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
  beforeDestroy() {
    window.removeEventListener('beforeunload', this.onLeaveAttempt);
  },
  methods: {
    onFormChange() {
      this.formChanged = true;
    },
    onLeaveAttempt(event) {
      if (this.formChanged) {
        event.returnValue = "Exit page? Any unsaved changes will be lost."; // Message included for legacy support. Browsers usually do not use it.
      }
    },
    submit() {
      if (this.$v.$invalid) {
        this.$v.$touch();
        return;
      }

      delete this.user.dues;
      delete this.user.phone_verified;
      delete this.user.emergency_contact_phone_verified;

      axios
        .put(this.dataUrl, this.user)
        .then(response => {
          this.hasError = false;
          if (this.user.clickup_email !== this.clickUpEmailInDatabase && this.user.clickup_email && this.user.clickup_email.length > 0) {
            this.clickUpEmailInDatabase = this.user.clickup_email;
            this.feedback = 'Saved! Look out for an email from ClickUp in the next few minutes.'
          } else {
            this.feedback = 'Saved!';
          }
          this.formChanged = false;
          console.log('success');
        })
        .catch(error => {
          if (
            error &&
            error.response &&
            error.response.status === 422 &&
            error.response.data &&
            error.response.data.errors &&
            typeof error.response.data.errors === 'object' &&
            error.response.data.errors !== null &&
            Object.keys(error.response.data.errors).length > 0 &&
            typeof error.response.data.errors[Object.keys(error.response.data.errors)[0]] === 'object' &&
            error.response.data.errors[Object.keys(error.response.data.errors)[0]].length > 0
          ) {
            let errors = error.response.data.errors;
            Swal.fire('Invalid Data', errors[Object.keys(errors)[0]][0], 'error');
            return;
          }
          this.hasError = true;
          this.feedback = '';
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
    graduationInfoRequired: function() {
      return this.user.primary_affiliation === "student";
    }
  },
  validations: {
    user: {
      phone: { maxLength: maxLength(15) },
      preferred_first_name: { alphaSpace },
      shirt_size: {},
      polo_size: {},
      graduation_semester: {maxLength: maxLength(6), minLength: minLength(6)},
      emergency_contact_name: { required },
      emergency_contact_phone: { required, maxLength: maxLength(15) },
    },
  },
};
</script>

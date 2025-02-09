<template>
  <div class="row">
    <div class="col-12">
      <form v-on:submit.prevent="submit">
        <h4>GT Directory Info</h4>
        <p>Information obtained via GT Single Sign-On. Update at <a href="https://passport.gatech.edu">Passport</a>.</p>

        <div class="form-group row">
          <label for="user-name" class="col-sm-2 col-form-label">Full Name</label>
          <div class="col-sm-10 col-lg-4">
            <input v-model="localUser.full_name" type="text" readonly class="form-control" id="user-name">
          </div>
        </div>

        <div class="form-group row">
          <label for="user-uid" class="col-sm-2 col-form-label">GT Username</label>
          <div class="col-sm-10 col-lg-4">
            <input v-model="localUser.uid" type="text" readonly class="form-control" id="user-uid">
          </div>
        </div>

        <div class="form-group row">
          <label for="user-gtemail" class="col-sm-2 col-form-label">GT Email</label>
          <div class="col-sm-10 col-lg-4">
            <input v-model="localUser.gt_email" type="text" readonly class="form-control" id="user-gtemail">
          </div>
        </div>

        <h4>Membership Information</h4>

        <div class="form-group row">
          <label class="col-sm-2 col-form-label">Dues Term</label>
          <div class="col-sm-10 col-lg-4">
            <select v-model="duesPackageChoice" class="custom-select"
                    :class="{ 'is-invalid': $v.duesPackageChoice.$error }">
              <option value="" style="display:none" v-if="!duesPackages">Loading...</option>
              <option value="" style="display:none" v-if="duesPackages && duesPackages.length === 0">No Dues Packages
                Available
              </option>
              <option value="" style="display:none" v-if="duesPackages && duesPackages.length > 0">Select One</option>
              <option v-for="duesPackage in duesPackages" :value="duesPackage.id">{{ duesPackage.name }} -
                ${{ duesPackage.cost }}
              </option>
            </select>
            <div class="invalid-feedback">
              Select a dues package.
            </div>
          </div>
        </div>

        <div v-if="graduationInfoRequired">
          <h4>Graduation Information</h4>

          <div class="form-group row">
            <label class="col-sm-2 col-form-label">Graduation Date</label>
            <div class="col-sm-10 col-lg-4">
              <term-input
                v-model="localUser.graduation_semester"
                :is-error="$v.localUser.graduation_semester.$error"
                @touch="$v.localUser.graduation_semester.$touch()">
              </term-input>
              <div class="invalid-feedback">
                Enter a valid graduation date.
              </div>
            </div>
          </div>
        </div>

        <h4>Information for Merchandise</h4>

        <div class="form-group row">
          <label class="col-sm-2 col-form-label">T-Shirt Size</label>
          <div class="col-sm-10 col-lg-4">
            <custom-radio-buttons
              v-model="localUser.shirt_size"
              :options="shirtSizeOptions"
              :is-error="$v.localUser.shirt_size.$error"
              @input="$v.localUser.shirt_size.$touch()">
            </custom-radio-buttons>
            <div class="invalid-feedback">
              You must choose a shirt size.
            </div>
          </div>
        </div>

        <div class="form-group row">
          <label class="col-sm-2 col-form-label">Polo Size</label>
          <div class="col-sm-10 col-lg-4">
            <custom-radio-buttons
              v-model="localUser.polo_size"
              :options="shirtSizeOptions"
              :is-error="$v.localUser.polo_size.$error"
              @input="$v.localUser.polo_size.$touch()">
            </custom-radio-buttons>
            <div class="invalid-feedback">
              You must choose a polo size.
            </div>
          </div>
        </div>

        <h4>Merchandise Selection</h4>
        <p v-if="!selectedPackage || (merchGroupNames.length > 0)">One item of RoboJackets merch from each group below
          is included with your dues payment. {{ merchDependencyText }}</p>
        <p v-else-if="user.primary_affiliation === 'student'">No merch is included in this dues package.</p>
        <p v-else>Only students are eligible for RoboJackets merch.</p>
        <div v-for="(merchlist, group) in merchGroups" class="form-group row">
          <label :for="'merch-'+group" class="col-sm-2 col-form-label">{{ group }}</label>
          <div class="col-sm-10 col-lg-4">
            <select :id="'merch-'+group" class="custom-select" v-model="merchlist.selection"
                    :class="{ 'is-invalid': $v.merchGroups.$each[group].$error }"
                    @input="$v.merchGroups.$each[group].$touch()">
              <option value="" style="display:none">Select One</option>
              <option v-for="merch in merchlist.list" :value="merch.id">{{ merch.name }}</option>
            </select>
            <div class="invalid-feedback">
              You must choose an item.
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-lg-6 col-12">
            <button v-if="submitInProgress" disabled class="btn btn-primary float-right">Please wait...</button>
            <button v-else type="submit" class="btn btn-primary float-right">Continue</button>
          </div>
        </div>

      </form>
    </div>
  </div>
</template>

<script>
import {maxLength, minLength, numeric, required} from 'vuelidate/lib/validators';
import TermInput from '../fields/TermInput.vue';

export default {
  components: {TermInput},
  props: ['user'],
  data() {
    return {
      shirtSizeOptions: [
        {value: 's', text: 'S'},
        {value: 'm', text: 'M'},
        {value: 'l', text: 'L'},
        {value: 'xl', text: 'XL'},
        {value: 'xxl', text: 'XXL'},
        {value: 'xxxl', text: 'XXXL'},
      ],
      duesPackages: null,
      duesPackageChoice: '',
      merchGroups: {},
      merchGroupNames: [],
      submitInProgress: false,
    };
  },
  mounted() {
    const dataUrl = '/api/v1/dues/packages/purchase?include=merchandise';
    axios
      .get(dataUrl)
      .then(response => {
        this.duesPackages = response.data.dues_packages;
      })
      .catch(response => {
        console.log(response);
        Swal.fire(
          'Connection Error',
          'Unable to load dues packages. Check your internet connection or try refreshing the page.',
          'error'
        );
      });
  },
  methods: {
    submit() {
      //Perform form validation
      if (this.$v.$invalid) {
        console.log("Ignoring form submit because form data is invalid");
        this.$v.$touch();
        return;
      }

      this.submitInProgress = true;

      Promise.all([
        this.saveUserUpdates(this.localUser),
        this.createDuesRequest(this.localUser.id, this.duesPackageChoice, this.merchGroups),
      ])
        .then(response => {
          this.$emit('next');
        })
        .catch(error => {
          if (error && error.response && error.response.status === 400) {
            this.$emit('next');
          } else if (
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
          } else {
            console.log(error);
            Swal.fire({
                title: 'Connection Error',
                html: 'Unable to save data. Check your internet connection or try refreshing the page.',
                icon: 'error',
              }
            );
          }
        })
        .finally(() => {
          this.submitInProgress = false;
        });
    },
    saveUserUpdates: function (user) {
      const baseUserUrl = '/api/v1/users/';
      const dataUserUrl = baseUserUrl + user.id;
      const userRequest = {
        polo_size: this.localUser.polo_size,
        shirt_size: this.localUser.shirt_size,
        graduation_semester: this.localUser.graduation_semester
      };

      return axios.put(dataUserUrl, userRequest);
    },
    createDuesRequest: function (userId, duesPackageId, merchGroups) {
      const merch = [];
      this.merchGroupNames.forEach(function (group) {
        merch.push(merchGroups[group].selection);
      });
      const duesRequest = {
        user_id: userId,
        dues_package_id: duesPackageId,
        merchandise: merch,
      };
      const duesTransactionsUrl = '/api/v1/dues/transactions';

      return axios.post(duesTransactionsUrl, duesRequest);
    },
  },
  computed: {
    localUser: function () {
      return this.user;
    },
    selectedPackage: function () {
      if (null === this.duesPackages) {
        return null;
      }
      return this.duesPackages.find(duespackage => duespackage.id == this.duesPackageChoice);
    },
    merchDependencyText: function () {
      const base = 'The options depend on your dues term selection above';
      if (!this.selectedPackage) {
        return `${base}, so please select that first.`;
      } else {
        return `${base}.`;
      }
    },
    graduationInfoRequired: function () {
      if (!this.selectedPackage) {
        return false;
      }

      return this.selectedPackage.restricted_to_students;
    }
  },
  watch: {
    duesPackageChoice: function (packageid, old) {
      if (null === this.selectedPackage) return;
      const dataUrl = `/api/v1/dues/packages/${packageid}?include=merchandise`;
      this.merchGroups = {};
      const tempthis = this;
      const groupNames = [];
      this.selectedPackage.merchandise.forEach(function (merch) {
        if (merch.group in tempthis.merchGroups) {
          tempthis.merchGroups[merch.group].list.push(merch);
        } else {
          // Use .$set because if you add a property to an object without it, Vue will not follow its changes.
          tempthis.$set(tempthis.merchGroups, merch.group, {
            selection: '',
            list: [merch],
          });
          groupNames.push(merch.group);
        }
      });
      this.merchGroupNames = groupNames;
      // If the user has never ordered a polo, only give them the polo option if there is a polo option in a group.
      if (!this.user.has_ordered_polo) {
        groupNames.forEach(function (group) {
          const polo = tempthis.merchGroups[group].list.find(merch => merch.name.startsWith('Polo '));
          if (polo) {
            tempthis.merchGroups[group].list = [polo];
          }
        });
      }
    }
  },
  validations() {
    return {
      localUser: {
        shirt_size: {
          required,
        },
        polo_size: {
          required,
        },
        graduation_semester: this.graduationInfoRequired ? {
          required: required,
          minLength: minLength(6),
          maxLength: maxLength(6),
        } : {}
      },
      duesPackageChoice: {
        required,
        numeric,
      },
      merchGroups: {
        $each: {
          selection: {
            required,
            numeric,
          },
        },
      }
    }
  },
};
</script>

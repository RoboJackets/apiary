<template>
  <div class="row">
    <div class="col-12">
      <form id="DuesRequiredInfoForm" v-on:submit.prevent="submit">
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
          <label for="duesPackage" class="col-sm-2 col-form-label">Dues Term</label>
          <div class="col-sm-10 col-lg-4">
            <select id="duesPackage" v-model="duesPackageChoice" class="custom-select" :class="{ 'is-invalid': $v.duesPackageChoice.$error }" @input="$v.duesPackageChoice.$touch()">
              <option value="" style="display:none" v-if="!duesPackages">Loading...</option>
              <option value="" style="display:none" v-if="duesPackages && duesPackages.length === 0">No Dues Packages Available</option>
              <option value="" style="display:none" v-if="duesPackages && duesPackages.length > 0">Select One</option>
              <option v-for="duesPackage in duesPackages" :value="duesPackage.id">{{duesPackage.name}} - ${{duesPackage.cost}}</option>
            </select>
            <div class="invalid-feedback">
              Select a dues package.
            </div>
          </div>
        </div>

        <h4>Information for Merchandise</h4>

        <div class="form-group row">
          <label for="user-shirtsize" class="col-sm-2 col-form-label">T-Shirt Size</label>
          <div class="col-sm-10 col-lg-4">
            <custom-radio-buttons
              v-model="localUser.shirt_size"
              :options="shirtSizeOptions"
              id="user-shirtsize"
              :is-error="$v.localUser.shirt_size.$error"
              @input="$v.localUser.shirt_size.$touch()">
            </custom-radio-buttons>
            <div class="invalid-feedback">
              You must choose a shirt size.
            </div>
          </div>
        </div>

        <div class="form-group row">
          <label for="user-polosize" class="col-sm-2 col-form-label">Polo Size</label>
          <div class="col-sm-10 col-lg-4">
            <custom-radio-buttons
              v-model="localUser.polo_size"
              :options="shirtSizeOptions"
              id="user-polosize"
              :is-error="$v.localUser.polo_size.$error"
              @input="$v.localUser.polo_size.$touch()">
            </custom-radio-buttons>
            <div class="invalid-feedback">
              You must choose a polo size.
            </div>
          </div>
        </div>

        <h4>Merchandise Selection</h4>
        <p>One item of RoboJackets merch from each group below is included with your dues payment. {{merchDependencyText}}</p>
        <div v-for="(merchlist, group) in merchGroups" class="form-group row">
          <label :for="'merch-'+group" class="col-sm-2 col-form-label">{{group}}</label>
          <div class="col-sm-10 col-lg-4">
            <select :id="'merch-'+group" class="custom-select" v-model="merchlist.selection" :class="{ 'is-invalid': $v.merchGroups.$each[group].$error }" @input="$v.merchGroups.$each[group].$touch()">
              <option value="" style="display:none">Select One</option>
              <option v-for="merch in merchlist.list" :value="merch.id">{{merch.name}}</option>
            </select>
            <div class="invalid-feedback">
              You must choose an item.
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-lg-6 col-12">
            <button type="submit" class="btn btn-primary float-right">Continue</button>
          </div>
        </div>

      </form>
    </div>
  </div>
</template>

<script>
import { required, numeric } from 'vuelidate/lib/validators';

export default {
  props: ['user'],
  data() {
    return {
      shirtSizeOptions: [
        { value: 's', text: 'S' },
        { value: 'm', text: 'M' },
        { value: 'l', text: 'L' },
        { value: 'xl', text: 'XL' },
        { value: 'xxl', text: 'XXL' },
        { value: 'xxxl', text: 'XXXL' },
      ],
      duesPackages: null,
      duesPackageChoice: '',
      merchGroups: {},
      merchGroupNames: [],
    };
  },
  mounted() {
    var dataUrl = '/api/v1/dues/packages/purchase?include=merchandise';
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
      //Perform form Validation
      if (this.$v.$invalid) {
        this.$v.$touch();
        return;
      }

      Promise.all([
        this.saveUserUpdates(this.localUser),
        this.createDuesRequest(this.localUser.id, this.duesPackageChoice, this.merchGroups),
      ])
        .then(response => {
          this.$emit('next');
        })
        .catch(error => {
          console.log(error.response.status);
          if (error.response.status == 400) {
            this.$emit('next');
          } else {
            console.log(error);
            Swal.fire(
              'Connection Error',
              'Unable to save data. Check your internet connection or try refreshing the page.',
              'error'
            );
          }
        });
    },
    saveUserUpdates: function(user) {
      var baseUserUrl = '/api/v1/users/';
      var dataUserUrl = baseUserUrl + user.id;

      delete this.localUser.dues;

      return axios.put(dataUserUrl, this.localUser);
    },
    createDuesRequest: function(userId, duesPackageId, merchGroups) {
      var merch = [];
      this.merchGroupNames.forEach(function(group) {
        merch.push(merchGroups[group].selection);
      });
      var duesRequest = {
        user_id: userId,
        dues_package_id: duesPackageId,
        merchandise: merch,
      };
      var duesTransactionsUrl = '/api/v1/dues/transactions';

      return axios.post(duesTransactionsUrl, duesRequest);
    },
  },
  computed: {
    localUser: function() {
      return this.user;
    },
    selectedPackage: function() {
      if (null === this.duesPackages) return null;
      return this.duesPackages.find(duespackage => duespackage.id == this.duesPackageChoice);
    },
    merchDependencyText: function() {
      var base = 'The options depend on your dues term selection above';
      if (!this.selectedPackage) return base + ', so please select that first.';
      else return base + '.';
    },
  },
  watch: {
    duesPackageChoice: function(packageid, old) {
      if (null === this.selectedPackage) return;
      var dataUrl = '/api/v1/dues/packages/' + packageid + '?include=merchandise';
      this.merchGroups = {};
      var tempthis = this;
      var groupNames = [];
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
          var polo = tempthis.merchGroups[group].list.find(merch => merch.name.startsWith('Polo '));
          if (polo) {
            tempthis.merchGroups[group].list = [polo];
          }
        });
      }
    }
  },
  validations: {
    localUser: {
      shirt_size: {
        required,
      },
      polo_size: {
        required,
      },
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
    },
  },
};
</script>

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
      duesPackages: null,
      duesPackageChoice: '',
    };
  },
  mounted() {
    var dataUrl = '/api/v1/dues/packages/purchase';
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
        this.createDuesRequest(this.localUser.id, this.duesPackageChoice),
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
    createDuesRequest: function(userId, duesPackageId) {
      var duesRequest = {
        user_id: userId,
        dues_package_id: duesPackageId,
      };
      var duesTransactionsUrl = '/api/v1/dues/transactions';

      return axios.post(duesTransactionsUrl, duesRequest);
    },
  },
  computed: {
    localUser: function() {
      return this.user;
    },
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
  },
};
</script>

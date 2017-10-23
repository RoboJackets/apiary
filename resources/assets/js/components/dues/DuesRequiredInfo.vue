<template>
  <div class="row">
    <div class="col-12">
      <form id="DuesRequiredInfoForm" v-on:submit.prevent="submit">
        <h3>GT Directory Info</h3>
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

        <h3>Information for Apparel</h3>

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
          </div>
        </div>      

        <h3>Membership Information</h3>

        <div class="form-group row">
          <label for="duesPackage" class="col-sm-2 col-form-label">Dues Term</label>
          <div class="col-sm-10 col-lg-4">
            <select id="duesPackage" v-model="duesPackageChoice" class="custom-select" :class="{ 'is-invalid': $v.duesPackageChoice.$error }" @input="$v.duesPackageChoice.$touch()">
              <option value="" style="display:none">Select One</option>
              <option v-for="duesPackage in duesPackages" :value="duesPackage.id">{{duesPackage.name}}</option>
            </select>
            <div class="invalid-feedback">
              Please select a dues package.
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
          {value: "s", text: "S"},
          {value: "m", text: "M"},
          {value: "l", text: "L"},
          {value: "xl", text: "XL"},
          {value: "xxl", text: "XXL"},
          {value: "xxxl", text: "XXXL"},
        ],
        duesPackages: [],
        duesPackageChoice: ''
      }
    },
    mounted() {
      var dataUrl = "/api/v1/dues/packages/available";
      axios.get(dataUrl)
        .then(response => {
          this.duesPackages = response.data.dues_packages;
        })
        .catch(response => {
          console.log(response);
          sweetAlert("Connection Error", "Unable to load dues packages. Check your internet connection or try refreshing the page.", "error");
        });
    },
    methods: {
      submit () {
        //Perform form Validation
        if (this.$v.$invalid) {
          this.$v.$touch();
          return;
        }

        Promise.all([
          this.saveUserUpdates(this.localUser), 
          this.createDuesRequest(this.localUser.id, this.duesPackageChoice)])
          .then(response => {
            this.$emit("next");
          })
          .catch(error => {
            console.log(error.response.status);
            if (error.response.status == 400) {
              this.$emit("next");
            } else {
              console.log(error);
              sweetAlert("Connection Error", "Unable to save data. Check your internet connection or try refreshing the page.", "error");
            }
          });
      },
      saveUserUpdates: function (user) {
        var baseUserUrl = "/api/v1/users/";
        var dataUserUrl = baseUserUrl + user.id;

        delete this.localUser.dues;

        return axios.put(dataUserUrl, this.localUser);
      },
      createDuesRequest: function (userId, duesPackageId) {
        var duesRequest = {
          user_id: userId,
          dues_package_id: duesPackageId
        };
        var duesTransactionsUrl = "/api/v1/dues/transactions";

        return axios.post(duesTransactionsUrl, duesRequest);
      }
    },
    computed: {
      localUser: function () {
        return this.user;
      }
    },
    validations: {
      localUser: {
        shirt_size: {
          required
        },
        polo_size: {
          required
        }
      },
      duesPackageChoice: {
        required,
        numeric
      }
    }
  }
</script>

<template>
  <div class="row">
    <div class="col-12">
      <form id="DuesRequiredInfoForm" v-on:submit.prevent="submit">
        <h3>GT Directory Info</h3>
        <p>Information obtained via GT Single Sign-On. Update at <a href="https://passport.gatech.edu">Passport</a>.</p>

        <div class="form-group row">
          <label for="user-name" class="col-sm-2 col-form-label">Name</label>
          <div class="col-sm-10 col-lg-4">
            <input v-model="localUser.name" type="text" readonly class="form-control" id="user-name">
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
              id="user-shirtsize">
            </custom-radio-buttons>
          </div>
        </div>

        <div class="form-group row">
          <label for="user-polosize" class="col-sm-2 col-form-label">Polo Size</label>
          <div class="col-sm-10 col-lg-4">
            <custom-radio-buttons
              v-model="localUser.polo_size"
              :options="shirtSizeOptions"
              id="user-polosize">
            </custom-radio-buttons>
          </div>
        </div>      

        <h3>Membership Information</h3>

        <div class="form-group row">
          <label for="user-polosize" class="col-sm-2 col-form-label">Dues Term</label>
          <div class="col-sm-10 col-lg-4">
            <select  class="custom-select">
              <option value="" style="display:none;"></option>
              <option v-for="duesPackage in duesPackages" :value="duesOption.value">{{duesPackage.name}}</option>
            </select>
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
        duesPackages: [ //TODO: Make these options dynamically populated
          {value: "1", name: "Full Year (2017-2018)"},
          {value: "2", name: "Fall 2017"},
          {value: "3", name: "Spring 2018"},
        ],
        
      }
    },
    mounted() {
      /* TODO: Hit API for DuesPackages
      var dataUrl = this.baseUrl + this.userUid;
      axios.get(this.dataUrl)
        .then(response => {
          this.localUser = response.data.user;
        })
        .catch(response => {
          console.log(response);
          sweetAlert("Connection Error", "Unable to load data. Check your internet connection or try refreshing the page.", "error");
        });
        */
    },
    methods: {
      submit () {
        var baseUrl = "/api/v1/users/";

        var dataUrl = baseUrl + this.localUser.uid;
        axios.put(dataUrl, this.localUser)
          .then(response => {
            this.$emit("next");
          })
          .catch(response => {
            console.log(response);
            sweetAlert("Connection Error", "Unable to save data. Check your internet connection or try refreshing the page.", "error");
          })
      }
    },
    computed: {
      localUser: function () {
        return this.user;
      }
    }
  }
</script>

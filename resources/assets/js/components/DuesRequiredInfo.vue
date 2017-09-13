<template>
  <div class="row">
    <div class="col-12">
      <form id="DuesRequiredInfoForm" v-on:submit.prevent="submit">
        <h3>GT Directory Info</h3>
        <p>Information obtained via GT Single Sign-On. Update at <a href="https://passport.gatech.edu">Passport</a>.</p>

        <div class="form-group row">
          <label for="user-name" class="col-sm-2 col-form-label">Name</label>
          <div class="col-sm-10 col-lg-4">
            <input v-model="user.name" type="text" readonly class="form-control" id="user-name">
          </div>
        </div>

        <div class="form-group row">
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

        <h3>Information for Apparel</h3>

        <div class="form-group row">
          <label for="user-shirtsize" class="col-sm-2 col-form-label">T-Shirt Size</label>
          <div class="col-sm-10 col-lg-4">
            <custom-radio-buttons
              v-model="user.shirt_size"
              :options="shirtSizeOptions"
              id="user-shirtsize">
            </custom-radio-buttons>
          </div>
        </div>

        <div class="form-group row">
          <label for="user-polosize" class="col-sm-2 col-form-label">Polo Size</label>
          <div class="col-sm-10 col-lg-4">
            <custom-radio-buttons
              v-model="user.polo_size"
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
              <option v-for="duesOption in duesOptions" :value="duesOption.value">{{duesOption.name}}</option>
            </select>
          </div>
        </div>

        

        <div class="form-group">
          <button type="submit" class="btn btn-primary">Continue</button>
          <em><span v-bind:class="{ 'text-danger': hasError}"> {{feedback}} </span></em>
        </div>

      </form>
    </div>
  </div>
</template>

<script>
  export default {
    props: ['userUid'],
    data() {
      return {
        user: {},
        feedback: '',
        hasError: false,
        dataUrl: '',
        baseUrl: "/api/v1/users/",
        shirtSizeOptions: [
          {value: "s", text: "S"},
          {value: "m", text: "M"},
          {value: "l", text: "L"},
          {value: "xl", text: "XL"},
          {value: "xxl", text: "XXL"},
          {value: "xxxl", text: "XXXL"},
        ],
        duesOptions: [ //TODO: Make these options dynamically populated
          {value: "1", name: "Full Year (2017-2018)"},
          {value: "2", name: "Fall 2017"},
          {value: "3", name: "Spring 2018"},
        ]
      }
    },
    mounted() {
      this.dataUrl = this.baseUrl + this.userUid;
      axios.get(this.dataUrl)
        .then(response => {
          this.user = response.data.user;
        })
        .catch(response => {
          console.log(response);
          sweetAlert("Connection Error", "Unable to load data. Check your internet connection or try refreshing the page.", "error");
        });
    },
    methods: {
      submit () {
        axios.put(this.dataUrl, this.user)
          .then(response => {
            this.hasError = false;
            this.feedback = "Saved!"
            console.log("success");
          })
          .catch(response => {
            this.hasError = true;
            this.feedback = "";
            console.log(response);
            sweetAlert("Connection Error", "Unable to save data. Check your internet connection or try refreshing the page.", "error");
          })
      }
    }
  }
</script>

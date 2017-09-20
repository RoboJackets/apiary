<template>
  <div class="row">
    <div class="col-12">
      <form id="DuesRequiredInfoForm" v-on:submit.prevent="submit">
        <h4>Additional Information</h4>
        <p>Providing RoboJackets with this optional information enables the RoboJackets leadership to better serve you.</p>

        <div class="form-group row">
          <label for="user-personalemail" class="col-sm-2 col-form-label">Personal Email</label>
          <div class="col-sm-10 col-lg-4">
            <input v-model="localUser.personal_email" type="email" class="form-control" id="user-personalemail">
          </div>
        </div>

        <div class="form-group row">
          <label for="user-uid" class="col-sm-2 col-form-label">Phone Number</label>
          <div class="col-sm-10 col-lg-4">
            <input v-model="localUser.phone" type="tel" class="form-control" id="user-uid" maxlength="15">
          </div>
        </div>

        <h4>Emergency Contact Information</h4>
        <p>Optional, but needed to generate Institute Approved Absence forms</p>

        <div class="form-group row">
          <label for="user-emergencycontactname" class="col-sm-2 col-form-label">Contact Name</label>
          <div class="col-sm-10 col-lg-4">
            <input v-model="localUser.emergency_contact_name" type="text" class="form-control" id="user-emergencycontactname">
          </div>
        </div>
        <div class="form-group row">
          <label for="user-emergencycontactphone" class="col-sm-2 col-form-label">Contact Phone Number</label>
          <div class="col-sm-10 col-lg-4">
            <input v-model="localUser.emergency_contact_phone" type="tel" class="form-control" id="user-emergencycontactphone" maxlength="15">
          </div>
        </div>

        <div class="row">
          <div class="col-lg-6 col-12">
            <button type="submit" class="btn btn-primary float-right">Continue</button>
            <button type="submit" class="btn btn-secondary float-right mx-2">Skip</button>
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

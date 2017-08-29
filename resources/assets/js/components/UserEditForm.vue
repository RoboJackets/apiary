<template>
  <div class="row">
    <div class="col-12">
      <form id="userEditForm" v-on:submit.prevent="submit">
        <h3>GT Directory Info</h3>
        <p>Information obtained via GT Single Sign-On. Update at <a href="https://passport.gatech.edu">Passport</a>.</p>

        <div class="form-group row">
          <label for="user-name" class="col-sm-2 col-form-label">Full Name</label>
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

          <label for="user-gtid" class="col-sm-2 col-form-label">GTID</label>
          <div class="col-sm-10 col-lg-4">
            <input v-model="user.gtid" type="text" readonly class="form-control" id="user-gtid">
          </div>
        </div>

        <h3>Additional Information</h3>

        <div class="form-group row">
          <label for="user-personalemail" class="col-sm-2 col-form-label">Personal Email</label>
          <div class="col-sm-10 col-lg-4">
            <input v-model="user.personal_email" type="email" class="form-control" id="user-personalemail" placeholder="None on record">
          </div>
        </div>

        <div class="form-group row">
          <label for="user-slackid" class="col-sm-2 col-form-label">Slack Username</label>
          <div class="col-sm-10 col-lg-4">
            <input v-model="user.slack_id" type="text" class="form-control" id="user-slackid" placeholder="None on record">
          </div>
        </div>

        <div class="form-group row">
          <label for="user-phone" class="col-sm-2 col-form-label">Phone Number</label>
          <div class="col-sm-10 col-lg-4">
            <input v-model="user.phone" type="tel" class="form-control" id="user-phone" placeholder="None on record">
          </div>
        </div>

        <div class="form-group row">
          <label for="user-shirtsize" class="col-sm-2 col-form-label">Shirt Size</label>
          <div class="col-sm-10 col-lg-4">
            <div class="btn-group" id="user-shirtsize" data-toggle="buttons">
              <label class="btn btn-secondary" v-bind:class="{ active: user.shirt_size=='s' }">
                <input v-model="user.shirt_size" type="radio" name="shirt_size" value="s" autocomplete="off"> S
              </label>
              <label class="btn btn-secondary" v-bind:class="{ active: user.shirt_size=='m' }">
                <input v-model="user.shirt_size" type="radio" name="shirt_size" value="m" autocomplete="off"> M
              </label>
              <label class="btn btn-secondary" v-bind:class="{ active: user.shirt_size=='l' }">
                <input v-model="user.shirt_size" type="radio" name="shirt_size" value="l" autocomplete="off"> L
              </label>
              <label class="btn btn-secondary" v-bind:class="{ active: user.shirt_size=='xl' }">
                <input v-model="user.shirt_size" type="radio" name="shirt_size" value="xl" autocomplete="off"> XL
              </label>
              <label class="btn btn-secondary" v-bind:class="{ active: user.shirt_size=='xxl' }">
                <input v-model="user.shirt_size" type="radio" name="shirt_size" value="xxl" autocomplete="off"> XXL
              </label>
              <label class="btn btn-secondary" v-bind:class="{ active: user.shirt_size=='xxxl' }">
                <input v-model="user.shirt_size" type="radio" name="shirt_size" value="xxxl" autocomplete="off"> XXXL
              </label>
            </div>
          </div>
        </div>

        <div class="form-group row">
          <label for="user-polosize" class="col-sm-2 col-form-label">Polo Size</label>
          <div class="col-sm-10 col-lg-4">
            <div class="btn-group" id="user-polosize" data-toggle="buttons">
              <label class="btn btn-secondary" v-bind:class="{ active: user.shirt_size=='s' }">
                <input v-model="user.polo_size" type="radio" name="polo_size" value="s" autocomplete="off"> S
              </label>
              <label class="btn btn-secondary" v-bind:class="{ active: user.shirt_size=='m' }">
                <input v-model="user.polo_size" type="radio" name="polo_size" value="m" autocomplete="off"> M
              </label>
              <label class="btn btn-secondary" v-bind:class="{ active: user.shirt_size=='l' }">
                <input v-model="user.polo_size" type="radio" name="polo_size" value="l" autocomplete="off"> L
              </label>
              <label class="btn btn-secondary" v-bind:class="{ active: user.shirt_size=='xl' }">
                <input v-model="user.polo_size" type="radio" name="polo_size" value="xl" autocomplete="off"> XL
              </label>
              <label class="btn btn-secondary" v-bind:class="{ active: user.shirt_size=='xxl' }">
                <input v-model="user.polo_size" type="radio" name="polo_size" value="xxl" autocomplete="off"> XXL
              </label>
              <label class="btn btn-secondary" v-bind:class="{ active: user.shirt_size=='xxxl' }">
                <input v-model="user.polo_size" type="radio" name="polo_size" value="xxxl" autocomplete="off"> XXXL
              </label>
            </div>
          </div>
        </div>

        

        <h3>Membership Information</h3>

        <div class="form-group row">
          <label for="user-joinsemester" class="col-sm-2 col-form-label">Join Semester</label>
          <div class="col-sm-10 col-lg-4">
            <term-input v-model="user.join_semester" id="user-joinsemester"></term-input>
          </div>
        </div>

        <div class="form-group row">
          <label for="user-graduationsemester" class="col-sm-2 col-form-label">Graduation Semester</label>
          <div class="col-sm-10 col-lg-4">
            <term-input v-model="user.graduation_semester" id="user-graduationsemester"></term-input>
          </div>
        </div>

        

        <h3>Emergency Contacts</h3>

        <div class="form-group row">
          <label for="user-emergencyname" class="col-sm-2 col-form-label">Contact Name</label>
          <div class="col-sm-10 col-lg-4">
            <input v-model="user.emergency_contact_name" type="text" class="form-control" id="user-emergencyname" placeholder="None on record">
          </div>
        </div>

        <div class="form-group row">
          <label for="user-emergencyphone" class="col-sm-2 col-form-label">Contact Phone Number</label>
          <div class="col-sm-10 col-lg-4">
            <input v-model="user.emergency_contact_phone" type="tel" class="form-control" id="user-emergencyphone" placeholder="None on record">
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
  export default {
    props: ['userUid'],
    data() {
      return {
        user: {},
        feedback: '',
        hasError: false,
        dataUrl: '',
        baseUrl: "/api/v1/users/"
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

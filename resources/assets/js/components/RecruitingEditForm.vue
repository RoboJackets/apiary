<template>
  <div class="row">
    <div class="col-12">
      <form id="recruitingEditForm" v-on:submit.prevent="submit">
        <h3>Visit Metadata</h3>
        <div class="form-group row">
          <label for="recruiting-created" class="col-sm-2 col-form-label">Created at</label>
          <div class="col-sm-10 col-lg-4">
            <input v-model="recruitingVisit.created_at" type="text" readonly class="form-control" id="recruiting-created">
          </div>

          <label for="recruiting-updated" class="col-sm-2 col-form-label">Last Updated</label>
          <div class="col-sm-10 col-lg-4">
            <input v-model="recruitingVisit.updated_at" type="text" readonly class="form-control" id="recruiting-updated">
          </div>
        </div>

        <div class="form-group row">
          <label for="recruiting-token" class="col-sm-2 col-form-label">Visit Token</label>
          <div class="col-sm-10 col-lg-4">
            <input v-model="recruitingVisit.visit_token" type="text" readonly class="form-control" id="recruiting-token">
          </div>

        </div>

        <h3>Visit Information</h3>

        <div class="form-group">
          <label for="recruiting-name">Name</label>
          <input v-model="recruitingVisit.recruiting_name" type="text" class="form-control" id="recruiting-name" name="recruiting-name" autocomplete="off">
          <small class="form-text text-muted">First and last name</small>
        </div>

        <div class="form-group">
          <label for="recruiting-email">Email</label>
          <input v-model="recruitingVisit.recruiting_email" type="email" class="form-control" id="recruiting-email" name="recruiting-email" autocomplete="off">
        </div>



        <!--- Commenting out survey data because it is hard 

        <fieldset class="form-group">
          <label for="heardfrom">How did you hear about RoboJackets?</label>
          <div class="custom-controls-stacked">
            <label class="custom-control custom-checkbox">
              <input v-model="recruitingVisit.recruiting_responses" type="checkbox" class="custom-control-input" name="heardfrom" value="faset">
              <span class="custom-control-indicator"></span>
              <span class="custom-control-description">FASET</span>
            </label>
          </div>
          <div class="custom-controls-stacked">
            <label class="custom-control custom-checkbox">
              <input v-model="recruitingVisit.recruiting_responses" type="checkbox" class="custom-control-input" name="heardfrom" value="tour">
              <span class="custom-control-indicator"></span>
              <span class="custom-control-description">Campus tour</span>
            </label>
          </div>
          <div class="custom-controls-stacked">
            <label class="custom-control custom-checkbox">
              <input v-model="recruitingVisit.recruiting_responses" type="checkbox" class="custom-control-input" name="heardfrom" value="member">
              <span class="custom-control-indicator"></span>
              <span class="custom-control-description">From another member</span>
            </label>
          </div>
          <div class="custom-controls-stacked">
            <label class="custom-control custom-checkbox">
              <input v-model="recruitingVisit.recruiting_responses" type="checkbox" class="custom-control-input" name="heardfrom" value="nonmember">
              <span class="custom-control-indicator"></span>
              <span class="custom-control-description">From a friend not in RoboJackets</span>
            </label>
          </div>
          <div class="custom-controls-stacked">
            <label class="custom-control custom-checkbox">
              <input v-model="recruitingVisit.recruiting_responses" type="checkbox" class="custom-control-input" name="heardfrom" value="social_media">
              <span class="custom-control-indicator"></span>
              <span class="custom-control-description">Social Media (Facebook, Twitter, Youtube, etc.)</span>
            </label>
          </div>
          <div class="custom-controls-stacked">
            <label class="custom-control custom-checkbox">
              <input v-model="recruitingVisit.recruiting_responses" type="checkbox" class="custom-control-input" name="heardfrom" value="website">
              <span class="custom-control-indicator"></span>
              <span class="custom-control-description">Website (RoboJackets.org)</span>
            </label>
          </div>
          <div class="custom-controls-stacked">
            <label class="custom-control custom-checkbox">
              <input v-model="recruitingVisit.recruiting_responses" type="checkbox" class="custom-control-input" name="heardfrom" value="frc">
              <span class="custom-control-indicator"></span>
              <span class="custom-control-description">FRC Event</span>
            </label>
          </div>
          <div class="custom-controls-stacked">
            <label class="custom-control custom-checkbox">
              <input v-model="recruitingVisit.recruiting_responses" type="checkbox" class="custom-control-input" name="heardfrom" value="ftc">
              <span class="custom-control-indicator"></span>
              <span class="custom-control-description">FTC Event</span>
            </label>
          </div>
          <div class="custom-controls-stacked">
            <label class="custom-control custom-checkbox">
              <input v-model="recruitingVisit.recruiting_responses" type="checkbox" class="custom-control-input" name="heardfrom" value="vex">
              <span class="custom-control-indicator"></span>
              <span class="custom-control-description">Vex Event</span>
            </label>
          </div>
        </fieldset>

        -->

        <div class="form-group">
          <button type="submit" class="btn btn-primary">Save Changes</button>
          <button type="button" v-on:click="sendEmail" class="btn btn-secondary">Send Email</button>
        </div>
        <em><span v-bind:class="{ 'text-danger': hasError}"> {{feedback}} </span></em>

      </form>
    </div>
  </div>
</template>

<script>
export default {
  props: ['recruitingVisitId'],
  data() {
    return {
      recruitingVisit: {},
      feedback: '',
      hasError: false,
      dataUrl: '',
      baseFasetUrl: '/api/v1/recruiting/',
      notificationUrl: '/api/v1/notification/manual',
    };
  },
  mounted() {
    this.dataUrl = this.baseFasetUrl + this.recruitingVisitId;
    axios
      .get(this.dataUrl)
      .then(response => {
        var visit = response.data.visit;
        var survey = visit.recruiting_responses.map(function(a) {
          return a.response;
        });
        visit.recruiting_responses = survey;
        this.recruitingVisit = visit;
      })
      .catch(response => {
        console.log(response);
        swal(
          'Connection Error',
          'Unable to load data. Check your internet connection or try refreshing the page.',
          'error'
        );
      });
  },
  methods: {
    submit() {
      axios
        .put(this.dataUrl, this.recruitingVisit)
        .then(response => {
          this.hasError = false;
          this.feedback = 'Saved!';
          console.log('success');
        })
        .catch(response => {
          this.hasError = true;
          this.feedback = '';
          console.log(response);
          swal(
            'Connection Error',
            'Unable to save data. Check your internet connection or try refreshing the page.',
            'error'
          );
        });
    },

    sendEmail(event) {
      if (this.recruitingVisit.recruiting_email) {
        axios
          .post(this.notificationUrl, {
            emails: [this.recruitingVisit.recruiting_email],
          })
          .then(response => {
            this.hasError = false;
            this.feedback = 'Sent!';
            console.log('success');
          })
          .catch(response => {
            this.hasError = true;
            this.feedback = '';
            console.log(response);
            swal(
              'Connection Error',
              'Unable to send email. Check your internet connection or try refreshing the page.',
              'error'
            );
          });
      }
    },
  },
};
</script>

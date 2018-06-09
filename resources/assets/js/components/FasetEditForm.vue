<template>
  <div class="row">
    <div class="col-12">
      <form id="fasetEditForm" v-on:submit.prevent="submit">
        <h3>Visit Metadata</h3>
        <div class="form-group row">
          <label for="faset-created" class="col-sm-2 col-form-label">Created at</label>
          <div class="col-sm-10 col-lg-4">
            <input v-model="fasetVisit.created_at" type="text" readonly class="form-control" id="faset-created">
          </div>

          <label for="faset-updated" class="col-sm-2 col-form-label">Last Updated</label>
          <div class="col-sm-10 col-lg-4">
            <input v-model="fasetVisit.updated_at" type="text" readonly class="form-control" id="faset-updated">
          </div>
        </div>

        <div class="form-group row">
          <label for="faset-token" class="col-sm-2 col-form-label">Visit Token</label>
          <div class="col-sm-10 col-lg-4">
            <input v-model="fasetVisit.visit_token" type="text" readonly class="form-control" id="faset-token">
          </div>

        </div>

        <h3>Visit Information</h3>

        <div class="form-group">
          <label for="faset-name">Name</label>
          <input v-model="fasetVisit.faset_name" type="text" class="form-control" id="faset-name" name="faset-name" autocomplete="off">
          <small class="form-text text-muted">First and last name</small>
        </div>

        <div class="form-group">
          <label for="faset-email">Email</label>
          <input v-model="fasetVisit.faset_email" type="email" class="form-control" id="faset-email" name="faset-email" autocomplete="off">
        </div>



        <!--- Commenting out survey data because it is hard 

        <fieldset class="form-group">
          <label for="heardfrom">How did you hear about RoboJackets?</label>
          <div class="custom-controls-stacked">
            <label class="custom-control custom-checkbox">
              <input v-model="fasetVisit.faset_responses" type="checkbox" class="custom-control-input" name="heardfrom" value="faset">
              <span class="custom-control-indicator"></span>
              <span class="custom-control-description">FASET</span>
            </label>
          </div>
          <div class="custom-controls-stacked">
            <label class="custom-control custom-checkbox">
              <input v-model="fasetVisit.faset_responses" type="checkbox" class="custom-control-input" name="heardfrom" value="tour">
              <span class="custom-control-indicator"></span>
              <span class="custom-control-description">Campus tour</span>
            </label>
          </div>
          <div class="custom-controls-stacked">
            <label class="custom-control custom-checkbox">
              <input v-model="fasetVisit.faset_responses" type="checkbox" class="custom-control-input" name="heardfrom" value="member">
              <span class="custom-control-indicator"></span>
              <span class="custom-control-description">From another member</span>
            </label>
          </div>
          <div class="custom-controls-stacked">
            <label class="custom-control custom-checkbox">
              <input v-model="fasetVisit.faset_responses" type="checkbox" class="custom-control-input" name="heardfrom" value="nonmember">
              <span class="custom-control-indicator"></span>
              <span class="custom-control-description">From a friend not in RoboJackets</span>
            </label>
          </div>
          <div class="custom-controls-stacked">
            <label class="custom-control custom-checkbox">
              <input v-model="fasetVisit.faset_responses" type="checkbox" class="custom-control-input" name="heardfrom" value="social_media">
              <span class="custom-control-indicator"></span>
              <span class="custom-control-description">Social Media (Facebook, Twitter, Youtube, etc.)</span>
            </label>
          </div>
          <div class="custom-controls-stacked">
            <label class="custom-control custom-checkbox">
              <input v-model="fasetVisit.faset_responses" type="checkbox" class="custom-control-input" name="heardfrom" value="website">
              <span class="custom-control-indicator"></span>
              <span class="custom-control-description">Website (RoboJackets.org)</span>
            </label>
          </div>
          <div class="custom-controls-stacked">
            <label class="custom-control custom-checkbox">
              <input v-model="fasetVisit.faset_responses" type="checkbox" class="custom-control-input" name="heardfrom" value="frc">
              <span class="custom-control-indicator"></span>
              <span class="custom-control-description">FRC Event</span>
            </label>
          </div>
          <div class="custom-controls-stacked">
            <label class="custom-control custom-checkbox">
              <input v-model="fasetVisit.faset_responses" type="checkbox" class="custom-control-input" name="heardfrom" value="ftc">
              <span class="custom-control-indicator"></span>
              <span class="custom-control-description">FTC Event</span>
            </label>
          </div>
          <div class="custom-controls-stacked">
            <label class="custom-control custom-checkbox">
              <input v-model="fasetVisit.faset_responses" type="checkbox" class="custom-control-input" name="heardfrom" value="vex">
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
  props: ['fasetVisitId'],
  data() {
    return {
      fasetVisit: {},
      feedback: '',
      hasError: false,
      dataUrl: '',
      baseFasetUrl: '/api/v1/faset/',
      notificationUrl: '/api/v1/notification/manual',
    };
  },
  mounted() {
    this.dataUrl = this.baseFasetUrl + this.fasetVisitId;
    axios
      .get(this.dataUrl)
      .then(response => {
        var visit = response.data.visit;
        var survey = visit.faset_responses.map(function(a) {
          return a.response;
        });
        visit.faset_responses = survey;
        this.fasetVisit = visit;
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
        .put(this.dataUrl, this.fasetVisit)
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
      if (this.fasetVisit.faset_email) {
        axios
          .post(this.notificationUrl, {
            emails: [this.fasetVisit.faset_email],
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

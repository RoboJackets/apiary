<template>
  <div class="row">
    <div class="col-12">
      <form id="eventCreateForm" v-on:submit.prevent="submit">

        <h3>Event Details</h3>

        <div class="form-group row">
          <label for="event-name" class="col-sm-2 col-form-label">Event Name<span style="color:red">*</span></label>
          <div class="col-sm-10 col-lg-4">
            <input
              v-model="event.name"
              type="text"
              class="form-control"
              :class="{ 'is-invalid': $v.event.name.$error }"
              id="event-name"
              @blur="$v.event.name.$touch()">
            <div class="invalid-feedback">
              You must enter a name for this event.
            </div>
          </div>

          <label for="event-organizer" class="col-sm-2 col-form-label">Organizer</label>
          <div class="col-sm-10 col-lg-4">
            <user-lookup v-model="event.organizer"></user-lookup>
          </div>
        </div>

        <div class="form-group row">
          <label for="event-starttime" class="col-sm-2 col-form-label">Start Time</label>
          <div class="col-sm-10 col-lg-4">
            <flat-pickr
              id="event-starttime"
              v-model="event.start_time"
              placeholder="Select start time"
              :required="true"
              :config="dateTimeConfig"
              input-class="form-control">
            </flat-pickr>
          </div>

          <label for="event-endtime" class="col-sm-2 col-form-label">End Time</label>
          <div class="col-sm-10 col-lg-4">
            <flat-pickr
              id="event-endtime"
              v-model="event.end_time"
              placeholder="Select start time"
              :required="true"
              :config="dateTimeConfig"
              input-class="form-control">
            </flat-pickr>
          </div>
        </div>

        <div class="form-group row">
          <label for="event-location" class="col-sm-2 col-form-label">Location</label>
          <div class="col-sm-10 col-lg-4">
            <input v-model="event.location" type="text" class="form-control" id="event-location">
          </div>

          <label for="event-anonymousrsvp-buttons" class="col-sm-2 col-form-label">Anonymous RSVP<span style="color:red">*</span></label>
          <div class="col-sm-10 col-lg-4">
            <custom-radio-buttons
              v-model="event.allow_anonymous_rsvp"
              :options="rsvpOptions"
              id="event-anonymousrsvp-buttons"
              :is-error="$v.event.allow_anonymous_rsvp.$error"
              @input="$v.event.allow_anonymous_rsvp.$touch()">
            </custom-radio-buttons>
            <div class="invalid-feedback">
              You must indicate whether to allow anonymous RSVPs.
            </div>
          </div>
        </div>

        <div class="form-group row">
          <label for="event-cost" class="col-sm-2 col-form-label">Cost</label>
          <div class="col-sm-10 col-lg-4">
            <div class="input-group">
              <div class="input-group-prepend">
                <div class="input-group-text">$</div>
              </div>
              <input
                v-model="event.cost"
                type="text"
                class="form-control"
                :class="{ 'is-invalid': $v.event.cost.$error }"
                id="event-cost"
                placeholder="Enter a decimal (10.00)"
                @blur="$v.event.cost.$touch()">
              <div class="invalid-feedback">
                Cost must be a number
              </div>
            </div>
          </div>
        </div>

        <div class="form-group">
          <button type="submit" class="btn btn-primary">Create</button>
          <a class="btn btn-secondary" href="/admin/events">Cancel</a>
          <em><span v-bind:class="{ 'text-danger': hasError}"> {{feedback}} </span></em>
        </div>

      </form>
    </div>
  </div>
</template>

<script>
import { required, numeric } from 'vuelidate/lib/validators';
export default {
  name: 'createEventForm',
  data() {
    return {
      event: {},
      feedback: '',
      hasError: false,
      baseUrl: '/api/v1/events',
      dateTimeConfig: {
        dateFormat: 'Y-m-d H:i:S',
        enableTime: true,
        altInput: true,
      },
      rsvpOptions: [{ value: '0', text: 'No' }, { value: '1', text: 'Yes' }],
    };
  },
  validations: {
    event: {
      name: { required },
      cost: { numeric },
      allow_anonymous_rsvp: { required },
    },
  },
  methods: {
    submit() {
      if (this.$v.$invalid) {
        this.$v.$touch();
        return;
      }

      //Set organizer_id to the id from the selected object
      let newEvent = this.event;
      if (newEvent.organizer instanceof Object) {
        newEvent.organizer_id = newEvent.organizer.id;
      }

      axios
        .post(this.baseUrl, newEvent)
        .then(response => {
          this.hasError = false;
          this.feedback = 'Saved!';
          console.log('success');
          window.location.href = '/admin/events/' + response.data.event.id;
        })
        .catch(response => {
          this.hasError = true;
          this.feedback = '';
          console.log(response);
          swal('Error', 'Unable to save data. Check your internet connection or try refreshing the page.', 'error');
        });
    },
  },
};
</script>

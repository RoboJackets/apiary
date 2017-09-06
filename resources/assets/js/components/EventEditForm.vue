<template>
  <div class="row">
    <div class="col-12">
      <form id="userEditForm" v-on:submit.prevent="submit">
        
        <h3>Event Details</h3>

        <div class="form-group row">
          <label for="event-name" class="col-sm-2 col-form-label">Event Name</label>
          <div class="col-sm-10 col-lg-4">
            <input v-model="event.name" type="text" class="form-control" id="event-name" placeholder="None on record">
          </div>

          <label for="event-organizer" class="col-sm-2 col-form-label">Organizer</label>
          <div class="col-sm-10 col-lg-4">
            <input v-model="event.organizer" type="text" class="form-control" id="user-organizer" readonly placeholder="None on record">
          </div>
        </div>

        <div class="form-group row">
          <label for="event-starttime" class="col-sm-2 col-form-label">Start Time</label>
          <div class="col-sm-10 col-lg-4">
            <input v-model="event.start_time" type="datetime-local" class="form-control" id="event-starttime" placeholder="None on record">
          </div>

          <label for="event-endtime" class="col-sm-2 col-form-label">End Time</label>
          <div class="col-sm-10 col-lg-4">
            <input v-model="event.end_time" type="datetime-local" class="form-control" id="user-endtime" placeholder="None on record">
          </div>
        </div>

        <div class="form-group row">
          <label for="event-location" class="col-sm-2 col-form-label">Location</label>
          <div class="col-sm-10 col-lg-4">
            <input v-model="event.location" type="text" class="form-control" id="event-location" placeholder="None on record">
          </div>
        </div>

        <div class="form-group">
          <button type="submit" class="btn btn-primary">Save Changes</button>
          <em><span v-bind:class="{ 'text-danger': hasError}"> {{feedback}} </span></em>
        </div>

      </form>

      <h3>RSVPs</h3>

     
      <datatable id="rsvp-admin-table"
        :data-object="event.rsvps"
        :columns="rsvpTableConfig">
      </datatable>
      
    </div>
  </div>
</template>

<script>
  export default {
    props: ['eventId'],
    data() {
      return {
        event: {},
        feedback: '',
        hasError: false,
        dataUrl: '',
        baseUrl: "/api/v1/events/",
        rsvpTableConfig: [
          {'title': 'ID', 'data': 'id'},
          {'title': 'User', 'data': 'user_id'},
          {'title': 'Response', 'data': 'response'},
          {'title': 'Source', 'data': 'source'},
          {'title': 'Time', 'data': 'created_at'}
        ]
      }
    },
    mounted() {
      this.dataUrl = this.baseUrl + this.eventId;
      axios.get(this.dataUrl)
        .then(response => {
          this.event = response.data.event;
          this.rows = response.data.event.rsvps;
        })
        .catch(response => {
          console.log(response);
          sweetAlert("Connection Error", "Unable to load data. Check your internet connection or try refreshing the page.", "error");
        });
    },
    methods: {
      submit () {
        axios.put(this.dataUrl, this.event)
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

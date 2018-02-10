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
            <input v-model="event.organizer" type="text" class="form-control" id="user-organizer" placeholder="None on record">
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
            <input v-model="event.location" type="text" class="form-control" id="event-location" placeholder="None on record">
          </div>
          <label for="event-anonymousrsvp" class="col-sm-2 col-form-label">Allow Anonymous RSVP</label>
          <div class="col-sm-10 col-lg-4">
            <div class="btn-group" id="user-shirtsize" data-toggle="buttons">
              <label class="btn btn-secondary" v-bind:class="{ active: event.allow_anonymous_rsvp==false }" @click.left="updateRadio">
                <input v-model="event.allow_anonymous_rsvp" type="radio" name="shirt_size" value="false" autocomplete="off"> No (default)
              </label>
              <label class="btn btn-secondary" v-bind:class="{ active: event.allow_anonymous_rsvp==true }"  @click.left="updateRadio">
                <input v-model="event.allow_anonymous_rsvp" type="radio" name="shirt_size" value="true" autocomplete="off"> Yes
              </label>
            </div>
          </div>
        </div>

        <div class="form-group">
          <button type="submit" class="btn btn-primary">Save Changes</button>
          <em><span v-bind:class="{ 'text-danger': hasError}"> {{feedback}} </span></em>
        </div>

      </form>

      <ul class="nav nav-tabs">
        <li class="nav-item">
          <a class="nav-link active" id="rsvp-tab" data-toggle="tab" href="#rsvps">RSVPs</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" id="rsvp-tab" data-toggle="tab" href="#attendance">Attendance</a>
        </li>
      </ul>

      <div class="tab-content">
        <div class="tab-pane show active"  id="rsvps">
          <h3>RSVPs</h3>
           
          <datatable id="rsvp-admin-table"
            :data-object="event.rsvps"
            :columns="rsvpTableConfig">
          </datatable>
        </div>

        <div class="tab-pane" id="attendance">
          <h3>Attendance</h3>

          <datatable id="attendance-view-table"
            :data-object="attendance"
            :columns="attendanceTableConfig">
          </datatable>
        </div>
        
      </div>
    </div>
  </div>
</template>

<script>
  export default {
    name: "editEventForm",
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
        ],
        attendance: [],
        attendanceUrl: '',
        attendanceTableConfig: [
          {'title': 'Name', 'data': null, 'render': function (data, type, row) {
            try {
              return data.attendee.name;
            } catch (e) {
              return "Non-member";
            }
          }},
          {'title': 'Time', 'data': 'created_at'}
        ],
        dateTimeConfig: {
          dateFormat: "Y-m-d H:i:S",
          enableTime:true,
          altInput: true
        }
      }
    },
    mounted() {
      this.dataUrl = this.baseUrl + this.eventId;
      axios.get(this.dataUrl)
        .then(response => {
          this.event = response.data.event;
        })
        .catch(response => {
          console.log(response);
          sweetAlert("Connection Error", "Unable to load data. Check your internet connection or try refreshing the page.", "error");
        });

      this.attendanceUrl = "/api/v1/attendance?attendable_type=App\\Event&attendable_id=" + this.eventId;
      axios.get(this.attendanceUrl)
        .then(response => {
          this.attendance = response.data.attendance;
        })
        .catch(response => {
          console.log(response);
          sweetAlert("Connection Error", "Unable to load data. Check your internet connection or try refreshing the page.", "error");
        });

      setInterval(this.updateAttendance, 5000);

    },
    methods: {
      submit () {
        var updatedEvent = this.event;
        delete updatedEvent.rsvps;

        axios.put(this.dataUrl, updatedEvent)
          .then(response => {
            this.hasError = false;
            this.feedback = "Saved!"
            console.log("success");
          })
          .catch(response => {
            this.hasError = true;
            this.feedback = "";
            console.log(response);
            sweetAlert("Error", "Unable to save data. Check your internet connection or try refreshing the page.", "error");
          })
      },
      updateRadio (event) {
        this.event.allow_anonymous_rsvp = event.target.firstChild.value == 'true';
      },
      updateAttendance () {
        axios.get(this.attendanceUrl)
        .then(response => {
          this.attendance = response.data.attendance;
        })
        .catch(response => {
          console.log(response);
          sweetAlert("Connection Error", "Unable to load data. Check your internet connection or try refreshing the page.", "error");
        });
      }

    }
  }
</script>

<template>
  <div class="row">
    <div class="col-12">
      <form id="userEditForm" v-on:submit.prevent="submit">

        <h3>Event Details</h3>

        <div class="form-group row">
          <label for="event-name" class="col-sm-2 col-form-label">Event Name</label>
          <div class="col-sm-10 col-lg-4">
            <input
              v-model="event.name"
              type="text"
              class="form-control"
              id="event-name"
              placeholder="None on record">
            <div class="invalid-feedback">
              You must enter a name for this event.
            </div>
          </div>

          <label for="event-organizer" class="col-sm-2 col-form-label">Organizer</label>
          <div class="col-sm-10 col-lg-4">
            <user-lookup :value="event.organizer" v-model="event.organizer"></user-lookup>
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
            <input v-model="event.location" type="text" class="form-control" id="event-location"
               placeholder="None on record">
          </div>
          <label for="event-anonymousrsvp-buttons" class="col-sm-2 col-form-label">Anonymous RSVP<span
              style="color:red">*</span></label>
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
            </div>
            <div class="invalid-feedback">
              Cost must be a number
            </div>
          </div>
        </div>

        <div class="form-group">
          <button type="submit" class="btn btn-primary">Save Changes</button>
          <a class="btn btn-secondary" href="/admin/events">Cancel</a>
          <button type="button" class="btn btn-danger" @click="deletePrompt">Delete</button>
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
        <div class="tab-pane show active" id="rsvps">
          <h3>RSVPs</h3>

          <datatable id="rsvp-admin-table"
           :data-object="event.rsvps"
           :columns="rsvpTableConfig">
          </datatable>
        </div>

        <div class="tab-pane" id="attendance">
          <h3>Attendance</h3>
          <button type="button" class="btn btn-primary btn-above-table" data-toggle="modal" data-target="#attendanceModal">Record Attendance</button>
          <attendance-modal
            id="attendanceModal"
            :attendableId="this.eventId"
            attendableType="App\Event">
          </attendance-modal>
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
import { required, numeric } from 'vuelidate/lib/validators';

export default {
  name: 'editEventForm',
  props: ['eventId'],
  data() {
    return {
      event: {},
      feedback: '',
      hasError: false,
      dataUrl: '',
      baseUrl: '/api/v1/events/',
      rsvpTableConfig: [
        { title: 'ID', data: 'id' },
        { title: 'User', data: 'user_id' },
        { title: 'Response', data: 'response' },
        { title: 'Source', data: 'source' },
        { title: 'Time', data: 'created_at' },
      ],
      attendance: [],
      attendanceQuery: {
        attendable_type: 'App\\Event',
        attendable_id: this.eventId,
      },
      attendanceUrl: '/api/v1/attendance/search',
      attendanceTableConfig: [
        { title: 'Time', data: 'created_at' },
        {
          title: 'Last Name',
          data: null,
          render: function(data, type, row) {
            try {
              return data.attendee.last_name;
            } catch (e) {
              return 'Non-member';
            }
          },
        },
        {
          title: 'First Name',
          data: null,
          render: function(data, type, row) {
            try {
              return data.attendee.first_name;
            } catch (e) {
              return '';
            }
          },
        },
        { title: 'GTID', data: 'gtid' },
      ],
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
  mounted() {
    this.dataUrl = this.baseUrl + this.eventId;
    axios
      .get(this.dataUrl)
      .then(response => {
        this.event = response.data.event;
      })
      .catch(response => {
        console.log(response);
        swal(
          'Connection Error',
          'Unable to load data. Check your internet connection or try refreshing the page.',
          'error'
        );
      });

    axios
      .post(this.attendanceUrl, this.attendanceQuery)
      .then(response => {
        this.attendance = response.data.attendance;
      })
      .catch(response => {
        console.log(response);
        swal(
          'Connection Error',
          'Unable to load data. Check your internet connection or try refreshing the page.',
          'error'
        );
      });

    // Listen for bootstrap modal close to reload attendance
    // TODO: Find a better way in Vue to do this w/o jQuery
    $('#attendanceModal').on('hidden.bs.modal', this.updateAttendance);
  },
  methods: {
    submit() {
      if (this.$v.$invalid) {
        this.$v.$touch();
        return;
      }

      let updatedEvent = this.event;
      delete updatedEvent.rsvps;

      //Delete these as they're computed by Eloquent
      delete updatedEvent.organizer_id;
      delete updatedEvent.organizer_name;
      //Set organizer_id to the id from the selected object
      updatedEvent.organizer_id = updatedEvent.organizer.id;

      axios
        .put(this.dataUrl, updatedEvent)
        .then(response => {
          this.hasError = false;
          this.feedback = 'Saved!';
          console.log('success');
        })
        .catch(response => {
          this.hasError = true;
          this.feedback = '';
          console.log(response);
          swal('Error', 'Unable to save data. Check your internet connection or try refreshing the page.', 'error');
        });
    },
    updateAttendance() {
      axios
        .post(this.attendanceUrl, this.attendanceQuery)
        .then(response => {
          this.attendance = response.data.attendance;
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
    deletePrompt() {
      let self = this;
      swal({
        title: 'Are you sure?',
        text: 'Once deleted, you will not be able to recover this event!',
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
        focusCancel: true,
        confirmButtonColor: '#dc3545',
      }).then(result => {
        if (result.value) {
          self.deleteEvent();
        }
      });
    },
    deleteEvent() {
      axios
        .delete(this.dataUrl)
        .then(response => {
          this.hasError = false;
          swal({
            title: 'Deleted!',
            text: 'The event has been deleted.',
            type: 'success',
            timer: 3000,
          }).then(result => {
            if (result.value) {
              window.location.href = '/admin/events';
            }
          });
        })
        .catch(error => {
          this.hasError = true;
          this.feedback = '';
          if (error.response.status == 403) {
            swal({
              title: 'Whoops!',
              text: "You don't have permission to perform that action.",
              type: 'error',
            });
          } else {
            swal(
              'Error',
              'Unable to process data. Check your internet connection or try refreshing the page.',
              'error'
            );
          }
        });
    },
  },
};
</script>

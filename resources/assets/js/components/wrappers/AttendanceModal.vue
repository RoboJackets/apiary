<template>
    <div class="modal fade" :id="id" tabindex="-1" role="dialog" :aria-labelledby="id" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" :id="id">Record Attendance</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="recordAttendanceForm" v-on:submit.prevent="submit">
                    <div class="row">
                        <div class="col-12">
                            <em>Swipe a BuzzCard or enter a GTID number</em>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12 col-lg-9">
                            <input  v-model="attendance.gtid"
                                    type="text"
                                    class="form-control"
                                    :class="{ 'is-invalid': $v.attendance.gtid.$error }"
                                    ref="input">
                        </div>
                        <div class="col-sm-3">
                            <button type="submit" class="btn btn-primary" @click="submit">Submit</button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <em><span v-bind:class="{ 'text-danger': hasError}"> {{feedback}} </span></em>
                        </div>
                    </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { required, numeric } from 'vuelidate/lib/validators';
export default {
  props: {
    id: '',
    attendableId: {
      type: [Number, String],
    },
    attendableType: {
      type: String,
    },
  },
  data() {
    return {
      attendance: {
        gtid: '',
        attendable_id: this.attendableId,
        attendable_type: this.attendableType,
        source: 'MyRoboJackets',
        includeName: 'true',
      },
      feedback: '',
      baseUrl: '/api/v1/attendance',
      hasError: false,
    };
  },
  watch: {
    'attendance.gtid': function(val, oldVal) {
      this.debouncedGTID();
    },
  },
  created: function() {
    this.debouncedGTID = _.debounce(this.parseGTID, 500);
  },
  methods: {
    parseGTID() {
      //Allows use of card readers that can't parse out data (#317)
      //Parses out the GTID using regex and updates the value accordingly
      let re = new RegExp('(9[0-9]{8})');
      if (!Number.isInteger(parseInt(this.attendance.gtid)) && this.attendance.gtid !== '') {
        let matches = re.exec(this.attendance.gtid);
        if (matches != null) {
          this.attendance.gtid = matches[0];
          //We have to manually submit it since the carriage return happens before the regex is run
          this.submit();
        }
      }
    },
    submit() {
      if (this.$v.$invalid) {
        this.$v.$touch();
        return;
      }

      axios
        .post(this.baseUrl, this.attendance)
        .then(response => {
          this.hasError = false;
          this.feedback = 'Saved! (' + response.data.attendance.name + ')';
          console.log('success');
          this.attendance.gtid = '';
          this.$refs.input.focus();
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
  validations: {
    attendance: {
      gtid: { required, numeric },
      attendable_type: { required },
      attendable_id: { required, numeric },
    },
  },
};
</script>

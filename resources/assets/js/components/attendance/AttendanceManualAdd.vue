<template>
    <div>
        <div class="row form-row">
            <label for="date" class="col-sm-2 col-form-label">Date<span style="color:red">*</span></label>
            <div class="col-sm-10 col-lg-4">
                <flat-pickr
                        id="date"
                        v-model="attendance.created_at"
                        placeholder="Select date"
                        :required="true"
                        :config="dateTimeConfig"
                        input-class="form-control"
                        :class="{ 'is-invalid': $v.attendance.created_at.$error }">
                </flat-pickr>
                <div class="invalid-feedback">You must select a date.</div>
            </div>
        </div>
        <div class="row form-row">
            <label for="teams-buttons" class="col-sm-2 col-form-label">Team<span style="color:red">*</span></label>
            <div class="col-sm-10 col-lg-4">
                <select id="teams-buttons" v-model="attendance.attendable_id" class="custom-select" :class="{ 'is-invalid': $v.attendance.attendable_id.$error }" @input="$v.attendance.attendable_id.$touch()">
                    <option value="" style="display:none" v-if="!teams">Loading...</option>
                    <option value="" style="display:none" v-if="teams && teams.length > 0">Select One</option>
                    <option v-for="team in teams" :value="team.value">{{team.text}}</option>
                </select>
                <div class="invalid-feedback">
                    You must select a team.
                </div>
            </div>
        </div>
        <div class="row form-row">
            <label for="gtid" class="col-sm-2 col-form-label">GTID<span style="color:red">*</span></label>
            <div class="col-sm-10 col-lg-4">
                <input
                        type="number"
                        id="gtid"
                        class="form-control"
                        :class="{ 'is-invalid': $v.attendance.gtid.$error }"
                        ref="input"
                        v-model="attendance.gtid"
                        @keyup.enter="submit"
                        @input="$v.attendance.gtid.$touch()"
                        @blur="$v.attendance.gtid.$touch()">
                <div class="invalid-feedback">
                    You must enter a GTID.
                </div>
            </div>
        </div>
        <div class="row form-row">
            <div class="col-12">
                <em><span v-bind:class="{ 'text-danger': hasError}"> {{feedback}} </span></em>
            </div>
        </div>
        <div class="row form-row">
            <button type="button" class="btn btn-primary" @click="submit">Submit</button>
            <span class="d-inline-block" id="swipes-tooltip" tabindex="0" data-toggle="tooltip" title="Select a team">
                <button type="button" class="btn btn-primary mx-2" @click.prevent="setToToday" data-toggle="modal" data-target="#attendanceModal" :disabled="!recordSwipesEnabled" :style="recordSwipesEnabled ? '' : 'pointer-events: none;'">Record Swipes</button>
            </span>
        </div>
        <attendance-modal id="attendanceModal" :attendableId="this.attendance.attendable_id" :attendableType="this.attendance.attendable_type"></attendance-modal>
    </div>
</template>
<script>
import { required, numeric, between, minLength, maxLength } from 'vuelidate/lib/validators';
import moment from 'moment';
export default {
  name: 'attendance-manual-add',
  data() {
    return {
      teams: [],
      feedback: '',
      hasError: false,
      attendance: {
        created_at: '',
        gtid: '',
        attendable_type: 'App\\Team',
        attendable_id: '',
        source: 'manual',
        includeName: true,
      },
      dateTimeConfig: {
        dateFormat: 'Y-m-d',
        enableTime: false,
        altInput: true,
        maxDate: 'today',
      },
      attendanceBaseUrl: '/api/v1/attendance',
      teamsBaseUrl: '/api/v1/teams',
      recordSwipesEnabled: false,
    };
  },
  watch: {
    'attendance.attendable_id': function(val, oldVal) {
      // Only enable the tooltip warning you to select a team when no team is selected
      this.recordSwipesEnabled = typeof this.attendance.attendable_id === 'number';
      $('#swipes-tooltip').tooltip(this.recordSwipesEnabled ? 'disable' : 'enable');
    },
  },
  methods: {
    loadTeams() {
      // Fetch teams from the API to populate buttons
      let self = this;
      axios
        .get(this.teamsBaseUrl)
        .then(response => {
          let rawTeams = response.data.teams;
          if (rawTeams.length < 1) {
            swal('Bueller...Bueller...', 'No teams found.', 'warning');
          } else {
            let alphaTeams = response.data.teams.filter(function(item) {
              return item.attendable;
            }).sort(function(a, b) {
              return a.name > b.name ? 1 : b.name > a.name ? -1 : 0;
            });
            alphaTeams.forEach(function(team) {
              self.teams.push({ value: team.id, text: team.name });
            });
          }
        })
        .catch(error => {
          if (error.response.status === 403) {
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
    submit() {
      // Submit attendance data

      if (this.$v.$invalid) {
        this.$v.$touch();
        return;
      }

      axios
        .post(this.attendanceBaseUrl, this.attendance)
        .then(response => {
          this.hasError = false;
          this.feedback = 'Saved! (' + response.data.attendance.name + ')';
          console.log('success');
          this.attendance.gtid = '';
          this.$refs.input.focus();
        })
        .catch(error => {
          console.log(error);
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
    setToToday() {
      this.attendance.created_at = moment().format('YYYY-MM-DD');
      this.attendance.gtid = '';
    },
  },
  mounted() {
    this.loadTeams();
    $('#swipes-tooltip').tooltip('enable');
  },
  validations: {
    attendance: {
      attendable_id: {
        required,
      },
      created_at: {
        required,
      },
      gtid: {
        required,
        numeric,
        minLength: minLength(9),
        maxLength: maxLength(9),
        between: between(900000000, 909999999),
      },
    },
  },
};
</script>
<style scoped>
.form-row {
  padding-bottom: 10px;
}
</style>

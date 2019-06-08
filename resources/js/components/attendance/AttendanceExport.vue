<template>
    <div>
        <div class="row form-row">
            <label for="teams-buttons" class="col-sm-2 col-form-label">Team<span style="color:red">*</span></label>
            <div class="col-sm-10 col-lg-4">
                <custom-radio-buttons
                        v-model="attendance.attendable_id"
                        :options="teams"
                        id="teams-buttons"
                        :is-error="$v.attendance.attendable_id.$error"
                        @input="$v.attendance.attendable_id.$touch()">
                </custom-radio-buttons>
                <div class="invalid-feedback">
                    You must select a team.
                </div>
            </div>
        </div>
        <div class="row form-row">
            <label for="start-date" class="col-sm-2 col-form-label">Start Date<span style="color:red">*</span></label>
            <div class="col-sm-10 col-lg-4">
                <flat-pickr
                        id="start-date"
                        v-model="attendance.start_date"
                        placeholder="Select start date"
                        :required="true"
                        :config="dateTimeConfig"
                        input-class="form-control"
                        :class="{ 'is-invalid': $v.attendance.start_date.$error }"
                >
                </flat-pickr>
                <div class="invalid-feedback">You must select a date.</div>
            </div>
        </div>
        <div class="row form-row">
            <label for="end-date" class="col-sm-2 col-form-label">End Date</label>
            <div class="col-sm-10 col-lg-4">
                <flat-pickr
                        id="end-date"
                        v-model="attendance.end_date"
                        placeholder="Defaults to current date"
                        :required="true"
                        :config="dateTimeConfig"
                        input-class="form-control">
                </flat-pickr>
            </div>
        </div>
        <div class="row form-row">
            <button type="button" class="btn btn-primary" v-on:click="loadAttendance">Submit</button>
        </div>
        <hr>
        <div id="attendance-table-div">
            <datatable id="attendance-export-table"
                       :data-object="attendanceTableData"
                       :columns="attendanceTableConfig">
            </datatable>
        </div>
    </div>
</template>

<script>
import { required, numeric } from 'vuelidate/lib/validators';
import moment from 'moment';
export default {
  name: 'attendance-export',
  data() {
    return {
      teams: [],
      attendance: {
        attendable_type: 'App\\Team',
        attendable_id: '',
        start_date: '',
        end_date: '',
      },
      attendanceBaseUrl: '/api/v1/attendance',
      teamsBaseUrl: '/api/v1/teams',
      dateTimeConfig: {
        dateFormat: 'Y-m-d',
        enableTime: false,
        altInput: true,
      },
      attendanceTableData: [],
      attendanceTableConfig: [
        {
          title: 'Date',
          data: null,
          render: function(data, type, row) {
            try {
              let date = data.created_at;
              return date.substr(0, date.indexOf(' '));
            } catch (e) {
              return '';
            }
          },
        },
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
        {
          title: 'GTID',
          data: null,
          render: function(data, type, row) {
            try {
              return data.gtid;
            } catch (e) {
              return '';
            }
          },
        },
      ],
    };
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
            let alphaTeams = response.data.teams.sort(function(a, b) {
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
    loadAttendance() {
      if (this.$v.$invalid) {
        this.$v.$touch();
        return;
      }

      let self = this;
      axios
        .post(this.attendanceBaseUrl + '/search', this.attendance)
        .then(response => {
          self.attendanceTableData = response.data.attendance;
        })
        .catch(error => {
          console.log(error);
          swal('Error', 'Unable to process data. Check your internet connection or try refreshing the page.', 'error');
        });

      //Sort the table by date then by last name per CoC request
      let table = $('#attendance-export-table').DataTable();
      table.order([0, 'asc'], [1, 'asc']);

      //Set the export file name (MAGIC!)
      let team_name = 'core';
      let end_date = this.attendance.end_date != '' ? this.attendance.end_date : moment().format('YYYY-MM-DD');
      let file_name = team_name + '_' + this.attendance.start_date + '_' + end_date;
      console.log(file_name);
      new $.fn.dataTable.Buttons(table, {
        buttons: [
          'copy',
          'excel',
          'print',
          {
            extend: 'csv',
            text: 'CSV',
            filename: file_name,
          },
        ],
      });
    },
  },
  mounted() {
    this.loadTeams();
  },
  validations: {
    attendance: {
      attendable_id: {
        required,
      },
      start_date: {
        required,
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

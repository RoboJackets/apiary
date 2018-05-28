<template>
    <div class="row">
        <template v-for="team in teams">
            <div class="col-sm-12 col-md-4" style="padding-top:50px">
                <!-- Yes, this is _supposed_ to be a div. Don't make it a button. -->
                <div class="btn btn-kiosk btn-secondary" :id="team.id" v-on:click="clicked">
                    {{ team.name }}
                </div>
            </div>
        </template>
    </div>
</template>

<script>
export default {
  data() {
    return {
      attendance: {
        gtid: '',
        attendable_id: '',
        attendable_type: 'App\\Team',
        source: 'kiosk',
        includeName: true,
      },
      attendanceBaseUrl: '/api/v1/attendance',
      teamsBaseUrl: '/api/v1/teams',
      teams: [],
    };
  },
  mounted() {
    //Remove focus from button
    document.activeElement.blur();
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + localStorage.getItem('api_token');
    this.loadTeams();
  },
  methods: {
    loadTeams() {
      // Fetch teams from the API to populate buttons
      axios
        .get(this.teamsBaseUrl)
        .then(response => {
          let rawTeams = response.data.teams;
          if (rawTeams.length < 1) {
            swal('Bueller...Bueller...', 'No teams found.', 'warning');
          } else {
            this.teams = response.data.teams.sort(function(a, b) {
              return a.name > b.name ? 1 : b.name > a.name ? -1 : 0;
            });
            this.startListening();
          }
        })
        .catch(error => {
          if (error.response.status === 403) {
            swal({
              title: 'Whoops!',
              text: "You don't have permission to perform that action.",
              type: 'error',
            });
          } else if (error.response.status === 401) {
            this.tokenPrompt();
          } else {
            swal(
              'Error',
              'Unable to process data. Check your internet connection or try refreshing the page.',
              'error'
            );
          }
        });
    },
    tokenPrompt() {
      // Prompt for API token to be stored in local browser storage
      let self = this;

      swal({
        title: 'Authentication',
        text: 'Please provide an API token to process data',
        input: 'text',
      }).then(result => {
        if (result === false) return false;
        if (result === '') {
          swal.showValidationError('Token field is required!');
          return false;
        }
        localStorage.setItem('api_token', result.value);
        axios.defaults.headers.common['Authorization'] = 'Bearer ' + localStorage.getItem('api_token');
        swal.close();
        self.loadTeams();
      });
    },
    startListening() {
      //Remove focus from button
      document.activeElement.blur();
      // Listen for keystrokes from card swipe (or keyboard)
      let buffer = '';
      window.addEventListener(
        'keypress',
        function(e) {
          if (this.attendance.attendable_id == '' && e.key == 'Enter') {
            //Enter was pressed but a team was not picked
            buffer = '';
            swal({
              title: 'Whoops!',
              text: 'Please select a team before swiping your BuzzCard',
              type: 'warning',
              timer: 2000,
            });
          } else if (e.key != 'Enter') {
            //A key that's not enter was pressed
            buffer += e.key;
          } else {
            //Enter was pressed
            this.cardPresented(buffer);
            buffer = '';
          }
        }.bind(this)
      );
    },
    clicked: function(event) {
      //Remove focus from button
      document.activeElement.blur();
      // When a team button is clicked, show a prompt to swipe BuzzCard
      let self = this;
      self.attendance.attendable_id = event.target.id;
      swal({
        title: 'Swipe your BuzzCard now',
        showCancelButton: true,
        closeOnCancel: false,
        allowOutsideClick: true,
        showConfirmButton: false,
        imageUrl: '/img/swipe-horiz-up.gif',
        imageWidth: 450,
      }).then(result => {
        if (!result.value) {
          self.clearFields();
          swal.close();
        }
      });
    },
    cardPresented: function(cardData) {
      // Card is presented, process the data
      let self = this;
      console.log('first cardData: ' + cardData);

      let pattTrackRaw = new RegExp('=(9[0-9]+)=');
      let pattError = new RegExp('[%;+][eE]\\?');

      if (this.isNumeric(cardData) && cardData.length == 9 && cardData[0] == '9') {
        // Numeric nine-digit number starting with a nine
        this.attendance.gtid = cardData;
        console.log('numeric cardData: ' + cardData);
        cardData = null;
        this.submit();
      } else if (pattTrackRaw.test(cardData)) {
        // Raw (unformatted) data from track 2 of the magnetic stripe
        let data = pattTrackRaw.exec(cardData)[1];
        console.log('raw cardData: ' + data);
        cardData = null;
        this.attendance.gtid = data;
        this.submit();
      } else if (pattError.test(cardData)) {
        // Error message sent from card reader
        console.log('error cardData: ' + pattError.exec(cardData));
        cardData = null;
        swal({
          title: 'Hmm...',
          text: 'There was an error reading your card. Please swipe again.',
          showCancelButton: true,
          showConfirmButton: false,
          type: 'warning',
        }).then(result => {
          if (!result.value) {
            self.clearFields();
            swal.close();
          }
        });
      } else {
        swal.close();
        console.log('unknown cardData: ' + cardData);
        cardData = null;
        swal({
          title: 'Hmm...',
          html: 'Card format not recognized.<br/>Contact #it-helpdesk for assistance.',
          showConfirmButton: true,
          type: 'error',
          timer: 3000,
        }).then(result => {
          self.clearFields();
          swal.close();
        });
      }
    },
    submit() {
      // Submit attendance data
      axios
        .post(this.attendanceBaseUrl, this.attendance)
        .then(response => {
          this.hasError = false;
          this.clearFields();
          swal({
            title: "You're in!",
            text: 'Nice to see you, ' + response.data.attendance.name + '.',
            timer: 2000,
            showConfirmButton: false,
            type: 'success',
          });
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
    clearFields() {
      //Remove focus from button
      document.activeElement.blur();
      this.attendance.attendable_id = '';
      this.attendance.gtid = '';
      console.log('fields cleared');
    },
    isNumeric(n) {
      return !isNaN(parseFloat(n)) && isFinite(n);
    },
  },
};
</script>

<style scoped>
/* Combination of btn-lg and btn-block with some customizations */
.btn-kiosk {
  min-height: 250px;
  font-weight: bolder;
  font-size: 2.75rem;
  width: 100%;
  padding: 0.5rem 1rem;
  line-height: 1.5;
  border-radius: 0;
  /* Vertically Center Text */
  display: flex;
  justify-content: center;
  align-items: center;
}
</style>

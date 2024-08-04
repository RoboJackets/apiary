<template>
  <div>
    <div v-show="currentStepName == 'dues-required-info'">
      <dues-required-info :user="user" @next="next" @back="back">
      </dues-required-info>
    </div>

    <div v-show="currentStepName == 'dues-additional-info'">
      <dues-additional-info :user="user" @next="next" @back="back"></dues-additional-info>
    </div>

    <div v-show="currentStepName == 'dues-demographics-info'">
      <demographics :user="user" @next="next" @back="back"></demographics>
    </div>

    <div v-show="currentStepName == 'join-teams'">
      <div class="row">
        <div class="col-12">
          <h3>Team Membership</h3>
        </div>
      </div>
      <div class="row">
        <div class="col-12">
          <p>This is the list of teams we know you're on. If this isn't accurate, update it here. You can update this at any point by visiting the teams page linked above.</p>
        </div>
      </div>
      <div class="row">
        <team-card v-for="team in teams" v-if="team.visible" :team="team" :user="user" :user-teams="userTeams" :key="team.id"></team-card>
      </div>
      <div class="row">
        <div class="col-6">
          <button @click="back" class="btn btn-secondary float-left">Back</button>
        </div>
        <div class="col-6">
          <button @click="next" class="btn btn-primary float-right">Finish</button>
        </div>
      </div>
    </div>

    <div v-show="currentStepName == 'dues-payment-instructions'">
      <payment-instructions>
        <p>
          To complete your registration, make a payment using one of the following methods.
        </p>
      </payment-instructions>
    </div>
  </div>
</template>


<script type="text/javascript">
/*
   *  @props
   */

export default {
  props: ['userUid', 'userTeams'],
  data() {
    return {
      steps: [
        'dues-required-info',
        'dues-additional-info',
        'dues-demographics-info',
        'join-teams',
        'dues-payment-instructions',
      ],
      currentStep: 0,
      user: {},
      dataUrl: '',
      baseUrl: '/api/v1/users/',
      teamsUrl: '/api/v1/teams',
      teams: {},
    };
  },
  mounted() {
    this.dataUrl = this.baseUrl + this.userUid;
    axios
      .get(this.dataUrl)
      .then(response => {
        this.user = response.data.user;
      })
      .catch(response => {
        console.log(response);
        Swal.fire(
          'Connection Error',
          'Unable to load data. Check your internet connection or try refreshing the page.',
          'error'
        );
      });
    axios
      .get(this.teamsUrl)
      .then(response => {
        this.teams = response.data.teams.sort(function(a, b) {
          return a.visible_on_kiosk && !b.visible_on_kiosk ? -1 :
                b.visible_on_kiosk && !a.visible_on_kiosk ? 1 :
                a.name > b.name ? 1 : b.name > a.name ? -1 : 0;
        });
      })
      .catch(response => {
        console.log(response);
        Swal.fire(
          'Connection Error',
          'Unable to load data. Check your internet connection or try refreshing the page.',
          'error'
        );
      });
  },
  methods: {
    next: function() {
      if (this.currentStep < this.steps.length) {
        //transition
        this.currentStep = this.currentStep + 1;
        $('html, body').animate({ scrollTop: 0 }, 'slow');
      } else {
        warn('No more steps');
      }
    },
    back: function() {
      if (this.currentStep > 0) {
        //transition
        this.currentStep = this.currentStep - 1;
        $('html, body').animate({ scrollTop: 0 }, 'slow');
      } else {
        warn('No step to go back to');
      }
    },
  },
  computed: {
    currentStepName: function() {
      try {
        return this.steps[this.currentStep];
      } catch (e) {
        return '';
      }
    },
  },
};
</script>

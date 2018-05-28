<template>
  <div>
    <div v-if="currentStepName == 'dues-required-info'">
      <dues-required-info :user="user" @next="next">
      </dues-required-info>
    </div>

    <div v-if="currentStepName == 'safety-agreement'">
      <safety-agreement :user-uid="user.uid" @next="next"></safety-agreement>
    </div>

    <div v-if="currentStepName == 'dues-additional-info'">
      <dues-additional-info :user="user" @next="next"></dues-additional-info>
    </div>

    <div v-if="currentStepName == 'dues-demographics-info'">
      <demographics :user="user" @next="next"></demographics>
    </div>

    <div v-if="currentStepName == 'dues-payment-instructions'">
      <payment-instructions>
        <p>
          To complete your annual registration, please submit the SGA-mandated dues payments. RoboJackets dues are $100 for the year or $55 for the semester.
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
  props: ['userUid'],
  data() {
    return {
      steps: [
        'dues-required-info',
        'safety-agreement',
        'dues-additional-info',
        'dues-demographics-info',
        'dues-payment-instructions',
      ],
      currentStep: 0,
      user: {},
      dataUrl: '',
      baseUrl: '/api/v1/users/',
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
        swal(
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

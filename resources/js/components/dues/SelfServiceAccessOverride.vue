<template>
  <div class="col-sm-6 com-md-3 col-lg-4"
       v-if="!loading && eligibility && (eligibility.eligible || eligibility.user_rectifiable)">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title">
          New Member Access
        </h4>
        <p class="card-text">
          As a new member, you can request to temporarily use RoboJackets services without
          paying dues until {{ overrideUntil }}.
        </p>
        <button class="btn btn-link p-0" @click="requestOverride()"
                v-if="!loading && !submittingRequest && eligibility && eligibility.eligible">Request Access
        </button>
        <p v-else-if="!submittingRequest" class="card-text">
          Before you can request access, you must complete the following
          task{{ unfinishedTasks.length === 1 ? '' : 's' }}:
        </p>
        <loading-spinner v-else text="Submitting request..."/>
        <ul v-if="unfinishedTasks.length">
          <li v-for="task in unfinishedTasks">{{ task }}</li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script>
import Toast from "../../mixins/Toast";
import Moment from "moment";

const OVERRIDE_ENDPOINT = '/api/v1/user/override/self';

export default {
  name: "SelfServiceAccessOverride",
  mixins: {
    Toast
  },
  mounted() {
    axios.post(OVERRIDE_ENDPOINT,
      {
        preview: true
      })
      .then(response => {
        this.eligibility = response.data;
      })
      .catch(e => {
        console.error(e);
      })
      .finally(() => {
        this.loading = false;
      })
  },
  data() {
    return {
      loading: true,
      submittingRequest: false,
      eligibility: null,
    }
  },
  computed: {
    unfinishedTasks() {
      const tasks = this.eligibility.tasks;
      return Object.keys(this.eligibility.tasks).filter(task => !tasks[task]);
    },
    unmetConditions() {
      const conditions = this.eligibility.conditions;
      return Object.keys(this.eligibility.conditions).filter(condition => !conditions[condition]);
    },
    overrideUntil() {
      return new Moment(this.eligibility.override_until).format("MMMM D, Y")
    }
  },
  methods: {
    requestOverride() {
      this.submittingRequest = true;
      axios.post(OVERRIDE_ENDPOINT, { preview: false })
        .then(response => {
          this.eligibility = response.data; // If the user has somehow become ineligible since loading the page, we
          // need to use the updated data
          console.log(response.data);
          if (response && response.data) {
            if (response.data.eligible) {
              Swal.fire({
                icon: 'success',
                title: 'Access Granted!',
                text: `You now have temporary access to RoboJackets services until ${this.overrideUntil}`,
                confirmButtonText: 'Continue'
              }).then(() => {
                window.location.reload();
              })
            } else {
              Swal.fire({
                icon: 'error',
                title: 'Unable to Grant Access',
                html: `Post in #it-helpdesk for assistance. <br /><br />
                         <small><strong>Reason:</strong> ${response.data.reason}</small><br />
                         <small><strong>Unmet conditions:</strong> ${this.unmetConditions.join(', ') || 'None' }</small><br />
                         <small><strong>Unfinished tasks:</strong> ${this.unfinishedTasks.join(', ') || 'None' }</small>
                  `
              })
            }
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Unable to Grant Access',
              text: 'An unknown error occurred while requesting access. Post in #it-helpdesk for assistance.'
            })
          }
        })
        .catch(e => {
          Swal.fire(
            'Connection Error',
            'Unable to request access. Check your internet connection or try refreshing the page.',
            'error'
          );
        })
        .finally(() => {
          this.submittingRequest = false;
        })
    }
  }
}
</script>

<style scoped>

</style>

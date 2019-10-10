<template>
    <div class="modal fade" :id="id" tabindex="-1" role="dialog" :aria-labelledby="id" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" :id="id">Add Members</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="inviteMemberForm" v-on:submit.prevent="submit">
                        <div class="row">
                            <div class="col-12">
                                <em>Enter a name (any part) to search...</em>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 col-lg-9">
                                <user-lookup v-model="user"></user-lookup>
                            </div>
                            <div class="col-sm-3">
                                <button type="button" class="btn btn-primary" @click="submit">Submit</button>
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
    teamId: {
      type: [Number, String],
    },
  },
  data() {
    return {
      user: '',
      member: {
        user_id: '',
        action: 'join',
      },
      feedback: '',
      baseUrl: '/api/v1/teams/',
      hasError: false,
    };
  },
  methods: {
    submit() {
      if (this.$v.$invalid) {
        this.feedback = 'Validation Error';
        this.hasError = true;
        this.$v.$touch();
        return;
      }

      let membersUrl = this.baseUrl + this.teamId + '/members';
      this.member.user_id = this.user.id;

      axios
        .post(membersUrl, this.member)
        .then(response => {
          this.hasError = false;
          this.feedback = 'Added ' + response.data.member + '!';
          this.member.user_id = '';
          this.user = '';
        })
        .catch(error => {
          console.log('error: ' + error);
          this.hasError = true;
          this.feedback = 'An error occurred!';
          if (error.response.status == 403) {
            Swal.fire({
              title: 'Whoops!',
              text: "You don't have permission to perform that action.",
              type: 'error',
            });
          } else {
            Swal.fire(
              'Error',
              'Unable to process data. Check your internet connection or try refreshing the page.',
              'error'
            );
          }
        });
    },
  },
  validations: {
    user: { required },
    member: {
      action: { required },
    },
  },
};
</script>

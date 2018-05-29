<template>
    <div>
        <button
                type="button" class="btn btn-secondary"
                :disabled="!this.user.is_active || !this.team.self_serviceable"
                v-if="memberOfTeam" v-on:click="changeMembership('leave')">
            Leave
        </button>
        <button
                type="button" class="btn btn-primary"
                :disabled="!this.user.is_active || !this.team.self_serviceable"
                v-else v-on:click="changeMembership('join')">
            Join
        </button>
        <em v-if="!this.user.is_active">&nbsp;&nbsp;<small>You must be an active member to join/leave teams.</small></em>
        <em v-if="!this.team.self_serviceable">&nbsp;&nbsp;<small>Join/leave is restricted for this team.</small></em>
    </div>
</template>
<script>
export default {
  props: {
    team: {},
    user: {},
  },
  data() {
    return {
      baseUrl: '/api/v1/teams/',
      user_teams: this.user.teams,
    };
  },
  computed: {
    memberOfTeam: function() {
      let self = this;
      return this.user_teams.some(function(val) {
        return val.id === self.team.id;
      });
    },
  },
  methods: {
    changeMembership: function(action) {
      let data = {
        user_id: this.user.id,
        team_id: this.team.id,
        action: action,
      };
      axios
        .post(this.baseUrl + this.team.id + '/members', data)
        .then(response => {
          if (response.status === 201 && data.action === 'join') {
            swal({
              type: 'success',
              title: 'Sweet!',
              text: 'You joined ' + this.team.name + '!',
              timer: 1500,
            });
            this.user_teams.push(response.data.team);
          } else if (response.status === 201 && data.action === 'leave') {
            swal({
              type: 'success',
              title: 'See you soon!',
              text: 'You left ' + this.team.name + '.',
              timer: 1500,
            });
            let index = this.user_teams.findIndex(t => t.id === this.team.id);
            if (index > -1) {
              this.user_teams.splice(index, 1);
            }
          } else {
            console.log(response);
            swal('Whoops!', 'Something went wrong. Please try again later.', 'error');
          }
        })
        .catch(response => {
          console.log(response);
          swal(
            'Connection Error',
            'Unable to modify team membership. Check your internet connection or try refreshing the page.',
            'error'
          );
        });
    },
  },
};
</script>
<style scoped>
.row {
  padding-bottom: 10px;
}
</style>

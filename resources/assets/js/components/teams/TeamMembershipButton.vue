<template>
    <div>
        <button v-if="memberOfTeam && this.user.is_active && this.team.self_serviceable"
                type="button" class="btn btn-secondary" v-on:click="changeMembership('leave')">
            Leave
        </button>
        <button v-else-if="!memberOfTeam && this.user.is_active && this.team.self_serviceable"
                type="button" class="btn btn-primary" v-on:click="changeMembership('join')">
            Join
        </button>
        <em v-else-if="this.user.is_active && !this.team.self_serviceable">&nbsp;&nbsp;
            <small>Contact an admin to {{ this.actionVerb }} this team.</small>
        </em>
        <em v-else-if="!this.user.is_active">&nbsp;&nbsp;
            <small>You must be an active member to {{ this.actionVerb }} teams.</small>
        </em>
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
    actionVerb: function() {
      return this.memberOfTeam ? 'leave' : 'join';
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

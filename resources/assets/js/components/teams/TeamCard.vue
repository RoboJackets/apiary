<template>
    <div class="col-sm-12 col-md-6 col-lg-4 card-teams">
        <div class="card mb-3 h-100">
            <div class="card-body" style="padding-bottom: 0">
                <h5 class="card-title"><b>{{ team.name }}</b></h5>
                <p class="card-text">{{ team.description }}</p>
                <template v-if="team.mailing_list_name || team.slack_channel_id">
                    <hr/>
                    <p class="card-text">
                    <template v-if="team.mailing_list_name">
                        Mailing List: <a :href="'https://lists.gatech.edu/sympa/info/' + team.mailing_list_name" target="_blank">{{ team.mailing_list_name }}</a>
                        <br/>
                    </template>
                    <template v-if="team.slack_channel_id">
                        Slack: <a :href="'https://slack.com/app_redirect?team=T033JPZLT&channel=' + team.slack_channel_id" target="_blank">{{ team.slack_channel_name }}</a>
                    </template>
                    </p>
                </template>
            </div>
            <div class="card-footer" style="min-height: 63px">
                <team-membership-button :team="team" :user="user"></team-membership-button>
            </div>
        </div>
    </div>
</template>
<script>
export default {
  props: {
    team: {},
    user: {},
  },
};
</script>

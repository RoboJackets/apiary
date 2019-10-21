<template>
  <div class="row">
    <div class="col-12">
      <div class="alert alert-danger" role="alert" v-if="message" v-html="messageText"></div>
      <form id="resumeUploadForm" enctype="multipart/form-data" method="post" :action="actionUrl">
        <input type="hidden" name="redirect" value="true">

        <p v-if="hasResume">You last uploaded your r&eacute;sum&eacute; on {{ resumeDate }}. You can view it <a :href="actionUrl">here</a>. If you would like to delete it, please ask in #it-helpdesk in Slack.</p>
        <p v-else>You do not have a r&eacute;sum&eacute; on file. You may have uploaded one previously, but they are deleted semesterly to ensure they're always accurate.</p>

        <div class="form-group row">
          <label for="user-preferredname" class="col-sm-2 col-form-label">R&eacute;sum&eacute;</label>
          <div class="col-sm-10 col-lg-4">
            <div class="input-group mb-3">
              <div class="custom-file">
                <input type="file" class="custom-file-input" id="resume" name="resume">
                <label class="custom-file-label" for="resume">Choose file...</label>
              </div>
            </div>
          </div>
        </div>

        <div class="form-group">
          <button type="submit" class="btn btn-primary">Upload</button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import moment from 'moment';
export default {
  props: ['userUid', 'message'],
  data() {
    return {
      user: {},
      feedback: '',
      hasError: false,
      dataUrl: '',
      baseUrl: '/api/v1/users/',
      actionUrl: '',
    };
  },
  mounted() {
    this.dataUrl = this.baseUrl + this.userUid;
    this.actionUrl = this.baseUrl + this.userUid + '/resume';
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
  },
  computed: {
    hasResume: function() {
      return !!this.user.resume_date;
    },
    resumeDate: function() {
      if (!this.hasResume) return '';
      return moment(this.user.resume_date).format('dddd, MMMM Do, YYYY');
    },
    messageText: function() {
      if (!this.message || this.message.length == 0) return '';

      var messages = {
        'resume_not_one_page': 'Your r&eacute;sum&eacute; must be one page long.',
        'resume_not_pdf': 'Your r&eacute;sum&eacute; must be a PDF.',
        'inactive': 'You must be an active member to upload your r&eacute;sum&eacute;.',
        'resume_required': 'You must attach a r&eacute;sum&eacute; to upload.',
      };
      return messages[this.message] || 'An unknown error occurred.';
    }
  },
};
</script>

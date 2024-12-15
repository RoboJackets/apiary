<template>
  <div class="row">
    <div class="col-12" v-if="loaded && user.is_active && user.is_student">
      <div class="alert alert-danger" role="alert" v-if="message" v-html="messageText"></div>
      <p v-if="hasResume && viewUrl">You last uploaded your resume on {{ resumeDate }}. You can view it <a
        :href="viewUrl">here</a>. If you would like to delete it, ask in #it-helpdesk in Slack.</p>
      <p v-else>You do not have a resume on file. You may have uploaded one previously, but they are deleted semesterly
        to ensure they're always accurate.</p>
      <p>Your resume must be a one page PDF. The maximum file size is 1MB.</p>

      <div class="mb-3 row">
        <label for="resume" class="col-sm-2 col-form-label">Resume</label>
          <input type="file" class="form-control" id="resume" name="resume" accept="application/pdf"
                     v-on:change="fileChange">
      </div>

      <div class="mb-3">
        <button class="btn btn-primary" :disabled="uploading || !selectedFile" v-on:click="onSubmit">{{ uploading ? "Uploading file..." : "Upload" }}</button>
      </div>
    </div>
    <div class="col-12" v-else-if="loaded && user.is_student">
      <strong>Resume upload unavailable</strong>
      <p>A benefit of being an active member of RoboJackets is being a part of our resume book we provide to sponsors.
        Once you pay dues, you will be able to upload your resume here.</p>
    </div>
    <div class="col-12" v-else-if="loaded">
      <strong>Resume upload unavailable</strong>
      <p>Only students are eligible for the RoboJackets resume book. If you believe you are seeing this message in
        error, ask in #it-helpdesk.
      </p>
    </div>
    <loading-spinner :active="!loaded" />
  </div>
</template>

<script>
import moment from 'moment';
import FileUploader from "../mixins/FileUploader";

export default {
  props: ['userUid', 'message'],
  mixins: [
    FileUploader
  ],
  data() {
    return {
      user: {},
      feedback: '',
      hasError: false,
      dataUrl: '',
      baseUrl: '/api/v1/users/',
      actionUrl: '',
      viewUrl: '',
      fileLabel: 'Choose file...',
      selectedFile: null,
      uploading: false,
    };
  },
  mounted() {
    this.dataUrl = this.baseUrl + this.userUid;
    this.viewUrl = `users/${this.userUid}/resume`;
    this.actionUrl = this.baseUrl + this.userUid + '/resume';
    axios
      .get(this.dataUrl)
      .then(response => {
        this.user = response.data.user;
      })
      .catch(response => {
        Swal.fire(
          'Connection Error',
          'Unable to load data. Check your internet connection or try refreshing the page.',
          'error'
        );
      });
  },
  computed: {
    hasResume: function () {
      return !!this.user.resume_date && this.loaded;
    },
    resumeDate: function () {
      if (!this.hasResume) return '';
      return moment(this.user.resume_date).format('dddd, MMMM Do, YYYY');
    },
    loaded: function () {
      // Pick an attribute users will always have
      return !!this.user.name;
    },
  },
  methods: {
    fileChange: function (e) {
      if (e.target.files.length > 0) {
        const file = e.target.files[0];
        if (file) {
          this.fileLabel = file.name || 'Choose file...';
          this.selectedFile = file
        }
      }
    },
    onSubmit: function (event) {
      this.uploading = true;
      const formData = new FormData()
      formData.append("resume", this.selectedFile)

      this.uploadFile(formData, this.actionUrl, null).then(() => {
          this.uploading = false;
          return Swal.fire({
            title: "Resume uploaded successfully",
            icon: "success",
          }).then(() => window.location = "/");
        }
      ).catch((e) => {
        this.uploading = false;

        const messages = {
          'resume_not_one_page': 'Your resume must be one page long.',
          'resume_not_pdf': 'Your resume must be a PDF.',
          'inactive': 'You must be an active member to upload your resume.',
          'ineligible': 'You must be a student to upload your resume.',
          'resume_required': 'You must attach a resume to upload.',
          'too_big': 'Uploaded files must be smaller than 1MB.',
        };

        let errorMsg = e.message || 'An unknown error occurred.';

        if (e.response && e.response.data && e.response.data.message) {
          errorMsg = messages[e.response.data.message];
        }

        Swal.fire({
          title: "Upload error",
          text: errorMsg,
          icon: "error",
        });
      })
    }
  },
};
</script>

<template>
  <div class="row">
    <div class="col-12">
      <form id="resumeUploadForm" enctype="multipart/form-data" method="post" :action="actionUrl">
        <input type="hidden" name="redirect" value="true">

        <p v-if="lastUpload">You last uploaded your r&eactue;sum&eacute; on {{ lastUpload }}. You can download it <a href="/resume/download">here</a>.</p>
        <p v-else>You do not have a resume on file. You may have uploaded one previously, but they are deleted semesterly to ensure they're always accurate.</p>
        <h3>Additional Information</h3>

        <div class="form-group row">
          <label for="user-preferredname" class="col-sm-2 col-form-label">R&eacute;sum&eacute;</label>
          <div class="col-sm-10 col-lg-4">
            <div class="input-group mb-3">
              <div class="custom-file">
                <input type="file" class="custom-file-input" id="resume" name="resume">
                <label class="custom-file-label" for="resume">Choose file</label>
              </div>
            </div>
          </div>
        </div>

        <div class="form-group">
          <button type="submit" class="btn btn-primary">Upload</button>
          <button type="button" class="btn btn-danger" @click="deletePrompt">Delete Existing R&eacute;sum&eacute;</button>
          <em><span v-bind:class="{ 'text-danger': hasError}"> {{feedback}} </span></em>
        </div>

      </form>
    </div>
  </div>
</template>

<script>
import { alpha, email, maxLength, required } from 'vuelidate/lib/validators';
import notGTEmail from '../customValidators/notGTEmail';

export default {
  props: ['userUid'],
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
  methods: {
    submit() {
      if (this.$v.$invalid) {
        this.$v.$touch();
        return;
      }

      delete this.user.dues;

      axios
        .put(this.dataUrl, this.user)
        .then(response => {
          this.hasError = false;
          this.feedback = 'Saved!';
          console.log('success');
        })
        .catch(response => {
          this.hasError = true;
          this.feedback = '';
          console.log(response);
          Swal.fire(
            'Connection Error',
            'Unable to save data. Check your internet connection or try refreshing the page.',
            'error'
          );
        });
    },
  },
  validations: {
    user: {
        // FIXME
    },
  },
};
</script>

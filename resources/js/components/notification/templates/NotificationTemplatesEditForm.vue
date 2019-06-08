<template>
    <div class="row">
        <div class="col-12">
            <form id="notificationTemplateEditForm" v-on:submit.prevent="submit">

                <div class="form-group row">
                    <label for="name" class="col-sm-2 col-form-label">Name<span style="color:red">*</span></label>
                    <div class="col-sm-10 col-lg-4">
                        <input v-model="template.name" type="text" class="form-control"
                               :class="{ 'is-invalid': $v.template.name.$error }" id="name" @blur="$v.template.name.$touch()"
                               placeholder="None on record">
                        <small><em>Internal Use Only</em></small>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="subject" class="col-sm-2 col-form-label">Subject<span style="color:red">*</span></label>
                    <div class="col-sm-10 col-lg-4">
                        <input v-model="template.subject" type="text" class="form-control"
                               :class="{ 'is-invalid': $v.template.subject.$error }" id="subject" @blur="$v.template.subject.$touch()"
                               placeholder="None on record">
                        <small><em>Public-Facing Subject</em></small>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="body_markdown" class="col-sm-2 col-form-label">Body<span style="color:red">*</span></label>
                    <div class="col-sm-12 col-lg-6">
                        <textarea v-model="template.body_markdown" rows="5" class="form-control" id="body_markdown"
                                  placeholder="None on record"></textarea>
                        <small><em>Use <a href="https://www.markdownguide.org/basic-syntax/">Markdown</a> markup.</em></small>
                    </div>
                </div>


                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <a class="btn btn-secondary" href="/admin/notification/templates">Cancel</a>
                    <em><span v-bind:class="{ 'text-danger': hasError}"> {{feedback}} </span></em>
                </div>

            </form>
        </div>
    </div>
</template>

<script>
import { required, numeric, alphaNum } from 'vuelidate/lib/validators';
export default {
  name: 'notificationTemplatesEditForm',
  props: ['templateId'],
  mounted() {
    this.dataUrl = this.baseUrl + this.templateId;
    axios
      .get(this.dataUrl)
      .then(response => {
        this.template = response.data.template;
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
  data() {
    return {
      template: {},
      feedback: '',
      hasError: false,
      baseUrl: '/api/v1/notification/templates/',
      dateTimeConfig: {
        dateFormat: 'Y-m-d H:i:S',
        enableTime: true,
        altInput: true,
      },
      yesNoOptions: [{ value: '0', text: 'No' }, { value: '1', text: 'Yes' }],
    };
  },
  validations: {
    template: {
      name: { required },
      subject: { required },
      body_markdown: { required },
    },
  },
  methods: {
    submit() {
      if (this.$v.$invalid) {
        this.$v.$touch();
        return;
      }

      axios
        .put(this.dataUrl, this.template)
        .then(response => {
          this.hasError = false;
          this.feedback = 'Saved!';
          console.log('success');
          window.location.href = '/admin/notification/templates/' + response.data.template.id;
        })
        .catch(response => {
          this.hasError = true;
          this.feedback = '';
          console.log(response);
          swal('Error', 'Unable to save data. Check your internet connection or try refreshing the page.', 'error');
        });
    },
  },
};
</script>

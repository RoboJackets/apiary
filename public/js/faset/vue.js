/*jslint browser:true devel:true*/
/*global Vue, Worker*/

var worker = new Worker('/js/faset/worker.js');

var form = new Vue({
  el: 'form',
  data: {
    isChecked: false,
    queued: ""
  },
  computed: {
    isRequired: function () {
      'use strict';
      return this.isChecked;
    }
  },
  methods: {
    submit: function () {
      'use strict';
      var formData = new FormData(event.target), values = formData.getAll('heardfrom'), result = {};
      result['faset_email'] = formData.get('faset-email');
      result['faset_name'] = formData.get('faset-name');
      result['faset_responses'] = [];
      result['created_at'] = new Date();
      if (values.includes('other')) {
        values.splice(values.indexOf('other'), 1);
        values.push(formData.get('other'));
      }
      result['faset_responses'][0] = {"1" : values};
      worker.postMessage(JSON.stringify(result));

      swal({
        title: "Success!",
        text: "Thanks for stopping by!",
        type: "success",
        timer: 1000,
        showConfirmButton: false
      });

      setTimeout(this.focusNameInput, 1000);

      document.getElementById("form").reset();
    },
    queueUpdate: function (e) {
      'use strict';
      this.queued = e.data;
    },
    focusNameInput: function() {
      document.getElementById("faset-name").focus();
      console.log("Input Focused");
    }
  }
});

worker.addEventListener('message', form.queueUpdate, false);

window.onbeforeunload = function() {
  if (form.queued != "") {
    return form.queued;
  }
}
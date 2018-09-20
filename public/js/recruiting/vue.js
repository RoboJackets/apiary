/*jslint browser:true devel:true*/
/*global Vue, Worker*/

var worker = new Worker('/js/recruiting/worker.js');

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
      result['recruiting_email'] = formData.get('recruiting-email');
      result['recruiting_name'] = formData.get('recruiting-name');
      result['recruiting_responses'] = [];
      result['created_at'] = new Date();
      if (values.includes('other')) {
        values.splice(values.indexOf('other'), 1);
        let otherText = formData.get('heardfrom-other-text');
        if (otherText) {
          values.push(otherText);
        }
      }
      result['recruiting_responses'] = values;
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
    checkOther: function (e) {
      $('#heardfrom-other').prop("checked", true);
    },
    queueUpdate: function (e) {
      'use strict';
      this.queued = e.data;
    },
    focusNameInput: function() {
      document.getElementById("recruiting-name").focus();
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
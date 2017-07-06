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

      document.getElementById("form").reset();
    },
    queueUpdate: function (e) {
      'use strict';
      this.queued = e.data;
    }
  }
});

worker.addEventListener('message', form.queueUpdate, false);


$(document).ready(function(){
  $('#successModal').on('shown.bs.modal', function (e) {
    window.setTimeout(function(){
      $('#successModal').modal('hide')
    },
    1000);
  });
});

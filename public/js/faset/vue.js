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
            result['faset-email'] = formData.get('faset-email');
            result['faset-name'] = formData.get('faset-name');
            result['faset-questions'] = [];
            if (values.includes('other')) {
                values.splice(values.indexOf('other'), 1);
                values.push(formData.get('other'));
            }
            result['faset-questions'][0] = {"1" : values};
            worker.postMessage(JSON.stringify(result));
            alert("Your form response has been saved. Thanks for stopping by!");
            document.getElementById("form").reset();
        },
        queueUpdate: function (e) {
            'use strict';
            this.queued = e.data;
        }
    }
});

worker.addEventListener('message', form.queueUpdate, false);

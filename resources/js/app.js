/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

import Vue from 'vue'
import Bugsnag from '@bugsnag/js'
import BugsnagPluginVue from '@bugsnag/plugin-vue'

var bugsnagKey = document.head.querySelector('meta[name="bugsnag-api-key"]').content;
if (bugsnagKey) {
    Bugsnag.start({
        apiKey: bugsnagKey,
        plugins: [new BugsnagPluginVue()]
    })
}

require('./bootstrap');
var axios = require('axios');

//Import SweetAlert2 for nice alert dialogs
import Swal from 'sweetalert2';
window.Swal = Swal;

if (bugsnagKey) {
    Bugsnag.getPlugin('vue').installVueErrorHandler(Vue)
}

// Import the Vuelidate validation plugin
import Vuelidate from 'vuelidate';
Vue.use(Vuelidate);

// Import the VueSelect component
import vSelect from 'vue-select';
Vue.component('v-select', vSelect);

// Import the FlatPickr Date Component
import flatPickr from 'vue-flatpickr-component';
import 'flatpickr/dist/flatpickr.css';
Vue.use(flatPickr);

//Import Moment for friendly timestamps
import VueMoment from 'vue-moment';
import moment from 'moment';
Vue.use(VueMoment, {
    moment,
});

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

// Form Fields
Vue.component('term-input', require('./components/fields/TermInput.vue').default);
Vue.component('custom-radio-buttons', require('./components/fields/CustomRadioButtons.vue').default);
Vue.component('user-lookup', require('./components/fields/UserLookup.vue').default);

// Large Scale Components
Vue.component('resume-upload-form', require('./components/ResumeUploadForm.vue').default);
Vue.component('payment-instructions', require('./components/PaymentInstructions.vue').default);
Vue.component('user-edit-form', require('./components/UserEditForm.vue').default);

// Attendance
Vue.component('attendance-kiosk', require('./components/attendance/AttendanceKiosk.vue').default);

// Dues
Vue.component('dues-sequence', require('./components/dues/DuesSequence.vue').default);
Vue.component('dues-required-info', require('./components/dues/DuesRequiredInfo.vue').default);
Vue.component('dues-additional-info', require('./components/dues/DuesAdditionalInfo.vue').default);
Vue.component('demographics', require('./components/dues/Demographics.vue').default);

//Teams
Vue.component('team-card', require('./components/teams/TeamCard.vue').default);
Vue.component('team-membership-button', require('./components/teams/TeamMembershipButton.vue').default);

const app = new Vue({
    el: '#app',
});


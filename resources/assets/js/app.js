
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
var axios = require('axios')
var dt = require('datatables.net/js/jquery.dataTables');
/**
 *  Import DataTables for Bootstrap4 module
 */
require('datatables.net-bs4');

window.Vue = require('vue');

import flatPickr from 'vue-flatpickr-component';
import 'flatpickr/dist/flatpickr.css';
Vue.use(flatPickr);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

// Form Fields
Vue.component('term-input', require('./components/fields/TermInput.vue'));
Vue.component('custom-radio-buttons', require('./components/fields/CustomRadioButtons.vue'));

// Large Scale Components

Vue.component('datatable', require('./components/Datatable.vue'));
Vue.component('faset-admin-table', require('./components/wrappers/FasetAdminTable.vue'))
Vue.component('faset-edit-form', require('./components/FasetEditForm.vue'));
Vue.component('users-admin-table', require('./components/wrappers/UsersAdminTable.vue'))
Vue.component('user-edit-form', require('./components/UserEditForm.vue'));
Vue.component('events-admin-table', require('./components/wrappers/EventsAdminTable.vue'))
Vue.component('event-edit-form', require('./components/EventEditForm.vue'));

// Dues

Vue.component('dues-sequence', require('./components/dues/DuesSequence.vue'));
Vue.component('dues-required-info', require('./components/dues/DuesRequiredInfo.vue'));
Vue.component('safety-agreement', require('./components/dues/SafetyAgreement.vue'));
Vue.component('dues-additional-info', require('./components/dues/DuesAdditionalInfo.vue'));
Vue.component('demographics', require('./components/dues/Demographics.vue'));

const app = new Vue({
    el: '#app'
});

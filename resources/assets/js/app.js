
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
var axios = require('axios')
var dt = require('datatables.net/js/jquery.dataTables');

// Import DataTables for Bootstrap4 module
require( 'datatables.net-bs4');
require( 'jszip' );
require( 'datatables.net-buttons-bs4' );
require( 'datatables.net-buttons/js/buttons.html5.js' );
require( 'datatables.net-buttons/js/buttons.print.js' );

//Import SweetAlert2 for nice alert dialogs
import swal from 'sweetalert2'
window.swal = swal;

window.Vue = require('vue');

// Import the Vuelidate validation plugin
import Vuelidate from 'vuelidate'
Vue.use(Vuelidate);

// Import the VueSelect component
import vSelect from 'vue-select'
Vue.component('v-select', vSelect);

// Import the FlatPickr Date Component
import flatPickr from 'vue-flatpickr-component';
import 'flatpickr/dist/flatpickr.css';
Vue.use(flatPickr);

//Import Moment for friendly timestamps
import VueMoment from 'vue-moment'
import moment from 'moment'
Vue.use(VueMoment, {
    moment,
});

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

// Form Fields
Vue.component('term-input', require('./components/fields/TermInput.vue'));
Vue.component('custom-radio-buttons', require('./components/fields/CustomRadioButtons.vue'));
Vue.component('user-lookup', require('./components/fields/UserLookup.vue'));

// Large Scale Components
Vue.component('datatable', require('./components/Datatable.vue'));
Vue.component('faset-admin-table', require('./components/wrappers/FasetAdminTable.vue'));
Vue.component('faset-edit-form', require('./components/FasetEditForm.vue'));
Vue.component('users-admin-table', require('./components/wrappers/UsersAdminTable.vue'));
Vue.component('user-edit-form', require('./components/UserEditForm.vue'));
Vue.component('payment-instructions', require('./components/PaymentInstructions.vue'));
Vue.component('accept-payment', require('./components/AcceptPayment.vue'));

// Attendance
Vue.component('attendance-modal', require('./components/wrappers/AttendanceModal.vue'));
Vue.component('attendance-kiosk', require('./components/attendance/AttendanceKiosk.vue'));
Vue.component('attendance-export', require('./components/attendance/AttendanceExport.vue'));

// Events
Vue.component('events-admin-table', require('./components/wrappers/EventsAdminTable.vue'));
Vue.component('event-edit-form', require('./components/events/EventEditForm.vue'));
Vue.component('event-create-form', require('./components/events/EventCreateForm.vue'));

// Dues

Vue.component('dues-sequence', require('./components/dues/DuesSequence.vue'));
Vue.component('dues-required-info', require('./components/dues/DuesRequiredInfo.vue'));
Vue.component('safety-agreement', require('./components/dues/SafetyAgreement.vue'));
Vue.component('dues-additional-info', require('./components/dues/DuesAdditionalInfo.vue'));
Vue.component('demographics', require('./components/dues/Demographics.vue'));

Vue.component('dues-admin-table', require('./components/wrappers/DuesAdminTable.vue'));
Vue.component('pending-dues-table', require('./components/wrappers/PendingDuesTable.vue'));
Vue.component('dues-transaction', require('./components/dues/DuesTransaction.vue'));

// Swag

Vue.component('swag-table', require('./components/wrappers/SwagTable.vue'));
Vue.component('swag-transaction', require('./components/swag/SwagTransaction.vue'));
Vue.component('distribute-swag', require('./components/swag/DistributeSwag.vue'));


const app = new Vue({
    el: '#app'
});

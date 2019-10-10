/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
var axios = require('axios');
var dt = require('datatables.net/js/jquery.dataTables');

// Import DataTables for Bootstrap4 module
require('datatables.net-bs4');
require('jszip');
require('datatables.net-buttons-bs4');
require('datatables.net-buttons/js/buttons.html5.js');
require('datatables.net-buttons/js/buttons.print.js');

//Import SweetAlert2 for nice alert dialogs
import Swal from 'sweetalert2';
window.Swal = Swal;

window.Vue = require('vue');

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
Vue.component('datatable', require('./components/Datatable.vue').default);
Vue.component('recruiting-admin-table', require('./components/wrappers/RecruitingAdminTable.vue').default);
Vue.component('recruiting-edit-form', require('./components/RecruitingEditForm.vue').default);
Vue.component('users-admin-table', require('./components/wrappers/UsersAdminTable.vue').default);
Vue.component('user-edit-form', require('./components/UserEditForm.vue').default);
Vue.component('payment-instructions', require('./components/PaymentInstructions.vue').default);
Vue.component('accept-payment', require('./components/AcceptPayment.vue').default);

// Attendance
Vue.component('attendance-modal', require('./components/wrappers/AttendanceModal.vue').default);
Vue.component('attendance-kiosk', require('./components/attendance/AttendanceKiosk.vue').default);
Vue.component('attendance-export', require('./components/attendance/AttendanceExport.vue').default);
Vue.component('attendance-manual-add', require('./components/attendance/AttendanceManualAdd.vue').default);

// Events
Vue.component('events-admin-table', require('./components/wrappers/EventsAdminTable.vue').default);
Vue.component('event-edit-form', require('./components/events/EventEditForm.vue').default);
Vue.component('event-create-form', require('./components/events/EventCreateForm.vue').default);

// Dues
Vue.component('dues-sequence', require('./components/dues/DuesSequence.vue').default);
Vue.component('dues-required-info', require('./components/dues/DuesRequiredInfo.vue').default);
Vue.component('safety-agreement', require('./components/dues/SafetyAgreement.vue').default);
Vue.component('dues-additional-info', require('./components/dues/DuesAdditionalInfo.vue').default);
Vue.component('demographics', require('./components/dues/Demographics.vue').default);

Vue.component('dues-admin-table', require('./components/wrappers/DuesAdminTable.vue').default);
Vue.component('pending-dues-table', require('./components/wrappers/PendingDuesTable.vue').default);
Vue.component('dues-transaction', require('./components/dues/DuesTransaction.vue').default);
Vue.component('show-payments', require('./components/payments/ShowPayments.vue').default);

// Swag
Vue.component('swag-table', require('./components/wrappers/SwagTable.vue').default);
Vue.component('swag-transaction', require('./components/swag/SwagTransaction.vue').default);
Vue.component('distribute-swag', require('./components/swag/DistributeSwag.vue').default);

//Teams
Vue.component('teams-admin-table', require('./components/wrappers/TeamsAdminTable.vue').default);
Vue.component('team-create-form', require('./components/teams/TeamCreateForm.vue').default);
Vue.component('team-edit-form', require('./components/teams/TeamEditForm.vue').default);
Vue.component('team-invite-modal', require('./components/teams/TeamInviteModal.vue').default);
Vue.component('team-card', require('./components/teams/TeamCard.vue').default);
Vue.component('team-membership-button', require('./components/teams/TeamMembershipButton.vue').default);

// Notifications
Vue.component(
    'notification-templates-admin-table',
    require('./components/notification/templates/NotificationTemplatesAdminTable.vue').default
);
Vue.component(
    'notification-templates-create-form',
    require('./components/notification/templates/NotificationTemplatesCreateForm.vue').default
);
Vue.component(
    'notification-templates-edit-form',
    require('./components/notification/templates/NotificationTemplatesEditForm.vue').default
);

const app = new Vue({
    el: '#app',
});

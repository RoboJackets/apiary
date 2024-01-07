/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

import Vue from 'vue'
//Import SweetAlert2 for nice alert dialogs
import Swal from 'sweetalert2';
// Import the Vuelidate validation plugin
import Vuelidate from 'vuelidate';
// Import the VueSelect component
import vSelect from 'vue-select';
// Import the FlatPickr Date Component
import flatPickr from 'vue-flatpickr-component';
import 'flatpickr/dist/flatpickr.css';
//Import Moment for friendly timestamps
import VueMoment from 'vue-moment';
import moment from 'moment';
import Toast from "./mixins/Toast";
import FileUploader from "./mixins/FileUploader";
import * as Sentry from "@sentry/vue";

var sentryDsn = document.head.querySelector('meta[name="sentry-dsn"]').content;
var sentryAppEnv = document.head.querySelector('meta[name="sentry-app-env"]').content;
var sentryRelease = document.head.querySelector('meta[name="sentry-release"]').content;
var sentryUserId = document.head.querySelector('meta[name="sentry-user-id"]');
var sentryUsername = document.head.querySelector('meta[name="sentry-username"]');
if (sentryDsn !== null) {
    if (sentryUserId !== null) {
        var initialScope = {
            user: {
                id: sentryUserId.content,
                username: sentryUsername.content,
            }
        }
    } else {
        var initialScope = {}
    }
    Sentry.init({
        Vue: Vue,
        dsn: sentryDsn,
        environment: sentryAppEnv,
        release: sentryRelease,
        initialScope: initialScope,
        attachProps: true,
        logErrors: true,
        tracesSampleRate: 1.0,
        tracingOptions: {
            trackComponents: true,
        },
        integrations: [
            new Sentry.BrowserTracing(),
            new Sentry.Feedback({
                colorScheme: "light",
                showName: false,
                showEmail: false,
                isNameRequired: false,
                isEmailRequired: false,
                useSentryUser: {
                    name: 'username',
                },
                buttonLabel: 'Feedback',
                submitButtonLabel: 'Send Feedback',
                formTitle: 'Feedback',
                messagePlaceholder: 'Please describe what you were trying to do, what you expected, and what actually happened.',
                successMessageText: 'Thank you for your feedback!',
                autoInject: window.location.pathname !== '/attendance/kiosk',
            }),
        ],
    });
    window.Sentry = Sentry;
} else {
    console.log('Sentry not loaded - DSN not present')
}

require('./bootstrap');
const axios = require('axios');

window.Swal = Swal.mixin({
    confirmButtonColor: "#3085d6", // Swal's real default color is #7367f0 (purple), even though that's not what the docs say
});

// Borrowed from https://sweetalert2.github.io/
const SwalToast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 5000,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer)
        toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
});
window.SwalToast = SwalToast;

Vue.use(Vuelidate);

Vue.component('v-select', vSelect);

Vue.use(flatPickr);

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
Vue.component('self-service-access-override', require('./components/dues/SelfServiceAccessOverride.vue').default);

// Payments
Vue.component('payment-history', require('./components/payments/PaymentHistory.vue').default);
Vue.component('payment-method-details', require('./components/payments/PaymentMethodDetails.vue').default);
Vue.component('payment-card-icon', require('./components/payments/PaymentCardIcon.vue').default);

// Teams
Vue.component('team-card', require('./components/teams/TeamCard.vue').default);
Vue.component('team-membership-button', require('./components/teams/TeamMembershipButton.vue').default);

// OAuth2
Vue.component('oauth2-authorizations', require('./components/oauth2/OAuth2Authorizations').default)
Vue.component('personal-access-tokens', require('./components/oauth2/PersonalAccessTokens').default)

// Utilities
Vue.component('loading-spinner', require('./components/LoadingSpinner').default)

const app = new Vue({
    el: '#app',
    mixins: [
        Toast,
        FileUploader,
    ],
});


/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
var axios = require('axios')
var dt = require('datatables.net/js/jquery.dataTables');
//var bs = require('datatables.net-bs/js/dataTables.bootstrap');


window.Vue = require('vue');

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

Vue.component('datatable', require('./components/Datatable.vue'));
Vue.component('faset-admin-table', require('./components/wrappers/FasetAdminTable.vue'))
Vue.component('faset-edit-form', require('./components/FasetEditForm.vue'));
Vue.component('users-admin-table', require('./components/wrappers/UsersAdminTable.vue'))

const app = new Vue({
    el: '#app'
});

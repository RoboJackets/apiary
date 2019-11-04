import Vuelidate from 'vuelidate'
Nova.booting((Vue, router) => {
    Vue.use(Vuelidate);

    Vue.component('makeawish', require('./components/nova/MakeAWishCard.vue').default);
    Vue.component('makeawish', require('./components/nova/MakeAWishCard.vue').default);
    Vue.component('makeawish-link', require('./components/nova/MakeAWishLink.vue').default);
    Vue.component('detail-hidden-field', require('./components/nova/HiddenFieldDetail.vue').default);
    Vue.component('text-metric', require('./components/nova/TextMetric.vue').default);
    Vue.component('collect-attendance', require('./components/nova/CollectAttendance.vue').default);

    router.addRoutes([
        {
            name: 'attendance-report',
            path: '/attendance-report',
            component: require('./components/nova/AttendanceReport.vue').default,
        },
    ])
})

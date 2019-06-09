Nova.booting((Vue, router) => {
    Vue.component('makeawish', require('./components/nova/MakeAWishCard.vue').default);
    Vue.component('detail-hidden-field', require('./components/nova/HiddenFieldDetail.vue').default);
    Vue.component('text-metric', require('./components/nova/TextMetric.vue').default);

    router.addRoutes([
        {
            name: 'attendance-report',
            path: '/attendance-report',
            component: require('./components/nova/AttendanceReport.vue').default,
        },
    ])
})

Nova.booting((Vue, router, store) => {
    Vue.component('makeawish', require('./components/nova/MakeAWishCard.vue'));
    Vue.component('detail-hidden-field', require('./components/nova/HiddenFieldDetail.vue'));
    Vue.component('text-metric', require('./components/nova/TextMetric.vue'));

    router.addRoutes([
        {
            name: 'attendance-report',
            path: '/attendance-report',
            component: require('./components/nova/AttendanceReport'),
        },
    ])
})

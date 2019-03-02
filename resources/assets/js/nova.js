//Vue.component('chartist-card', require('./components/nova/ChartistCard.vue'));

Nova.booting((Vue, router) => {
    Vue.component('makeawish', require('./components/nova/MakeAWishCard.vue'));

    router.addRoutes([
        {
            name: 'attendance-report',
            path: '/attendance-report',
            component: require('./components/nova/AttendanceReport'),
        },
    ])
})

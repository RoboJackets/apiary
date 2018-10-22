Nova.booting((Vue, router) => {
    router.addRoutes([
        {
            name: 'attendance-report',
            path: '/attendance-report',
            component: require('./components/nova/AttendanceReport'),
        },
    ])
})

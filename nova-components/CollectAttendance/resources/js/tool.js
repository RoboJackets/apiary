import Vuelidate from 'vuelidate'
Nova.booting((Vue, router, store) => {
    Vue.use(Vuelidate)
    Vue.component('collect-attendance', require('./components/Tool'))
})

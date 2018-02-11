<template>
    <v-select
            label="name"
            :on-search="getOptions"
            :options="this.userOptions"
            placeholder="Enter a Username, GTID#, or First Name">
    </v-select>
</template>

<script>
    export default {
        data() {
            return {
                dataUrl: "",
                baseUrl: "/api/v1/users/search?keyword=",
                userOptions: [],
            }
        },
        methods: {
            getOptions(search, loading) {
                loading(true);
                console.log("options");
                this.searchUsers(loading, search);
            },
            searchUsers: _.debounce((loading, search) => {
                console.log('searching');
                axios.get(this.baseUrl + encodeURIComponent(search))
                    .then(response => {
                        loading(false);
                        this.userOptions = response.user.options;
                    })
                    .catch(response => {
                        console.log(response);
                        sweetAlert("Connection Error", "Unable to load data. Check your internet connection or try refreshing the page.", "error");
                    });
            }, 350),
        }
    }
</script>
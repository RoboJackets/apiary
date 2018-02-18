<template>
    <v-select
            label="name"
            :on-search="getOptions"
            :on-change="change"
            :filter="filter"
            :options="this.options"
            placeholder="Enter a First Name"
            :value="value">
    </v-select>
</template>

<script>
    export default {
        data() {
            return {
                dataUrl: "",
                baseUrl: "/api/v1/users/search?keyword=",
                options: [],
                searchKeyword: ""
            }
        },
        props: {
            value: {
                type: [String, Object]
            }
        },
        methods: {
            getOptions(search, loading) {
                loading(true);
                this.search(loading, search);
            },
            search: _.debounce(function(loading, search) {
                axios.get(this.baseUrl + encodeURIComponent(search))
                    .then(response => {
                        loading(false);
                        this.options = response.data.users;
                    })
                    .catch(response => {
                        console.log(response);
                        swal("Connection Error", "Unable to load data. Check your internet connection or try refreshing the page.", "error");
                    });
            }, 350),
            change(value) {
                if (value instanceof Object) {
                    this.$emit('input', value);
                }
            },
            //This controls which results show up in the dropdown after results are returned
            //TODO: Make necessary modifications to allow search by username/GTID, then update placeholder
            filter(option, label, search) {
                return (label || '').toLowerCase().indexOf(search.toLowerCase()) > -1
            }
        }
    }
</script>
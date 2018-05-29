<template>
    <datatable id="teams-admin-table"
               data-url="/api/v1/teams"
               data-path="teams"
               :columns="tableConfig">
    </datatable>
</template>

<script>
    export default {
        data() {
            return {
                tableConfig: [
                    {
                        title: 'Name',
                        data: 'name',
                        render: function (data, type, row) {
                            var link = "<a href='/admin/teams/" + row['slug'] + "'>" + data + '</a>';
                            return type === 'display' ? link : data;
                        },
                    },
                    {title: 'Description', data: 'description'},
                    {title: 'Founding Year', data: 'founding_year'},
                    {
                        title: 'Visible',
                        data: 'visible',
                        render: function (data, type, row) {
                            return data === 1 ? 'Yes' : 'No';
                        },
                    },
                    {
                        title: 'Attendable',
                        data: 'attendable',
                        render: function (data, type, row) {
                            return data === 1 ? 'Yes' : 'No';
                        },
                    },
                    {
                        title: 'Self-Serviceable',
                        data: 'self-serviceable',
                        render: function (data, type, row) {
                            return data === 1 ? 'Yes' : 'No';
                        },
                    },
                ],
            };
        },
    };
</script>

<template>
  <datatable v-if="this.tableFilter == 'index'"
             id="swag-index-table" 
             data-url="/api/v1/dues/transactions/paid?include=user,user.teams"
             data-path="dues_transactions"
             data-link="/admin/swag/"
             :columns="tableConfig">
  </datatable>
  <datatable v-else-if="this.tableFilter == 'pending'"
             id="swag-pending-table"
             data-url="/api/v1/dues/transactions/pendingSwag?include=user,user.teams"
             data-path="dues_transactions"
             data-link="/admin/swag/"
             :columns="tableConfig">
  </datatable>
</template>

<script>
export default {
  props: {
    tableFilter: {
      required: true,
    },
  },
  data() {
    return {
      tableConfig: [
        { title: 'ID', data: 'id' },
        { title: 'Name', data: 'user.name' },
        { title: 'Teams', data: function(row) {
            const teams = row.user.teams.map(function(team) {
              return team.name;
            });
            if (teams.length === 0) {
              return "None";
            }
            return teams.join(", ");
        }},
        { title: 'Dues Package', data: 'package.name' },
        { title: 'Shirt', data: 'swag_shirt_status' },
        { title: 'Polo', data: 'swag_polo_status' },
      ],
    };
  },
};
</script>

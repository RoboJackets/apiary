<template>
  <datatable id="dues-admin-table"
    data-url="/api/v1/dues/transactions/pending?include=user,user.teams"
    data-path="dues_transactions"
    data-link="/admin/dues/"
    :columns="tableConfig">
  </datatable>
</template>

<script>
export default {
  data() {
    return {
      tableConfig: [
        { title: 'ID', data: 'id' },
        { title: 'Timestamp', data: 'updated_at.date' },
        { title: 'Name', data: 'user.name' },
        { title: 'Teams', data: function(row) {
            const teams = row.user.teams.map(function(team) {
              return team.name;
            });
            if (teams.length === 0) {
              return "N/A";
            }
            return teams.join(", ");
          }},
        { title: 'Dues Package', data: 'package.name' },
      ],
    };
  },
};
</script>

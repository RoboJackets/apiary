<template>
  <datatable id="dues-admin-table"
    data-url="/api/v1/dues/transactions?include=user,payment,user.teams"
    data-path="dues_transactions"
    :columns="tableConfig">
  </datatable>
</template>

<script>
export default {
  data() {
    return {
      tableConfig: [
        { title: 'ID', data: 'id' },
        { title: 'Timestamp', data: 'created_at.date' },
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
        { title: 'Status', data: 'status', className: 'text-capitalize' },
        { title: 'Amount Paid', data: 'payment[0].amount' },
        { title: 'Payment Method', data: 'payment[0].method', className: 'text-capitalize' },
        { title: 'Date Paid', data: 'payment[0].created_at' },
      ],
    };
  },
};
</script>

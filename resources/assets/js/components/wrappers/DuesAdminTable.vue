<template>
  <datatable id="dues-admin-table"
    data-url="/api/v1/dues/transactions"
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
        { title: 'Timestamp', data: 'created_at' },
        { title: 'Name', data: 'user.name' },
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

<template>
  <datatable id="dues-admin-table"
    data-url="/api/v1/dues/transactions/pending?include=user"
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
        { title: 'Dues Package', data: 'package.name' },
      ],
    };
  },
};
</script>

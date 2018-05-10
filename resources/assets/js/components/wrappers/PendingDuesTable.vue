<template>
  <datatable id="dues-admin-table"
    data-url="/api/v1/dues/transactions/pending"
    data-path="dues_transactions"
    :columns="tableConfig">
  </datatable>
</template>

<script>
export default {
  data() {
    return {
      tableConfig: [
        {
          title: 'ID',
          data: 'id',
          render: function(data, type, row) {
            var link = "<a href='/admin/dues/" + data + "'>" + data + '</a>';
            return type === 'display' ? link : data;
          },
        },
        { title: 'Timestamp', data: 'updated_at' },
        { title: 'Name', data: 'user.name' },
        { title: 'Dues Package', data: 'package.name' },
      ],
    };
  },
};
</script>

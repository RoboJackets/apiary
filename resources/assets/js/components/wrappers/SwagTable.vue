<template>
  <datatable v-if="this.tableFilter == 'index'"
             id="swag-index-table" 
             data-url="/api/v1/dues/transactions/paid" 
             data-path="dues_transactions"
             data-link="/admin/swag/"
             :columns="tableConfig">
  </datatable>
  <datatable v-else-if="this.tableFilter == 'pending'"
             id="swag-pending-table"
             data-url="/api/v1/dues/transactions/pendingSwag"
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
        { title: 'Dues Package', data: 'package.name' },
        { title: 'Shirt', data: 'swag_shirt_status' },
        { title: 'Polo', data: 'swag_polo_status' },
      ],
    };
  },
};
</script>

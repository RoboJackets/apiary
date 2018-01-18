<template>
  <datatable id="swag-pending-table"
    data-url="/api/v1/dues/transactions/pendingSwag"
    data-path="dues_transactions"
    :columns="tableConfig">
  </datatable>
</template>

<script>
  export default {
    data() {
      return {
        tableConfig: [
          {'title': 'ID', 'data':'id', 'render': function (data, type, row) {
            var link = "<a href='/admin/swag/" + data + "'>" + data + "</a>";
            return type === "display" ? link : data;
          }},
          {'title': 'Name', 'data':'user.name'},
          {'title': 'Dues Package', 'data':'package.name'},
          {'title': 'Shirt', 'data':'swag_shirt_status'},
          {'title': 'Polo', 'data':'swag_polo_status'},
        ]
      }
    }
  }
</script>
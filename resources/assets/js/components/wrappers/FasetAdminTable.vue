<template>
  <datatable id="faset-admin-table"
    data-url="/api/v1/faset"
    :columns="tableConfig"> 
  </datatable>
</template>

<script>
  export default {
    data() {
      return {
        tableConfig: [
          {'title': 'ID', 'data':'id', 'render': function (data, type, row) {
            var link = "<a href='faset/" + data + "'>" + data + "</a>";
            return type === "display" ? link : data;
          }}, 
          {'title': 'Timestamp', 'data':'created_at'}, 
          {'title': 'Name', 'data':'faset_name'}, 
          {'title': 'Email', 'data':'faset_email'}
        ]
      }
    }
  }
</script>
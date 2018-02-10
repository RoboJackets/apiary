<template>
  <datatable id="events-admin-table"
    data-url="/api/v1/events"
    data-path="events"
    :columns="tableConfig">
  </datatable>
</template>

<script>
  export default {
    data() {
      return {
        tableConfig: [
          {'title': 'ID', 'data':'id', 'render': function (data, type, row) {
            var link = "<a href='/admin/events/" + data + "'>" + data + "</a>";
            return type === "display" ? link : data;
          }}, 
          {'title': 'Name', 'data':'name'}, 
          {'title': 'Organizer', 'data':'organizer_name'}
        ]
      }
    }
  }
</script>
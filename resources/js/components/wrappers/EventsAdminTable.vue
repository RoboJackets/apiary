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
        { title: 'ID', data: 'id' },
        { title: 'Name', data: 'name' },
        { title: 'Organizer', data: 'organizer_name' },
      ],
    };
  },
};
</script>

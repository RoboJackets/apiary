<template>
  <datatable id="notification-templates-admin-table"
    data-url="/api/v1/notification/templates"
    data-path="templates"
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
        { title: 'Subject', data: 'subject' },
        { title: 'Last Updated', data: 'updated_at.date' },
      ],
    };
  },
};
</script>

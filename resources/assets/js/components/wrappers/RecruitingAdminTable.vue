<template>
  <datatable id="recruiting-admin-table"
    data-url="/api/v1/recruiting"
    data-path="visits"
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
        { title: 'Name', data: 'recruiting_name' },
        { title: 'Email', data: 'recruiting_email' },
      ],
    };
  },
};
</script>

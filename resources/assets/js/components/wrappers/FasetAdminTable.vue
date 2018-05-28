<template>
  <datatable id="faset-admin-table"
    data-url="/api/v1/faset"
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
        { title: 'Name', data: 'faset_name' },
        { title: 'Email', data: 'faset_email' },
      ],
    };
  },
};
</script>

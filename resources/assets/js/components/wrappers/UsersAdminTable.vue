<template>
  <datatable id="users-admin-table"
    data-url="/api/v1/users"
    data-path="users"
    :columns="tableConfig">
  </datatable>
</template>

<script>
export default {
  data() {
    return {
      tableConfig: [
        { title: 'GT Username', data: 'uid' },
        { title: 'GTID', data: 'gtid' },
        { title: 'First Name', data: 'first_name' },
        { title: 'Last Name', data: 'last_name' },
        { title: 'Email', data: 'gt_email' },
      ],
    };
  },
};
</script>

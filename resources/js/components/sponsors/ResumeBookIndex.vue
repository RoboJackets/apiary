<template>
  <div class="container">
    <h1>Resume Book</h1>

    <div v-if="!users || users.length === 0" class="empty">
      No resumes found.
    </div>

    <table v-else class="table table-striped table-hover">
      <thead>
        <tr>
          <th class="d-none">User ID</th>
          <th>Name</th>
          <th>Major</th>
          <th>Graduation Semester</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="user in users" :key="user.id">
          <td class="d-none">{{ user.id }}</td>
          <td>{{ user.name }}</td>
          <td>{{ user.major }}</td>
          <td>{{ user.graduation_semester }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
export default {
  name: 'ResumeBookIndex',
  props: {
    // Expect an array of user objects to be passed in when the page loads.
    // Each user object can contain: name, uid, major, graduation_semester
    users: {
      type: Array,
      default: () => []
    }
  }
}
</script>

<style scoped>
.empty {
  color: #666;
  align-items: center;
  justify-content: center;
  display: flex;
  margin-top: 8px;
}
</style>

<template>
  <div class="container-fluid">
    <h1>Resume Book</h1>

    <div class="row">
      <!-- Sidebar -->
      <div class="col-auto vh-100 overflow-auto">
        <div class="h-100">
          <major-filter
            @update-majors="majors => console.log('Selected majors:', majors)">
          </major-filter>
        </div>
      </div>

      <!-- Main Content -->
      <div class="col-auto overflow-auto">
        <div class="container-fluid">
          <div v-if="!users || users.length === 0" class="empty">
            No resumes found.
          </div>

          <table v-else class="table table-striped table-hover">
            <thead>
              <tr>
                <th class="d-none">User ID</th>
                <th>Name</th>
                <!-- <th>Major</th> -->
                <th>Graduation Semester</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="user in users" :key="user.id">
                <td class="d-none">{{ user.id }}</td>
                <td>{{ user.first_name }}</td>
                <!-- <td>{{ user.major }}</td> -->
                <td>{{ user.graduation_semester }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
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

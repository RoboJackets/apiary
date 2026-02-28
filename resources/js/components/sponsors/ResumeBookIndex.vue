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
        <div class="d-flex flex-column gap-3">
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
      default: () => [
        { id: 1, first_name: 'Alice', graduation_semester: 'Fall 2024' },
        { id: 2, first_name: 'Bob', graduation_semester: 'Spring 2025' },
        { id: 3, first_name: 'Charlie', graduation_semester: 'Fall 2024' },
        { id: 4, first_name: 'Diana', graduation_semester: 'Spring 2025' },
        { id: 5, first_name: 'Eve', graduation_semester: 'Fall 2025' },
        { id: 6, first_name: 'Frank', graduation_semester: 'Spring 2025' },
        { id: 7, first_name: 'Grace', graduation_semester: 'Fall 2024' },
        { id: 8, first_name: 'Henry', graduation_semester: 'Spring 2026' },
        { id: 9, first_name: 'Iris', graduation_semester: 'Fall 2025' },
        { id: 10, first_name: 'Jack', graduation_semester: 'Spring 2025' },
        { id: 11, first_name: 'Kate', graduation_semester: 'Fall 2024' },
        { id: 12, first_name: 'Leo', graduation_semester: 'Fall 2025' },
        { id: 13, first_name: 'Mia', graduation_semester: 'Spring 2026' },
        { id: 14, first_name: 'Noah', graduation_semester: 'Spring 2025' },
        { id: 15, first_name: 'Olivia', graduation_semester: 'Fall 2024' },
        { id: 16, first_name: 'Paul', graduation_semester: 'Fall 2025' },
        { id: 17, first_name: 'Quinn', graduation_semester: 'Spring 2026' },
        { id: 18, first_name: 'Rachel', graduation_semester: 'Spring 2025' },
        { id: 19, first_name: 'Sam', graduation_semester: 'Fall 2024' },
        { id: 20, first_name: 'Tina', graduation_semester: 'Fall 2025' }
      ]
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

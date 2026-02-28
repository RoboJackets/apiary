<template>
  <div class="container">
    <h3>Majors</h3>
    <div class="checkbox-group">
      <div v-for="major in majors" :key="major.id" class="form-check">
        <input
          type="checkbox"
          class="form-check-input"
          :id="`major-${major.id}`"
          :value="major.id"
          v-model="selectedMajors"
          @change="$emit('update-majors', selectedMajors)"
        />
        <label class="form-check-label" :for="`major-${major.id}`">
          {{ major.display_name || major.gtad_majorgroup_name }}
        </label>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'MajorFilter',
  emits: ['update-majors'],
  data() {
    return {
      majors: [],
      selectedMajors: []
    }
  },
  mounted() {
    this.fetchMajors();
  },
  methods: {
    async fetchMajors() {
    //   try {
    //     const response = await fetch('/sponsor/majors');
    //     if (!response.ok) {
    //       throw new Error(`HTTP error! status: ${response.status}`);
    //     }
    //     const data = await response.json();
    //     this.majors = data.majors;
    //     console.log(data);
    //   } catch (error) {
    //     console.error('Error fetching majors:', error);
    //   }
        // Mock data for testing
        this.majors = [
            { id: 1, display_name: 'Computer Science', gtad_majorgroup_name: 'CS' },
            { id: 2, display_name: 'Electrical Engineering', gtad_majorgroup_name: 'EE' },
            { id: 3, display_name: 'Mechanical Engineering', gtad_majorgroup_name: 'ME' },
            { id: 4, display_name: 'Civil Engineering', gtad_majorgroup_name: 'CE' },
            { id: 5, display_name: 'Chemical Engineering', gtad_majorgroup_name: 'CHE' },
            { id: 6, display_name: 'Aerospace Engineering', gtad_majorgroup_name: 'AE' },
            { id: 7, display_name: 'Biomedical Engineering', gtad_majorgroup_name: 'BME' },
            { id: 8, display_name: 'Mathematics', gtad_majorgroup_name: 'MATH' },
            { id: 9, display_name: 'Physics', gtad_majorgroup_name: 'PHYS' },
            { id: 10, display_name: 'Chemistry', gtad_majorgroup_name: 'CHEM' },
            { id: 11, display_name: 'Biology', gtad_majorgroup_name: 'BIO' },
            { id: 12, display_name: 'Business Administration', gtad_majorgroup_name: 'BA' },
            { id: 13, display_name: 'Economics', gtad_majorgroup_name: 'ECON' },
            { id: 14, display_name: 'Finance', gtad_majorgroup_name: 'FIN' },
            { id: 15, display_name: 'Marketing', gtad_majorgroup_name: 'MKT' },
            { id: 16, display_name: 'Accounting', gtad_majorgroup_name: 'ACC' },
            { id: 17, display_name: 'Psychology', gtad_majorgroup_name: 'PSYCH' },
            { id: 18, display_name: 'English Literature', gtad_majorgroup_name: 'ENG' },
            { id: 19, display_name: 'History', gtad_majorgroup_name: 'HIST' },
            { id: 20, display_name: 'Environmental Science', gtad_majorgroup_name: 'ENVSCI' },
        ];
    }
  }
}
</script>

<style scoped>

.checkbox-group {
  display: flex;
  flex-direction: column;
}

</style>

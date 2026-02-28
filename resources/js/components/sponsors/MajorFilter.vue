<template>
  <div class="major-filter">
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
      try {
        const response = await fetch('/sponsor/majors');
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();
        this.majors = data.majors;
        console.log(data);
      } catch (error) {
        console.error('Error fetching majors:', error);
      }
    }
  }
}
</script>

<style scoped>
.major-filter {
  margin-bottom: 1.5rem;
}

.major-filter h3 {
  margin-bottom: 1rem;
  font-size: 1.1rem;
  font-weight: 600;
  color: #212529;
}

.checkbox-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

</style>

<template>
  <div v-if="active" class="mx-auto text-center">
    <div class="spinner-border mb-2 mx-auto" role="status">
      <span class="visually-hidden">{{ text }}</span>
    </div>
    <p>{{ text }}</p>
  </div>
</template>

<script>
export default {
  name: "LoadingSpinner.vue",
  props: {
    text: {
      type: String,
      default: "Loading...",
    },
    active: {
      type: Boolean,
      default: true,
    },
  }
}
</script>

<style scoped>

</style>

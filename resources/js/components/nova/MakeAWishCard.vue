<template>
  <div class="card relative px-6 py-4 relative">
    <div class="flex mb-4">
      <h3 class="mr-3 text-base text-80 font-bold">Make a Wish</h3>
    </div>
    <p class="text-xl mb-4">Having issues? Have suggestions? <strong><a target="_blank" class="no-underline" :href="url">Let us know!</a></strong></p>
  </div>
</template>

<script>
export default {
  computed: {
    url: function() {
      return 'https://docs.google.com/forms/d/e/1FAIpQLSelERsYq3gLmHbWvVCWha5iCU8z3r9VYC0hCN4ArLpMAiysaQ/viewform?entry.1338203640=' + window.location;
    },
  },
}
</script>

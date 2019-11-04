<template>
    <a class="text-primary dim no-underline" target="_blank" :href="url">Make a Wish</a>
</template>

<script>
export default {
  // This is not elegant because there is no way to force a re-compute of a computed value.
  data: function() {
    return {
      url: null,
    };
  },
  mounted() {
    this.url = 'https://docs.google.com/forms/d/e/1FAIpQLSelERsYq3gLmHbWvVCWha5iCU8z3r9VYC0hCN4ArLpMAiysaQ/viewform?entry.1338203640=' + window.location;
  },
  watch: {
    '$route': function(route) {
      this.url = 'https://docs.google.com/forms/d/e/1FAIpQLSelERsYq3gLmHbWvVCWha5iCU8z3r9VYC0hCN4ArLpMAiysaQ/viewform?entry.1338203640=' + window.location;
    },
  },
}
</script>

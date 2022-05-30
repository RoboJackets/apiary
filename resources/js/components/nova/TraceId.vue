<template>
  <span v-if="traceId">
    <span class="px-1">&middot;</span><a class="text-primary dim no-underline" target="_blank" :href="url">Trace ID: {{ traceId }}</a>
  </span>
</template>

<script>
export default {
  // This is not elegant because there is no way to force a re-compute of a computed value.
  data: function() {
    return {
      url: null,
      traceId: null,
    };
  },
  mounted() {
    var transaction = Sentry.getCurrentHub().getScope().getTransaction();
    if (transaction) {
      this.traceId = transaction.traceId;
      this.url = 'https://sentry.io/organizations/robojackets/performance/trace/' + this.traceId;
    }
  },
  watch: {
    '$route': function(route) {
      var transaction = Sentry.getCurrentHub().getScope().getTransaction();
      if (transaction) {
        this.traceId = transaction.traceId;
        this.url = 'https://sentry.io/organizations/robojackets/performance/trace/' + this.traceId;
      }
    },
  },
}
</script>

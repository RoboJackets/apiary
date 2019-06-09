<template>
  <loading-card :loading="loading" class="metric px-6 py-4 relative">
    <div class="flex mb-4">
      <h3 class="mr-3 text-base text-80 font-bold">{{ title }}</h3>
    </div>
    
    <p class="flex items-center text-4xl mb-4">{{ value }}</p>
  </loading-card>
</template>

<script>
export default {
  props: {
    card: {
      type: Object,
      required: true,
    },
    resourceName: {
      type: String,
      default: '',
    },
    resourceId: {
      type: Number,
      default: 0,
    },
  },
  data: function() {
    return {
      loading: true,
      value: '',
      title: '',
    };
  },
  mounted: function() {
    console.log('mounted textmetric');
    var url = '/nova-api/' + this.resourceName + '/' + this.resourceId + '/metrics/' + this.card.uriKey;
    Nova.request().get(url).then(function(response) {
      this.value = response.value.value;
      this.title = response.value.title;
      this.loading = false;
    });
  },
}
</script>

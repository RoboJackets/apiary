<template>
  <loading-card :loading="loading" class="metric px-6 py-4 relative">
    <div class="flex mb-4">
      <h3 class="mr-3 text-base text-80 font-bold">{{ card.name }}</h3>
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
    };
  },
  mounted: function() {
    var url = '/nova-api/' + this.resourceName + '/' + this.resourceId + '/metrics/' + this.card.uriKey;
    console.log(this.card);

    var thisObj = this;
    Nova.request().get(url).then(function(response) {
      thisObj.value = response.data.value.value;
      thisObj.title = response.data.value.title;
      thisObj.loading = false;
    });
  },
}
</script>

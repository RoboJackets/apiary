<template>
  <loading-card :loading="loading" class="metric px-6 py-4 relative">
    <div class="flex mb-4">
      <h3 class="me-3 text-base text-80 font-bold">{{ card.name }}</h3>

      <select v-if="card.ranges.length > 0" @change="refresh" class="ms-auto min-w-24 h-6 text-xs no-appearance bg-40">
        <option v-for="range in card.ranges" :key="range.value" :value="range.value" :selected="range.value == selectedRange">{{ range.label }}</option>
      </select>
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
      type: [Number, String],
      default: 0,
    },
  },
  data: function() {
    return {
      loading: true,
      value: '',
      selectedRange: null,
    };
  },
  computed: {
    url: function() {
      return '/nova-api/' + this.resourceName + '/' + this.resourceId + '/metrics/' + this.card.uriKey;
    }
  },
  mounted: function() {
    if (this.card.ranges.length > 0) {
      this.selectedRange = this.card.ranges[0].value;
    }

    this.refresh();
  },
  methods: {
    refresh: function(event) {
      this.loading = true;
      if (event) {
        this.selectedRange = event.target.value;
      }

      var thisObj = this;
      Nova.request().get(this.url, this.card.ranges.length > 0 ? {params: {range: this.selectedRange}} : {}).then(function(response) {
        thisObj.value = response.data.value.value;
        thisObj.title = response.data.value.title;
        thisObj.loading = false;
      });
    },
  },
  watch: {
    'resourceId': function() {
      if (this.card.ranges.length > 0) {
        this.selectedRange = this.card.ranges[0].value;
      }

      this.refresh(null);
    },
  },
}
</script>

<template>
  <div class="px-3 mb-6">
    <loading-card :loading="false" class="pt-4">
      <div class="flex mb-4 mx-6">
        <h3 class="mr-3 text-base text-80 font-bold">{{ title }}</h3>
        <p v-if="weeklyTotal" class="ml-auto text-black text-xl font-bold">{{ weeklyTotal }}
          <span v-if="totalSuffix" class="ml-.5 text-sm text-80">{{ totalSuffix }}</span>
        </p>
      </div>
      <div ref="chart" class="z-40 absolute pin rounded-b-lg ct-chart ct-double-octave text-black"/>
    </loading-card>
  </div>
</template>

<script>
import Chartist from 'chartist';
import ctPointLabels from 'chartist-plugin-pointlabels';

export default {
  props: {
    title: String,
    total: String,
    totalSuffix: String,
    data: Object,
    options: Object,
    type: {
      type: String,
      required: true,
    },
  },
  mounted() {
    switch (this.type) {
    case 'bar':
      new Chartist.Bar(this.$refs.chart, this.data, this.options);
      break;
    case 'line':
      new Chartist.Line(this.$refs.chart, this.data, this.options);
      break;
    default:
      console.log('Invalid chart type ' + this.type);
      break;
    }
  },
}
</script>

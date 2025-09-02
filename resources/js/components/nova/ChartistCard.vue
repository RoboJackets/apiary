<template>
  <div class="px-3 mb-6">
    <loading-card :loading="loading" class="pt-4">
      <div class="flex mb-4 mx-6" ref="legend">
        <h3 class="me-3 text-base text-80 font-bold">{{ title }}</h3>
        <p v-if="total" class="ms-auto text-black text-xl font-bold">{{ total }}
          <span v-if="totalSuffix" class="ml-.5 text-sm text-80">{{ totalSuffix }}</span>
        </p>
      </div>
      <div ref="chart" class="z-40 absolute pin rounded-b-lg ct-chart ct-double-octave text-black"></div>
    </loading-card>
  </div>
</template>

<script>
import Chartist from 'chartist';

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
    loading: {
      type: Boolean,
      default: false,
    },
    legend: {
      type: Boolean,
      default: false,
    },
  },
  data: function() {
    return {
      chart: null,
    };
  },
  mounted() {
    var options = this.options || {};
    if (this.legend) {
      options.plugins = [Chartist.plugins.legend({
        position: this.$refs.legend,
      })];
    }

    switch (this.type) {
      case 'bar':
        this.chart = new Chartist.Bar(this.$refs.chart, this.data, options);
        break;
      case 'line':
        this.chart = new Chartist.Line(this.$refs.chart, this.data, options);
        break;
      default:
        console.log('Invalid chart type ' + this.type);
        break;
    }
  },
  watch: {
    data: function(val) {
      this.chart.update(val);
    },
  },
}
</script>

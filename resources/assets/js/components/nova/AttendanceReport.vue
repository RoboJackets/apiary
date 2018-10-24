<template>
  <div class="attendancereport">
    <heading class="mb-6">Attendance</heading>

    <div class="flex flex-wrap -mx-3 mb-3">
      <ChartistCard title="Average Daily Members (All Teams)" class="w-full" total="123" total-suffix="per week" :data="data" :options="options" type="bar"/>

      <ChartistCard title="BattleBots" class="w-1/2" :data="data" :options="options" type="line"/>

    <!-- <base-trend-metric :chart-data="data" title="fdsa" />-->
    </div>
  </div>
</template>

<script>
import Chartist from 'chartist';
import ctPointLabels from 'chartist-plugin-pointlabels';
import ChartistCard from './ChartistCard';

export default {
  components: {
    ChartistCard,
  },
  data() {
    return {
      data: {
        labels: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
        series: [ [10, 12, 9, 7, 8, 5, 15] ],
      },
      options: {
        fullWidth: false,
        chartPadding: {
          // right: 40
        },
        axisY: {
          low: 0,
        },
        axisX: {
          showGrid: false,
        },
        plugins: [
          ctPointLabels({
            textAnchor: 'middle',
          }),
        ],
      },
    };
  },
  mounted() {
    new Chartist.Bar(this.$refs.chart2, {
      labels: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
      series: [ [8, 9, 16, 7, 8, 9, 12] ],
    }, {
      fullWidth: false,
      chartPadding: {
        // right: 40
      }
    });
  },
}
</script>

<style>
/* Scoped Styles */
</style>

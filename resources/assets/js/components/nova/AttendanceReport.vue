<template>
  <div class="attendancereport">
    <heading class="mb-6">Attendance</heading>

    <div class="flex flex-wrap -mx-3 mb-3">
      <div class="px-3 mb-6 w-full">
        <loading-card :loading="false" class="pt-4">
          <div class="flex mb-4 mx-6">
            <h3 class="mr-3 text-base text-80 font-bold">Average Daily Members (All Teams)</h3>
            <p class="ml-auto text-black text-lg font-bold">{{ data.weeklyTotal }}
              <span class="ml-.5 text-sm text-80">per week</span>
            </p>
          </div>
          <div ref="chart" class="z-40 absolute pin rounded-b-lg ct-chart ct-double-octave text-black"/>
        </loading-card>
      </div>
      <div class="px-3 mb-6 w-full">
        <loading-card :loading="false" class="flex flex-col items-center justify-center" style="min-height: 300px">
          <div ref="chart1" class="z-40 rounded-b-lg ct-chart p-1" />
          <div ref="chart2" class="z-40 rounded-b-lg ct-chart p-1" />
        </loading-card>
      </div>

    <!-- <base-trend-metric :chart-data="data" title="fdsa" />-->
    </div>
  </div>
</template>

<script>
import Chartist from 'chartist';
import ctPointLabels from 'chartist-plugin-pointlabels';

export default {
  data() {
    return {
      data: {
        labels: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
        series: [ [10, 12, 9, 7, 8, 5, 15] ],
        weeklyTotal: 506,
      }
    };
  },
  mounted() {
    new Chartist.Bar(this.$refs.chart, {
      labels: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
      series: [ [10, 12, 9, 7, 8, 5, 15] ],
    }, {
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
    });
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

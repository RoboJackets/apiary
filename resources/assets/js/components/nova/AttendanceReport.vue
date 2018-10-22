<template>
    <div>
        <heading class="mb-6">Attendance</heading>

        <!-- TODO: loading-card -->
        <card class="flex flex-col items-center justify-center" style="min-height: 300px">
            <div ref="chart1" class="z-40 rounded-b-lg ct-chart p-1" />
            <div ref="chart2" class="z-40 rounded-b-lg ct-chart p-1" />
        </card>
    </div>
</template>

<script>
import Chartist from 'chartist';

export default {
    mounted() {
        new Chartist.Bar(this.$refs.chart1, {
            labels: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
            series: [ [10, 12, 9, 7, 8, 5, 15] ],
        }, {
            fullWidth: false,
            chartPadding: {
                // right: 40
            }
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

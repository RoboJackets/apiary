<template>
  <div class="attendancereport">
    <div class="flex flex-wrap -mx-3 mb-3">
      <heading class="mb-6 mx-3">Attendance</heading>
      <select @change="newSelection" class="ml-auto min-w-24 h-6 text-xs no-appearance bg-white mx-3">
        <option v-for="range in ranges" :key="range" :value="range" :selected="selectedRange == range">{{ range }} weeks</option>
      </select>

      <ChartistCard :title="'Average Daily Members (All Teams, Last ' + selectedRange + ' Weeks)'" class="w-full" :loading="loading" :total="data.averageWeeklyMembers" total-suffix="average total members per week" :data="data.averageDailyMembers" :options="dailyAverageBarGraphOptions" type="bar"/>

      <ChartistCard class="w-1/2" :loading="loading" v-for="team in data.byTeam" :key="team.name" :data="team.data" :options="teamLineGraphOptions" :title="team.name" type="line" legend/>

      <ChartistCard class="w-full" :loading="loading" :data="data.events" :options="eventBarGraphOptions" :title="'Events (Last ' + selectedRange + ' Weeks)'" :total="data.totalEventAttendees" total-suffix="different people attended an event" :fallback="'No events in the last ' + selectedRange + ' weeks'" type="bar" v-if="data.events.labels.length > 0"/>
      <div class="px-3 mb-6 w-full" v-if="!loading && data.events.labels.length == 0">
        <base-value-metric :loading="false" :title="'Events (Last ' + selectedRange + ' Weeks)'" :value="'No events in the last ' + selectedRange + ' weeks'" prefix=""/>
      </div>
    </div>
  </div>
</template>

<script>
import Chartist from 'chartist';
import ctPointLabels from 'chartist-plugin-pointlabels';
import 'chartist-plugin-legend';
import ChartistCard from './ChartistCard';
// moment and lodash are available globally already, but Chartist is not

export default {
  components: {
    ChartistCard,
  },
  data() {
    return {
      loading: true,
      range: 52,
      data: {
      },
      dailyAverageBarGraphOptions: {
        fullWidth: false,
        axisY: {
          low: 0,
        },
        axisX: {
          showGrid: false,
        },
        plugins: [
          ctPointLabels({
            textAnchor: 'middle',
            labelInterpolationFnc: function(value) {
              return value.toFixed(1);
            },
          }),
        ],
      },
      teamLineGraphOptions: {
        lineSmooth: Chartist.Interpolation.none(),
        showPoint: false,
        axisX: {
          type: Chartist.FixedScaleAxis,
          divisor: 12,
          showGrid: true,
          labelInterpolationFnc: function(value) {
            return moment(value).format('MMM D');
          },
        },
        plugins: [],
      },
      eventBarGraphOptions: {
        fullWidth: false,
        axisY: {
          low: 0,
          onlyInteger: true,
        },
        axisX: {
          showGrid: false,
        },
        plugins: [
          ctPointLabels({
            textAnchor: 'middle',
            labelInterpolationFnc: function(value) {
              return value.toFixed(0);
            },
          }),
        ],
      },
      selectedRange: 52,
      ranges: [
        12,
        26,
        52,
        104,
      ],
    };
  },
  mounted() {
    this.getData();
  },
  methods: {
    newSelection(event) {
      this.selectedRange = event.target.value;
      this.getData();
    },
    getData() {
      this.loading = true;
      Nova.request().get('/api/v1/attendance/statistics?range=' + this.selectedRange).then(response => {
        var stats = response.data.statistics;

        var averageDailyMembersLabels = [];
        var averageDailyMembersSeries = [];
        for (var label in stats.averageDailyMembers) {
          averageDailyMembersLabels.push(label);
          averageDailyMembersSeries.push(stats.averageDailyMembers[label]);
        }

        var eventLabels = [];
        var eventSeries = [];
        for (var label in stats.events) {
          eventLabels.push(label);
          eventSeries.push(stats.events[label]);
        }

        var formatted = {
          averageDailyMembers: {
            labels: averageDailyMembersLabels,
            series: [averageDailyMembersSeries],
          },
          averageWeeklyMembers: stats.averageWeeklyMembers.toFixed(1),
          byTeam: [],
          events: {
            labels: eventLabels,
            series: [eventSeries],
          },
          totalEventAttendees: stats.eventAttendeeTotal,
        };

        for (var teamName in stats.byTeam) {
          var teamData = stats.byTeam[teamName];
          var team = {
            name: teamName,
            data: {
              series: [],
            },
          };

          // Used in a loop below, so only load this once
          var currentYear = moment().year();

          // Create one series per year, so group by year and then map each to a series object
          var series = _.chain(teamData).toPairs().groupBy(function(row) {
            return row[0].substr(0, 4);
          }).map(function(yearData, year) {
            // If there are missing week data points in the year, fill them in with zeros
            if (yearData.length < 52) { // FIXME
              for (var i = 1; i <= 52; i++) {
                var weekName = year + ' ' + i;
                // Skip if that week already has a row (search by the zeroth index being equal to the week name)
                if (_.some(yearData, [0, weekName])) continue;
                else yearData.splice(i - 1, 0, [weekName, 0]);
              }
            }
            return {
              name: year,
              data: _.map(yearData, function(row) {
                // Parse the year given so the date is set correctly, but then change it to the current year so the
                // chart series are overlapping
                return {
                  x: moment(row[0], 'GGGG W').year(currentYear).toDate(),
                  y: row[1],
                };
              }),
            };
          }).reverse().value();
          team.data.series = series;

          formatted.byTeam.push(team);
        }

        this.data = formatted;
        this.loading = false;
      });
    }
  },
}
</script>

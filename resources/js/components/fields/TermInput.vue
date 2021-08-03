<template>
  <div class="form-inline" :class="{ 'is-invalid': isError }">
    <select v-model="semester" id="semesterSelect" class="custom-select" :class="{ 'is-invalid': isError && semester.length !== 2 }">
      <option value="" style="display:none;">Semester</option>
      <option value="08">Fall</option>
      <option value="02">Spring</option>
      <option value="05">Summer</option>
    </select>
    <input v-if="semester.length === 2"
      v-model="year" id="yearSelect" class="form-control" :class="{ 'is-invalid': isError }" maxlength="4" size="6" type="number" min="2000" max="3000" placeholder="Year">
    <button type="radio" class="btn btn-secondary float-right" v-on:click="change()">Current Semester</button>
  </div>
</template>

<script>
/*
 * @prop term - 6-digit Banner term format (YYYYMM)
 * @prop isError: Boolean, defines whether error styles should be displayed
 * @emits input - 6-digit Banner term format (YYYYMM) on update
 */
export default {
  model: {
    prop: 'term',
    event: 'input',
  },
  props: {
    term: {
      type: String,
      default: '',
    },
    isError: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      semester: null,
      year: null,
    }
  },
  computed: {
    semester: {
      get: function() {
        if (this.term) {
          return this.term.slice(-2);
        } else {
          return '';
        }
      },
      set: function(newSemester) {
        var term = this.year + '' + newSemester;
        this.$emit('input', term);
        
        if (this.term && this.term.length === 6) {
          this.$emit('touch', term)
        }
      },
    },
    year: {
      get: function() {
        if (this.term) {
          return this.term.slice(0, -2);
        } else {
          return '';
        }
      },
      set: function(newYear) {
        if (this.semester.length === 2) {
          var term = newYear + '' + this.semester;
          this.$emit('input', term);
        }

        if (this.term && this.term.length === 6) {
          this.$emit('touch', term)
        }
      },
    },
  },
  watch: {
    semester: function(val) {
      this.semester = val;
    },
    year: function(val) {
      this.year = val;
    },
  },
  methods: {
    change: function() {
      var d = new Date();
      var monthValue = d.getMonth();
      var yearValue = d.getFullYear();
      if (monthValue <= 5) {
        document.getElementById('semesterSelect').value = '02';
        this.semester = '02';
      } else if (monthValue <= 9) {
        document.getElementById('semesterSelect').value = '05';
        this.semester = '05';
      } else if (monthValue <= 12) {
        document.getElementById('semesterSelect').value = '08';
        this.semester = '08';
      }
      document.getElementById('yearSelect').value = yearValue;
      this.year = yearValue;
    },
  },
};
</script>

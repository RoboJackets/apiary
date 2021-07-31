<template>
  <div class="form-inline" :class="{ 'is-invalid': isError }">
    <select v-model="semester" class="custom-select" :class="{ 'is-invalid': isError }">
      <option value="" style="display:none;">Semester</option>
      <option value="08">Fall</option>
      <option value="02">Spring</option>
      <option value="05">Summer</option>
    </select>
    <input v-model="year" class="form-control" :class="{ 'is-invalid': isError }" maxlength="4" size="4" type="number" min="2000" max="3000" placeholder="Year">
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
        if (term.length === 6) {
          this.$emit('input', term);
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
        var term = newYear + '' + this.semester;
        if (term.length === 6) {
          this.$emit('input', term);
        }
      },
    },
  },
};
</script>

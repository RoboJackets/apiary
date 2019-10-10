<template>
  <div class="form-inline">
    <select v-model="semester" class="custom-select">
      <option value="" style="display:none;"></option>
      <option value="08">Fall</option>
      <option value="02">Spring</option>
      <option value="05">Summer</option>
    </select>
    <input v-model="year" class="form-control" size="4" type="text" min="2000" max="3000">
  </div>
</template>

<script>
/*
 * @prop term - 6-digit Banner term format (YYYYMM)
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
        this.$emit('input', term);
      },
    },
  },
};
</script>

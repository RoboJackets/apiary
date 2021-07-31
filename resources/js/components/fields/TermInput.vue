<template>
  <div class="form-inline">
    <select v-model="semester" class="custom-select">
      <option value="" style="display:none;">Semester</option>
      <option value="08">Fall</option>
      <option value="02">Spring</option>
      <option value="05">Summer</option>
    </select>
    <input v-model="year" class="form-control" size="4" type="text" min="2000" max="3000" placeholder="Year">
  </div>
</template>

<script>
/*
 * @prop value - 6-digit Banner term format (YYYYMM)
 * @emits input - 6-digit Banner term format (YYYYMM) on update
 */
export default {
  model: {
    prop: 'value',
    event: 'input',
  },
  props: {
    value: {
      type: String,
      default: '',
    },
  },
  computed: {
    semester: {
      get: function() {
        if (this.value) {
          return this.value.slice(-2);
        } else {
          return '';
        }
      },
      set: function(newSemester) {
        var value = this.year + '' + newSemester;
        this.$emit('input', value);
      },
    },
    year: {
      get: function() {
        if (this.value) {
          return this.value.slice(0, -2);
        } else {
          return '';
        }
      },
      set: function(newYear) {
        var value = newYear + '' + this.semester;
        this.$emit('input', value);
      },
    },
  },
};
</script>

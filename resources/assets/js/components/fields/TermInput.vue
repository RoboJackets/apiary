<template>
  <div class="form-inline">
    <select v-bind:value="semester" v-on:input="updateSemester($event.target.value)" class="custom-select">
      <option value="" style="display:none;"></option>
      <option value="08">Fall</option>
      <option value="01">Spring</option>
      <option value="05">Summer</option>
    </select>
    <input v-bind:value="year" v-on:input="updateYear($event.target.value)" class="form-control" size="4" type="text" min="2000" max="3000">
  </div>
</template>

<script>
  export default {
    model:{
      prop: 'term',
      event: 'input'
    },
    props: {
      term: {
        type: String
      }
    },
    data() {
      return {
        semester: '',
        year: ''
      }
    },
    mounted() {
      this.parseTerm();
    },
    methods: {
      parseTerm: function () {
        var term = this.term;
        console.log("Term: " + typeof(this.term) + this.term);
        this.semester = '08';
        this.year = '2013';
      },
      updateYear: function (year) {
        // Ensure that we return a String
        var term = this.semester + "" + year;
        this.year = year;
        this.$emit('input', term)
      },
      updateSemester: function (semester) {
        // Ensure that we return a String
        var term = semester + "" + this.year;
        this.semester = semester;
        this.$emit('input', term)
      }
    }
  }
</script>


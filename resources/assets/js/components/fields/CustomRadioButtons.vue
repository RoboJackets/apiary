<template>
  <div class="btn-group" :id="id" data-toggle="buttons">
    <label v-for="option in options" class="btn btn-secondary" v-bind:class="{ active: option.value == value}" @click.left="update">
      <input v-model="value" type="radio" :value="option.value" autocomplete="off"> {{option.text}}
    </label>
  </div>
</template>


<script type="text/javascript">
  /*
   *  @props options: An array of Objects in the format {value: "value", text: "text"} such that value is the Form Value and Text is the text displayed to the user
   *  @props value: value to set the currently selected option. Best used with v-model
   * @props id: id of the ButtonGroup
   */

  export default {
    props: {
      options: {
        type: Array,
        required: true
      },
      value: {
        type: String,
        default: ''
      },
      id: {
        type: String,
        default: ''
      }
    },
    methods: {
      update: function (event) {
        //this.value = event.target.firstChild.value;
        this.$emit('input', event.target.firstChild.value);
      }
    }
  }
</script>
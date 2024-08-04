<template>
    <img style="margin-bottom: 0.25em" class="align-middle" width="30" :src="imgPath" :alt="userFacingCardBrand" />
</template>

<script>
export default {
  name: 'PaymentCardIcon.vue',
  props: {
    normalizedCardBrand: {
      type: String,
    },
    userFacingCardBrand: {
      type: String,
    }
  },
  computed: {
    imgPath() {
      return `/img/${this.normalizedCardBrand}.svg`
    }
  }
}
</script>

<style scoped>

</style>

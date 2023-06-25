<template>
  <div>
    <div>{{ methodPresentation }} {{ acceptedByPresentation }}</div>
    <div v-if="isCard">
      <payment-card-icon v-if="cardImageAvailable"
                         :normalizedCardBrand="normalizedCardBrand"
                         :userFacingCardBrand="cardBrandPresentation"
      /> {{ cardInfoString }}
    </div>
  </div>
</template>

<script>
export default {
  name: 'PaymentMethodDetails.vue',
  props: {
    payment: {
      type: Object,
    }
  },
  computed: {
    isSquare() {
      return this.payment.method === "square";
    },
    isCard() {
      return ["square", "swipe"].includes(this.payment.method);
    },
    cardType() {
      const rawCardType = this.payment.card_type;
      if (!rawCardType) {
        return "";
      }


      return `${rawCardType.charAt(0).toUpperCase()}${rawCardType.substring(1).toLowerCase()}`;
    },
    methodPresentation() {
      if (this.payment.method === "square") {
        return "Online Payment";
      }

      return this.payment.method_presentation;
    },
    acceptedByPresentation() {
      if (this.isSquare) {
        return "";
      }

      if (this.payment.recorded_by_user && this.payment.recorded_by_user.name) {
        return `accepted by ${this.payment.recorded_by_user.name}`;
      }

      return "";
    },
    normalizedCardBrand() {
      if (!this.payment.card_brand) {
        return "";
      }

      return this.payment.card_brand.toLowerCase().replaceAll(/[_\-\s]/g, "");
    },
    cardBrandPresentation() {
      const cardBrandMap = {
        "americanexpress": "American Express",
        "discover": "Discover",
        "visa": "Visa",
        "mastercard": "MasterCard",
      }

      if (cardBrandMap[this.normalizedCardBrand]) {
        return cardBrandMap[this.normalizedCardBrand];
      }

      return this.payment.card_brand || "Unknown card brand";
    },
    cardInfoString() {
      if (this.cardBrandPresentation === "Unknown card brand") {
        if (!this.payment.last_4) {
          return "";
        } else {
          return `Unknown card ending in ${this.payment.last_4}`
        }
      }

      if (this.payment.last_4) {
        return `${this.cardBrandPresentation} ${this.cardType} ${this.payment.last_4}`;
      }

      return this.cardBrandPresentation;
    },
    cardImageAvailable() {
      return ["americanexpress", "discover", "visa", "mastercard"].includes(this.normalizedCardBrand);
    },
  }
}
</script>

<style scoped>

</style>

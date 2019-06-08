<template>
  <div>
    <h3 v-if="payments.length !== 0">Payments</h3>

    <div v-for="(payment, index) in payments">
      <template v-if="payments.length > 1">
        <hr/>
        <h5>Payment {{index + 1}}</h5>
      </template>
      <div class="form-group row">
        <label for="payment-method" class="col-sm-2 col-form-label">Payment Method</label>
        <div class="col-sm-10 col-lg-4">
          <input 
          v-model="payment.method_presentation"
          type="text"
          readonly
          class="form-control"
          id="payment-method">
        </div>

        <label for="payment-timestamp" class="col-sm-2 col-form-label">TImestamp</label>
        <div class="col-sm-10 col-lg-4">
          <input
          v-model="payment.created_at"
          type="text"
          readonly
          class="form-control"
          id="payment-timestamp">
        </div>
      </div>

      <div class="form-group row">  
        <label for="payment-amount" class="col-sm-2 col-form-label">Payment Amount</label>
        <div class="col-sm-10 col-lg-4">
          <div class="input-group">
            <div class="input-group-prepend">
              <div class="input-group-text">$</div>
            </div>
            <input
            v-model="payment.amount"
            type="text"
            readonly
            class="form-control"
            id="payment-amount">
          </div>
        </div>

        <label for="payment-fee" class="col-sm-2 col-form-label">Transaction Fee</label>
        <div class="col-sm-10 col-lg-4">
          <div class="input-group">
            <div class="input-group-prepend">
              <div class="input-group-text">$</div>
            </div>
            <input
            v-model="payment.processing_fee"
            type="text"
            readonly
            class="form-control"
            id="payment-fee">
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    payments: {
      required: true,
      type: Array,
    },
  },
};
</script>

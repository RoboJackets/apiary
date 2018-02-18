<template>
  <div class="row">
    <div class="col-12">
      <form id="AcceptPaymentForm" v-on:submit.prevent="submit">
        <div class="form-group row">
          <label for="payment-method" class="col-12 col-md-3 col-lg-2 col-form-label">Payment Method</label>
          <div class="col-12 col-md-9 col-lg-4">
            <custom-radio-buttons
              v-model="payment.method"
              :options="paymentMethods"
              id="payment-method"
              :is-error="$v.payment.method.$error"
              @input="$v.payment.method.$touch()">
            </custom-radio-buttons>
          </div>
        </div>

        <div class="form-group row">
          <label for="payment-amount" class="col-sm-2 col-form-label">Payment Amount</label>
          <div class="col-sm-10 col-lg-4">
            <div class="input-group">
              <span class="input-group-addon">$</span>
              <input
                v-model="payment.amount"
                type="number"
                class="form-control"
                :class="{ 'is-invalid': $v.payment.amount.$error }"
                @input="$v.payment.amount.$touch()"
                id="payment-amount">
            </div>
            <small id="payment-amount-help" class="form-text text-muted">
              Record the actual amount of money being collected. including surcharges or processing fees.
            </small>
          </div>
        </div>

        <div class="row">
          <div class="col-sm-10 col-md-3 col-lg-2">
            <p v-if="payment.method != ''">
              <strong>Additional Instructions: </strong>
            </p>
          </div>
          <div class="col-12 col-md-9 col-lg-6">
            <template v-if="payment.method == 'cash'">
              <ol>
                <li>Place the money in the cash box.</li>
                <li>Record the cash transaction in the paper receipt book. Offer the carbon copy to the member.</li>
              </ol>
            </template>
            <template v-else-if="payment.method == 'check'">
              <ol>
                <li>Verify that the check is properly filled out.</li>
                <ul>
                  <li>The <em>To</em> field of the check should say <em>RoboJackets</em>.</li>
                  <li>The check should be signed on the front.</li>
                  <li>The check should not be endorsed (signed on the back).</li>
                </ul>
                <li>Place the check in the cash box.</li>
                <li>Record the check transaction in the paper receipt book. Offer the carbon copy to the member.</li>
              </ol>
            </template>
            <template v-else-if="payment.method == 'swipe'">
              <p class="font-weight-bold">Swiped cards incur a $3 processing fee. Add $3 to payment amount.</p>
              <ol>
                <li>Open the Square Point of Sale App.</li>
                <li>Select "Library" from the top tabs.</li>
                <li>Find the correct dues option and add 1 to the cart</li>
                <li>Press the charge button, ensuring that the total matches the cost (Plus $3).</li>
                <li>Follow the prompts to complete the transaction.</li>
                <li>It is not required to provide a paper receipt for swiped card transactions.</li>
              </ol>
            </template>
              <template v-else-if="payment.method == 'square'">
              <p class="font-weight-bold">TREASURER USE ONLY!</p> Square will usually automatically enter a payment. Only use this if you've confirmed that a payment went through in the Square dashboard but didn't update here.
              <ul>
                  <li>Square transactions incur a $3 processing fee. Add $3 to payment amount.</li>
                  <li>This is <em>not</em> the same as SquareCash.</li>
              </ul>
            </template>
            <template v-else-if="payment.method == 'squarecash'">
              <p class="font-weight-bold">TREASURER USE ONLY!</p> Check with the treasurer before marking SquareCash payments as complete.
              <ol>
                <li>View the email saying that the money has been deposited into our account. This is the second email of a given transaction.</li>
                <li>It is not required to provide a paper receipt for SquareCash transactions.</li>
              </ol>
            </template>
          </div>
        </div>

        <div class="row">
          <div class="col-12 col-md-6">
            <button type="submit" class="btn btn-primary float-right">Submit</button>
          </div>
        </div>

      </form>
    </div>
  </div>
</template>

<script>
  /*
   *  @props transactionType: Morph of the Transaction model to attach the payment
   *  @props transactionID: ID of the specific morph of a given Transaction Model
   *  @props amount: The amount of currency in this payment
   *  @emit done: Event emitted after a payment is recorded successfully
   */

  import { numeric, required, sameAs } from 'vuelidate/lib/validators';

  export default {
    props: {
      transactionType: {
        type: String,
        required: true
      },
      transactionId: {
        type: Number,
        required: true
      },
      amount: {
        required: true
      }
    },
    data() {
      return {
        payment: {
          payable_id: this.transactionId,
          payable_type: "App\\" + this.transactionType,
          method: "",
          amount: null
        },
        paymentMethods: [
          {value: "cash", text: "Cash"},
          {value: "check", text: "Check"},
          {value: "swipe", text: "Swiped Card"},
          {value: "square", text: "Square (Online)"},
          {value: "squarecash", text: "SquareCash"},
        ],
        baseUrl: "/api/v1/payments",
      }
    },
    methods: {
      submit: function() {
        //Perform form Validation
        if (this.$v.$invalid) {
          this.$v.$touch();
          return;
        }

        axios.post(this.baseUrl, this.payment)
          .then(response => {
            this.$emit('done');
          })
          .catch(response => {
            console.log(response);
            swal("Connection Error", "Unable to record payment. Check your internet connection or try refreshing the page.", "error");
          })
      }
    },
    computed: {
      expectedAmount: function() {
        if (this.payment.method == 'swipe' || this.payment.method == 'square') {
          return parseFloat(this.amount) + 3;
        } else {
          return parseFloat(this.amount);
        }
      }
    },
    validations: {
      payment: {
        method: {
          required
        },
        amount: {
          numeric,
          SameAsExpected (value) {
            return value == this.expectedAmount;
          }
        }
      }
    }
  }
</script>

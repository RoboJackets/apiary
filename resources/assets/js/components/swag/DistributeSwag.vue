<template>
  <div class="row">
    <div class="col-12">
      <form id="DistributeSwagForm" v-on:submit.prevent="submit">
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
      swag_polo_status: {
        required: true
      }, 
      swag_shirt_status: {
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
            sweetAlert("Connection Error", "Unable to record payment. Check your internet connection or try refreshing the page.", "error");
          })
      }
    },
    computed: {
      expectedAmount: function() {
        if (this.payment.method == 'swipe') {
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

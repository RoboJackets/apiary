<template>
  <div>
    <h3>Transaction Details</h3>
    <div class="form-group row">
      <label for="user-name" class="col-sm-2 col-form-label">Name</label>
      <div class="col-sm-10 col-lg-4">
        <input v-model="user.name" type="text" readonly class="form-control" id="user-name">
      </div>

      <label for="transactions-timestamp" class="col-sm-2 col-form-label">Timestamp</label>
      <div class="col-sm-10 col-lg-4">
        <input v-model="duesTransaction.updated_at" type="text" readonly class="form-control" id="transactions-timestamp">
      </div>
    </div>

    <div class="form-group row">
      <label for="dues-package" class="col-sm-2 col-form-label">Dues Package</label>
      <div class="col-sm-10 col-lg-4">
        <input v-model="package.name" type="text" readonly class="form-control" id="dues-package">
      </div>

      <label for="package-cost" class="col-sm-2 col-form-label">Dues Cost</label>
      <div class="col-sm-10 col-lg-4">
        <div class="input-group">
          <span class="input-group-addon">$</span>
          <input v-model="package.cost" type="text" readonly class="form-control" id="package-cost">
        </div>
      </div>
    </div>
    <template v-if="duesTransaction.status == 'pending'">
      <h3>
        Record Payment
      </h3>
      <accept-payment
        transaction-type="DuesTransaction"
        :transaction-id="parseInt(duesTransactionId)"
        :amount="package.cost"
        @done="paymentSubmitted">
      </accept-payment>
    </template>
  </div>
</template>

<script>
  export default {
    props: {
      duesTransactionId: {
        required:true
      }
    },
    data() {
      return {
        duesTransaction: {},
        user: {},
        package: {},
        dataUrl: "",
        baseUrl: "/api/v1/dues/transactions/"
      }
    },
    mounted() {
      this.dataUrl = this.baseUrl + this.duesTransactionId;
      axios.get(this.dataUrl)
        .then(response => {
          this.duesTransaction = response.data.dues_transaction;
          this.user = this.duesTransaction.user;
          this.package = this.duesTransaction.package;
        })
        .catch(response => {
          console.log(response);
          sweetAlert("Connection Error", "Unable to load data. Check your internet connection or try refreshing the page.", "error");
        });
    },
    methods: {
      paymentSubmitted: function () {
        window.location.href= "/admin/dues/pending";
      }
    }
  }
</script>
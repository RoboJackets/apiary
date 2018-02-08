<template>
  <div>
    <h3>Distribution Details</h3>
    <div class="form-group row">
      <label for="user-name" class="col-sm-2 col-form-label">Name</label>
      <div class="col-sm-10 col-lg-4">
        <input v-model="user.name" type="text" readonly class="form-control" id="user-name">
      </div>

      <label for="dues-package" class="col-sm-2 col-form-label">Dues Package</label>
      <div class="col-sm-10 col-lg-4">
        <input v-model="package.name" type="text" readonly class="form-control" id="dues-package">
      </div>
    </div>

    <div class="form-group row">
      <label for="shirt_size" class="col-sm-2 col-form-label">Shirt Size</label>
      <div class="col-sm-10 col-lg-4">
        <input v-model="user.shirt_size" type="text" readonly class="form-control" id="shirt_size"
               style="text-transform:uppercase">
      </div>

      <label for="polo_size" class="col-sm-2 col-form-label">Polo Size</label>
      <div class="col-sm-10 col-lg-4">
        <div class="input-group">
          <input v-model="user.polo_size" type="text" readonly class="form-control" id="polo_size"
                 style="text-transform:uppercase">
        </div>
      </div>
    </div>
    <template>
      <h3>
        Record Distribution
      </h3>
      <distribute-swag
        transaction-type="DuesTransaction"
        :transaction-id="parseInt(duesTransactionId)"
        :swag_polo_provided="duesTransaction.swag_polo_provided"
        :swag_shirt_provided="duesTransaction.swag_shirt_provided"
        :swag_polo_status="duesTransaction.swag_polo_status"
        :swag_shirt_status="duesTransaction.swag_shirt_status"
        @done="swagDistributed">
      </distribute-swag>
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
      swagDistributed: function () {
        window.location.href= (document.referrer) ? document.referrer : "/admin/swag";
      }
    }
    }
</script>
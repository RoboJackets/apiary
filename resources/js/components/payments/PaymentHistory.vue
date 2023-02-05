<template>
  <div>
    <loading-spinner :active="loading" text="Crunching the numbers..." />
    <div v-if="!loading">
      <p>Below is a list of all payments you have made in MyRoboJackets. If you have any questions about
        what you see here, post in #treasury-helpdesk for assistance.</p>
      <table class="table table-responsive table-borderless table-striped">
        <thead>
        <tr>
          <th scope="col">Date</th>
          <th scope="col">Item</th>
          <th scope="col">Amount Paid</th>
          <th scope="col">Payment Method</th>
          <th scope="col">Notes</th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="payment in duesPayments" :key="payment.id">
          <td class="align-middle">{{ payment.updated_at | moment("MMM D, YYYY h:mm A") }}</td>
          <td class="align-middle">Dues: {{ payment.dues_transaction.package.name }}</td>
          <td class="align-middle">${{ payment.amount }}</td>
          <td class="align-middle"><payment-method-details :payment="payment" /></td>
          <td class="align-middle"><a v-if="payment.receipt_url" :href="payment.receipt_url">View Receipt</a></td>
        </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
export default {
  name: "PaymentHistory.vue",
  props: {
    userUid: {
      type: String,
      required: true,
    },
  },
  data() {
    return {
      loading: true,
      duesPayments: [],
    }
  },
  mounted() {
    this.fetchData();
  },
  methods: {
    async fetchData() {
      this.loading = true;
      const paymentsData = await axios.get(`/api/v1/payments/user/${this.userUid}`);
      this.duesPayments = paymentsData.data.duesTransactions;
      this.loading = false;
    }
  }
}
</script>

<style scoped>

</style>

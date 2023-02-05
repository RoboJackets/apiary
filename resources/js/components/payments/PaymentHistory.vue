<template>
  <div>
    <p>Below is a list of all payments you have made in MyRoboJackets. If you have any questions about
      what you see here, post in #treasury-helpdesk for assistance.</p>
    <h4>Dues Transactions</h4>
    <table class="table table-sm table-responsive table-borderless">
      <thead>
      <tr>
        <th scope="col">Payment Date</th>
        <th scope="col">Dues Package</th>
        <th scope="col">Amount Paid</th>
        <th scope="col">Notes</th>
      </tr>
      </thead>
      <tbody>
      <tr v-for="payment in duesPayments" :key="payment.id">
        <td class="align-middle">{{ payment.created_at }}</td>
        <td class="align-middle">{{ payment.dues_transaction.package.name }}</td>
        <td class="align-middle">${{ payment.amount }} ({{ payment.method_presentation }})</td>
        <td class="align-middle">View Receipt</td>
      </tr>
      </tbody>
    </table>
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
    }
  }
}
</script>

<style scoped>

</style>

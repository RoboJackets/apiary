<template>
  <div>
    <p>Below is a list of all payments you have made in MyRoboJackets. If you have any questions about
      what you see here, post in <a
        href="https://robojackets.slack.com/channels/treasury-helpdesk">#treasury-helpdesk</a> for assistance.</p>
    <p><em>Dates are displayed in your device's current timezone.</em></p>
    <loading-spinner :active="loading" text="Crunching the numbers..."/>
    <div v-if="!loading && error" class="alert alert-danger" role="alert">
      {{ error }}. Check your internet connection or try refreshing the page.
    </div>
    <table v-else-if="!loading" class="table table-responsive table-borderless table-striped payments-table">
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
        <tr v-if="!payments.length">
          <td class="align-middle text-center" colspan="5">You haven't made any payments yet.</td>
        </tr>
        <tr v-for="payment in payments" :key="payment.id">
          <td class="align-middle">{{ payment.updated_at | moment("MMM D, YYYY h:mm A") }}</td>
          <td class="align-middle">{{ getPaymentTitle(payment) }}</td>
          <td class="align-middle">${{ payment.amount }}</td>
          <td class="align-middle">
            <payment-method-details :payment="payment"/>
          </td>
          <td class="align-middle"><a v-if="payment.receipt_url" :href="payment.receipt_url">View Receipt</a></td>
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
      error: false,
      payments: [],
    }
  },
  mounted() {
    this.fetchData();
  },
  methods: {
    async fetchData() {
      this.loading = true;
      try {
        const paymentsData = await axios.get(`/api/v1/payments/user/${this.userUid}`);
        this.payments = paymentsData.data.payments;
      } catch (e) {
        this.error = `Unable to fetch payments data: ${e.message}`
      } finally {
        this.loading = false;
      }
    },
    getPaymentTitle(payment) {
      if (payment.dues_transaction) {
        if (payment.dues_transaction.package && payment.dues_transaction.package.name) {
          return `Dues: ${payment.dues_transaction.package.name}`;
        }
        return `Dues: Payment ID ${payment.id}`;
      }

      if (payment.travel_assignment) {
        if (payment.travel_assignment.travel && payment.travel_assignment.travel.name) {
          return `Travel: ${payment.travel_assignment.travel.name}`;
        }

        return `Travel: Payment ID ${payment.id}`;
      }

      return `Payment ID ${payment.id}`;
    }
  }
}
</script>

<style scoped>
.payments-table td {
  min-width: 140px;
}
</style>

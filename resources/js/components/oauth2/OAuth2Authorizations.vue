<template>
  <div>
    <loading-spinner :active="loading" />
    <div v-if="!loading && error" class="alert alert-danger" role="alert">
      {{ error }}
    </div>
    <div v-else-if="!loading && !tokens.length">
      <p>You haven't granted any applications access to your MyRoboJackets account.</p>
    </div>
    <div v-else-if="!loading">
      <p>You have granted the following applications access to your MyRoboJackets account.</p>
      <div class="alert alert-warning">
        Applications may be listed multiple times if you have provided multiple authorizations, such as by signing in
        on multiple devices. To completely remove an application, click Remove for each of its entries below.
      </div>
      <table class="table table-sm table-responsive table-borderless">
        <thead>
        <tr>
          <th scope="col">Application</th>
          <th scope="col">Authorized On</th>
          <th scope="col">Actions</th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="token in tokens" :key="token.id">
          <td class="align-middle" v-if="token.client && token.client.name">{{ token.client.name }}</td>
          <td class="align-middle" v-else>Unnamed Client Application</td>
          <td class="align-middle">{{ token.created_at | moment("MMM D, YYYY") }}</td>
          <td class="align-middle">
            <button type="button" class="btn btn-sm btn-outline-danger"
                    :disabled="deleting"
                    v-on:click="deleteToken(token)">Remove</button>
          </td>
        </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import Toast from "../../mixins/Toast";

export default {
  name: "OAuth2Authorizations",
  mixins: [Toast],
  data() {
    return {
      loading: true,
      deleting: false,
      error: false,
      tokens: [],
    }
  },
  mounted() {
    this.fetchData();
  },
  methods: {
    async fetchData() {
      this.loading = true;
      try {
        const tokens = await axios.get("/oauth/tokens")
        this.tokens = tokens.data.filter(token => {
          const expires_at = new Date(token.expires_at);
          return !token.revoked && expires_at > new Date() // Don't show expired or revoked tokens
        }).sort((a, b) => {
          const dateFormat = "YYYY-MM-DD HH:mm:sss";
          const aDate = this.$moment(a.created_at, dateFormat);
          const bDate = this.$moment(b.created_at, dateFormat);
          return aDate - bDate;
        })
      } catch (e) {
        this.error = `Unable to fetch authorized applications: ${e.message}`
      } finally {
        this.loading = false;
      }
    },
    async deleteToken(token) {
      this.deleting = true;

      const confirmResult = await Swal.fire({
        title: `Are you sure?`,
        text: `Once removed, you will have to sign into ${token.client.name} again.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Remove',
        confirmButtonColor: '#dc3545',
      })

      if (!confirmResult.isConfirmed) {
        this.deleting = false;
        return;
      }

      try {
        await axios.delete(`/oauth/tokens/${token.id}`);
        this.toast('success', `Application access revoked`)
        await this.fetchData();
      } catch (e) {
        Swal.fire(
          'Connection Error',
          'Unable to remove this application. Check your internet connection or try refreshing the page.',
          'error'
        );
      } finally {
        this.deleting = false;
      }
    },
  }
}
</script>

<style scoped>

</style>

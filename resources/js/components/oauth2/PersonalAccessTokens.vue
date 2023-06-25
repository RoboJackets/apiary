<template>
  <div>
    <loading-spinner :active="loading" />
    <div v-if="!loading && error" class="alert alert-danger" role="alert">
      {{ error }}
    </div>
    <div v-else-if="!loading && !tokens.length">
      <p>You don't have any personal access tokens right now.</p>
    </div>
    <div v-else-if="!loading">
      <p>The following personal access tokens have access to your account.</p>
      <table class="table table-sm table-responsive table-borderless">
        <thead>
        <tr>
          <th scope="col">Name</th>
          <th scope="col">Created On</th>
          <th scope="col">Actions</th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="token in tokens" :key="token.id">
          <td class="align-middle">{{ token.name || "Unnamed Personal Access Token" }}</td>
          <td class="align-middle">{{ token.created_at | moment("MMMM D, YYYY") }}</td>
          <td class="align-middle">
            <button type="button" class="btn btn-sm btn-outline-danger"
                    :disabled="deleting"
                    v-on:click="deletePersonalAccessToken(token)">Remove</button>
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
  name: "PersonalAccessTokens.vue",
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
    this.fetchData()
  },
  methods: {
    async fetchData() {
      this.loading = true;
      try {
        const tokens = await axios.get("/oauth/personal-access-tokens")
        this.tokens = tokens.data.filter(token => {
          const expires_at = this.$moment(token.expires_at);
          return !token.revoked && expires_at > this.$moment() // Don't show expired or revoked tokens
        }).sort((a, b) => {
          const dateFormat = "YYYY-MM-DD HH:mm:sss";
          const aDate = this.$moment(a.created_at, dateFormat);
          const bDate = this.$moment(b.created_at, dateFormat);
          return aDate - bDate;
        })
      } catch (e) {
        this.error = `Unable to fetch personal access tokens: ${e.message}`
      } finally {
        this.loading = false;
      }
    },
    async deletePersonalAccessToken(token) {
      this.deleting = true;

      const confirmResult = await Swal.fire({
        title: `Remove ${token.name}?`,
        text: "Once deleted, this personal access token will no longer work.",
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
        await axios.delete(`/oauth/personal-access-tokens/${token.id}`);
        this.toast('success', `${token.name} removed`)
        await this.fetchData();
      } catch (e) {
        Swal.fire(
          'Connection Error',
          'Unable to remove this personal access token. Check your internet connection or try refreshing the page.',
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

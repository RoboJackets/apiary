<template>
  <div class="row">
    <div class="col-12">
      <form id="fasetEditForm" v-on:submit.prevent="submit">
        <div class="form-group">
          <label for="faset-name">Name</label>
          <input v-model="fasetVisit.faset_name" type="text" class="form-control" id="faset-name" name="faset-name" autocomplete="off">
          <small class="form-text text-muted">First and last name</small>
        </div>

        <div class="form-group">
          <label for="faset-email">Email</label>
          <input v-model="fasetVisit.faset_email" type="email" class="form-control" id="faset-email" name="faset-email" autocomplete="off">
        </div>
      </form>
    </div>
  </div>
</template>

<script>
  export default {
    props: ['fasetVisitId'],
    data() {
      return {
        fasetVisit: {},
        baseFasetUrl: "/api/v1/faset/"
      }
    },
    mounted() {
      var dataUrl = this.baseFasetUrl + this.fasetVisitId;
      axios.get(dataUrl)
        .then(response => {
          this.fasetVisit = response.data;

        })
        .catch(response => {
          console.log(response);
          sweetAlert("Connection Error", "Unable to load data. Check your internet connection or try refreshing the page.", "error");
        });
    }
  }
</script>

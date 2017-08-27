<template>
  <div class="row">
    <div class="col-12">
      <table id="DataTable" class="table table-striped table-bordered table-hover" cellspacing="0" width="100%">
      </table>
    </div>
  </div>
</template>

<script>
  /*
   *  @props dataUrl: An https endpoint that when called will return an array of objects with data compatible with DataTables
   *  @props columns: Columns config data for DataTables, API: https://datatables.net/reference/option/
   * @props dataPath: the top level key that holds the data
   */
  export default {
    props: ['columns', 'dataUrl', 'dataPath'],
    data() {
      return {
        tableData: {},
        columnsDatatables: []
      }
    },
    mounted() {
      axios.get(this.dataUrl)
        .then(response => {
          this.tableData = response.data;

          $('#DataTable').DataTable({
            stateSave: true,
            data: this.tableData[this.dataPath],
            columns: this.columns,
            pageLength: 100,
            lengthMenu: [20, 50, 100, 200, 500, 5000]
          });
        })
        .catch(response => {
          console.log(response);
          sweetAlert("Connection Error", "Unable to load data. Check your internet connection or try refreshing the page.", "error");
        });

    }
  }
</script>

<template>
  <div class="row">
    <div class="col-12">
      <table :id="id" class="table table-striped table-bordered table-hover" cellspacing="0" width="100%">
      </table>
    </div>
  </div>
</template>

<script>
  /*
   *  @props dataUrl: (Optional) An https endpoint that when called will return an array of objects with data compatible with DataTables
   *  @props dataObject: The data object that will be used to populate the table if no dataUrl is supplied
   *  @props columns: Columns config data for DataTables, API: https://datatables.net/reference/option/
   *  @props dataPath: the top level key that holds the data
   *  @props delete: boolean value indicating if there should be delete buttons on each row
   */
  export default {
    props: {
      columns: Array,
      dataUrl: String,
      dataObject: Array,
      dataPath: String,
      id: {
        type: String,
        default: "DataTable"
      }
    },
    data() {
      return {
        tableData: [],
        table: {}
      }
    },
    mounted() {
      let customDom = "<'row'<'col-sm-6'l><'col-sm-6'f>>" +
          "<'row'<'col-sm-12'tr>>" +
          "<'row'<'col-sm-5'Bi><'col-sm-7'p>>";
      if (typeof(this.dataUrl) !== 'undefined') {
        axios.get(this.dataUrl)
          .then(response => {
            this.tableData = response.data[this.dataPath];

            this.table = $('#' + this.id).DataTable({
              stateSave: true,
              data: this.tableData,
              columns: this.columns,
              pageLength: 100,
              lengthMenu: [20, 50, 100, 200, 500, 5000],
              dom: customDom,
              buttons: [
                  'copy', 'csv', 'excel', 'print'
              ]
            });
          })
          .catch(response => {
            console.log(response);
            swal("Connection Error", "Unable to load data. Check your internet connection or try refreshing the page.", "error");
          });
      } else {
        this.tableData = this.dataObject;

        this.table = $('#' + this.id).DataTable({
          stateSave: true,
          data: this.tableData,
          columns: this.columns,
          pageLength: 100,
          lengthMenu: [20, 50, 100, 200, 500, 5000],
          dom: customDom,
          buttons: [
              'copy', 'csv', 'excel', 'print'
          ]
        });
      }
    },
    watch: {
      dataObject: function(newDataObject) {
        if (newDataObject) {

          this.tableData = newDataObject;

          this.table.clear();
          this.table.rows.add(this.tableData).draw();
        }
      }
    }
  }
</script>

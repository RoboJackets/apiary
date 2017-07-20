<template>
  <div class="container">
    <div class="row">
      <div class="col-12">
        <table id="DataTable" class="table table-striped table-bordered" cellspacing="0" width="100%">
        </table>
      </div>
    </div>
  </div>
</template>

<script>
  

  export default {
    props: ['columns', 'dataUrl'],
    data() {
      return {
        tableData: {},
        columnsDatatables: []
      }
    },
    mounted() {
      console.log(this.dataUrl);
      console.log(this.columns);

      this.columnsDatatables = this.columns.map(function(column) {
        return {'title': column, 'data': column};
      });

      axios.get(this.dataUrl)
        .then(response => {
          this.tableData = response.data;

          $('#DataTable').DataTable({
            data: this.tableData,
            columns: this.columnsDatatables
          });
        })
        .catch(response => {
          console.log(response);
        });


      
    }
  }
</script>

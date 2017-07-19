<template>
  <div class="container">
    <div class="row">
      <div class="col-12">
        <table id="DataTable">
          <thead>
            <tr>
              <th>Firstname</th>
              <th>Lastname</th> 
              <th>Age</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Jill</td>
              <td>Smith</td> 
              <td>50</td>
            </tr>
            <tr>
              <td>Eve</td>
              <td>Jackson</td> 
              <td>94</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
  

  export default {
    mounted() {
      $('#DataTable').DataTable();
    }
  }
</script>

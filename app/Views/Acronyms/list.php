<div class="row p-2 p-md-4 mb-3">
<?php if (count($data) == 0): ?>
  <div class="col-12">
    <div class="alert alert-warning" role="alert">
      No records found.
    </div>
  </div>
  <?php else: ?>


    <div class="col-12">

    <table class="table  table-hover" id="acronyms-list">
      <thead class="thead-dark">
        <tr>
          <th scope="col">#</th>
          <th scope="col">Acronym</th>
          <th scope="col" style="width:55%">Description</th>
          <th scope="col">Update Date</th>
          <th scope="col">Action</th>
        </tr>
      </thead>
      <tbody class="bg-white">
        <?php foreach ($data as $key=>$row): ?>
            <tr scope="row" id="<?php echo $row['id'];?>">
                <td><?php echo $key+1; ?></td>
                <td><?php echo $row['acronym'];?> </td>
                <td><?php echo $row['description'];?></td>
                <td><?php $timestamp = strtotime($row['update_date']) + (330*60); echo date("Y-m-d h:i A", $timestamp); ?></td>

                <td>
                    <a title="Edit" href="/documents-acronyms/add/<?php echo $row['id'];?>" class="btn btn-warning">
                        <i class="fa fa-edit"></i>
                    </a>
                    <?php if (session()->get('is-admin')): ?>
                    <a title="Delete" onclick="deleteItem(<?php echo $row['id'];?>)" class="btn btn-danger ml-2">
                        <i class="fa fa-trash text-light"></i>
                    </a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

</div>
<?php endif; ?>

</div>

<script>
  $(document).ready( function () {
    var table = $('#acronyms-list').DataTable({
      "responsive": true,
      "autoWidth": false,
      "stateSave": true
    });
    $('.l-navbar .nav__link, #footer-icons').on('click', function () {
      table.state.clear();
    });
  });

 function deleteItem(id){

    bootbox.confirm("Do you really want to delete record?", function(result) {
      if(result){
        $.ajax({
           url: '/documents-acronyms/delete/'+id,
           type: 'GET',
           success: function(response){
              console.log(response);
              console.log('/documents-acronyms/delete/'+id);
              response = JSON.parse(response);
              if(response.success == "True"){
                  $("#"+id).fadeOut(800)
              }else{
                 bootbox.alert('Record not deleted.');
              }
            }
         });
      }else{
        console.log('Delete Cancelled');
      }

    });

 }

</script>


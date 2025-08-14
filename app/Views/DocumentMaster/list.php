
<div class="row p-0 p-md-4">

<?php if (count($data) == 0): ?>

  <div class="col-12">
    <div class="alert alert-warning" role="alert">
      No records found.
    </div>
  </div>

<?php else: ?>

    <div class="col-12">
      <table class="table  table-hover"  id="document-master-list">
        <thead>
          <tr>
            <th scope="col">#</th>
            <th scope="col" style="width:35px">ID</th>
            <th scope="col">Name</th>
            <th scope="col">Reference</th>
            <th scope="col">Category</th>
            <th scope="col">Version</th>
            <th scope="col">Status</th>
            <th scope="col"  style="width:80px">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white ">
          <?php foreach ($data as $key=>$row): ?>
              <tr scope="row" id="<?php echo $row['id'];?>">
                  <td><?php echo $key+1; ?></td>
                  <td>RF-<?php echo $row['id'];?></td>
                  <td><?php echo $row['name'];?></td>
                  <td><?php echo $row['ref'];?></td>
                  <td><?php echo $row['category'];?></td>
                  <td><?php echo $row['version'];?></td>
                  <td><?php echo $row['status'];?></td>
                  <td>
                      <a href="/documents-master/add?id=<?php echo $row['id'];?>" class="btn btn-warning">
                          <i class="fa fa-edit"></i>
                      </a>
                      <?php if (session()->get('is-admin')): ?>
                      <a onclick="deleteDocument(<?php echo $row['id'];?>)" class="btn btn-danger ml-2">
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
    var table = $('#document-master-list').DataTable({
      "responsive": true,
      "stateSave": true,
      "autoWidth": false
    });
    $('.l-navbar .nav__link, #footer-icons').on('click', function () {
      table.state.clear();
    });
  });

 function deleteDocument(id){

    bootbox.confirm("Do you really want to delete the document?", function(result) {
      if(result){
        $.ajax({
           url: '/documents-master/delete?id='+id,
           type: 'GET',
           success: function(response){
              console.log(response);
              response = JSON.parse(response);
              if(response.success == "True"){
                  $("#"+id).fadeOut(800)
              }else{
                 bootbox.alert('Document not deleted.');
              }
            }
         });
      }else{
        console.log('Delete Cancelled');
      }

    });

 }

</script>



<div class="row p-0 p-md-4 justify-content-center">
<?php if (count($data) == 0): ?>

  <div class="col-12">
    <div class="alert alert-warning" role="alert">
      No records found.
    </div>
  </div>


  <?php else: ?>
    <div class="col-12">
      <table class="table  table-hover" id="teams-list">
        <thead class="thead-dark">
          <tr>
            <th scope="col">#</th>
            <th scope="col">Name</th>
            <th scope="col">Email id</th>
            <th scope="col">Role</th>
            <th scope="col">Responsibility</th>
            <?php if (session()->get('is-admin')): ?>
            <th scope="col" style="width:125px">Actions</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody class="bg-white ">
          <?php foreach ($data as $key=>$row): ?>
              <?php 
                if($row['is-admin']){
                  $rowClass = "table-primary";
                  $rowTitle = "Admin";
                }else if($row['is-manager']){
                    $rowClass = "table-success";
                    $rowTitle = "Manager";
                }else if(!$row['is-active']){
                  $rowClass = "table-dark";
                  $rowTitle = "Disabled";
                }
                else{
                  $rowClass = "";
                  $rowTitle = "User";
                }
              ?>
              <tr title="<?= $rowTitle ?>""  class="<?= $rowClass ?>" scope="row" id="<?php echo $row['id'];?>">
                  <td><?php echo $key+1; ?></td>
                  <td><?php echo $row['name'];?></td>
                  <td><?php echo $row['email'];?></td>
                  <td><?php echo $row['role'];?></td>
                  <td><?php echo $row['responsibility'];?></td>
                  <?php if (session()->get('is-admin')): ?>
                  <td>
                      <a href="/team/add/<?php echo $row['id'];?>" class="btn btn-warning">
                          <i class="fa fa-edit"></i>
                      </a>
                      
                      <a onclick="deleteMember(<?php echo $row['id'];?>)" class="btn btn-danger ml-2">
                          <i class="fa fa-trash text-light"></i>
                      </a>
                      
                  </td>
                  <?php endif; ?>
              </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

<?php endif; ?>
  
</div>

<script>
  $(document).ready( function () {
    var table =  $('#teams-list').DataTable({
      "responsive": true,
      "stateSave": true,
      "autoWidth": false
    });
    $('.l-navbar .nav__link, #footer-icons').on('click', function () {
      table.state.clear();
    });
  });
 function deleteMember(id){

    bootbox.confirm("Do you really want to delete record?", function(result) {
      if(result){
        $.ajax({
           url: '/team/delete/'+id,
           type: 'GET',
           success: function(response){
              console.log(response);
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


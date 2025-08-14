
<div class="row p-0 p-md-4 justify-content-center">

<div class="col-12 pt-3 mb-4 pt-md-0 pb-md-0">
    <div class="btn-group btn-group-toggle ">
      <a href="/risk-mapping?view=Active" class="btn <?= (($view == "Active") ? "btn-primary" : "btn-secondary") ?>">Active</a>
      <a href="/risk-mapping?view=InActive" class="btn <?= (($view == "InActive") ? "btn-primary" : "btn-secondary") ?>">InActive</a>
    </div>
</div>


<?php if (count($data) == 0): ?>

  <div class="col-12">
    <div class="alert alert-warning" role="alert">
      No records found.
    </div>
  </div>
  <?php else: ?>
    <div class="col-12">
      <table id="risk-mapping-list" class="table  table-hover">
      <thead >
        <tr>
          <th scope="col">#</th>
          <th scope="col">Risk Item</th>
          <th scope="col">Risk Methodology</th>
          <th scope="col">Status</th>
          <th scope="col" style="width:90px">Actions</th>
        </tr>
      </thead>
      <tbody class="bg-white">
        <?php foreach ($data as $key=>$row): ?>
            <tr scope="row" id="<?php echo $row['id'];?>">
                <td><?php echo $key+1; ?></td>
                <td><?php echo $row['name'];?></td>
                <td><?php echo $row['risk-methodology'];?></td>
                <td><?php echo $row['status'];?></td>
                <td>
                    <a href="/risk-mapping/add/<?php echo $row['id'];?>" class="btn btn-warning ml-2">
                        <i class="fa fa-edit"></i>
                    </a>
                    <a onclick="deleteRiskCategory(<?php echo $row['id'];?>)" class="btn btn-danger ml-2">
                        <i class="fa fa-trash text-light"></i>
                    </a>
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
  var table = $('#risk-mapping-list').DataTable({
    "responsive": true,
    "stateSave": true,
    "autoWidth": false
  });

  $('.l-navbar .nav__link, #footer-icons').on('click', function () {
    table.state.clear();
  });
  
});

function deleteRiskCategory(id){
    bootbox.confirm("Do you really want to delete the Risk Category mapping?", function(result) {
      if(result){
        $.ajax({
          url: '/risk-mapping/delete/'+id,
          type: 'GET',
          success: function(response){
              response = JSON.parse(response);
              if(response.success == "True"){
                  $("#"+id).fadeOut(800)
              }else{
                bootbox.alert('Risk mapping not deleted.');
              }
            }
        });
      }else{
        console.log('Delete Cancelled');
      }

    });

}

</script>


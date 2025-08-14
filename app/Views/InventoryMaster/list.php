<?php
  $uri = service('uri');
?>
  <div class="row p-md-3">
    <div class="col-12">
        <div class="btn-group btn-group-toggle">
          <a href="/inventory-master" 
              class="btn <?= ((!strpos($uri,'?')) ? " btn-primary" : "btn-secondary") ?>">
            Active
          </a>
          <a href="/inventory-master?view=in-active"
              class="btn <?= ((strpos($uri,'/inventory-master?view=in-active'))  ? " btn-primary" : "btn-secondary") ?>">
            Defective
          </a>
          <a href="/inventory-master?view=not-found"
              class="btn <?= ((strpos($uri,'/inventory-master?view=not-found'))  ? " btn-primary" : "btn-secondary") ?>">
            Not Found
          </a>
          <a href="/inventory-master?view=cal-overdue"
              class="btn <?= ((strpos($uri,'/inventory-master?view=cal-overdue'))  ? " btn-primary" : "btn-secondary") ?>">
            Calibration Overdue
          </a>
        </div>      
    
    <a class="pull-right btn btn-success btn-large" style="margin-right:40px" 
    href="/inventory-master/createExcel" title="Export Assets"><i class="fa fa-file-excel"></i></a>
    </div>
  </div>

  <div class="row p-0 p-md-3">
    <?php if (count($data) == 0): ?>
    <div class="col-12">
      <div class="alert alert-warning" role="alert">
        No records found.
      </div>
    </div>
  <?php else: ?>


    <div class="col-12">

      <table class="table  table-hover" id="inventory-list">
        <thead class="thead-dark">
          <tr>
            <th scope="col">#</th>
            <th scope="col" style="width:35px">ID</th>
            <th scope="col">Asset</th>
            <!-- <th scope="col">Type</th> -->
            <th scope="col">Description</th>
            <!-- <th scope="col">Make</th>
            <th scope="col">Model</th> -->
            <th scope="col">Serial</th>
            <?php if((strpos($uri,'/inventory-master?view=cal-overdue'))): ?>
              <th scope="col">Overdue Date</th>
            <?php endif; ?>
            <th scope="col">Used By</th>
            <th scope="col" style="width:80px">Action</th>
          </tr>
        </thead>
        <tbody class="bg-white">
          <?php foreach ($data as $key=>$row): ?>
              <tr scope="row" id="<?php echo $row['id'];?>">
                  <td><?php echo $key+1; ?></td>
                  <td>I-<?php echo $row['id']; ?></td>
                  <td><?php echo $row['item'];?> </td>
                  <!-- <td><?php //echo $row['type'];?> </td> -->
                  <td><?php echo $row['description'];?> </td>
                  <!-- <td><?php //echo $row['make'];?></td>
                  <td><?php //echo $row['model'];?></td> -->
                  <td><?php echo $row['serial'];?></td>
                  <?php if((strpos($uri,'/inventory-master?view=cal-overdue'))): ?>
                  <td><?php echo (!(int)$row['cal_due']) ? '' : date("Y-m-d", strtotime($row['cal_due']) + (330*60)); ?></td>
                  <?php endif; ?>
                  <td><?php echo isset($row['used_by']) ? (isset($teamMembers[$row['used_by']])? $teamMembers[$row['used_by']] : "Not allocated") : '';?></td>
                  <?php 
                    /* //Status column is removed now, based on the cal_date, we need to change the status options, kept it as futher useage
                    if(!(int)$row['cal_date']){ echo $row['status'];  }else{
                      $cal_date = strtotime($row['cal_date']) + (330*60);  $today_Date = strtotime($today_date) + (330*60);
                      if($cal_date < $today_Date){ echo "Cal-Overdue";  }else{ echo $row['status']; }
                    }
                    */ 
                  ?>
                  <td>
                      <a title="Edit" href="/inventory-master/add/<?php echo $row['id'];?>" class="btn btn-warning">
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
    var table = $('#inventory-list').DataTable({
      "responsive": true,
      "stateSave": true,
      "autoWidth": false,
    });      
    $('.l-navbar .nav__list .nav__link, #footer-icons').on('click', function () {
      table.state.clear();
    });

  });

 function deleteItem(id){

    bootbox.confirm("Do you really want to delete record?", function(result) {
      if(result){
        $.ajax({
           url: '/inventory-master/delete/'+id,
           type: 'GET',
           success: function(response){
              console.log(response);
              console.log('/inventory-master/delete/'+id);
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



<div class="row justify-content-center p-0 p-md-4">
    
    <div class="col-12 col-md-7">
        <?php if (count($data) == 0): ?>
        
        <div class="alert alert-warning" role="alert">
            No records found.
        </div>

        <?php else: ?>
            
        <table class="table rounded  table-hover"  id="users-list">
            <thead>
                <tr>
                <th scope="col">#</th>
                <th scope="col">Name</th>
                <th scope="col">Email id</th>
                <th scope="col">Admin</th>
                </tr>
            </thead>
            <tbody class="bg-white">
                <?php foreach ($data as $key=>$row): ?>
                    <tr scope="row" >
                        <td><?php echo $key+1; ?></td>
                        <td><?php echo $row ['name'];?></td>
                        <td><?php echo $row['email'];?></td>
                        <td><input id="<?php echo $row ['id'];?>" 
                                    data-on="Yes" data-off="No"
                                    type="checkbox" <?php echo $row['is-admin'] ? 'checked' : '';?>
                                    data-toggle="toggle" onchange="changeStatus(<?php echo $row ['id'];?>)">
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
            
        <?php endif; ?>
    </div>
  
</div>

<script>
$(document).ready( function () {
    $('#users-list').DataTable({
      "responsive": true,
      "autoWidth": false
    });
});

function changeStatus(id){

    console.log('change '+id );
    $.ajax({
        url: '/admin/users/updateStatus',
        type: 'POST',
        data: { id: id} ,
        success: function (response) {
            console.log(response);
        },
        error: function () {
            alert("error");
        }
    }); 
}
</script>
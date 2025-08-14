<div class="row justify-content-center">
  <div class="col-12 col-md-7 col-lg-6 mt-3">
    <div class="container">
      <?php if (count($data) == 0): ?>

      <div class="alert alert-warning" role="alert">
        No records found.
      </div>

      <?php else: ?>
        <div class="table-responsive">
          <table class="table  table-hover">
            <thead>
              <tr>
                <th scope="col">#</th>
                <th scope="col">Name</th>
                <th scope="col" style="min-width:125px;">Action</th>
                
              </tr>
            </thead>
            <tbody class="bg-white ">
              <?php foreach ($data as $key=>$row): ?>
              <tr scope="row" id="<?php echo $row['id'];?>">
                <td><?php echo $key+1; ?></td>
                <td><?php echo $row['name'];?></td>
              
                <td>
                  <a href="/documents-templates/add/<?php echo $row['id'];?>" class="btn btn-warning">
                    <i class="fa fa-edit"></i>
                  </a>
                  <?php if (session()->get('is-admin')): ?>
                  <a onclick="deleteTemplate(<?php echo $row['id'];?>)" class="btn btn-danger ml-2">
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
  </div>
</div>


<script>
  function deleteTemplate(id) {

    bootbox.confirm("Do you really want to delete the template?", function (result) {
      if (result) {
        $.ajax({
          url: '/documents-templates/delete/' + id,
          type: 'GET',
          success: function (response) {
            console.log(response);
            response = JSON.parse(response);
            if (response.success == "True") {
              $("#" + id).fadeOut(800)
            } else {
              bootbox.alert('Template not deleted.');
            }
          }
        });
      } else {
        console.log('Delete Cancelled');
      }

    });

  }
</script>
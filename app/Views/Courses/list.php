
<div class="row p-0 p-md-4 justify-content-center">

<!-- <div class="col-12 pt-3 mb-4 pt-md-0 pb-md-0">
    <div class="btn-group btn-group-toggle ">
      <a href="/courses?view=Active" class="btn <?= (($view == "Active") ? "btn-primary" : "btn-secondary") ?>">Active</a>
      <a href="/courses?view=InActive" class="btn <?= (($view == "InActive") ? "btn-primary" : "btn-secondary") ?>">InActive</a>
    </div>
</div> -->

<?php if (count($data) == 0): ?>

  <div class="col-12">
    <div class="alert alert-warning" role="alert">
      No records found.
    </div>
  </div>
  <?php else: ?>
    <div class="col-12">
      <table id="course-list" class="table  table-hover">
      <thead >
        <tr>
          <th scope="col">#</th>
          <th scope="col">Title</th>
          <th scope="col">URL</th>
          <th scope="col" style="min-width:300px;">Description</th>
          <th scope="col">K-Points</th>
          <th scope="col">Is Certified</th>
          <th scope="col" style="min-width:100px;">Actions</th>
        </tr>
      </thead>
      <tbody class="bg-white">
        <?php foreach ($data as $key=>$row): ?>
            <tr scope="row" id="<?php echo $row['course_id'];?>">
                <td><?php echo $key+1; ?></td>
                <td><?php echo $row['title'];?></td>
                <td><?php echo $row['url'];?></td>
                <td style="width:100px !important"><?php echo $row['description'];?></td>
                <td><?php echo $row['k-points'];?></td>
                <td><?php echo $row['is_certified'];?></td>
                <td style="width:100px !important">
                <?php if (session()->get('is-admin')): ?>
                    <a href="/courses/add/<?php echo $row['course_id'];?>" class="btn btn-warning btn-sm">
                        <i class="fa fa-edit"></i>
                    </a>
                <?php endif; ?>
                    <a onclick="addCourse(<?php echo $row['course_id'];?>)" class="btn btn-success btn-sm" title="Assign Course to self">
                    <i class="fa fa-clone"></i>
                    </a>
                <?php if (session()->get('is-admin')): ?>
                <a onclick="deleteCourse(<?php echo $row['course_id'];?>)" class="btn btn-danger ml-1 btn-sm">
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
  var table = $('#course-list').DataTable({
    "responsive": true,
    "stateSave": true,
    "autoWidth": false
  });

  $('.l-navbar .nav__link, #footer-icons').on('click', function () {
    table.state.clear();
  });
  
});

function deleteCourse(id){
    bootbox.confirm("Do you really want to delete the course?", function(result) {
      if(result){
        $.ajax({
          url: '/courses/delete/'+id,
          type: 'GET',
          success: function(response){
              response = JSON.parse(response);
              if(response.success == "True"){
                  $("#"+id).fadeOut(800)
              }else{
                bootbox.alert('Course not deleted.');
              }
            }
        });
      }else{
        console.log('Delete Cancelled');
      }

    });
  }


  function addCourse(courseId, userId){
    bootbox.confirm("Do you really want to enroll for the course?", function(result) {
      if(result){
        $.ajax({
          url: '/userCourses/addCourse/'+courseId,
          type: 'GET',
          success: function(response){
              response = JSON.parse(response);
              if(response.success == "True"){
                bootbox.alert('Successfully enrolled for course.');
              }else{
                bootbox.alert('Already enrolled for course.');
              }
            }
        });
      }else{
        console.log('Enrollment Cancelled');
      }

    });
  }

</script>


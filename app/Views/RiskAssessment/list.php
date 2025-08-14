<?php
  $uri = service('uri');
  ?>

<div class="row p-2 p-md-3">
      <div class="col-2">
        <div class="form-group mb-0">
        <label class="font-weight-bold text-muted">Project</label><br />
          <select class="form-control selectpicker" id="projects" name="projects" data-style="btn-secondary" data-live-search="true" data-size="8" >
            <option value="" disabled >
              Select Project
            </option>
            <?php foreach ($projects as $key=>$value): ?>
              <option  <?= (($selectedProject == $key) ? "selected" : "") ?> value="<?=  $key ?>"><?=  $value ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="col-2">
        <div class="form-group mb-2">
        <label class="font-weight-bold text-muted">Risk Type</label><br />
        <select class="form-control selectpicker" onchange="getSelectedStatusData(0)" data-live-search="true" data-size="8" name="riskTypes" id="riskTypes" data-style="btn-secondary" data-live-search="true" data-size="8" >
              <option value="" disabled>
                      Select Risk Type
                  </option>
                  <?php foreach ($riskCategory as $key=>$value): ?>
                    <option 
                      <?= (($riskCategorySelected == $value) ? "selected": '') ?>
                      value="<?=  $value ?>" ><?=  $value ?></option>
                  <?php endforeach; ?>
          </select>
        </div>
      </div>

   
      
        <div class="col-3">
          <div class="btn-group btn-group-toggle" >
          <div id="data-open-issue-soup-matrix">
                <div  class="col-12">
                    <div class="form-group">
                    <label class="font-weight-bold text-muted">Status</label><br />
                      <div class="btn-group btn-group-toggle btn-security-toggle" id="listblock" >
                      <div class="btn <?= ( (!strpos($uri,'?') || (strpos($uri, 'status=All')) || (strpos($uri, 'status=sync'))) ? "btn-primary" : "btn-secondary") ?> id="RDanchor" title="" onclick="getSelectedStatusData(1)">
                            <input type="radio" name="status-type" value="All" id="status-type1" /> All
                          </div>
                          <div class="btn <?= (strpos($uri, 'status=Open') ? "btn-primary" : "btn-secondary") ?>" id="RDanchor" title="" onclick="getSelectedStatusData(2)">
                            <input type="radio" name="status-type" value="Open"  id="status-type2"/> Open
                          </div>
                          <div class="btn <?= (strpos($uri, 'status=Close') ? "btn-primary" : "btn-secondary") ?>" id="RDanchor" title="" onclick="getSelectedStatusData(3)">
                            <input type="radio" name="status-type" value="Close"  id="status-type3"/> Close
                          </div>
                      </div>
                    </div>
                </div>
            </div>
          </div>
        </div>
      
        <div class="col-3">
        <form id="form-upload-user" method="post" autocomplete="off">
                <div class="sub-result"></div>
                <div class="result"></div>
                <div class="form-group">
                    <label class="control-label">Upload Assessment <small class="text-danger">*</small></label>
                    <input type="file" class="form-control form-control-sm" id="attachments" name="attachments" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" required>
                    <small class="text-danger">Upload excel file only.</small>
                    <br/>
                    <button type="submit" class="btn btn-primary btn-sm waves-effect waves-light" id="btnUpload">Upload</button>
                </div>
                <div class="form-group">
                    <div class="text-center">
                        <div class="user-loader" style="display: none; ">
                            <i class="fa fa-spinner fa-spin"></i> <small>Please wait ...</small>
                        </div>
                    </div>
                </div>
                <!-- <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-sm waves-effect waves-light" id="btnUpload">Upload</button>
                </div> -->
            </form>
                  </div>

         <div class="col-1">
        <a class="pull-right btn btn-warning btn-large" style="margin-top:33px" 
    href="/risk-assessment/createRiskTemplate"><i class="fa fa-file-excel-o"></i>Download Template</a>
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
          <table class="table table-hover risk-assessment" id="risk-assessment-list">
            <thead >
              <tr>
                <th scope="col">#</th>
                <th scope="col" style="width:35px">ID</th>
                <th scope="col">Risk</th>
                <th scope="col" style="width:35%">Risk Description</th>
                <th scope="col"  style="width:83px"> <?= (($riskCategorySelected == 'Vulnerability') ? "CVSS 3.1 Base Score" : "RPN") ?> </th>
                <th scope="col">Status</th>
                <th scope="col" style="width:80px">Action</th>
              </tr>
            </thead>
            <tbody  class="bg-white">
              <?php foreach ($data as $key=>$row): ?>
                  <tr scope="row" id="<?php echo $row['id'];?>">
                      <td><?php echo $key+1; ?></td>
                      <td>RA-<?php echo $row['id']; ?></td>
                      <td><?php echo $row['risk'];?></td>
                      <td><?php echo $row['risk_description'];?></td>
                      <?php if (isset($row['CVSS_3_1_base_risk_assessment']) && $row['CVSS_3_1_base_risk_assessment'] !=0): ?>
                  <td><?php echo $row['CVSS_3_1_base_risk_assessment'];?></td>
                <?php else: ?><td> -- </td><?php endif; ?>
                      <td><?php echo $row['status'];?></td>
                      <td>
                          <a href="/risk-assessment/add?id=<?php echo $row['id'];?>" class="btn btn-warning">
                              <i class="fa fa-edit"></i>
                          </a>
                          <?php if (session()->get('is-admin')): ?>
                          <a onclick="deleteItem(<?php echo $row['id'];?>)" class="btn btn-danger ml-2">
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
$(document).ready(function(){
  var table = $('#risk-assessment-list').DataTable({
      "responsive": true,
      "stateSave": true,
      "autoWidth": false,
  });
  $('.l-navbar .nav__link, #footer-icons').on('click', function () {
      table.state.clear();
  });

  // $('.get-risks-sync').click(function(){
  //   var selectedProjectId = $("#projects").val();
  //   var url = `risk-assessment?status=sync&project_id=${selectedProjectId}`
  //   console.log("url:", url);
  //   window.location = url;
  // });

});


function getSelectedRiskTypeData() {
  var selectedRisk = $("#riskTypes").val();
  var url = `risk-assessment?type=${selectedRisk}`
  console.log("url:", url);
  window.location = url;
}

function getSelectedStatusData(id) {
  var idVal,obj,status,riskType,url;
  riskType = $("#riskTypes").val();
  if(id != 0){
    $('#listblock  div').removeClass('btn-primary').addClass('btn-secondary');
    idVal = "#status-type"+id;
    $(idVal).parent().removeClass("btn-secondary").addClass('btn-primary');
    obj = {1:"All", 2:"Open", 3:"Close"};
    status = obj[id];
    url = `risk-assessment?status=${status}&type=${riskType}`
  }else{
    url = `risk-assessment?status=All&type=${riskType}`
  }
  console.log("url:", url);
  window.location = url;
}

 function deleteItem(id){
    bootbox.confirm("Do you really want to delete record?", function(result) {
      if(result){
        $.ajax({
           url: '/risk-assessment/delete?id='+id,
           type: 'GET',
           success: function(response){
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

 $("body").on("submit", "#form-upload-user", function(e) {
            e.preventDefault();
            var data = new FormData(this);
            $.ajax({
                type: 'POST',
                url: "<?php echo base_url('risk-assessment/import') ?>",
                data: data,
                dataType: 'json',
                contentType: false,
                cache: false,
                processData:false,
                beforeSend: function() {
                    $("#btnUpload").prop('disabled', true);
                    $(".user-loader").show();
                }, 
                success: function(result) {
                    $("#btnUpload").prop('disabled', false);
                    if($.isEmptyObject(result.error_message)) {
                        $(".result").html(result.success_message);
                    } else {
                        $(".sub-result").html(result.error_message);
                    }
                    $(".user-loader").hide();
                    getSelectedStatusData(1);
                }
            });
        });

</script>


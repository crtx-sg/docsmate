<div class="row p-md-3">
      <div class="col-3">
        <div class="form-group mb-0">
        <label class="font-weight-bold text-muted">Product</label><br />
          <select class="form-control selectpicker" onchange="getSelectedStatusData()" id="products" name="products" data-style="btn-secondary" data-live-search="true" data-size="8" >
            <option value="" disabled >
              Select Product
            </option>
            <?php foreach ($products as $key=>$value): ?>
              <option  <?= (($selectedProduct == $key) ? "selected" : "") ?> value="<?=  $key ?>"><?=  $value ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="col-3">
        <form id="form-upload-user" method="post" autocomplete="off">
                <div class="sub-result"></div>
                <div class="result"></div>
                <div class="form-group">
                    <label class="control-label">Upload TestCases <small class="text-danger">*</small></label>
                    <input type="file" class="form-control form-control-sm" id="attachments" name="attachments" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" required>
                    <small class="text-danger">Upload Excel file only.</small>
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
            </form>
                  </div>

         <div class="col-1">
        <a class="pull-right btn btn-warning btn-large" style="margin-top:33px" 
    href="/test-cases/createTestCasesTemplate"><i class="fa fa-file-excel-o"></i>Download Template</a>
         </div>

  </div>
<div class="row p-0 p-md-4">
<?php if (count($data) == 0): ?>

  <div class="col-12">
    <div class="alert alert-warning" role="alert">
      No records found.
    </div>
  </div>


  <?php else: ?>
    <div class="col-12">
      <table class="table  table-hover table-responsive" id="test-cases-list">
        <thead>
          <tr>
            <th scope="col">#</th>
            <th scope="col" style="width:43px">ID</th>
            <th scope="col">Test</th>
            <th scope="col" style="width:45%">Description</th>
            <th scope="col">Update Date</th>
            <th scope="col" style="width:80px">Action</th>
          </tr>
        </thead>
        <tbody  class="bg-white">     
          <?php foreach ($data as $key=>$row): ?>
              <tr scope="row" id="<?php echo $row['id'];?>">
                  <td><?php echo $key+1; ?></td>
                  <td>TC-<?php echo $row['id']; ?></td>
                  <td><?php echo $row['testcase']; ?></td>
                  <td><?php echo $row['description'];?></td>
                  <td><?php $timestamp = strtotime($row['update_date']) + (330*60); echo date("Y-m-d h:i A", $timestamp); ?></td>
                  <td>
                      <a href="/test-cases/add/<?php echo $row['id'];?>" class="btn btn-warning">
                          <i class="fa fa-edit"></i>
                      </a>
                      <?php if (session()->get('is-admin')): ?>
                      <a onclick="deleteTestCase(<?php echo $row['id'];?>)" class="btn btn-danger ml-2">
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
    var table = $('#test-cases-list').DataTable({
      "responsive": true,
      // "scrollX": true,
      "autoWidth": false,
      "stateSave": true,
      // "fixedHeader": true,
    });
    $('.l-navbar .nav__link, #footer-icons').on('click', function () {
      table.state.clear();
    });
    $('.get-risks-sync').click(function(){
      var url = `test-cases?status=sync`
      window.location = url;
    });
  });

  function getSelectedStatusData() {
    var url;
    var selectedProductId = $("#products").val();

    url = `test-cases?product-id=${selectedProductId}`;
    window.location = url;
  }


 function deleteTestCase(id){

    bootbox.confirm("Do you really want to delete record?", function(result) {
      if(result){
        $.ajax({
           url: '/test-cases/delete/'+id,
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
                url: "<?php echo base_url('test-cases/import') ?>",
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
                    getSelectedStatusData();
                }
            });
        });


</script>


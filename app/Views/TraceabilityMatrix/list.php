<?php
$uri = service('uri');
?>
<div class="row p-2 p-md-3">
      <div class="col-3">
              <div class="form-group mb-0">
              <label class="font-weight-bold text-muted">Product</label><br />
                <select class="form-control selectpicker" onchange="getSelectedStatusData(4)" id="products" name="products" data-style="btn-secondary" data-live-search="true" data-size="8" >
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
      <div class="form-group mb-0">
      <label class="font-weight-bold text-muted">Requirement Type</label><br />
        <select class="form-control selectpicker" onchange="getSelectedStatusData(3)" data-live-search="true" data-size="8" name="requirementType" id="requirementType" data-style="btn-secondary">
          <option value="" disabled <?= (isset($selectedCategory) && ($selectedCategory != '')) ? '' : 'selected' ?>>
            Select Requirement Type
          </option>
          <?php foreach ($requirementCategory as $reqCat) : ?>
            <option <?= isset($selectedCategory) ? (($selectedCategory == $reqCat["value"]) ? 'selected' : '') : '' ?> value="<?= $reqCat["value"] ?>"><?= $reqCat["value"] ?></option>
          <?php endforeach; ?>
        </select>
      </div>
          </div>
      <div class="col-2">
          <div class="btn-group btn-group-toggle" >
          <div id="data-open-issue-soup-matrix">
          <div  class="col-12">
          <div class="form-group">
          <label class="font-weight-bold text-muted">View</label><br />
            <div class="btn-group btn-group-toggle btn-security-toggle" id="listblock">
              <div class="btn <?= ((!strpos($uri, '?') || (strpos($uri, 'status=List'))) ? "btn-primary" : "btn-secondary") ?>" id="1" onclick="getSelectedStatusData(1)">
                <input type="radio" name="status-type" value="All" id="status-type1" /> List
              </div>
              <div class="btn <?= (strpos($uri, 'status=Gap') ? "btn-primary" : "btn-secondary") ?>" id="2" onclick="getSelectedStatusData(2)">
                <input type="radio" name="status-type" value="Open" id="status-type2" /> Gap
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  </div>
<div class="row p-0 p-md-3">
  <?php if ((count($data) == 0) && ($listViewDisplay == true)) : ?>
    <div class="col-12">
      <div class="alert alert-warning" role="alert">
        No records found.
      </div>
    </div>
  <?php else : ?>
    <?php if (($listViewDisplay == true)) : ?>
      <div class="col-12">

        <table class="table  table-hover" id="traceability-list">
          <thead>
            <tr>
              <th scope="col">#</th>
              <th scope="col" style="width:35px">ID</th>
              <th class="<?php echo (($rootTraceabilityColumn == 1) ? '' : 'd-none'); ?>" scope="col" id="headerLabel1">User Needs</th>
              <th class="<?php echo (($rootTraceabilityColumn == 2) ? '' : 'd-none'); ?>" scope="col" id="headerLabel2">Standards</th>
              <th class="<?php echo (($rootTraceabilityColumn == 3) ? '' : 'd-none'); ?>" scope="col" id="headerLabel3">Guidance</th>
              <th scope="col">System</th>
              <th scope="col">Subsystem</th>
              <th scope="col">Test</th>
              <th scope="col" style="width:80px">Action</th>
            </tr>
          </thead>
          <tbody class="bg-white">
            <?php $count = 1;
            foreach ($data as $key => $row) : ?>
              <tr scope="row" id="<?php echo $row['id']; ?>">
                <td><?php echo $count++; ?></td>
                <td>TM-<?php echo $row['id']; ?></td>
                <td id="<?php echo $row['id']; ?>" class="<?php echo (($rootTraceabilityColumn == 1) ? '' : 'd-none'); ?>" onmouseover="getDescByRequirement('<?= $row['id'] ?>', '<?= $row['cncr'] ?>')"><?php if (isset($row['cncr'])) {
                                                                                              echo $row['cncr'];
                                                                                            } ?></td>
                <td class="<?php echo (($rootTraceabilityColumn == 2) ? '' : 'd-none'); ?>"><?php if (isset($row['standards'])) {
                                                                                              echo $row['standards'];
                                                                                            } ?></td>
                <td class="<?php echo (($rootTraceabilityColumn == 3) ? '' : 'd-none'); ?>"><?php if (isset($row['guidance'])) {
                                                                                              echo $row['guidance'];
                                                                                            } ?></td>
                <td><?php if (isset($row['system'])) {
                      echo $row['system'];
                    } ?></td>
                <td><?php if (isset($row['subsysreq'])) {
                      echo $row['subsysreq'];
                    } ?></td>
                <td><?php if (isset($row['testcase'])) {
                      echo $row['testcase'];
                    } ?></td>

                <td style="width:125px">
                  <a href="/traceability-matrix/add/<?php echo $row['id']; ?>" class="btn btn-warning">
                    <i class="fa fa-edit"></i>
                  </a>
                  <?php if (session()->get('is-admin')) : ?>
                    <a onclick="deleteTraceabilityMatrix(<?php echo $row['id']; ?>)" class="btn btn-danger ml-2">
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
  <?php endif; ?>

  <?php if ($listViewDisplay == false) : ?>
    <?php if (!count($data)) : ?>
      <div class="col-12">
        <div class="alert alert-warning" role="alert">
          No records found.
        </div>
      </div>
    <?php else : ?>
      <div class="col-12">
        <table class="table  table-hover" id="traceability-gaps">
          <thead>
            <tr>
              <th class="<?php echo (($rootTraceabilityColumn == 1) ? '' : 'd-none'); ?>" scope="col" id="headerLabel1">User Needs</th>
              <th class="<?php echo (($rootTraceabilityColumn == 2) ? '' : 'd-none'); ?>" scope="col" id="headerLabel2">Standards</th>
              <th class="<?php echo (($rootTraceabilityColumn == 3) ? '' : 'd-none'); ?>" scope="col" id="headerLabel3">Guidance</th>

              <th scope="col">System</th>
              <th scope="col">Subsystem</th>
              <th scope="col">Test</th>
            </tr>
          </thead>
          <tbody class="bg-white">
            <tr scope="row">
              <td class="<?php echo (($rootTraceabilityColumn == 1) ? '' : 'd-none'); ?>"><?php if (isset($data['User Needs'])) {
                                                                                            echo $data['User Needs'];
                                                                                          } ?></td>
              <td class="<?php echo (($rootTraceabilityColumn == 2) ? '' : 'd-none'); ?>"><?php if (isset($data['Standards'])) {
                                                                                            echo $data['Standards'];
                                                                                          } ?></td>
              <td class="<?php echo (($rootTraceabilityColumn == 3) ? '' : 'd-none'); ?>"><?php if (isset($data['Guidance'])) {
                                                                                            echo $data['Guidance'];
                                                                                          } ?></td>

              <td><?php if (isset($data['System'])) {
                    echo $data['System'];
                  } ?></td>
              <td><?php if (isset($data['Subsystem'])) {
                    echo $data['Subsystem'];
                  } ?></td>
              <td><?php if (isset($data['testcase'])) {
                    echo $data['testcase'];
                  } ?></td>
            </tr>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  <?php endif; ?>

</div>

<script>
  $(document).ready(function() {
    var table1 = $('#traceability-list').DataTable({
      "responsive": true,
      "autoWidth": false,
      "stateSave": true,
      "fixedHeader": true,
    });
    var table2 = $('#traceability-gaps').DataTable({
      "responsive": true,
      "autoWidth": false,
      "stateSave": true,
      "fixedHeader": true,
    });
    $('.l-navbar .nav__link, #footer-icons').on('click', function() {
      table1.state.clear();
      table2.state.clear();
    });

  });

  function deleteTraceabilityMatrix(id) {

    bootbox.confirm("Do you really want to delete record?", function(result) {
      if (result) {
        $.ajax({
          url: '/traceability-matrix/delete/' + id,
          type: 'GET',
          success: function(response) {
            response = JSON.parse(response);
            if (response.success == "True") {
              $("#" + id).fadeOut(800)
            } else {
              bootbox.alert('Record not deleted.');
            }
          }
        });
      } else {
        console.log('Delete Cancelled');
      }

    });

  }

  function getSelectedStatusData(id) {
    var idVal, obj, status, riskType, url, selectId;
    categoryType = $("#requirementType").val();
    var selectedProductId = $("#products").val();
    if (id == 3 || id == 4) {
      if(id == 4){
        selectId = "product";
      }
      id = $('#listblock .btn-primary').attr('id');
    }
    if (id != '' && categoryType != '') {
      $('#listblock  div').removeClass('btn-primary').addClass('btn-secondary');
      idVal = "#status-type" + id;
      $(idVal).parent().removeClass("btn-secondary").addClass('btn-primary');
      obj = {
        1: "List",
        2: "Gap"
      };
      status = obj[id];
      console.log("id val:"+id);
      url = `traceability-matrix?status=${status}&type=${categoryType}&product-id=${selectedProductId}`;
      // if(selectId == "product" && categoryType && status){
      //   url = `traceability-matrix?status=${status}&type=${categoryType}&product-id=${selectedProductId}`;
      // }
      // else{
      //   url = `traceability-matrix?status=${status}&type=${categoryType}`;
      // }
    } else {
      url = `traceability-matrix?status=List&type=User Needs&product-id=${selectedProductId}`;
      // if(selectId == "product" && type=="User Needs" && status == "List"){
      //   url = `traceability-matrix?status=List&type=User Needs&product-id=${selectedProductId}`;
      // }
      // else{
      //   url = `traceability-matrix?status=List&type=User Needs`;
      // }
    }
    window.location = url;
  }

  function getDescByRequirement(id,requirement){
    $.ajax({
          url: '/traceability-matrix/getDescriptionByReq/'+requirement,
           type: 'GET',
           success: function(response){
              response = JSON.parse(response);
              if(response.success == "True"){
                $("#"+id).attr('title', response.description[0]['description']);
              }
              else{
                 bootbox.alert('Data not existed.');
              }
            }
         });
  }
</script>
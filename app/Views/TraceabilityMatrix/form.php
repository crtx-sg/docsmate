
  <div class="row p-0 p-md-4">
    <div class="col-12 col-sm8- offset-sm-2 col-md-6 offset-md-3 mt-3 pt-3 pb-3 form-color">
      <div class="container11">
        <h3><?= $formTitle ?></h3>
        <hr>
        <?php if (session()->get('success')): ?>
          <div class="alert alert-success" role="alert">
            <?= session()->get('success') ?>
          </div>
        <?php endif; ?>
        <!-- Submit action -->
        <form class="" action="/traceability-matrix/<?= $action ?>" method="post">
          <div class="row">
            <?php if (isset($validation)): ?>
              <div class="col-12 ">
                <div class="alert alert-danger" role="alert">
                  <?= $validation->listErrors() ?>
                </div>
              </div>
            <?php endif; ?>
            
            <div class="col-12">
              <div class="form-group">
                  <label class = "col-3" for="Traceability-to">Traceability To</label>
                  <select class="selectpicker col-8" data-live-search="true" data-size="8" name="Traceability-to" id="Traceability-to" >
                    <option value=""  <?= (isset($member['root_requirement']) && ($member['root_requirement'] != '')) ? '' : 'selected' ?>>
                        Select Traceability To
                    </option>
                    <?php foreach ($requirementCategory as $reqCat): ?>
                        <option 
                          <?= isset($member['root_requirement']) ? (($member['root_requirement'] == $reqCat["value"]) ? 'selected': '') : '' ?>
                          value="<?=  $reqCat["value"] ?>" ><?=  $reqCat["value"] ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
            </div>

            <div class="col-12 col-sm-6" id="rootMapping1">
              <div class="form-group">
                <label class = "font-weight-bold text-muted" for="userNeeds" >User Needs</label>
                <select  class="form-control  selectpicker" data-live-search="true" data-size="8" name="userNeeds[]" id="userNeeds" onchange="getIDDescription('#userNeeds', 1)">
                  <option value="" disabled <?= isset($member['userNeeds']) ? '' : 'selected' ?>>
                        Select User Needs
                    </option>
                  <?php foreach ($userNeedsList as $key=>$value): ?>
                  <option 
                    <?= isset($userNeedsListKeys) ? ((in_array($key, $userNeedsListKeys)) ? 'selected': '') : '' ?>
                    value="<?=  $key ?>" ><?=  $value ?></option>
                <?php endforeach; ?>
              </select>
              </div>
            </div>

            <div class="col-12 col-sm-6" id="rootMapping2">
              <div class="form-group">
                <label class = "font-weight-bold text-muted" for="standards" >Standards</label>
                <select  class="form-control  selectpicker" data-live-search="true" name="standards[]" id="standards" onchange="getIDDescription('#standards', 2)">
                  <option value="" disabled <?= isset($member['standards']) ? '' : 'selected' ?>>
                   Select Standards 
                  </option>
                    <?php foreach ($standardsList as $key=>$value): ?>
                  <option 
                    <?= isset($standardsKeys) ? ((in_array($key, $standardsKeys)) ? 'selected': '') : '' ?>
                    value="<?=  $key ?>" ><?=  $value ?></option>
                  <?php endforeach; ?>
                </select>
                </div>
            </div>

            <div class="col-12 col-sm-6" id="rootMapping3">
              <div class="form-group">
                <label class = "font-weight-bold text-muted" for="guidance" >Guidance</label>
                <select class="form-control selectpicker" data-live-search="true" data-size="8" name="guidance[]" id="guidance" onchange="getIDDescription('#guidance', 3)">
                  <option value="" disabled <?= isset($member['guidance']) ? '' : 'selected' ?>>
                   Select Guidance 
                   </option>
                    <?php foreach ($guidanceList as $key=>$value): ?>
                  <option 
                    <?= isset($guidanceKeys) ? ((in_array($key, $guidanceKeys)) ? 'selected': '') : '' ?>
                    value="<?=  $key ?>" ><?=  $value ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div class="col-12  col-sm-6" id="userNeeds_description">
            </div>  

            <div class="col-12 col-sm-6">
              <div class="form-group">
              <label class = "font-weight-bold text-muted" for="sysreq">System</label>
              <select class="form-control selectpicker" data-live-search="true" multiple name="sysreq[]" id="sysreq">
                  <option disabled value> Select System </option>
                  <?php foreach ($systemList as $key=>$value): ?>
                  <option 
                    <?= isset($systemKeys) ? ((in_array($key, $systemKeys)) ? 'selected': '') : '' ?>
                    value="<?=  $key ?>" ><?=  $value ?></option>
                <?php endforeach; ?>
              </select>
              </div>
            </div>

            <div class="col-12   col-sm-6">
              <div class="form-group">
              <label class = "font-weight-bold text-muted" for="subsysreq">Subsystem</label>
              <select class="form-control selectpicker" data-live-search="true" data-size="8" multiple name="subsysreq[]" id="subsysreq">
                  <option disabled value> Select Subsystem </option>
                  <?php foreach ($subSystemList as $key=>$value): ?>
                  <option 
                    <?= isset($subsystemKeys) ? ((in_array($key, $subsystemKeys)) ? 'selected': '') : '' ?>
                    value="<?=  $key ?>" ><?=  $value ?></option>
                <?php endforeach; ?>
              </select>
              </div>
            </div>

            <div class="col-12 col-sm-6">
              <div class="form-group">
              <label class = "font-weight-bold text-muted" for="testcase">Test</label>
              <select class="form-control selectpicker" data-live-search="true" data-size="8"  multiple  name="testcase[]" id="testcase">
                  <option disabled value>Select Testcase</option>
                  <?php foreach ($testCases as $key=>$value): ?>
                  <option 
                    <?= isset($testcaseKeys) ? ((in_array($key, $testcaseKeys)) ? 'selected': '') : '' ?>
                    value="<?=  $key ?>" ><?=  $value ?></option>
                <?php endforeach; ?>
              </select>
              </div>
            </div>

            <div class="col-12 col-sm-6" id="projectMapping">
                <div class="form-group">
                <label class = "font-weight-bold text-muted" for="product-id">Product</label>
              <select class="form-control  selectpicker" data-live-search="true" data-size="8" name="product-id" id="product-id" onChange="toggleProjectMappingData()">
              <option value="" disabled <?= isset($member['product-id']) ? '' : 'selected' ?>>
                  Select Product
              </option>
              <?php foreach ($products as $key=>$value): ?>
                <option 
                  <?= isset($member['product-id']) ? (($member['product-id'] == $key) ? 'selected': '') : '' ?>
                  value="<?=  $key ?>"><?=  $value ?></option>
              <?php endforeach; ?>
            </select>
                </div>
            </div>
            <div class="col-12">
              <div class="form-group">
              <label class = "font-weight-bold text-muted" for="design">Design</label>
              <textarea name="design" id="design" class="form-control section_content"><?= isset($member['design']) ? $member['design'] : '' ?></textarea>         
              </div>
            </div>

            <div class="col-12">
              <div class="form-group">
              <label class = "font-weight-bold text-muted" for="code">Code</label>
              <textarea  name="code" id="code" class="form-control section_content"><?= isset($member['code']) ? $member['code'] : '' ?></textarea>      
              </div>
            </div>

            <div class="col-12">
              <div class="form-group">
              <label class = "font-weight-bold text-muted" for="description">Description</label>
              <textarea  name="description" id="description" class="form-control section_content"><?= isset($member['description']) ? $member['description'] : '' ?></textarea>
              </div>
            </div>        


        
            <div class="col-12  col-sm-6" >
              <div class="form-group">
                <div id="testcase_description" style="border: 1ps solid black;"></div>
              </div>
            </div>  

          </div>

          <div class="row">
            <div class="col-12 col-sm-4">
              <button type="submit" class="btn btn-primary">Submit</button>
            </div>
          </div>

        </form>
      </div>

    
    </div>
  </div>





<script>
  $(document).ready(function(){

    $('[data-toggle="popover"]').popover();
    $('#rootMapping1, #rootMapping2, #rootMapping3').css('display', 'none');
    var isEditForm = "<?php echo $isEditForm;?>";
    if(isEditForm){
      toggleRootMappingData();
    }else{
      $('#userNeeds_description').css('display', 'none');
    }
  });

  function toggleRootMappingData() {
    var categoryType = $("#Traceability-to").val();
    $('#rootMapping1, #rootMapping2, #rootMapping3').css('display', 'none');
    $('#userNeeds_description').css('display', 'none');
    if(categoryType){
      $('#userNeeds_description').css('display', 'block');
    }

    switch(categoryType){
      case 'User Needs':
        $('#rootMapping1').css('display', 'block');
        getIDDescription('#userNeeds', 1);    
        break;
      case 'Standards':
        $('#rootMapping2').css('display', 'block');
        getIDDescription('#standards', 2);
        break;
      case 'Guidance':
        $('#rootMapping3').css('display', 'block');
        getIDDescription('#guidance', 3);
        break;
    }
  }
  
 function getIDDescription(selectedBox, type){
  var id = $(selectedBox).val();
    var descID = "#userNeeds_description";
      if(id){
        $.ajax({
          url: '/traceability-matrix/getIDDescription/'+id+'/'+type,
           type: 'GET',
           success: function(response){
              response = JSON.parse(response);
              if(response.success == "True"){
                $(descID).html(response.description[0]['description']);
                  setTimeout(function(){
                    $(descID).css('border', '2px solid #CCCCCC');
                  },100);
              }
              else{
                 bootbox.alert('Data not existed.');
              }
            }
         });
      }else{
        $(descID).html('').css('border', 'none');
      }
 }
 
</script>

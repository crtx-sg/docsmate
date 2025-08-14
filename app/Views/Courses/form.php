<style>
.box {
  box-shadow: 0px 1px 32px -12px #607D8B;
  background-color: #fff;
  padding: 10px 35px 10px 30px;
  border-radius: 8px;
}

.box-header {
  border-bottom: 1px solid;
  font-size: 19px !important;
  height:35px;
}

.activeDiv{
  border-left: 1px solid #ddd;
  border-right: 1px solid #ddd;
  border-bottom: 1px solid #ddd;
  padding:10px;
  border-radius: 8px;
  background: #e9ecef;
  word-wrap: break-word;
  /* white-space:pre-wrap; */
}

br + br { display: none; }

</style>
<div class="row p-0 p-md-4">
  <div class="col-12 col-sm8- offset-sm-2 col-md-7 offset-md-3 mt-1 pt-3 pb-3 form-color ">
    <div class="container">
      <h3><?= $formTitle ?></h3>
      <hr>
      <?php if (session()->get('success')): ?>
        <div class="alert alert-success" role="alert">
          <?= session()->get('success') ?>
        </div>
      <?php endif; ?>
      <form class="" action="/courses/<?= $action ?>" method="post">
        <div class="row">
          <div class="col-12 col-sm-6">
            <div class="form-group">
              <label class = "font-weight-bold text-muted" for="name">Title</label>
              <input required type="text" class="form-control" name="title" id="title"
              value="<?= isset($course['title']) ? $course['title'] :'' ?>">
            </div>
          </div>

          <!-- <div class="row"> -->
          <div class="col-12 col-sm-6">
            <div class="form-group">
            <label class = "font-weight-bold text-muted" for="url">URL</label>
            <input required type="text" class="form-control" name="url" id="url"
            value="<?= isset($course['url']) ? $course['url'] : '' ?>" >
            </div>
          </div>

          <div class="col-12">
            <div class="form-group">
              <label class = "font-weight-bold text-muted" for="description">Description</label>
              <textarea class="form-control" name="description" id="description" maxlength=500><?=
              isset($course['description']) ? trim($course['description']) : ''
              ?></textarea>
            </div>
          </div>

        
          <div class="col-12 col-sm-4">
            <div class="form-group">
                <label class = "font-weight-bold text-muted" for="is_certified">Is Certified</label> <br/>
                <input class="mt-4" type="checkbox" name="is_certified" id="is_certified" 
                        data-on="Yes" data-off="No" <?= isset($course['is_certified']) ? ($course['is_certified'] ? 'checked' : '') : '' ;?> data-toggle="toggle" >
                    
            </div>
          </div>
          <div class="col-12 col-sm-4">
            <div class="form-group">
              <label class = "font-weight-bold text-muted" for="status">Status</label>
              <select required class="form-control selectpicker" data-size="8" name="status" id="status" >
              <option value="" disabled <?= isset($course['status']) ? '' : 'selected' ?>>
                  Select
              </option>
              <?php foreach ($statusList as $value): ?>
                <option 
                  <?= isset($course['status']) ? (($course['status'] == $value) ? 'selected': '') : '' ?>
                  value="<?= $value ?>" >
                  <?= $value ?>
                </option>
              <?php endforeach; ?>
              
            </select>
            
            </div>
          </div>
          

          <div class="col-12">
            <div class="box">
                <div class="text-center font-weight-bold box-header"><span id="rpnHeading">K-Points Assessment</span></div>
                <div class="mt-3 box-body">
                    <div class="row">

                        <div class="col-12 text-center" id="data-k-points-matrix">
                            <?php foreach ($kPointsList as $key=>$value): ?>
                            <div>
                                <?php if (($value['id']) < 5) : ?>
                                <div class="form-group">
                                    <label class="font-weight-bold text-muted"><?php echo $value['category'];?></label>
                                    <br />
                                            <div class="btn-group btn-group-toggle btn-security-toggle" id="listblock<?php echo $key; ?>">
                                                <?php foreach ($value['options'] as $key1 => $value1) : ?>
                                                    <div class="btn btn-sm <?php echo (($value['value']) ==  $value1['title']) ? "btn-primary" : "btn-secondary"; ?> " id="RDanchor<?php echo $key;
                                                                                                                                                                                    echo $key1; ?>" data-toggle="popover" data-placement="left" title="<?php echo $value1['description']; ?>" onclick="calculateRAValue(<?php echo $key; ?> ,<?php echo $key1; ?>)">
                                                        <input type="radio" name="<?php echo str_replace(' ', '', $value['category']);?>-status-type" value="<?php echo $value1['value'] . '/' . $value1['title']; ?>" <?php echo (($value['value']) ==  $value1['title']) ? "checked" : ""; ?> />
                                                        <?php echo $value1['title']; ?>
                                                    </div>
                                        &nbsp;
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="col-12 col-sm-6 mt-6" id="data-k-points">
                            <div class="form-group">
                                <label class="font-weight-bold text-muted" for="KPoints">K Points</label>
                                <input type="text" class="form-control" name="k-points" id="k-points" readonly value="<?= isset($kPointsList[4]["value"]) ? $kPointsList[4]["value"] : '' ?>">
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        
          
          
        <?php if (isset($validation)): ?>
          <div class="col-12">
            <div class="alert alert-danger" role="alert">
              <?= $validation->listErrors() ?>
            </div>
          </div>
        <?php endif; ?>
        </div>

        <div class="row">
          <div class="col-12 mt-4 text-center">
            <button type="submit" class="btn btn-primary">Submit</button>
          </div>

        </div>
      </form>
    </div>
  </div>
</div>

<script>
function calculateRAValue(id, id1) {
    //removeing the all primary class and checked type and  added secondary class
    $('#listblock' + id + ' div').removeClass('btn-primary').addClass('btn-secondary');
    $('#listblock' + id + ' input').removeAttr('checked');
    //adding primary class to selected one
    var idVal = "#RDanchor" + id + id1;
    $(idVal).removeClass("btn-secondary").addClass('btn-primary');
    //calculating the rpn and adding checked attribute to get the values in controller
    var activeList = $('.btn-security-toggle .btn-primary input');
    //console.log(activeList);
    var points = 0;
    var rpn = 1;
    const activeRisk = [];
    for (var i = 0; i < activeList.length; i++) {
    $(activeList[i]).attr('checked', true);
    rpn = rpn * ($(activeList[i]).val()).split('/')[0];
        if (activeList.length == 4) {
            activeRisk.push(($(activeList[i]).val()).split('/')[0]);
        }
    }
    var numberArray = activeRisk.map(Number);
    for(var j=0; j<numberArray.length;j++){
      points+=numberArray[j];
    }
    $('#k-points').val(points);
}

</script>
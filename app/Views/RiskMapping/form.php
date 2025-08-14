<div class="row">
  <div class="col-12 col-sm8- offset-sm-2 col-md-7 offset-md-3 mt-1 pt-3 pb-3 form-color ">
    <div class="container">
      <h3><?= $formTitle ?></h3>
      <hr>
      <?php if (session()->get('success')): ?>
        <div class="alert alert-success" role="alert">
          <?= session()->get('success') ?>
        </div>
      <?php endif; ?>
      <form class="" action="/risk-mapping/<?= $action ?>" method="post">
          <div class="col-12">
            <div class="form-group">
              <label class = "font-weight-bold text-muted" for="name">Risk Item</label>
              <input required type="text" class="form-control" name="name" id="name"
              value="<?= isset($riskCategory['name']) ? $riskCategory['name'] :'' ?>">
            </div>
          </div>
     
          <div class="col-12">
                <div class="form-group">
                    <label class="font-weight-bold text-muted" for="risk-methodology">Risk Methodology</label>
                    <select class="form-control  selectpicker" data-live-search="true" data-size="8"
                        name="risk-methodology" id="risk-methodology">
                        <option value="" disabled <?= isset($riskCategory['risk-methodology']) ? '' : 'selected' ?>>
                            Select
                        </option>
                        <?php foreach ($riskMethodology as $riskMeth): ?>
                        <option
                            <?= isset($riskCategory['risk-methodology']) ? (($riskCategory['risk-methodology'] == $riskMeth["value"]) ? 'selected': '') : '' ?>
                            value="<?=  $riskMeth["value"] ?>"><?=  $riskMeth["value"] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
          <div class="col-12">
            <div class="form-group">
              <label class = "font-weight-bold text-muted" for="status">Status</label>
              <select required class="form-control selectpicker" data-size="8" name="status" id="status" >
              <option value="" disabled <?= isset($riskCategory['status']) ? '' : 'selected' ?>>
                  Select
              </option>
              <?php foreach ($statusList as $value): ?>
                <option 
                  <?= isset($riskCategory['status']) ? (($riskCategory['status'] == $value) ? 'selected': '') : '' ?>
                  value="<?= $value ?>" >
                  <?= $value ?>
                </option>
              <?php endforeach; ?>
              
            </select>
            
            </div>
          </div>
          
        <?php if (isset($validation)): ?>
          <div class="col-12">
            <div class="alert alert-danger" role="alert">
              <?= $validation->listErrors() ?>
            </div>
          </div>
        <?php endif; ?>

        <div class="row">
          <div class="col-12 text-center">
            <button type="submit" class="btn btn-primary">Submit</button>
          </div>

        </div>
      </form>
    </div>
  </div>
</div>


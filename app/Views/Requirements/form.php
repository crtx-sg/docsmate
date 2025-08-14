
  <div class="row p-0 p-md-4">
    <div class="col-12 col-sm8- offset-sm-2 col-md-6 offset-md-3 mt-1 pt-3 pb-3 form-color">

      <div class="container">
        <h3><?= $formTitle ?></h3>
        <hr>
        <?php if (session()->get('success')): ?>
          <div class="alert alert-success" role="alert">
            <?= session()->get('success') ?>
          </div>
        <?php endif; ?>
        <!-- Submit action -->
        <form class="" action="/requirements/<?= $action ?>" method="post">
          <div class="row">
            <?php if (isset($validation)): ?>
              <div class="col-12">
                <div class="alert alert-danger" role="alert">
                  <?= $validation->listErrors() ?>
                </div>
              </div>
            <?php endif; ?>
            <div class="col-12 col-sm-5">
              <div class="form-group">
              <label class = "font-weight-bold text-muted" for="type">Type</label>
               <select class="form-control  selectpicker" data-live-search="true" data-size="8" name="type" id="type">
                <option value="" disabled <?= isset($member['type']) ? '' : 'selected' ?>>
                    Select Type
                </option>
                <?php foreach ($requirementCategory as $reqCat): ?>
                  <option 
                  <?= isset($member['type']) ? (($member['type'] == $reqCat["value"]) ? 'selected': '') : '' ?>
                      value="<?=  $reqCat["value"] ?>" ><?=  $reqCat["value"] ?></option>
                <?php endforeach; ?>
              </select>
              </div>
              </div>
              <div class="col-12 col-sm-6" id="projectMapping">
                <div class="form-group">
                <label class = "font-weight-bold text-muted" for="product-id">Product</label>
              <select class="form-control  selectpicker" data-live-search="true" data-size="8" name="product-id" id="product-id">
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
              <label class = "font-weight-bold text-muted" for="requirement">Requirements</label>
              <input type="text" class="form-control" name="requirement" id="requirement"
              value="<?= isset($member['requirement']) ? $member['requirement'] : '' ?>" >
              </div>
            </div>

            <div class="col-12">
              <div class="form-group">
                <label class = "font-weight-bold text-muted" for="description">Description</label>
                <textarea class="form-control" name="description" id="description" ><?=
                  isset($member['description']) ? trim($member['description']) : ''
                  ?></textarea>
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
    var isEditForm = "<?php echo $isEditForm;?>";
  });
  </script>
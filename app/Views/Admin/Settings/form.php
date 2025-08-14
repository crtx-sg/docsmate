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
      <form class="" action="/settings/<?= $action ?>" method="post">
        <div class="row">
          <div class="col-12 col-sm-5">
            <div class="form-group">
              <label class = "font-weight-bold text-muted" for="name">Name</label>
              <input required type="text" class="form-control" name="name" id="name"
              value="<?= isset($product['name']) ? $product['name'] :'' ?>">
            </div>
          </div>

          <div class="col-12 col-sm-6">
            <div class="form-group">
            <label class = "font-weight-bold text-muted" for="version">Display Name</label>
            <input required type="text" class="form-control" name="display-name" id="display-name"
            value="<?= isset($product['display-name']) ? $product['display-name'] : '' ?>" >
            </div>
          </div>

          <div class="col-12">
            <div class="form-group">
              <label class = "font-weight-bold text-muted" for="description">Description</label>
              <textarea class="form-control" name="description" id="description" maxlength=200><?=
              isset($product['description']) ? trim($product['description']) : ''
              ?></textarea>
            </div>
          </div>

          <div class="col-12 col-sm-4">
            <div class="form-group">
              <label class = "font-weight-bold text-muted" for="status">Status</label>
              <select required class="form-control selectpicker" data-size="8" name="status" id="status" >
              <option value="" disabled <?= isset($product['status']) ? '' : 'selected' ?>>
                  Select
              </option>
              <?php foreach ($statusList as $value): ?>
                <option 
                  <?= isset($product['status']) ? (($product['status'] == $value) ? 'selected': '') : '' ?>
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
        </div>

        <div class="row">
          <div class="col-12 text-center">
            <button type="submit" class="btn btn-primary">Submit</button>
          </div>

        </div>
      </form>
    </div>
  </div>
</div>


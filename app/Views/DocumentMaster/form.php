<div class="p-0 p-sm-4">
  <div class="row">
    <div class="col-12 col-sm8- offset-sm-2 col-md-6 offset-md-3 mt-1 pt-3 pb-3 form-color">
      <div class="container">
        <h3><?= $formTitle ?></h3>
        <hr>
        <?php if (session()->get('success')): ?>
          <div class="alert alert-success" role="alert">
            <?= session()->get('success') ?>
          </div>
        <?php endif; ?>
        <form class="" action="/documents-master/<?= $action ?>" method="post">
          <div class="row">
            <div class="col-12 col-sm-6">
              <div class="form-group">
               <label class = "font-weight-bold text-muted" for="name">Name</label>
               <input type="text" class="form-control" name="name" id="name"
                value="<?= isset($document['name']) ? $document['name'] :'' ?>">
              </div>
            </div>
          
            <div class="col-12  col-sm-6">
              <div class="form-group">
               <label class = "font-weight-bold text-muted" for="version">Version</label>
               <input type="text" class="form-control" name="version" id="version"
                value="<?= isset($document['version']) ? $document['version'] : '' ?>" >
              </div>
            </div>
            <div class="col-12">
              <div class="form-group">
               <label class = "font-weight-bold text-muted" for="description">Description</label>
               <textarea class="form-control" name="description" id="description" maxlength=100><?=
                isset($document['description']) ? trim($document['description']) : ''
                ?></textarea>
              </div>
            </div>
            <div class="col-12 col-sm-4">
              <div class="form-group">
               <label class = "font-weight-bold text-muted" for="category">Category</label>
               <select class="form-control  selectpicker" data-live-search="true" data-size="8" name="category" id="category">
                    <option value="" disabled <?= isset($document['category']) ? '' : 'selected' ?>>
                        Select
                    </option>

                    <?php foreach ($referenceCategory as $refCat): ?>
                        <option 
                          <?= isset($document['category']) ? (($document['category'] == $refCat["value"]) ? 'selected': '') : '' ?>
                          value="<?=  $refCat["value"] ?>" ><?=  $refCat["value"] ?></option>
                    <?php endforeach; ?>

                </select>
             </div>
           </div>
           <div class="col-12 col-sm-4">
              <div class="form-group">
               <label class = "font-weight-bold text-muted" for="status">Status</label>
               <select class="form-control selectpicker" name="status" id="status" >
                <option value="" disabled <?= isset($document['status']) ? '' : 'selected' ?>>
                    Select
                </option>
                <?php foreach ($statusList as $value): ?>
                  <option 
                    <?= isset($document['status']) ? (($document['status'] == $value) ? 'selected': '') : '' ?>
                    value="<?= $value ?>" >
                    <?= $value ?>
                  </option>
                <?php endforeach; ?>
              </select>
              </div>
            </div>

            <div class="col-12 col-sm-4">
              <div class="form-group">
               <label class = "font-weight-bold text-muted" for="location">Location</label>
               <input type="text" class="form-control" name="location" id="location" 
               value="<?= isset($document['location']) ? $document['location'] : '' ?>" >
              </div>
            </div>
            <div class="col-12">
              <div class="form-group">
               <label class = "font-weight-bold text-muted" for="ref">Reference</label>
               <input type="text" class="form-control" name="ref" id="ref" 
               value="<?= isset($document['ref']) ? $document['ref'] : '' ?>" >
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
</div>

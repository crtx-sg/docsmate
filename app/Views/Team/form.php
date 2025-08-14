
  <div class="row p-0 p-md-4 justify-content-center">
    <div class="col-11 col-sm-10 col-lg-5 mt-1 pt-3 pb-3 form-color">

      <div class="container">
        <h3><?= $formTitle ?></h3>
        <hr>
        <?php if (session()->get('success')): ?>
          <div class="alert alert-success" role="alert">
            <?= session()->get('success') ?>
          </div>
        <?php endif; ?>
        <form class="" action="/team/<?= $action ?>" method="post">
          <div class="row">
            <?php if (isset($validation)): ?>
              <div class="col-12">
                <div class="alert alert-danger" role="alert">
                  <?= $validation->listErrors() ?>
                </div>
              </div>
            <?php endif; ?>
            <div class="col-12 col-sm-6">
              <div class="form-group">
               <label class = "font-weight-bold text-muted" for="name">Name</label>
               <input type="text" class="form-control" name="name" id="name" 
               value="<?= isset($member['name']) ? $member['name'] : '' ?>">
              </div>
            </div>
          
           

            <div class="col-12  col-sm-6">
              <div class="form-group">
              <label class = "font-weight-bold text-muted" for="role">Role</label>
              <select class="form-control  selectpicker" data-live-search="true" data-size="8" name="role" id="role">
                  <option value="" disabled <?= isset($member['role']) ? '' : 'selected' ?>>
                      Select
                  </option>

                  <?php foreach ($userRole as $user_role): ?>
                      <option 
                        <?= isset($member['role']) ? (($member['role'] == $user_role["value"]) ? 'selected': '') : '' ?>
                        value="<?=  $user_role["value"] ?>" ><?=  $user_role["value"] ?></option>
                  <?php endforeach; ?>

                </select>

              </div>
            </div>

            <div class="col-12 col-sm-10">
              <div class="form-group">
                <label class = "font-weight-bold text-muted" for="responsibility">Responsibility</label>
                <input type="text" class="form-control" name="responsibility" id="responsibility"
                value="<?= isset($member['responsibility']) ? $member['responsibility'] : '' ?>" >
              </div>
            </div>

            <div class="col-12 col-sm-2">
              <div class="form-group">
                  <label class = "font-weight-bold text-muted" for="is-manager">Manager</label> <br/>
                  <input class="mt-4" type="checkbox" name="is-manager" id="is-manager" 
                          data-on="Yes" data-off="No" <?= isset($member['is-manager']) ? ($member['is-manager'] ? 'checked' : '') : '' ;?> data-toggle="toggle" >
            
              </div>
            </div>

          <!-- </div> -->

          <!-- <div class="row mt-sm-2"> -->
            <div class="col-12 col-sm-8">
                <div class="form-group">
                  <label class = "font-weight-bold text-muted" for="email">Email address</label>
                  <input 
                      <?php if(isset($member['email'])){ if($member['email'] != ""){echo "readonly";} } ?>
                      type="text" class="form-control" name="email" id="email"
                      value="<?= isset($member['email']) ? $member['email'] : '' ?>" >
                </div>
            </div>

            <div class="col-12 col-sm-2 " >
              <div class="form-group">

                  <label class = "font-weight-bold text-muted" for="is-admin">Admin</label> <br/>
                  <input class="mt-4" type="checkbox" name="is-admin" id="is-admin" 
                          data-on="Yes" data-off="No" <?= isset($member['is-admin']) ? ($member['is-admin'] ? 'checked' : '') : '' ;?> data-toggle="toggle" >
              
              </div>
            </div>

            <div class="col-12 col-sm-2 " >
              <div class="form-group">

                  <label class = "font-weight-bold text-muted" for="is-active">Active</label> <br/>
                  <input class="mt-4" type="checkbox" name="is-active" id="is-active" 
                          data-on="Yes" data-off="No" <?= isset($member['is-active']) ? ($member['is-active'] ? 'checked' : '') : '' ;?> data-toggle="toggle" >
              
              </div>
            </div>

            

          </div>
        
          <div class="row justify-content-center mt-2">
            <div class="col-2">
              <button type="submit" class="btn btn-primary">Submit</button>
            </div>
          </div>

        </form>
      </div>

    </div>
  </div>


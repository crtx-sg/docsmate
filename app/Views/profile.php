
  <div class="row p-0 p-md-4 justify-content-center">
    <div class="col-11 col-sm-10 col-lg-5 mt-1 pt-3 pb-3 form-color">
      <div class="container">
        <h3><?= $user['name'] ?></h3>
        <hr>
        <?php if (session()->get('success')): ?>
          <div class="alert alert-success" role="alert">
            <?= session()->get('success') ?>
          </div>
        <?php endif; ?>
        <form class="" action="/profile" method="post">
          <div class="row">
            <div class="col-12">
              <div class="form-group">
               <label for="name">Name</label>
               <input type="text" class="form-control" name="name" id="name" value="<?= set_value('name', $user['name']) ?>">
              </div>
            </div>
          <div class="col-12">
              <div class="form-group">
               <label for="email">Email address</label>
               <input type="text" class="form-control" readonly id="email" value="<?= $user['email'] ?>">
              </div>
            </div>
            <div class="col-12 col-sm-6">
              <div class="form-group">
               <label for="password">Password</label>
               <input type="password" class="form-control" name="password" id="password" value="">
             </div>
           </div>
           <div class="col-12 col-sm-6">
             <div class="form-group">
              <label for="password_confirm">Confirm Password</label>
              <input type="password" class="form-control" name="password_confirm" id="password_confirm" value="">
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
            <div class="col-12 col-sm-4">
              <button type="submit" class="btn btn-primary">Update</button>
            </div>

          </div>
        </form>
      </div>
    </div>
  </div>
<footer class="website-footer">
    <p><b>Note:</b> Please configure Docsgo IM using this <a target="_blank" href="/openfire/docsgoIM_config_document.pdf"><b
                >configuration document</b></a> so as to get real time update of your documents and reviews.</p>
</footer>

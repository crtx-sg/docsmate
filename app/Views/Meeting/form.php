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
      <form class="" action="/meeting/<?= $action ?>" method="post">
      <?php if (isset($validation)): ?>
          <div class="col-12">
            <div class="alert alert-danger" role="alert">
              <?= $validation->listErrors() ?>
            </div>
          </div>
        <?php endif; ?>
        <div class="row">
        <div class="col-12  col-sm-12">
            <div class="form-group">
              <label class = "font-weight-bold text-muted" for="title">Title</label>
              <input required type="text" class="form-control" name="title" id="title" value="<?= isset($meeting['title']) ? $meeting['title'] : '' ?>">
            </div>
          </div>
          </div>
        <div class="row">
                        <input type="hidden" id="meeting-id" name="meeting-id"
                            value="<?= isset($meeting['meeting-id']) ? $meeting['meeting-id']: '' ?>" />
                       
                        
          <div class="col-12  col-sm-3">
            <div class="form-group">
              <label class = "font-weight-bold text-muted" for="entry-date">Entry Date</label>
              <?php
              $defaultDate ="";
              if(isset($meeting['entry-date'])){
                $defaultDate = $meeting['entry-date'];
              }
              else{
                $defaultDate = date("Y-m-d");
              }
              ?>
              <input required type="date" class="form-control" name="entry-date" id="entry-date"
              value="<?= $defaultDate ?>">
            </div>
          </div>
          
          <div class="col-12">
            <div class="form-group">
              <label class = "font-weight-bold text-muted" for="notes">Notes</label>
              <textarea class="form-control" name="notes" id="notes"><?=
              isset($meeting['notes']) ? trim($meeting['notes']) : ''
              ?></textarea>
            </div>
          </div>
                
        
        </div>

        <?php if(!isset($meeting['meeting-id'])){ ?>
        <div class="row">
          <div class="col-12 text-center">
            <button type="submit" class="btn btn-primary">Submit</button>
          </div>

        </div>
        <?php } ?>
      </form>
    </div>
  </div>
</div>
<script>
$(document).ready(function() {
   // $('#entry-date').datepicker('setDate', date("Y-m-d"));
});

</script>

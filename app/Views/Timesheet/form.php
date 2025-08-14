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
      <form class="" action="/timesheet/<?= $action ?>" method="post">
      <?php if (isset($validation)): ?>
          <div class="col-12">
            <div class="alert alert-danger" role="alert">
              <?= $validation->listErrors() ?>
            </div>
          </div>
        <?php endif; ?>
        <div class="row">
                        <input type="hidden" id="timesheet-id" name="timesheet-id"
                            value="<?= isset($timesheet['timesheet-id']) ? $timesheet['timesheet-id']: '' ?>" />
                            <!-- <input type="hidden" id="project-id" name="project-id" value="<?= $project_id ?>" /> -->
                       
                        <div class="col-12 col-sm-3">
                            <div class="form-group">
                                    <label class="font-weight-bold text-muted" for="project">Project</label>
                                        <select class="form-control  selectpicker" data-live-search="true" data-size="8"
                                            name="project-id" id="project-id">
                                            <option value="" disabled
                                                <?= (isset($timesheet['project-id']) && ($timesheet['project-id'] != 0) ) ? '' : 'selected' ?>>
                                                Select Project
                                            </option>
                                            <?php foreach ($projects as $key=>$value): ?>
                                            <option
                                                <?= isset($timesheet['project-id']) ? (($timesheet['project-id'] == $key) ? 'selected': '') : '' ?>
                                                value="<?=  $key ?>"><?=  $value ?></option>
                                            <?php endforeach; ?>
                                        </select>
                            </div>
                        </div>
          &nbsp;&nbsp;&nbsp;&nbsp;
          <div class="col-12 col-sm-3">
            <div class="form-group">
              <label class = "font-weight-bold text-muted" for="type">Task Type</label>
              
              <select class="form-control  selectpicker" data-live-search="true" data-size="8" name="type" id="type">
              <option value="" disabled <?= isset($timesheet['type']) ? '' : 'selected' ?>>
                  Select
              </option>
              <?php foreach ($timeTrackerType as $timeType): ?>
                <option 
                  <?= isset($timesheet['type']) ? (($timesheet['type'] == $timeType["value"]) ? 'selected': '') : '' ?>
                  value="<?=  $timeType["value"] ?>"><?=  $timeType["value"] ?></option>
              <?php endforeach; ?>
              
            </select>

            </div>
          </div>

          <div class="col-12  col-sm-3">
            <div class="form-group">
              <label class = "font-weight-bold text-muted" for="entry-date">Start Date</label>
              <?php
              $defaultDate ="";
              if(isset($timesheet['entry-date'])){
                $defaultDate = $timesheet['entry-date'];
              }else{
                $defaultDate = date("Y-m-d");
              }?>
              <input required type="date" class="form-control" name="entry-date" id="entry-date"
              value="<?= $defaultDate ?>">
            </div>
          </div>
          <input type="hidden" id="user-id" name="user-id" value="<?= session()->get('id') ?>" />
          <div class="col-12 col-sm-2">
            <div class="form-group">
            <label class="font-weight-bold text-muted" for="status">Status</label>
                                <select class="form-control  selectpicker" data-live-search="true" data-size="8"
                                    name="status" id="status">
                                    <option value="" disabled>
                                        Select
                                    </option>
                                    <?php foreach ($timesheetStatus as $time): ?>
                                    <option
                                        <?= isset($timesheet['status']) ? (($timesheet['status'] == $time["value"]) ? 'selected': '') : '' ?>
                                        value="<?=  $time["value"] ?>"><?=  $time["value"] ?></option>
                                    <?php endforeach; ?>
                                </select>
            
            </div>
          </div>
          
          <div class="col-12  col-sm-4">
            <div class="form-group">
              <label class = "font-weight-bold text-muted" for="log-date">Log Date</label>
              <?php $defaultLogDate = date("Y-m-d");?>
              <input required type="date" class="form-control" name="log-date" id="log-date"
              value="<?= $defaultLogDate ?>">
            </div>
          </div>


          <div class="col-12  col-sm-4">
            <div class="form-group">
            <input type="hidden" id="edit-log-hours" name="edit-log-hours" value="NotChanged" />
              <label class = "font-weight-bold text-muted" for="day-log-hours">Hours Today</label>
              <input required type="number" class="form-control" name="day-log-hours" id="day-log-hours"
              value="00" min="00" max="24">
              
            </div>
          </div>

          <div class="col-12  col-sm-4">
            <div class="form-group">
              <label class = "font-weight-bold text-muted" for="total-logged-hours">Total Hours</label>
              <input readOnly type="number" class="form-control" name="total-logged-hours" id="total-logged-hours"
              value="<?= isset($timesheet['total-logged-hours']) ? $timesheet['total-logged-hours'] : '' ?>">
            </div>
          </div>

        
          
          <?php if(isset($timesheet['log'])){ ?>
          <div class="col-12"><span class="pull-right">
          <button data-toggle="popover" data-placement="left" title="List Messages" type="button" class="ml-1 btn btn-sm btn-orange box-shadow-right btn-warning" onclick="listMessages(<?php echo $timesheet['timesheet-id'];?>)">
              <i class="fa fa-list"></i>
          </button>
          </span>
          </div>
          <?php } ?>

          <div class="col-12">
            <div class="form-group">
              <label class = "font-weight-bold text-muted" for="log">Log</label>
              <textarea class="form-control" name="log" id="log" maxlength=2500></textarea>
            </div>
          </div>
                
          <div class="col-12">
            <div class="form-group">
            <label class = "font-weight-bold text-muted" for="dependencies">Dependencies</label>
            <input  type="text" class="form-control" name="dependencies" id="dependencies"
            value="<?= isset($timesheet['dependencies']) ? $timesheet['dependencies'] : '' ?>" >
            </div>
          </div>
          
        
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
<script>
$(document).ready(function() {
    $('#entry-date').datepicker('setDate', date("Y-m-d"));
    $('#log-date').datepicker('setDate', date("Y-m-d"));
});

$("#day-log-hours").on("change keyup paste", function(){
  $("#edit-log-hours").val("Changed");
})

function listMessages(timesheetId) {
    var commentsHtml = "";

    $.ajax({
    type: 'GET',
    url: '/timesheet/getLogMessages/' +timesheetId,
      success: function (response) {
        var data = JSON.parse(response);
        const res = data.result;
        const msgContent = JSON.parse(res);
        if(msgContent != null){
            msgContent.forEach((logData, i) => {
            if (i == 0) {
                commentsHtml +=
                    `<ul class="list-group scroll scroll-orange" style="max-height: 300px;overflow-y: auto;">`;
            }
            commentsHtml += `<li class="list-group-item list-group-item-action" style="font-size:14px;line-height:1.5rem">
                                          <span style="white-space: pre-line"> ${logData.message} </span>`
                                          if(typeof (logData.logHrs) != 'undefined'){
                                            commentsHtml += `<footer class="blockquote-footer text-right"> For <cite>${logData.logHrs}&nbsp;&nbsp;Hours </cite> on ${formatDate(logData.timestamp)}</footer>`
                                          }else{
                                            commentsHtml += `<footer class="blockquote-footer text-right">${formatDate(logData.timestamp)}</footer>`
                                            }
                                    `</li>`;
            if (i == (msgContent.length - 1)) {
                commentsHtml += `</ul>`;
            }

          });
        }
      var dialog = bootbox.dialog({
        title: "Log trail for the task",
        message: `<div class="row">
                            <div class="col-12">${commentsHtml}</div>
                        </div>
                        `,
    });

      },
      error: function (err) {
          console.log(err);
      }
    });
}

</script>

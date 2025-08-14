<?php $userId = session()->get('id');  ?>
<div class="row p-0 p-md-4 justify-content-center">

<div class="col-12 pt-3 mb-4 pt-md-0 pb-md-0">
<div class="row">
            <div class="col-md-2">
                <div class="form-group mb-0">
                    <label class="font-weight-bold text-muted" for="projects">Project</label>
                    <select class="form-control selectpicker" onchange="getTableRecords(true)" id="projects"
                        data-style="btn-secondary" data-live-search="true" data-size="8">
                        <option value="ALL">
                            All
                        </option>
                        <?php foreach ($projects as $key => $value): ?>
                        <option <?=(($selectedProject == $key) ? "selected" : "")?> value="<?=$key?>"><?=$value?>
                        </option>
                        <?php endforeach;?>
                    </select>
                </div>

            </div>
            <?php if (session()->get('is-admin') || session()->get('is-manager')): ?>
            <div class="col-md-2">
                <div class="form-group mb-0">
                    <label class="font-weight-bold text-muted" for="selectedUser">Member</label>
                    <select class="form-control selectpicker" onchange="getTableRecords(true)" id="selectedUser"
                        data-style="btn-secondary" data-live-search="true" data-size="8">
                        <option value="ALL">
                            All
                        </option>
                        <?php foreach ($teamMembers as $key => $value): ?>
                        <option <?=(($selectedUser == $key) ? "selected" : "")?> value="<?=$key?>"><?=$value?>
                        </option>
                        <?php endforeach;?>
                    </select>
                </div>
            </div>
            <?php endif; ?>
            <div class="col-md-2">
                <label class="font-weight-bold text-muted">Status</label><br />
                <div class="btn-group btn-group-toggle ">
                    <?php foreach ($timesheetStatus as $timeStatus): ?>
                    <?php
                        $statusId = str_replace(' ', '_', $timeStatus);
                        $selected = ($selectedStatus == $timeStatus) ? true : false;
                        //$statusCount = (isset($reviewsCount[$revStatus])) ? $reviewsCount[$revStatus] : 0;
                    ?>
                    <label class="lbl_<?= $statusId ?> btn <?=($selected ? " btn-primary" : "btn-secondary")?>">
                        <input type="radio" name="view" value="<?=$timeStatus?>" autocomplete="off"
                            onclick="getTableRecords()" <?=($selected ? "checked" : "")?>> <?=$timeStatus?>
                    </label>
                    <?php endforeach;?>
                </div>
            </div>
</div>
</div>
    <div class="col-12">
      <table id="timesheet-list" class="table  table-hover">
      <thead class="thead-dark">
        <tr>
          <th scope="col">#</th>
          <th scope="col" style="width:30px">Project</th>
          <th scope="col" style="width:30px">User</th>
          <th scope="col">Start Date</th>
          <th scope="col">Task Type</th>
          <th scope="col">Total Hours</th>
          <th scope="col">Hours Today</th>
          <!-- <th scope="col" style="min-width:150px;">Log</th> -->
          <!-- <th scope="col" style="min-width:100px;">Dependencies</th> -->
          <th scope="col" style="width:96px">Update date</th>
          <th scope="col" style="min-width:80px;text-align:center;">Action</th>
        </tr>
      </thead>
      <tbody class="bg-white" id="tbody"></tbody>
        
    </table>
  </div>
  </div>

<script>

var userId, timesheetStatus, table = null;

$(document).ready(function() {
  userId = <?= $userId ?>;
    timesheetStatus = <?= json_encode($timesheetStatus) ?>;

    table = initializeDataTable('timesheet-list');

    getTableRecords();

});


$(document).on({
    ajaxStart: function() {
        $("#loading-overlay").show();
    },
    ajaxStop: function() {
        $("#loading-overlay").hide();
    }
});

function deleteTimesheet(id){

bootbox.confirm("Do you really want to delete record?", function(result) {
  if(result){
    $.ajax({
       url: '/timesheet/delete/'+id,
       type: 'GET',
       success: function(response){
          response = JSON.parse(response);
          if(response.success == "True"){
              $("#"+id).fadeOut(800)
          }else{
             bootbox.alert('Record not deleted.');
          }
        }
     });
  }else{
    console.log('Delete Cancelled');
  }

});

}


function getTableRecords(updateStats = false) {
    const selectedView = $("input[name='view']:checked").val();
    const selectedProjectId = $("#projects").val();
    const selectedUsers = $("#selectedUser").val();
    
    var url = `/timesheet/getTimesheets?view=${selectedView}&project_id=${selectedProjectId}&user_id=${selectedUsers}`;

    $("#addButton").attr("href", `/timesheet/add?project_id=${selectedProjectId}`);

    $(".btn-group label").removeClass("btn-primary").addClass("btn-secondary");
    $(`.lbl_${selectedView.replace(/\s/g, '_')}`).removeClass("btn-secondary").addClass("btn-primary");

    makeRequest(url)
        .then((response) => {
            const timesheetsList = response.timesheets;
            populateTable(timesheetsList);
        })
        .catch((err) => {
            console.log(err);
            showPopUp('Error', "An unexpected error occured on server.");
        })

}

function populateTable(timesheetsList) {
    dataInfo = {
        "rowId": 'timesheet-id',
        "requiredFields": ['name', 'person', 'entry-date', 'type', 'total-logged-hours', 'day-log-hours', 'updated_at'],
        "dateFields": ["updated_at"],
        "action": [{
                title: "List Logs",
                buttonClass: "btn btn-primary",
                iconClass: "fa fa-list",
                clickTrigger: "listMessages",
                clickParams: ['timesheet-id']
            },
            {
                title: "Edit",
                buttonClass: "btn btn-warning",
                iconClass: "fa fa-edit",
                clickTrigger: "edit",
                clickParams: ['timesheet-id']
            },
            {
                title: "Delete",
                buttonClass: "btn btn-danger",
                iconClass: "fa fa-trash",
                clickTrigger: "deleteTimesheet",
                clickParams: ['timesheet-id'],
                // condition: {
                //     on: 'assigned-to',
                //     with: userId
                // }
            }
        ]
    };

    if (timesheetsList.length) {
        table.destroy();
    }

    $('#tbody').html("");
    var data = getHTMLtable(timesheetsList, dataInfo);
    $('#tbody').append(data);

    if (timesheetsList.length) {
        table = initializeDataTable('timesheet-list');
    }

    var dt = $('#timesheet-list').DataTable();
    //hide the project column
    if($("#projects").val() == "ALL")
        dt.column(1).visible(true);
    else
        dt.column(1).visible(false);

    //hide the user column
    if($("#selectedUser").val() == "ALL")
        dt.column(2).visible(true);
    else
        dt.column(2).visible(false);

}

function edit(id) {
    location.href = `/timesheet/add/${id}`;
}

function listMessages(timesheetId) {
    var commentsHtml = "";
    var rowIndex = 1;
    var table = $('#timesheet-list').DataTable();

    $('#timesheet-list').off('click').on( 'click', 'tr', function () {
        rowIndex += table.row( this ).index();
    });

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
        title: "Log trail for task #"+rowIndex,
        size: 'large',
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
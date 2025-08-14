<?php $userId = session()->get('id');  ?>
<div class="row p-0 p-md-4 justify-content-center">

<div class="col-12 pt-3 mb-4 pt-md-0 pb-md-0">

    <div class="row">
            <div class="col-md-2">
                <div class="form-group mb-0">
                    <label class="font-weight-bold text-muted" for="from_date">From Date</label>
                    <input type="date" class="form-control" required name="from_date"
                                    id="from_date" value="<?= isset($selectedFromDate) ? $selectedFromDate : '' ?>">
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group mb-0">
                    <label class="font-weight-bold text-muted" for="to_date">To Date</label>
                    <input type="date" class="form-control" required name="to_date"
                                    id="to_date" value="<?= isset($selectedToDate) ? $selectedToDate : '' ?>">
                </div>
            </div>
           
            <div class="col-md-2">
                <div class="form-group mb-0">
                <button class="btn btn-primary " onclick="getTableRecords(true)" style="margin:28px;">
                    <i class="fa fa-filter" aria-hidden="true"></i>
                </button>
                </div>
            </div>

            <div class="col-md-2">
                <div class="form-group mb-0">
            <a target="_blank" class="pull-right btn btn-warning" style="margin:28px" onclick="downloadTimesheet();" id="timesheet-download"
            ><i class="fa fa-file-excel-o"></i> Export to Excel</a>
                </div>
            </div>
        </div>
    </div>
    </div>
    <div class="col-12">
        <table class="table  table-hover" id="timesheet-reports">
            <thead class="thead-dark">
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Task</th>
                    <th scope="col">Log</th>
                    <th scope="col">Total Hours</th>
                    <th scope="col">Status</th>
                    <th scope="col">Member</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white " id="tbody"></tbody>
        </table>
    </div>

<!-- </div> -->

<script>
var table = null;

$(document).ready(function() {
    table = initializeDataTable('timesheet-reports');

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

function getTableRecords(updateStats = false) {
    const selectedFromDate = $("#from_date").val();
    const selectedToDate = $("#to_date").val();
    var url = `/timesheet/reports/getTimesheetReports?from_date=${selectedFromDate}&to_date=${selectedToDate}`;

    makeRequest(url)
        .then((response) => {
            const timesheetReports = response.timesheetReportsData;
            populateTable(timesheetReports);
        })
        .catch((err) => {
            console.log(err);
            showPopUp('Error', "An unexpected error occured on server.");
        })
}

    
function populateTable(timesheetReports)
{
    dataInfo = {
        "rowId": 'timesheet-id',
        "requiredFields": ['task', 'log', 'tHours', 'status', 'name'],
        "dateFields": [],
        "action": []
    };

    if (timesheetReports.length) {
        table.destroy();
    }

    $('#tbody').html("");
    //console.log(userCoursesReport);
    var data = getHTMLtable(timesheetReports, dataInfo);
    $('#tbody').append(data);

    if (timesheetReports.length) {
        table = initializeDataTable('timesheet-reports');
    }
    var dt = $('#timesheet-reports').DataTable();
    //hide the sixth column
    dt.column(6).visible(false);
}

function downloadTimesheet() {
    const selectedFromDate = $("#from_date").val();
    const selectedToDate = $("#to_date").val();
    var url = `/timesheet/createExcel?from_date=${selectedFromDate}&to_date=${selectedToDate}`;

    $.ajax({
        url: url,
        success: function(response) {
            data = JSON.parse(response)
            if (data.success == "True") {
                var host = 'https://'+location.hostname+'/'+data.fileDownloadUrl;
                window.open(host, '_blank');
                showFloatingAlert("Success: File downloaded!");
            } else {
                showErrorPopup("Download Error", "No data to download the file", 'lg');
            }
        },
        error: function(error) {
            showErrorPopup("Download Error", "No data to download the file", 'lg');
        }
    });
}

function showErrorPopup(title, message, width) {
    bootbox.alert({
        title: title,
        message: message,
        centerVertical: true,
        backdrop: 'static',
        size: width,
        buttons: {
            ok: {
                label: 'Close'
            }
        }
    });
}

</script>
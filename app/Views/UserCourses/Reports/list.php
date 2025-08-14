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
        </div>
    </div>
    </div>
    <div class="col-12">
        <table class="table  table-hover" id="user-courses-reports">
            <thead class="thead-dark">
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">User</th>
                    <th scope="col">Courses Planned</th>
                    <th scope="col">Courses Completed</th>
                    <th scope="col">K-Points</th>
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
    table = initializeDataTable('user-courses-reports');

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
    var url = `/userCourses/reports/getUserCoursesReports?from_date=${selectedFromDate}&to_date=${selectedToDate}`;

    makeRequest(url)
        .then((response) => {
            const userCoursesReports = response.coursesReports;
            populateTable(userCoursesReports);
        })
        .catch((err) => {
            console.log(err);
            showPopUp('Error', "An unexpected error occured on server.");
        })
}

    
function populateTable(userCoursesReport)
{
    dataInfo = {
        "rowId": 'user_course_id',
        "requiredFields": ['name', 'pDate', 'cDate', 'points'],
        "dateFields": [],
        "action": []
    };

    if (userCoursesReport.length) {
        table.destroy();
    }

    $('#tbody').html("");
    //console.log(userCoursesReport);
    var data = getHTMLtable(userCoursesReport, dataInfo);
    $('#tbody').append(data);

    if (userCoursesReport.length) {
        table = initializeDataTable('user-courses-reports');
    }
    var dt = $('#user-courses-reports').DataTable();
    //hide the fifth column
    dt.column(5).visible(false);
}

</script>
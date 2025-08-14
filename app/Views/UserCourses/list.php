<?php $userId = session()->get('id');  ?>
<div class="row p-0 p-md-4 justify-content-center">

    <div class="col-12 pt-3 mb-4 pt-md-0 pb-md-0">

        <div class="row">
            <?php if(session()->get('is-admin') || session()->get('is-manager')): ?>
            <div class="col-md-2">
                <div class="form-group mb-0">
                    <label class="font-weight-bold text-muted" for="selectedUser">User</label>
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
            <?php else: ?>
                <input type="hidden" class="form-control"  name="selectedUser" id="selectedUser" value="<?= $userId ?>">
            <?php endif; ?>

            <div class="col-md-6">
                <label class="font-weight-bold text-muted">Status</label><br />
                <div class="btn-group btn-group-toggle ">
                    <?php foreach ($userCourseStatus as $courStatus): ?>
                    <?php
                        $statusId = str_replace(' ', '_', $courStatus);
                        $selected = ($selectedStatus == $courStatus) ? true : false;
                        $statusCount = (isset($userCoursesCount[$courStatus])) ? $userCoursesCount[$courStatus] : 0;
                    ?>
                    <label class="lbl_<?= $statusId ?> btn <?=($selected ? " btn-primary" : "btn-secondary")?>">
                        <input type="radio" name="view" value="<?=$courStatus?>" autocomplete="off"
                            onclick="getTableRecords()" <?=($selected ? "checked" : "")?>> <?=$courStatus?>
                            <span class="stats_<?= $statusId ?> badge badge-light ml-1 "><?= $statusCount ?></span>
                    </label>
                    <?php endforeach;?>
                </div>

            </div>

            <div class="col-sm-2">
                <div class="form-group">
                    <label class="font-weight-bold text-muted" for="K-Points">Targeted K-Points</label>
                    <button style="width:120px;" type="button" id="K-Points" data-toggle="popover"
                        data-placement="top" data-content="<?= $userTotKPoints ?>"
                        class="btn btn-info truncate"><?= $userTotKPoints ?></button>
                </div>
            </div>

            <div class="col-sm-2">
                <div class="form-group">
                    <label class="font-weight-bold text-muted" for="achievedK-Points">Achieved K-Points</label>
                    <button style="width:120px;" type="button" id="achievedK-Points" data-toggle="popover"
                        data-placement="top" data-content="<?= $userAchievedKPoints ?>"
                        class="btn btn-info truncate"><?= $userAchievedKPoints ?></button>
                </div>
            </div>

        </div>


    </div>

    <div class="col-12">
        <table class="table  table-hover" id="user-courses-list">
            <thead class="thead-dark">
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">User</th>
                    <th scope="col">Course</th>
                    <th scope="col">Planned Date</th>
                    <th scope="col">Completed Date</th>
                    <th scope="col">Status</th>
                    <th scope="col" style="width:96px">Update date</th>
                    <th scope="col" style="width:90px">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white " id="tbody"></tbody>
        </table>
    </div>

</div>

<script>
var userId, userCourseStatus, table = null;

$(document).ready(function() {
    userId = <?= $userId ?>;
    userCourseStatus = <?= json_encode($userCourseStatus) ?>;

    table = initializeDataTable('user-courses-list');

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
    const selectedView = $("input[name='view']:checked").val();
    const selectedUsers = $("#selectedUser").val();
    var url = `/userCourses/getUserCourses?view=${selectedView}&user_id=${selectedUsers}`;

    $("#addButton").attr("href", `/userCourses/add`);

    $(".btn-group label").removeClass("btn-primary").addClass("btn-secondary");
    $(`.lbl_${selectedView.replace(/\s/g, '_')}`).removeClass("btn-secondary").addClass("btn-primary");

    makeRequest(url)
        .then((response) => {
            const userCoursesList = response.userCourses;
            populateTable(userCoursesList);
        })
        .catch((err) => {
            console.log(err);
            showPopUp('Error', "An unexpected error occured on server.");
        })

    if (updateStats) {
        var url = `/userCourses/getUserCoursesStats?user_id=${selectedUsers}`;

        makeRequest(url)
            .then((response) => {
                const userCourseStats = response.userCourseStats;
                updateCount(userCourseStats);
            })
            .catch((err) => {
                console.log(err);
                showPopUp('Error', "An unexpected error occured on server.");
            })
    }
    var pointsUrl = `/userCourses/getUserTotalKPoints?user_id=${selectedUsers}`;
        makeRequest(pointsUrl)
            .then((response) => {
                const userKPoints = response.points;
                $("#K-Points").html(userKPoints);
            })
            .catch((err) => {
                console.log(err);
                showPopUp('Error', "An unexpected error occured on server.");
            })

    var pointsUrl = `/userCourses/getUserAchievedKPoints?user_id=${selectedUsers}`;
        makeRequest(pointsUrl)
            .then((response) => {
                const userAchievedKPoints = response.points;
                $("#achievedK-Points").html(userAchievedKPoints);
            })
            .catch((err) => {
                console.log(err);
                showPopUp('Error', "An unexpected error occured on server.");
            })
}

function updateCount(updatedCount) {
    userCourseStatus.forEach(status => {
        var count = 0;
        if (updatedCount != null) {
            if (updatedCount.hasOwnProperty(status)) {
                count = updatedCount[status];
            }
        }


        $(`.stats_${status.replace(/\s/g, '_')}`).text(count);
    })

}

function populateTable(userCoursesList) {
    dataInfo = {
        "rowId": 'user_course_id',
        "requiredFields": [ 'register', 'course_title','planned_date', 'completed_date', 'status', 'updated_at'],
        "dateFields": ["updated-at"],
        "action": [{
                title: "Edit",
                buttonClass: "btn btn-warning btn-sm",
                iconClass: "fa fa-edit",
                clickTrigger: "edit",
                clickParams: ['user_course_id']
            },
            {
                title: "Delete",
                buttonClass: "btn btn-danger btn-sm",
                iconClass: "fa fa-trash",
                clickTrigger: "deleteUserCourse",
                clickParams: ['user_course_id'],
                condition: {
                    on: 'user_id',
                    with: userId
                }
            }
        ]
    };

    if (userCoursesList.length) {
        table.destroy();
    }

    $('#tbody').html("");
    var data = getHTMLtable(userCoursesList, dataInfo);
    $('#tbody').append(data);

    if (userCoursesList.length) {
        table = initializeDataTable('user-courses-list');
    }

    var dt = $('#user-courses-list').DataTable();
    //hide the review type column
    if($("#selectedUser").val() == "ALL")
        dt.column(1).visible(true);
    else
        dt.column(1).visible(false);

}

function edit(id) {
    location.href = `/userCourses/add/${id}`;
}

function deleteUserCourse(id) {

    bootbox.confirm("Do you really want to delete your course?", function(result) {
        if (result) {
            $.ajax({
                url: '/userCourses/delete/' + id,
                type: 'GET',
                success: function(response) {
                    response = JSON.parse(response);
                    if (response.success == "True") {
                        $("#" + id).fadeOut(800)
                    } else {
                        bootbox.alert('Your added course not deleted.');
                    }
                }
            });
        } else {
            console.log('Delete Cancelled');
        }

    });

}
</script>
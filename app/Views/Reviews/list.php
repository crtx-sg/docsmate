<?php $userId = session()->get('id');  ?>
<div class="row p-0 p-md-4 justify-content-center">

    <div class="col-12 pt-3 mb-4 pt-md-0 pb-md-0">

        <div class="row">
            <div class="col-md-2">
                <div class="form-group mb-0">
                    <label class="font-weight-bold text-muted" for="projects">Project</label>
                    <select class="form-control selectpicker" onchange="getTableRecords(true)" id="projects"
                        data-style="btn-secondary" data-live-search="true" data-size="8">
                        <option value="" disabled>
                            Select Project
                        </option>
                        <?php foreach ($projects as $key => $value): ?>
                        <option <?=(($selectedProject == $key) ? "selected" : "")?> value="<?=$key?>"><?=$value?>
                        </option>
                        <?php endforeach;?>
                        <?php foreach ($completedProjects as $key1 => $value1): ?>
                        <option <?=(($selectedProject == $key1) ? "selected" : "")?> value="<?=$key1?>" style="color:gray"><?=$value1?>
                        </option>
                        <?php endforeach;?>
                    </select>
                </div>

            </div>

            <div class="col-md-2">
                <div class="form-group mb-0">
                    <label class="font-weight-bold text-muted" for="selectedUser">Contributor</label>
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

            <div class="col-md-2">
                            <div class="form-group mb-0">
                                <label class="font-weight-bold text-muted" for="category">Review Type</label>
                                <select class="form-control  selectpicker" data-live-search="true" data-size="8" data-style="btn-secondary" 
                                    name="category" id="category" onchange="getTableRecords(true)">
                                    <option value="ALL">
                                        All
                                    </option>
                                    <?php foreach ($reviewCategory as $revCat): ?>
                                    <option <?= (($category == $revCat["value"]) ? "selected": "")?>
                                        value="<?=$revCat["value"]?>"><?=$revCat["value"]?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
            </div>

            <div class="col-md-6">
                <label class="font-weight-bold text-muted">Status</label><br />
                <div class="btn-group btn-group-toggle ">
                    <?php foreach ($reviewStatus as $revStatus): ?>
                    <?php
                        $statusId = str_replace(' ', '_', $revStatus);
                        $selected = ($selectedStatus == $revStatus) ? true : false;
                        $statusCount = (isset($reviewsCount[$revStatus])) ? $reviewsCount[$revStatus] : 0;
                    ?>
                    <label class="lbl_<?= $statusId ?> btn <?=($selected ? " btn-primary" : "btn-secondary")?>">
                        <input type="radio" name="view" value="<?=$revStatus?>" autocomplete="off"
                            onclick="getTableRecords()" <?=($selected ? "checked" : "")?>> <?=$revStatus?>
                        <span class="stats_<?= $statusId ?> badge badge-light ml-1 "><?= $statusCount ?></span>
                    </label>
                    <?php endforeach;?>
                </div>

            </div>

        </div>


    </div>

    <div class="col-12">
        <table class="table  table-hover" id="reviews-list">
            <thead class="thead-dark">
                <tr>
                    <th scope="col">#</th>
                    <th scope="col" style="width:30px">ID</th>
                    <th scope="col">Review type</th>
                    <th scope="col">Review Item</th>
                    <th scope="col">Author</th>
                    <th scope="col">Reviewer</th>
                    <th scope="col">Approved By</th>
                    <th scope="col">Approved Date</th>
                    <th scope="col" style="width:96px">Update date</th>
                    <th scope="col" style="width:90px">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white " id="tbody"></tbody>
        </table>
    </div>

</div>

<script>
var userId, reviewStatus, table = null;

$(document).ready(function() {
    userId = <?= $userId ?>;
    reviewStatus = <?= json_encode($reviewStatus) ?>;

    table = initializeDataTable('reviews-list');

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
    const selectedProjectId = $("#projects").val();
    const selectedUsers = $("#selectedUser").val();
    const selectedReviewCategory = $("#category").val();
    var url = `/reviews/getReviews?view=${selectedView}&project_id=${selectedProjectId}&user_id=${selectedUsers}&category=${selectedReviewCategory}`;

    $("#addButton").attr("href", `/reviews/add?project_id=${selectedProjectId}`);

    $(".btn-group label").removeClass("btn-primary").addClass("btn-secondary");
    $(`.lbl_${selectedView.replace(/\s/g, '_')}`).removeClass("btn-secondary").addClass("btn-primary");

    makeRequest(url)
        .then((response) => {
            const reviewsList = response.reviews;
            populateTable(reviewsList);
        })
        .catch((err) => {
            console.log(err);
            showPopUp('Error', "An unexpected error occured on server.");
        })

    if (updateStats) {
        var url = `/reviews/getReviewStats?project_id=${selectedProjectId}&user_id=${selectedUsers}&category=${selectedReviewCategory}`;

        makeRequest(url)
            .then((response) => {
                const reviewStats = response.reviewStats;
                updateCount(reviewStats);
            })
            .catch((err) => {
                console.log(err);
                showPopUp('Error', "An unexpected error occured on server.");
            })
    }

}

function updateCount(updatedCount) {
    reviewStatus.forEach(status => {
        var count = 0;
        if (updatedCount != null) {
            if (updatedCount.hasOwnProperty(status)) {
                count = updatedCount[status];
            }
        }


        $(`.stats_${status.replace(/\s/g, '_')}`).text(count);
    })

}

function populateTable(reviewsList) {
    dataInfo = {
        "rowId": 'id',
        "requiredFields": ['reviewId', 'review-name', 'context', 'author', 'reviewer', 'approver','approved-at','updated-at'],
        "dateFields": ["updated-at"],
        "action": [{
                title: "Edit",
                buttonClass: "btn btn-warning",
                iconClass: "fa fa-edit",
                clickTrigger: "edit",
                clickParams: ['id']
            },
            {
                title: "Delete",
                buttonClass: "btn btn-danger",
                iconClass: "fa fa-trash",
                clickTrigger: "deleteReview",
                clickParams: ['id'],
                condition: {
                    on: 'assigned-to',
                    with: userId
                }
            }
        ]
    };

    if (reviewsList.length) {
        table.destroy();
    }

    $('#tbody').html("");
    var data = getHTMLtable(reviewsList, dataInfo);
    $('#tbody').append(data);

    if (reviewsList.length) {
        table = initializeDataTable('reviews-list');
    }

    var dt = $('#reviews-list').DataTable();

    var selectedStatus = $("input[name='view']:checked").val();

    //hide the review type column
    if($("#category").val() == "ALL")
        dt.column(2).visible(true);
    else
        dt.column(2).visible(false);

    //hide/unhide the approved-by and approved-at columns based on status
    if(selectedStatus != "Approved"){
        dt.column(6).visible(false);
        dt.column(7).visible(false);
        dt.column(8).visible(true);
    }else if(selectedStatus == "Approved"){
        dt.column(6).visible(true);
        dt.column(7).visible(true);
        dt.column(8).visible(false);
    }

}

function edit(id) {
    location.href = `/reviews/add/${id}`;
}

function deleteReview(id) {

    bootbox.confirm("Do you really want to delete the review?", function(result) {
        if (result) {
            $.ajax({
                url: '/reviews/delete/' + id,
                type: 'GET',
                success: function(response) {
                    response = JSON.parse(response);
                    if (response.success == "True") {
                        $("#" + id).fadeOut(800)
                    } else {
                        bootbox.alert('Review not deleted.');
                    }
                }
            });
        } else {
            console.log('Delete Cancelled');
        }

    });

}
</script>
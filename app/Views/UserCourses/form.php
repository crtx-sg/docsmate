<style>
.d2h-wrapper {
    overflow-y: scroll;
    max-height: 80vh;
}

.d2h-code-linenumber {
    position: relative;
}

.truncate {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.withReviewBox {
    max-height: 311px;
}

.withoutReviewBox {
    max-height: 540px;
}

.hide {
    display: none;
}

.reviewDiv {
    max-width: 520px;
}
</style>
<div class="p-2">
    <div class="row justify-content-center">
        <div class="col-12 col-md-7 mx-auto">
            <?php if (session()->get('success')): ?>
            <div class="alert alert-success" role="alert">
                <?= session()->get('success') ?>
            </div>
            <?php endif; ?>
        </div>
        <div class="col-12 col-lg-7 ml-3 pr-0 pl-0">
            <div class="form-color">
                <div class="card-header" style="border:0px !important;">
                    <div class="row pl-2 pr-2">
                            <h3 style="width: 600px;font-size:1.2rem;margin:0px;padding-top:8px;" class="truncate" data-toggle="popover" data-placement="top"
                                data-content="<?= $formTitle ?>"><?= $formTitle ?></h3>
                    </div>
                </div>
                <form class="p-3" action="/userCourses/<?= $action ?>" method="post">
                    <div class="row">
                        <?php if (isset($validation)): ?>
                        <div class="col-12">
                            <div class="alert alert-danger" role="alert">
                                <?= $validation->listErrors() ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        <input type="hidden" id="course_id" name="course_id" value="<?= $course_id ?>" />
                        
                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-muted" for="course_id">Course</label>
                                <select disabled class="form-control selectpicker"  id="course_id" name="course_id"
                        data-style="btn-secondary" data-live-search="true" data-size="8">
                        <option value="" disabled <?= isset($userCourses['course_id']) ? '' : 'selected' ?>>
                                        Select
                                    </option>
                        <?php foreach ($courses as $key => $value): ?>
                        <option <?= isset($userCourses['course_id']) ? (($userCourses['course_id'] == $key) ? 'selected': '') : '' ?>
                                        value="<?=  $key ?>"><?=  $value ?></option>
                        <?php endforeach;?>
                    </select>
                            </div>
                        </div>
                       
                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-muted" for="user_id">User</label>
                                <select disabled class="form-control  selectpicker" data-live-search="true" data-size="15"
                                    name="user_id" id="user_id">
                                    <option value="" disabled <?= isset($userCourses['user_id']) ? '' : 'selected' ?>>
                                        Select
                                    </option>
                                    <?php foreach ($teamMembers as $key=>$value): ?>
                                    <option
                                        <?= isset($userCourses['user_id']) ? (($userCourses['user_id'] == $key) ? 'selected': '') : '' ?>
                                        value="<?=  $key ?>"><?=  $value ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-muted" for="planned_date">Planned Date</label>
                                <input type="date" class="form-control" required name="planned_date"
                                    id="planned_date" value="<?= isset($userCourses['planned_date']) ? $userCourses['planned_date'] : '' ?>">
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-muted" for="completed_date">Completed Date</label>
                                <input type="date" class="form-control" name="completed_date"
                                    id="completed_date" value="<?= isset($userCourses['completed_date']) ? $userCourses['completed_date'] : '' ?>">
                            </div>
                        </div>

                   

                        <div class="col-12 col-sm-5 statusDiv">
                            <div class="form-group">
                                <label class="font-weight-bold text-muted" for="status">Status</label>
                                <?php if(isset($userCourses['status']) && ($userCourses['status'] == "Completed")){ ?>
                                    <input type="text" class="form-control"  name="status"
                                    id="status" value="<?= $userCourses['status']?>">
                                <?php }
                                else{
                                ?>
                                <select class="form-control  selectpicker" data-live-search="true" data-size="8"
                                    name="status" id="status">
                                    <option value="" disabled>
                                        Select
                                    </option>
                                    <?php foreach ($userCourseStatus as $cour): ?>
                                    <option
                                        <?= isset($userCourses['status']) ? (($userCourses['status'] == $cour["value"]) ? 'selected': '') : '' ?>
                                        value="<?=  $cour["value"] ?>"><?=  $cour["value"] ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?php } ?>
                            </div>
                        </div>
                    </div>

                        <div class="col-12 col-sm-2 text-center">
                            <button type="submit" style="margin-top: 32px;" class="btn btn-primary">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
       
<script>
var review, toggleReviewBox = true;

$(document).ready(function() {
    $('[data-toggle="popover"]').popover({
        trigger: "hover"
    });

    $(".sticky").parents().css("overflow", "visible")
    $("body").css("overflow-x", "hidden");
    //$('#completedDateId').css('display', 'none');
    // var isEditForm = "<?php //echo $isEditForm;?>";
    // if(isEditForm){
        //console.log("in if");
        //toggleStatusMappingData();
   // }else{
        //console.log("in else");
     // $('#completedDateId').css('display', 'none');
    //}

    
$(document).on({
    ajaxStart: function() {
        $("#loading-overlay").show();
    },
    ajaxStop: function() {
        $("#loading-overlay").hide();
    }
});


$(".alert-success").fadeTo(2000, 500).slideUp(500, function() {
    $(".alert-success").slideUp(500);
});

});

// function toggleStatusMappingData() {
//     var status = $("#status").val();
//     $('#completedDateId').css('display', 'none');
//     if(status  == 'Completed'){
//       $('#completedDateId').css('display', 'block');
//     }
// }

</script>
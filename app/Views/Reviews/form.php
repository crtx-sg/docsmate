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
                        <div class="">
                            <h3 style="width: 600px;font-size:1.2rem;margin:0px;padding-top:8px;" class="truncate" data-toggle="popover" data-placement="top"
                                data-content="<?= $formTitle ?>"><?= $formTitle ?></h3>
                        </div>
                        <div class="ml-auto">
                            <?php if (isset($nearByReviews)): ?>
                            <a data-toggle="popover" style="border: 1px solid;" data-placement="left"
                                title="<?= isset($nearByReviews['prevId']) ? 'R-'.$nearByReviews['prevId']: '' ?>"
                                data-content="Previous Review" href="/reviews/add/<?= $nearByReviews['prevId'] ?>"
                                class="btn btn-light text-primary ml-4 <?= isset($nearByReviews['prevId']) ? '': 'disabled' ?>">
                                <i class="fas fa-angle-left"></i>
                            </a>
                            <a data-toggle="popover" style="border: 1px solid;" data-placement="right"
                                title="<?= isset($nearByReviews['nextId']) ? 'R-'.$nearByReviews['nextId']: '' ?>"
                                data-content="Next Review" href="/reviews/add/<?= $nearByReviews['nextId'] ?>"
                                class="btn btn-light text-primary ml-2 <?= isset($nearByReviews['nextId']) ? '': 'disabled' ?>">
                                <i class="fas fa-angle-right"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <form class="p-3" action="/reviews/<?= $action ?>" method="post">
                    <div class="row">
                        <?php if (isset($validation)): ?>
                        <div class="col-12">
                            <div class="alert alert-danger" role="alert">
                                <?= $validation->listErrors() ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        <input type="hidden" id="project-id" name="project-id" value="<?= $project_id ?>" />
                        <input type="hidden" id="reviewId" name="reviewId"
                            value="<?= isset($review['id']) ? $review['id']: '' ?>" />
                        <div class="col-12 col-sm-3">
                            <div class="form-group">
                                <label class=" text-muted" for="project-name">Project</label>
                                <button style="width:175px;" type="button" id="project-name" data-toggle="popover"
                                    data-placement="top" data-content="<?= $project_name ?>"
                                    class="btn btn-info truncate"><?= $project_name ?></button>
                            </div>
                        </div>
                        <div class="col-12 col-sm-3">
                            <div class="form-group">
                                <label class=" text-muted" for="category">Category</label>
                                <select class="form-control  selectpicker" data-live-search="true" data-size="8"
                                    name="category" id="category">
                                    <option value="" disabled <?= isset($review['category']) ? '' : 'selected' ?>>
                                        Select
                                    </option>
                                    <?php foreach ($reviewCategory as $revCat): ?>
                                    <option
                                        <?= isset($review['category']) ? (($review['category'] == $revCat["value"]) ? 'selected': '') : '' ?>
                                        value="<?=  $revCat["value"] ?>"><?=  $revCat["value"] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-12  col-sm-3">
                            <div class="form-group">
                                <label class=" text-muted" for="review-name">Review type</label>
                                <input type="text" readOnly class="form-control" required name="review-name" id="review-name"
                                    value="<?= isset($review['review-name']) ? $review['review-name'] : '' ?>">
                            </div>
                        </div>
                        <div class="col-12 col-sm-3">
                            <div class="form-group">
                                <label class=" text-muted" for="assigned-to">Author</label>
                                <select class="form-control  selectpicker" data-live-search="true" data-size="8"
                                    name="assigned-to" id="assigned-to">
                                    <option value="" disabled <?= isset($review['assigned-to']) ? '' : 'selected' ?>>
                                        Select
                                    </option>
                                    <?php foreach ($teamMembers as $key=>$value): ?>
                                    <option
                                        <?= isset($review['assigned-to']) ? (($review['assigned-to'] == $key) ? 'selected': '') : '' ?>
                                        value="<?=  $key ?>"><?=  $value ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-sm-8">
                            <div class="form-group">
                                <label class=" text-muted" for="context">Review Item</label>
                                <input maxlength=60 type="text" class="form-control" required name="context"
                                    id="context" value="<?= isset($review['context']) ? $review['context'] : '' ?>">
                            </div>
                        </div>
                        <div class="col-12 col-sm-4">
                            <div class="form-group">
                                <label class=" text-muted" for="review-by">Reviewer</label>
                                <select class="form-control  selectpicker" data-live-search="true" data-size="8" multiple 
                                    name="review-by[]" id="review-by">
                                    <option value="" disabled>
                                        Select
                                    </option>
                                    <?php foreach ($teamMembers as $key=>$value): ?>
                                    <option
                                        <?= isset($review['review-by']) ? ((in_array($key, $review['review-by'])) ? 'selected': '') : '' ?>
                                        value="<?=  $key ?>"><?=  $value ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div
                            class="col-12 differential <?=isset($review['category']) ? (($review['category'] == "Code") ? '' : 'd-none' ) : 'd-none'?>">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-10"><label class=" text-muted" for="code-diff">Code
                                            Diff</label>
                                    </div>
                                    <div class="col-2">
                                        <button type="button" id="btn_diff_eval"
                                            class="btn btn-sm  btn-outline-dark float-right mt-1"
                                            onclick='evaluteDiff("code-diff", "show")'>
                                            Evaluate</button>
                                        <button type="button" id="btn_text_eval"
                                            class="btn btn-sm  btn-outline-dark float-right mt-1 d-none"
                                            onclick='evaluteDiff("code-diff", "hide")'>
                                            Edit</button>
                                    </div>
                                </div>
                                <textarea class="form-control" name="code-diff" id="code-diff"></textarea>
                                <div id="diffDiv"></div>
                            </div>
                        </div>
                        <div class="col-12 ">
                            <div class="form-group">
                                <label class=" text-muted" for="review-ref">Author/Reviewer Comments</label>
                                <textarea class="form-control" name="review-ref" id="review-ref"><?=
                           isset($review['review-ref']) ? trim($review['review-ref']) : ''
                           ?></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row justify-content-md-center">
                        <?php
                     $showSubmit = true;
                     if(isset($review['id'])){
                         if(session()->get('id') != $review['assigned-to']){
                             $showSubmit = false;
                         }
                     }
                     ?>
                        <?php if($showSubmit): ?>
                        <div class="col-12 col-sm-4 statusDiv">
                            <div class="form-group">
                                <label class=" text-muted" for="status">Status</label>
                                <select class="form-control  selectpicker" data-live-search="true" data-size="8"
                                    name="status" id="status">
                                    <option value="" disabled>
                                        Select
                                    </option>
                                    <?php foreach ($reviewStatus as $rev): ?>
                                    <option
                                        <?= isset($review['status']) ? (($review['status'] == $rev["value"]) ? 'selected': '') : '' ?>
                                        value="<?=  $rev["value"] ?>"><?=  $rev["value"] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-sm-2 ">
                            <button type="submit" style="margin-top: 32px;" class="btn btn-primary">Submit</button>
                        </div>
                        <?php endif ?>
                    </div>
                </form>
            </div>
        </div>
        <?php if(isset($review['id'])): ?>
        <div class="col reviewDiv pr-0 pl-0">
            <div class="col sticky">
                <div class="form-color">
                    <div class="card-header" style="border:0px !important;">
                        <div class="row">
                            <div class="col-10">
                                <h5 class="text-primary mt-2">Review Comments</h5>
                            </div>
                            <div class="col-2">
                                <button onclick="showReview()" class="btn btn-outline-primary float-right">
                                    <i class="fas fa-plus "></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div style="word-break: break-word;">
                        <ul class="commentsList list-group scroll scroll-primary withoutReviewBox"></ul>
                    </div>
                    <div>
                        <div class="form-group hide reviewbox p-2">
                            <textarea class="form-control" name="description" id="description"></textarea>
                            <div class="d-flex w-100 justify-content-end mt-2">
                                <?php
                                $showStatus = false;
                                $reviewId = "";
                                if(isset($review['id'])){
                                    $reviewId = $review['id'];
                                    //if(session()->get('id') == $review['review-by']){
                                        if(in_array(session()->get('id'), $review['review-by'])){
                                        $showStatus = true;
                                    }
                                }
                                ?>
                                <div style="width:175px">
                                    <?php if($showStatus): ?>
                                    <select class="form-control selectpicker" data-live-search="true" data-size="8"
                                        name="reviewStatus" id="reviewStatus">
                                        <option value="" disabled>
                                            Select
                                        </option>
                                        <?php foreach ($reviewStatus as $rev): ?>
                                        <option
                                            <?= isset($review['status']) ? (($review['status'] == $rev["value"]) ? 'selected': '') : '' ?>
                                            value="<?=  $rev["value"] ?>"><?=  $rev["value"] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php endif ?>
                                </div>
                                <div>
                                    <button title="Save" class="btn btn-success ml-4" onclick="saveComment('<?= $reviewId ?>')">
                                        <i class="fas fa-save"></i>
                                    </button>
                                    <button title="Cancel" class="btn btn-dark ml-1" onclick="showReview()">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif ?>
    </div>
</div>
<a id="back-to-top" href="#" class="btn btn-light btn-lg back-to-top text-primary" role="button"><i
        class="fas fa-chevron-up"></i></a>
<script>
var review, initialCategory, initialName, toggleReviewBox = true,
    reviewComments = [],
    commentEditId = "",
    userName = "";

$(document).ready(function() {
    $('[data-toggle="popover"]').popover({
        trigger: "hover"
    });

    $(".sticky").parents().css("overflow", "visible")
    $("body").css("overflow-x", "hidden");

    userName = "<?= session()->get('name') ?>";

    <?php if (isset($review)): ?>
    review = <?= json_encode($review) ?>;
    if (review["code-diff"] != null && review["code-diff"] != "") {
        var differential = review["code-diff"];
        $('#code-diff').val(differential)
        setTimeout(function() {

            evaluteDiff("code-diff", "show");
        }, 500);

    }
    if (review.description != null && review.description != "") {
        const temp = JSON.parse(review.description);
        const comments = Object.values(temp);
        reviewComments = comments;

        comments.forEach((comment) => {
            addReviewCommentToUI(review.id, comment);
        })
    }
    <?php endif; ?>

    initialCategory = $('#category').val();
    initialName = $('#review-name').val();
});

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


function addLineToComment() {
    const element = $(this);
    const filePath = element.parentsUntil('div.d2h-wrapper').find("span.d2h-file-name").text();
    const parentElement = element.parent().siblings("td");
    const diff = parentElement.find(".d2h-code-line-ctn");
    const codeLine = "`" + diff.text().trim() + "`";
    const message = `**Line ${element.text().trim()}** ${filePath}\n\n ${codeLine}`;

    const $codemirror = $('textarea[name="description"]').nextAll('.CodeMirror')[0].CodeMirror;
    const existingVal = $codemirror.getDoc().getValue();
    $codemirror.getDoc().setValue(existingVal + "\n" + message + "\n");
    //    var toElement = $("label:contains('Comment')")[0];
    //    $('html, body').animate({
    //        scrollTop: $(toElement).offset().top
    //    }, 1000);
    if ($(".reviewDiv").length) {
        if (!$(".reviewbox").is(":visible")) {
            showReview();
            textareaFocus($codemirror);
        }
    }

}

function saveComment(reviewId) {
    const $codemirror = $('textarea[name="description"]').nextAll('.CodeMirror')[0].CodeMirror;
    const message = $codemirror.getDoc().getValue();

    if (message != "") {
        $codemirror.getDoc().setValue("");

        const updateStatus = $("#reviewStatus").length;
        let reviewStatus = "";
        if (updateStatus) {
            reviewStatus = $("#reviewStatus").val();
        }

        let data = {
            "commentId": commentEditId,
            reviewId,
            message,
            reviewStatus
        };

        makePOSTRequest('/reviews/saveComment', data)
            .then((response) => {
                if (response.success == "True") {
                    const comment = response.comment;
                    if (commentEditId != "") {
                        let previousComment = getObjectFromArray(commentEditId, reviewComments);
                        reviewComments.splice(previousComment[0], 1);
                        $("#" + commentEditId).remove();
                    }
                    if (response.updateStatus == "True") {
                        if ($("#status").length) {
                            $("#status").val(reviewStatus);
                            $('.selectpicker').selectpicker('refresh');
                        }

                    }

                    reviewComments.push(comment);
                    addReviewCommentToUI(review.id, comment);
                    showReview();
                    showFloatingAlert(response.message);
                } else {
                    showPopUp('Error', response.errorMsg);
                }
            })
            .catch((err) => {
                console.log(err);
                showPopUp('Error', "An unexpected error occured on server.");
            })

    } else {
        showReview();
    }


}

function evaluteDiff(sectionId, visibility) {

    if (visibility == "show") {

        const $codemirror = $('textarea[name="' + sectionId + '"]').nextAll('.CodeMirror')[0].CodeMirror;
        var sectionValue = $codemirror.getValue();
        const targetElement = document.getElementById('diffDiv');
        const configuration = {
            drawFileList: true,
            matching: 'none',
        };

        const diff2htmlUi = new Diff2HtmlUI(targetElement, sectionValue, configuration);
        diff2htmlUi.draw();

        $("#diffDiv").removeClass('d-none');

        $("#btn_text_eval").removeClass('d-none');
        $("#btn_diff_eval").addClass('d-none');

        var toolbar = $("#" + sectionId).closest('div').find('.editor-toolbar');
        var codeMirrorDiv = $("#" + sectionId).closest('div').find('.CodeMirror');
        $(toolbar).addClass('d-none');
        $(codeMirrorDiv).addClass('d-none');

        $(".line-num1").click(addLineToComment);
        $(".line-num2").click(addLineToComment);

    } else {

        $("#diffDiv").addClass('d-none');
        $("#btn_text_eval").addClass('d-none');
        $("#btn_diff_eval").removeClass('d-none');
        var toolbar = $("#" + sectionId).closest('div').find('.editor-toolbar');
        var codeMirrorDiv = $("#" + sectionId).closest('div').find('.CodeMirror');
        $(toolbar).removeClass('d-none');
        $(codeMirrorDiv).removeClass('d-none');
        const $codemirror = $('textarea[name="' + sectionId + '"]').nextAll('.CodeMirror')[0].CodeMirror;
        $codemirror.refresh();

    }

}

$("#category").change(function() {
    var selectedText = $(this).find("option:selected").text();
    var selectedValue = $(this).val();
    if (initialCategory == selectedValue) {
        $('#review-name').val(initialName);
    } else {
        $('#review-name').val(selectedValue + " Review");
    }
    if (selectedValue == "Code") {
        $(".differential").removeClass('d-none');
    } else {
        $(".differential").addClass('d-none');
    }

});
</script>
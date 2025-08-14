<style>
.nav-link.active {
    color: #f8f9fa !important;
    background-color: lightslategray !important;
}

.nav-item {
    background: #e9ecef;
}

.reviewComments textarea {
    height: 300px;
}

.scroll-primary {
    max-height: 625px;
    overflow: scroll;
    overflow-x: hidden;
}

.scroll-primary::-webkit-scrollbar {
    width: 0.25rem;
}

.scroll-primary::-webkit-scrollbar-track {
    width: #007bff;
}

.scroll-primary::-webkit-scrollbar-thumb {
    background: #007bff;
}


.truncate {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.withReviewBox {
    max-height: 211px;
}

.withoutReviewBox {
    max-height: 540px;
}

.hide {
    display: none;
}

.reviewDiv {
    max-width: 500px;
}

.sectionLinks{
    font-size:1rem;
    cursor:pointer;
    box-shadow: inset 0 0 0 0.01px #fff;
	-webkit-transition: all ease 0.8s;
	-moz-transition: all ease 0.8s;
	transition: all ease 0.8s;
}

.sectionLinks:hover{
    box-shadow: inset 400px 0 0 0 #007bff;
    color: #fff !important;
}

.sectionLinksNav > .nav-link.active{
    background-color: #007bff !important;
}

/* @media (max-width: 1480px){
    .reviewDiv{
        max-width:412px;
    }
} */

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

            <!-- Form starts here -->
            <form id="documentForm" action="/documents/save" method="post">
                <?php
                if(isset($projectDocument)){
                    $docId = $projectDocument["id"];
                    $heading = 'D-'. $docId ." ".$projectDocument['file-name'];
                    $title = $jsonObject["cp-line3"];
                    $docType = $projectDocument["type"];
                    
                }else{
                    $heading = $formTitle;
                    $title = $docTitle;
                    $docId = "";
                    $docType = $type;                    
                }
                     
            ?>


                <!-- Hidden Fields for form -->
                <input type="hidden" id="project-id" name="project-id" value="<?= $project_id ?>">
                <input type="hidden" id="id" name="id" value="<?= $docId ?>">
                <input type="hidden" id="type" name="type" value="<?= $docType ?>">
                <?php if($docId == ""){ ?>
                <input type="hidden" id="author-id" name="author-id" value="<?= session()->get('id') ?>">
                <?php } ?>

                <div class="card  mt-2 form-color" style="border:0px !important;">
                    <!-- Document Title -->
                    <div class="card-header" style="border:0px !important;">
                        <div class="row pl-2 pr-2">
                            <div class="">
                                <h3 style="width: 650px;font-size:1.2rem;margin:0px;padding-top:8px;" class="truncate" data-toggle="popover" data-placement="top"
                                    data-content="<?= $heading ?>"><?= $heading ?></h3>
                            </div>
                            <div class="ml-auto">
                                <?php if (isset($projectDocument)): ?>

                                <a title="Preview" onclick="generatePreview(this, <?php echo $docId;?>)"
                                    class="ml-2 btn bg-primary text-light">
                                    <i class="fa fa-eye" aria-hidden="true"></i>
                                </a>

                                <?php endif; ?>
                                <?php if (isset($nearByDocuments)): ?>
                                <a data-toggle="popover" style="border: 1px solid;" data-placement="left"
                                    title="<?= isset($nearByDocuments['prevId']) ? 'D-'.$nearByDocuments['prevId']: '' ?>"
                                    data-content="Previous Document"
                                    href="/documents/add/?id=<?= $nearByDocuments['prevId'] ?>"
                                    class="btn btn-light text-primary ml-4 <?= isset($nearByDocuments['prevId']) ? '': 'disabled' ?>">
                                    <i class="fas fa-angle-left"></i>
                                </a>
                                <a data-toggle="popover" style="border: 1px solid;" data-placement="right"
                                    title="<?= isset($nearByDocuments['nextId']) ? 'D-'.$nearByDocuments['nextId']: '' ?>"
                                    data-content="Next Document"
                                    href="/documents/add/?id=<?= $nearByDocuments['nextId'] ?>"
                                    class="btn btn-light text-primary ml-2 <?= isset($nearByDocuments['nextId']) ? '': 'disabled' ?>">
                                    <i class="fas fa-angle-right"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <ul class="nav nav-tabs nav-justified sticky" id="myTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active lead" id="header-tab" data-toggle="tab" href="#header" role="tab"
                                aria-controls="header" aria-selected="true">Header</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link lead" id="section-tab" data-toggle="tab" href="#section" role="tab"
                                aria-controls="section" aria-selected="false">Sections</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link lead" id="revision-tab" data-toggle="tab" href="#revision" role="tab"
                                aria-controls="revision" aria-selected="false">Revision History</a>
                        </li>
                    </ul>

                    <div class="tab-content pt-1 pl-3 pr-3" id="myTabContent">

                        <div class="tab-pane fade show active" id="header" role="tabpanel" aria-labelledby="header-tab">
                            <?php if (isset($existingDocs)): ?>
                            <div class="row">
                                                             
                                <div class="col-12 col-sm-4">
                                    <div class="form-group">
                                        <label class=" text-muted" for="project-name">Project</label>
                                        <br />
                                        <button type="button" id="project-name"
                                            class="btn btn-info"><?= $project_name ?>
                                        </button>

                                    </div>
                                </div>


                                <!-- <div class="col-12 col-sm-2"></div> -->

                                <div class="col-12 col-sm-6 <?= isset($projectDocument["id"]) ? 'd-none' : '' ?>">
                                    <div class="form-group">
                                        <label class="text-muted" for="existingDocs">Copy from</label>
                                        <!-- <div class="input-group"> -->
                                            <?php 
                                                if($allExistingDocs != "TRUE" ){
                                                    $docsDrop = $existingDocs["my"];
                                                    $checked = "checked";
                                                }else{
                                                    $docsDrop = $existingDocs["all"];
                                                    $checked = "";
                                                }

                                                if(isset($selectedExistingDocId)){
                                                    $selectedDocId = $selectedExistingDocId;
                                                }else{
                                                    $selectedDocId = "";
                                                }
                                            ?>

                                            <select class="form-control  selectpicker" data-live-search="true"
                                                data-size="8" id="existingDocs">
                                                <option value="" selected>
                                                    Select
                                                </option>
                                                <?php foreach ($docsDrop as $key=>$value): ?>
                                                <option <?=  ($value["id"] == $selectedDocId)? 'selected' : '' ?>
                                                    value='<?=  $value["id"] ?>'><?=  $value['title'] ?></option>
                                                <?php endforeach; ?>

                                            </select>
                                            </div>
                                        </div>
                                            <div class="col-12 col-sm-2">
                                            <div class="form-group">
                                            <!-- <div class="input-group"> -->
                                            <input type="checkbox" id="existingDocType" data-on="MY"
                                                data-onstyle="success" data-offstyle="info" data-off="ALL"
                                                <?= $checked ?> data-toggle="toggle" style="margin-left:180px; !important">
                                            </div>
                                   
                                            <!-- </div> -->
                                        </div>


                            </div>
                            <?php endif; ?>
                            <div class="row">
                            <?php if(isset($projectDocument["id"])){ ?>
                                <div class="col-12 col-sm-4">
                                    <div class="form-group">
                                        <label class=" text-muted" for="project-name">Project</label>
                                        <br />
                                        <button type="button" id="project-name"
                                            class="btn btn-info"><?= $project_name ?>
                                        </button>

                                    </div>
                                </div>
                                <?php } ?>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class=" text-muted" for="cp-line3">Title</label>
                                        <input required type="text" class="form-control" name="cp-line3" id="cp-line3"
                                            value="<?= $title  ?>" maxlength="64">
                                    </div>
                                </div>

                                <div class="col-4">
                                    <div class="form-group">
                                        <label class=" text-muted" for="reviewer-id">Reviewer</label>
                                        <select class="form-control selectpicker" data-live-search="true" data-size="8"
                                            id="reviewer-id" multiple name="reviewer-id[]" requried>
                                            <option disabled  value> -- select a reviewer -- </option>
                                            <?php foreach ($teams as $key=>$value): ?>
                                            <option
                                                <?= isset($projectDocument['reviewer-id']) ? ((in_array($key, $projectDocument['reviewer-id'])) ? 'selected': '') : '' ?>
                                                value='<?=  $key ?>'> 
                                                <?=  $value ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-12 col-sm-2">
                                    <div class="form-group">
                                        <label class=" text-muted" for="cp-line5">Revision</label>
                                        <input type="text" class="form-control" name="cp-line5" id="cp-line5"
                                            value="<?= $jsonObject["cp-line5"] ?>">
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6">
                                    <div class="form-group">
                                        <label class=" text-muted" for="cp-approval-matrix">Note on First Page</label>
                                        <input type="text" class="form-control" name="cp-approval-matrix"
                                            id="cp-approval-matrix" value="<?= $jsonObject["cp-approval-matrix"] ?>">
                                    </div>
                                </div>

                                <?php if(isset($docId) && $docId != ""){ ?>
                                <div class="col-12 col-sm-3">
                                    <div class="form-group">
                                        <label class=" text-muted" for="author-id">Author</label>
                                        <select class="form-control selectpicker" data-live-search="true" data-size="8"
                                            id="author-id" name="author-id" requried>
                                            <!-- <option disabled selected value> -- select an author -- </option> -->
                                            <?php foreach ($teams as $key=>$value): ?>
                                            <option
                                                <?= isset($projectDocument['author-id']) ? (($projectDocument['author-id'] == $key) ? 'selected': '') : '' ?>
                                                value='<?=  $key ?>'>
                                                <?=  $value ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <?php } ?>

                                <div class="col-12 col-sm-3">
                                    <div class="form-group">
                                        <label class=" text-muted" for="cp-line4">Document ID</label>
                                        <input type="text" class="form-control" name="cp-line4" id="cp-line4"
                                            value="<?= $jsonObject["cp-line4"] ?>">
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group">
                                        <label class=" text-muted" for="cp-change-history">Change
                                            History</label>
                                        <textarea class="form-control" name="cp-change-history"
                                            id="cp-change-history"><?= $jsonObject["cp-change-history"] ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit button shouldb be visible if -->
                            <!-- it is new or owned by author -->
                            <?php 

                            $showSubmit = True;
                            if(isset($projectDocument["id"])){
                                if(session()->get('id') == $projectDocument['author-id'] ){
                                    $showSubmit = True;
                                }else{
                                    $showSubmit = False;
                                }
                            }

                        ?>

                            <?php if($showSubmit): ?>

                            <div class="row">

                                <div class="col-12 col-sm-3"></div>

                                <div class="col-12 col-sm-4">

                                    <div class="form-group">
                                        <select class="form-control" name="status" id="status">
                                            <option value="" disabled>
                                                Select Status
                                            </option>
                                            <?php foreach ($documentStatus as $doc): ?>
                                            <option
                                                <?= isset($projectDocument["status"]) ? (($projectDocument["status"] == $doc["value"]) ? 'selected': '') : '' ?>
                                                value="<?=  $doc["value"] ?>"><?=  $doc["value"] ?></option>
                                            <?php endforeach; ?>

                                        </select>
                                    </div>
                                </div>

                                <div class="col-12 col-sm-4 ">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </div>


                            </div>

                            <?php endif; ?>


                        </div>



                        <div class="tab-pane fade" id="section" role="tabpanel" aria-labelledby="section-tab">

                            <div class="row" >
                                <div class="col-3 p-0">
                                
                                    <div class="bg-white rounded sticky" style="top:50px">

                                        <div class="nav nav-pills flex-column sectionLinksNav text-center">
                                            <?php foreach ($jsonObject['sections'] as $index=>$section): ?>
                                            <a class="sectionLinks nav-link <?=($index == 0) ? 'active': '' ?> text-wrap" style="width: 11.5rem;" 
                                                onclick="goToDiv(this,'#div_<?=  $section['id'] ?>')" ><?=  $section["title"] ?></a>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Creating Sections -->
                                <div class="col-9 p-0">
                                    <?php foreach ($jsonObject['sections'] as $section): ?>

                                    <div class="col-12 mb-3 pl-1 pr-1 template__section" id="div_<?=  $section["id"] ?>">
                                        <!-- Section Title -->
                                        <div class="card-header text-white"  style="border:0px !important;background: lightslategray;">
                                            <div class="row">
                                                <!-- If a section has a dropdown than take half the width otherwise take full width -->
                                                <div class="col-7">
                                                    
                                                            <div class="input-group">
                                                                <!-- Show Review button only if it matches with assigned reviewer id -->
                                                                <?php if (isset($projectDocument['id'])): ?>
                                                                <div class="input-group-prepend">
                                                                    <button type="button"
                                                                            class="btn btn-sm btn-warning"
                                                                            onclick="addLineToComment('<?=$section['title']?>')"
                                                                            title="Add review comment">
                                                                            <i class="fas fa-list "></i>
                                                                    </button>
                                                                </div>
                                                               
                                                                <?php endif; ?>
                                                                <input style="background: wheat;color: black;" type="text" class="form-control" readonly value="<?=  $section["title"] ?>" aria-label="Input group example" aria-describedby="btnGroupAddon">

                                                            </div>
                                                        
                                                </div>

                                                <?php if (isset($section["type"])): ?>
                                                <?php if ($section["type"] == "database"): ?>
                                                <div class="col-5">
                                                    <div class="input-group">
                                                        <select class="form-control selectpicker" data-actions-box="true"
                                                            data-live-search="true" data-size="8"
                                                            id="select_<?=  $section["id"] ?>" multiple>
                                                            <?php foreach ($lookUpTables[$section["tableName"]] as $key=>$value): ?>
                                                                
                                                            <option value='<?=  $value['id'] ?>'>
                                                                <?=  $value[$section["headerColumns"] ] ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                        <div class="input-group-append">
                                                            <button type="button"
                                                            title = "Add to <?=  $section["title"] ?>"
                                                            class="btn btn-sm btn-orange "
                                                            onclick='insertTable("<?=  $section["id"] ?>","<?=$section["tableName"] ?>", "<?=  $section["contentColumns"] ?>" )'>
                                                            <i class="fa fa-plus" aria-hidden="true"></i></button>
                                                        </div>
                                                        
                                                    </div>
                                                </div>
                                               
                                                <?php endif; ?>

                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <!-- Section Body -->

                                        <div class="card-body p-0" >
                                            <textarea class="form-control sections" name="<?=  $section["id"] ?>"
                                                id="<?=  $section["id"] ?>"><?=  $section["content"] ?></textarea>
                                        </div>

                                    </div>

                                    <?php endforeach; ?>
                                </div>
                            </div>

                        </div>

                        <div class="tab-pane fade" id="revision" tole="tabpanel" aria-labelledby="revision-tab">
                            <div class="alert alert-warning revisionAlert" role="alert">
                                No revision history available.
                            </div>
                            <table class="table table-hover d-none revisionTable">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Revision log</th>
                                        <th scope="col">User</th>
                                        <th scope="col" style="width:125px">Timestamp</th>
                                    </tr>
                                </thead>
                                <tbody id="revisionBody">

                                </tbody>
                            </table>
                        </div>

                    </div>


                </div>


            </form>


        </div>

        <!-- Review Div -->
        <?php if (isset($projectDocument['project-id'])): ?>
        <div class="col reviewDiv pr-0 pl-0 mt-2">
            <div class="col sticky">
                <div class="form-color">
                    <div class="card-header" style="border:0px !important;">
                        <div class="row">
                            <div class="col-10">
                                <h5 class="text-primary mt-2 reviewHeading">
                                    <?= isset($documentReview['id']) ? '<a target="_blank" href="/reviews/add/'.$documentReview['id'].'">'.'R-'.$documentReview['id'].'</a>': ' Review' ?> Comments
                                </h5>
                            </div>
                            <div class="col-2">
                                <button onclick="showReview()" class="btn btn-outline-primary float-right">
                                    <i class="fas fa-plus "></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="">
                        <ul class="commentsList list-group scroll scroll-primary withoutReviewBox" style="margin-right:-4px"></ul>
                    </div>
                    <div>
                        <div class="form-group hide reviewbox p-2">
                            <textarea class="form-control" name="description" id="description"></textarea>
                            <div class="d-flex w-100 justify-content-center mt-2">
                                <?php
                                $showStatus = false;
                                $showCategory = true;
                                $reviewId = "";
                                $selectedStatus = $projectDocument['status'];

                                //if(session()->get('id') == $projectDocument['reviewer-id']){
                                if(in_array(session()->get('id'), $projectDocument['reviewer-id'])){
                                    $showStatus = true;
                                }

                                if(isset($documentReview['id'])){
                                    $showCategory = false;
                                    $reviewId = $documentReview['id'];
                                }
                                ?>

                                <?php if($showCategory): ?>
                                <div style="width:165px">
                                    <div class="form-group">
                                        <label class=" text-muted" for="reviewCategory">Category</label>
                                        <select class="form-control selectpicker" data-live-search="true" data-size="8"
                                            name="reviewCategory" id="reviewCategory">
                                            <option value="" disabled>
                                                Select
                                            </option>
                                            <?php foreach ($reviewCategory as $key=>$value): ?>
                                            <option
                                                <?= isset($documentReview['category']) ? (($documentReview['category'] == $value) ? 'selected': '') : '' ?>
                                                value="<?=  $value ?>"><?=  $value ?></option>
                                            <?php endforeach; ?>
                                        </select>

                                    </div>

                                </div>
                                <?php endif ?>

                                <?php if($showStatus): ?>
                                <div style="width:165px;margin-left:10px">
                                    <div class="form-group">
                                        <label class=" text-muted" for="reviewStatus">Status</label>
                                        <select class="form-control selectpicker" data-live-search="true" data-size="8"
                                            name="reviewStatus" id="reviewStatus">
                                            <option value="" disabled>
                                                Select
                                            </option>
                                            <?php foreach ($documentStatus as $doc): ?>
                                            <option <?= ($selectedStatus == $doc["value"]) ? 'selected': ''?>
                                                value="<?=  $doc["value"] ?>"><?=  $doc["value"] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <?php endif ?>
                                <div class="row" style="margin-top:31px;">
                                    <button title="Save" style="height:40px" class="btn btn-success ml-4"
                                        onclick="saveReview('<?= $reviewId ?>')"><i class="fas fa-save"></i></button>
                                    <button title="Cancel" style="height:40px" class="btn btn-dark ml-1" onclick="showReview()"><i
                                            class="fas fa-times"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<a id="back-to-top" href="#" class="btn btn-light btn-lg back-to-top text-primary" role="button"><i
        class="fas fa-chevron-up"></i></a>
<script>
var userName;
var existingDocList = [];
var revisionHistory = null;
var lookUpTables;

if($("#existingDocs").val() != ""){
    $('#status').val("Draft").attr("selected", "selected");
}


//For Review
class Review {
    constructor() {
        this.id = '';
        this.docId = '';
        this.projectId = '';
        this.reviewName = '';
        this.category = '';
        this.context = '';
        this.description = '';
        this.reviewBy = '';
        this.assignedTo = '';
        this.reviewRef = '';
        this.status = '';
    }
}
var documentReview = new Review();
var commentEditId = "",
    reviewedSection = [],
    toggleReviewBox = true,
    reviewComments = [];

$(document).on({
    ajaxStart: function() {
        $("#loading-overlay").show();
    },
    ajaxStop: function() {
        $("#loading-overlay").hide();
    }
});

$(document).ready(function() {
    $('[data-toggle="popover"]').popover({
        trigger: "hover"
    });


    userName = "<?= session()->get('name') ?>";

    <?php if (isset($lookUpTables)): ?>
    lookUpTables = <?= json_encode($lookUpTables,   JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>;
    <?php endif; ?>

    <?php if (isset($existingDocs)): ?>
    existingDocList = <?= json_encode($existingDocs,  JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>;
    <?php endif; ?>


    <?php if(isset($projectDocument["id"])): ?>
    <?php if($projectDocument["revision-history"] != null): ?>
    revisionHistory = <?= json_encode($projectDocument["revision-history"]) ?>;
    showRevisionHistory(revisionHistory);
    <?php endif; ?>
    <?php endif; ?>

    <?php if (isset($documentReview)): ?>
    $(".reviewDiv").removeClass('d-none');
    var savedReview = <?= json_encode($documentReview,  JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>;
    documentReview.id = savedReview["id"];
    documentReview.docId = "<?= $projectDocument['id'] ?>";
    documentReview.projectId = savedReview["project-id"];
    documentReview.reviewName = savedReview["review-name"];
    documentReview.category = savedReview["category"];
    documentReview.context = savedReview["context"];
    documentReview.description = savedReview["description"];
    documentReview.reviewBy = savedReview["review-by"];
    documentReview.assignedTo = savedReview["assigned-to"];
    documentReview.reviewRef = savedReview["review-ref"];
    documentReview.status = savedReview["status"];

    if (documentReview.description != null && documentReview.description != "") {
        const temp = JSON.parse(documentReview.description);
        const comments = Object.values(temp);
        reviewComments = comments;

        comments.forEach((comment) => {
            addReviewCommentToUI(documentReview.id, comment);
        })
    }
    <?php else: ?>
    <?php if(isset($projectDocument["id"])): ?>
    documentReview.docId = "<?= $projectDocument['id'] ?>";
    documentReview.assignedTo = "<?= $projectDocument['author-id'] ?>";
    documentReview.projectId = $("#project-id").val();
    documentReview.context = "<?= $projectDocument['file-name'] ?>";
    documentReview.status = "<?= $projectDocument['status'] ?>";
    <?php endif; ?>
    <?php endif; ?>

    $(".sticky").parents().css("overflow", "visible");
    $("body").css("overflow-x", "hidden");

});

document.addEventListener('DOMContentLoaded', function(){ 
  const sections = document.querySelectorAll(".template__section");
  const sections_links = document.querySelectorAll(".sectionLinks");
  
  // functions to add and remove the active class from links as appropriate
  const makeActive = (link) => sections_links[link].classList.add("active");
  const removeActive = (link) => sections_links[link].classList.remove("active");
  const removeAllActive = () => [...Array(sections.length).keys()].forEach((link) => removeActive(link));
  
  const sectionMargin = 0;
  
  let currentActive = 0;

  // listen for scroll events
  window.addEventListener("scroll", () => {
    
    const current = sections.length - [...sections].reverse().findIndex((section) => window.scrollY >= section.offsetTop - sectionMargin ) - 1
  
    if (current !== currentActive) {
      removeAllActive();
      currentActive = current;
      makeActive(current);
    }
  });
}, false);

function goToDiv(e, target){
    
    var targetElement = e;

    $('html, body').animate({
        scrollTop: $(target).offset().top-50
    }, 1000);

} 

function insertTable(sectionId, tableName, columnValues) {
    var selectedIds = $("#select_" + sectionId).val();
    var table = lookUpTables[tableName];
   
    var content = "";

    if(tableName == "unitTest"){
        content = toMarkdown(columnValues, selectedIds, table);
    } else{
        var indexes = columnValues.split(',');
        var dataFormat = "table";
    
        if (tableName == "traceabilityMatrix" || tableName == "riskAssessment") {
            dataFormat = "list";
        }

        if (dataFormat == "table") {
            // columnValues = columnValues.toUpperCase();
            columnValues = columnValues.split(',');
            columnValues.forEach((column, i) => {
                columnValues[i] = capitalize(column);
            });
            columnValues = columnValues.join('|');
            var thead = "| " + columnValues + " |\n";

            indexes.forEach((index, i) => {
                thead += "|-------";

                if (i == (indexes.length - 1)) {
                    thead += "|\r\n";
                }

            });

            content = thead;

            selectedIds.forEach((id) => {
                var record = table.find(x => x.id === id);

                indexes.forEach((index, j) => {
                    if (j == 0) {
                        content += "| ";
                    }

                    var value = record[index];
                    if(value != null)
                        content += value.replace(/(\r\n|\n|\r)/gm, "") + " |";

                    if (j == (indexes.length - 1)) {
                        content += "\n";
                    }
                });

            });

        } else {
            selectedIds.forEach((id) => {
                var record = table.find(x => x.id === id);
                indexes.forEach((index, i) => {
                    var value = record[index];
                    if(index == 'risk'){
                        value= value.replace(/V- |S- |AT- |OI- |SI- /g,'');
                    }
                    var docTitle = "<?= $heading ?>";
                    
                    if((docTitle.indexOf("DesignInput") > -1) && (index == "CVSS_3_1_base_risk_assessment")){
                        // Don't show base score severity for Minutes,DesignInput Document
                    }else{
                        content += "|" + ((capitalize(index) == 'Hazard Analysis') ? "Hazard-Analysis & Mitigation" : capitalize(index)) + "|" + ((value != null) ? value.replace(/(\r\n|\n|\r)/gm, "") : "") +
                        "|\r\n";
                    }
                    
                    if (i == 0) {
                        content += "|---------|---------|\r\n";
                    }
                    if (i == (indexes.length - 1)) {
                        content += "\n";
                    }
                    
                });

            });


        }
    }

    const $codemirror = $('textarea[name="' + sectionId + '"]').nextAll('.CodeMirror')[0].CodeMirror;
    const existingVal = $codemirror.getDoc().getValue();
    $codemirror.getDoc().setValue(existingVal + "\n" + content);

}

// Revision History Feature
function showRevisionHistory(revisionHistory) {
    $(".revisionTable").removeClass('d-none');
    $(".revisionAlert").addClass('d-none');

    revisionHistory = JSON.parse(revisionHistory);
    revisionHistory = revisionHistory['revision-history'].reverse();
    // console.log(revisionHistory);
    var totalCount = revisionHistory.length;
    var rowClass = "";
    var iconClass = "";
    $('#revisionBody').html("");
    revisionHistory.forEach((revision) => {
        if (revision["type"] == "Created") {
            rowClass = "primary";
            iconClass = "fa fa-plus-circle";
        } else if (revision["type"] == "Edited") {
            rowClass = "success";
            iconClass = "fa fa-pencil";
        } else {
            rowClass = "warning";
            iconClass = "fas fa-list";
        }
        var td = `<td> 
                        <a title="${revision["type"]}" class="text-light btn btn-${rowClass}">
                            <i class="${iconClass}"></i>
                        <span class="ml-2  badge  badge-light">${totalCount}</span>
                        </a>
                    </td>`;
        td += `<td>${revision["log"]}</td>`;
        td += `<td>${revision["who"]}</td>`;
        td += `<td>${formatDate(revision["dateTime"])}</td>`;
        $('#revisionBody').append(`<tr class="table-${rowClass}">` + td + '</tr>');
        totalCount--;
    });
}


// For Reloading Sections in section tab
$("#section-tab").click(function() {
    var sectionLinks = $('.sectionLinks');
    sectionLinks.removeClass('active');
    $(sectionLinks[0]).addClass('active');
    setTimeout(function() {
        $('.sections').each(function() {
            var $cm = $(this).nextAll('.CodeMirror')[0].CodeMirror;
            $cm.refresh();
            $('.editor-preview').addClass('scroll scroll-primary');
            // $cm.on("update", function(el) {
            //     const updatedValue = el.getValue()
            //     var textarea = el.getTextArea();
            //     textarea.innerHTML = updatedValue;
            // });
        });

    }, 500);
});

// For Updating Existing List Dropdown
$("#existingDocType").change(function() {
    const checked = $(this).prop('checked');
    var options;
    if (checked) {
        options = existingDocList["my"];
    } else {
        options = existingDocList["all"];
    }

    var docOptions = '<option value="" disabled selected>SELECT</option>';
    options.forEach((value) => {
        docOptions += `<option value="${value["id"]}">${value["title"]}</option>`;
    });


    var selectExistingDocs = $("#existingDocs");
    selectExistingDocs.empty();
    selectExistingDocs.append(docOptions);
    selectExistingDocs.selectpicker('refresh');

});

$("#existingDocs").change(function() {
    const existingId = this.value;
    const project_id = $("#project-id").val();
    // console.log(this.value);
    const url = `/documents/add?project_id=${project_id}&existing_id=${this.value}`;
    // console.log(url);
    location.href = url;
});

$('form').on('submit', function(e) {
    e.preventDefault();

    var docId = $("#id").val();
    const reviewerId = $("#reviewer-id").val();
    if (reviewerId == null) {
        showPopUp("Validation Error", "Reviewer is required!");
        return false;
    }

    var $codemirror1 = $('textarea[name="cp-change-history"]').nextAll('.CodeMirror')[0].CodeMirror;
    var changeHistory = $codemirror1.getValue();

    //Creating formData as original form is sometimes not taking updated values
    //of textareas.
    var formData = {
        'id': $("#id").val(),
        'type': $("#type").val(),
        'project-id': $("#project-id").val(),
        'reviewer-id': $("#reviewer-id").val(),
        'author-id': $("#author-id").val(),
        'status': $("#status").val(),
        'cp-line3': $("#cp-line3").val(),
        'cp-line4': $("#cp-line4").val(),
        'cp-line5': $("#cp-line5").val(),
        'cp-approval-matrix': $("#cp-approval-matrix").val(),
        'cp-change-history': changeHistory,
    }

    var allSections = $("textarea.sections");
    for (var i = 0; i < allSections.length; i++) {
        var sectionId = allSections[i].id;
        const $codemirror = $('textarea[name="' + sectionId + '"]').nextAll('.CodeMirror')[0].CodeMirror;
        var sectionValue = $codemirror.getValue();

        formData[sectionId] = sectionValue;

    }
    //Restricting the use to enter the any html tags in RISK section, otherthan imported
    var validateTypeStr = $("#type").val();
    if (validateTypeStr.indexOf('riskmanagement-report') > -1 || validateTypeStr.indexOf('riskmanagement-plan') > -1) {
        var validateData = (formData['section7']).replace(/<li>/g, '').replace(/<\/li>/g, '').replace(/<ol>/g,
            '').replace(/<\/ol>/g, '').replace(/=>/g, '').replace(/<br\/>/g, '').replace(/<br>/g, '');
        if ((validateData.indexOf('<>') > -1)) {
            showPopUp("Validation Error", "Remove empty <> braces in Risk Assessment");
            return false;
        }
    }

    var successMessage = "Document created successfully.";
    if (docId != "") {
        successMessage = "Document updated successfully.";
    }


    $.ajax({
        type: 'post',
        url: '/documents/save',
        data: formData,
        success: function(response) {
            response = JSON.parse(response);
            if (response.success == "True") {
                if (docId == "") {
                    location.href = "/documents/add?id=" + response.id;
                } else {
                    const fileName = response.fileName;
                    $(".heading").text(fileName);

                    revisionHistory = response.revisionHistory;
                    showRevisionHistory(revisionHistory);
                    showPopUp("Success", successMessage);
                }




            } else {
                showPopUp("Failure", "Failed to update document");
            }
        },
        error: function(err) {
            showPopUp("Failure", "Error occured on server.");
        }
    });

});

function addLineToComment(sectionName) {
    const $codemirror = $('textarea[name="description"]').nextAll('.CodeMirror')[0].CodeMirror;
    const existingVal = $codemirror.getDoc().getValue();

    if (!reviewedSection.includes(sectionName)) {
        reviewedSection.push(sectionName);
        sectionName += " - \n"
    } else {
        sectionName = ""
    }

    if (existingVal != "") {
        sectionName = "\n" + sectionName;
    }


    if (sectionName != "") {
        $codemirror.getDoc().setValue(existingVal + sectionName);
        textareaFocus($codemirror);
    }

    if ($(".reviewDiv").length) {
        if (!$(".reviewbox").is(":visible")) {
            showReview();
            textareaFocus($codemirror);
        }
    }

}



function saveReview(reviewId) {
    const $codemirror = $('textarea[name="description"]').nextAll('.CodeMirror')[0].CodeMirror;
    const message = $codemirror.getDoc().getValue();

    if (message != "") {
        $codemirror.getDoc().setValue("");
        documentReview.description = message;

        //For reviewer to update the status
        const updateStatus = $("#reviewStatus").length;
        if (updateStatus) {
            documentReview.status = $("#reviewStatus").val();
        }

        if (reviewId == '') {
            console.log('create new review');
            const reviewCategory = $("#reviewCategory").val();

            documentReview.category = reviewCategory;
            documentReview.reviewName = reviewCategory + " Review";
            documentReview.reviewBy = $("#reviewer-id").val();
        }

        if (commentEditId != "") {
            documentReview["commentId"] = commentEditId;
        }

        submitReview();

    } else {
        showReview();
    }

}

function submitReview() {

    $.ajax({
        type: 'POST',
        url: '/reviews/addDocReview',
        data: documentReview,
        success: function(response) {
            response = JSON.parse(response);
            if (response.success == "True") {
                const reviewId = response.reviewId;

                if (documentReview["id"] == "") {
                    $(".reviewHeading").text(`R-${reviewId} Comments`);
                    documentReview["id"] = reviewId;
                }

                if (commentEditId != "") {
                    let previousComment = getObjectFromArray(commentEditId, reviewComments);
                    reviewComments.splice(previousComment[0], 1);
                    $("#" + commentEditId).remove();
                }

                const comment = response.comment;
                reviewComments.push(comment);
                addReviewCommentToUI(documentReview["id"], comment);
                showReview();
                showFloatingAlert(response.message);

                if ($("#status").length) {
                    $("#status").val(response.status);
                    $('.selectpicker').selectpicker('refresh');
                }

                showRevisionHistory(response.revisionHistory);

            } else {
                showPopUp("Failure", "Failed to add a review comment!.");
            }
        },
        error: function(err) {
            showPopUp("Failure", "Failed to add a review comment!."+err);
        }
    })

}

//Auto Hiding of Success alert after document generation
$(".alert-success").fadeTo(2000, 500).slideUp(500, function() {
    $(".alert-success").slideUp(500);
});


function generatePreview(e, id) {
    var url = '/generate-documents/downloadDocuments/3/' + id;
    var anchor = $(e);
    var iTag = anchor.find('i');
    $.ajax({
        url: url,
        beforeSend: function() {
            $(anchor).addClass('disabled');
            $(iTag).addClass('fa-spinner fa-spin');
        },
        complete: function() {
            $(anchor).removeClass('disabled');
            $(iTag).removeClass('fa-spinner fa-spin');
        },
        success: function(response) {
            if (response == "no data") {
                showPopUp("Project Documents", "No file is available to download");
            } 
            response = JSON.parse(response);
            if(response.success == 'True'){
                var host = 'https://'+location.hostname+'/'+response.fileName;
                window.open(host, '_blank');
            }else {
                showPopUp("Project Documents", "Unable to view the document");
            }
        },
        error: function(error) {
            // console.log("Something worng3:", error.responseJSON['message']);
            // console.log("Something worng4:", error.responseText);
            if (error.responseJSON && error.responseJSON != '') {
                showPreview("Preview Error", "Please remove custom tags if any exists. <br/> " + error
                    .responseJSON['message'], 'lg');
            } else if (error.responseText && error.responseText != '') {
                showPreview("Preview Error", "Please remove custom tags if any exists. <br/>" + error
                    .responseText, 'lg');
            } else {
                showPreview("Preview Error", "Unable to view the file");
            }
        }
    });
}

function showPreview(title, message, width) {
    bootbox.alert({
        title: title,
        message: message,
        centerVertical: true,
        backdrop: 'static',
        size: width,
        className: 'preview-modal',
        buttons: {
            ok: {
                label: 'Close'
            }
        }
    });
}
</script>

<style>
.preview-modal>.modal-content {
    width: 200% !important;
}

.preview-modal img {
    max-width: 250px;
}

.pandoc-mark-css {
    font-family: 'Arial, sans-serif';
    border-spacing: 0 10px;
    font-family: 'Arial, sans-serif';
    font-size: 11;
    width: 100%;
    padding: 10px;
    border: 1px #bbb solid !important;
    border-collapse: collapse;
}

.pandoc-mark-css tbody tr:first-child td {
    padding-top: 8px;
    font-weight: bold;
    height: 50px;
    text-align: left;
    background-color: #cbebf2;
}
</style>
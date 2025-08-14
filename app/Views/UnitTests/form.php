<style>
    .box {
        box-shadow: 0px 1px 22px -12px #607D8B;
        background-color: #fff;
        padding: 15px;
        border-radius: 8px;
    }

    .box-header {
        border-bottom: 1px solid;
        font-size: 19px !important;
    }

    .truncate {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .accordion {
        background-color: #e9ecef;
        color: #444;
        cursor: pointer;
        padding: 8px 11px;
        text-align: left;
        border: none;
        outline: none;
        transition: 0.4s;
    }

    .active-accordion {
        background-color: #b8daff;
        color: #007bff;
    }

    .accordion:hover {
        background-color: #d6d8db;
    }

    .breadcrumb {
        padding: 0.3rem .3rem 0.7rem 0.7rem;
    }

    .breadcrumb-item:hover {
        color: #007bff !important;
        cursor: pointer;
    }

    .breadcrumb-active {
        color: #007bff !important;
        padding: 5px 8px;
        background: white;
        border-radius: 4px;
    }

    /* Style the accordion panel. Note: hidden by default */
    .panel {
        padding: 0 18px;
        background-color: #edf5fa;
        max-height: 0;
        overflow: auto;
        transition: max-height 0.2s ease-out;
        border-left: 1px solid #d9dde1;
        border-right: 1px solid #d9dde1;
    }

    .breadcrumb-item+.breadcrumb-item::before {
        content: "|";
        vertical-align: top;
        line-height: 18px;
        margin-top: 4px;
        font-size: 27px;
        color: #ddd;
    }
</style>
<script src="https://rawgit.com/jackmoore/autosize/master/dist/autosize.min.js"></script>
<?php
$templateArray = json_decode($unitTest->json);
$project_name =  $unitTest->getProjectName();
$author_name = $unitTest->getAuthorName();
?>

<input type="hidden" id="id" name="id" value="<?= $unitTest->id ?>" />
<div class="row p-0 pr-md-4 pl-md-4 pt-3 justify-content-center mb-2">
    <div class="col-12 col-lg-4">
        <div class="box">
            <div class="text-center box-header"><span>Test Details</span></div>
            <div class="mt-3 box-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <input type="hidden" id="project_id" name="project_id" value="<?= $unitTest->project_id ?>" />
                            <label class="text-muted" style="width: 100%;">Project</label>
                            <button style="width: 100%" type="button" id="project" data-toggle="popover" data-placement="top" data-content="<?= $project_name ?>" class="btn btn-sm table-secondary truncate"><?= $project_name ?></button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <input type="hidden" id="author_id" name="author_id" value="<?= $unitTest->author_id ?>" />
                            <input type="hidden" id="author" name="author" value="<?= $author_name ?>" />
                            <label class="text-muted" style="width: 100%;">Tested by</label>
                            <button style="width: 100%" type="button" id="project" data-toggle="popover" data-placement="top" data-content="<?= $author_name ?>" class="btn btn-sm table-secondary truncate"><?= $author_name ?></button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="text-muted" for="name">Module Name</label>
                            <input type="text" class="form-control" name="name" id="name" value="<?= $unitTest->name ?>">
                            <div class="invalid-feedback">
                                Please provide a module name.
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="text-muted" for="verified_on">Verified On</label>
                            <input type="date" class="form-control" name="verified_on" id="verified_on" value="<?= $templateArray->meta->verified_on ?>">
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label class="text-muted" for="build_env">Build Environment</label>
                            <textarea class="form-control" style="min-height: 115px;" name="build_env" id="build_env"><?= urldecode($templateArray->meta->build_env) ?></textarea>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label class="text-muted" for="additional_info">Additional Info</label>
                            <textarea class="form-control" style="min-height: 115px;" name="additional_info" id="additional_info"><?= urldecode($templateArray->meta->additional_info) ?></textarea>
                        </div>
                    </div>
                </div>

                <?php if (session()->get('id') == $unitTest->author_id) : ?>
                    <div class="row justify-content-center">
                        <div class="col-3">
                            <button type="button" class="btn btn-primary" onclick="saveUnitTest()">Save</button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-8">
        <div class="row">
            <div class="col-12">
                <div class="box">
                    <div style="width: 100%;display: inline-flex;" class="box-header">
                        <div class=" text-center" style="width: 93%;">
                            <span>Test Cases</span>
                        </div>
                        <div style="width: 7%;margin-top:-7px">
                            <button class="btn btn-sm btn-purple float-right" onclick="addTestCase()" data-toggle="popover" data-placement="top" data-content="Add a test case">
                                Add
                            </button>
                        </div>
                    </div>
                    <nav aria-label="breadcrumb" class="pt-2">
                        <ol class="breadcrumb"></ol>
                    </nav>
                    <div class="testCasesContainer box-body mt-3 scroll scroll-dark" style="max-height: 70vh; min-height: 63vh"></div>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    var template = <?= $unitTest->json ?>;
    class TestCase {
        constructor() {
            <?php
            $array = json_decode($testCaseTemplate, true);
            foreach ($array as $key => $value) {
                echo "this.$key = '';\n";
            }
            ?>
        }
    }

    $(document).ready(function() {
        <?php if (session()->get('success')) : ?>
            showFloatingAlert("<?= session()->get('success') ?>");
        <?php endif; ?>

        initializePopover();

        if (template.testcases.length) {
            deployTemplateData();
        } else {
            addTestCaseNav();
        }

        var templateId = "<?= isset($templateId) ? $templateId : '' ?>";
        if (templateId != '') {
            $("#customDD").val(templateId);
            $("#customDD").selectpicker('refresh');
        }
    });

    $(document).on({
        ajaxStart: function() {
            $("#loading-overlay").show();
        },
        ajaxStop: function() {
            $("#loading-overlay").hide();
        }
    });

    $("#customDD").change(function() {
        const templateId = this.value;
        const projectId = $("#project_id").val();
        location.href = `/unit-tests/add/project/${projectId}/template/${templateId}`;
    });

    function initializePopover() {
        $('[data-toggle="popover"]').popover({
            trigger: "hover"
        });
    }

    function deployTemplateData() {
        template.testcases.forEach((testCase) => {
            addTestCaseToUI(testCase);
        })
    }

    function addTestCaseNav(id = 0, navTitle = "") {
        let activeClass = 'breadcrumb-active';
        if (id == 0) {
            activeClass = 'text-muted';
            navTitle = `Click Add button to include a test case in this Unit Test.`;
        } else {
            $(`.breadcrumb_0`).remove();
            $('.breadcrumb-item span').removeClass('breadcrumb-active');
            $('.breadcrumb-item').addClass('text-muted');
        }
        $(".breadcrumb").append(`
            <li 
                class="breadcrumb-item breadcrumb_${id} mt-2"
                aria-current="page" 
                onclick="onAccordionClick(${id})"
                >
                <span class="${activeClass} breadcrumb_${id}_title">
                    ${unescape(navTitle)}
                </span>
            </li> 
        `)

    }

    function addTestCaseToUI(testCase) {
        const testCaseHtml = getTestCaseHtml(testCase);
        $(".testCasesContainer").append(testCaseHtml);
        cleanUpText();
        $(`#tc${testCase.id}_pass_or_fail`).bootstrapToggle();
        autoResizeTextarea(testCase.id);
        onAccordionClick(testCase.id);
        addTestCaseNav(testCase.id, testCase.name);
        initializePopover();
    }

    function getTestCaseHtml(testCase) {
        const pass = testCase.pass_or_fail;
        const checked = (pass == "" || pass == "PASS") ? "checked" : "";
        return `
            <div 
                class="accordion accordion_${testCase.id} row" 
                >
                    <div class="col" onclick="onAccordionClick('${testCase.id}')">
                        <span class="accordion_${testCase.id}_title">${unescape(testCase.name)}</span>
                    </div>
                    <div class="col-auto">
                        <span 
                            class="ml-3" 
                            data-toggle="popover" 
                            data-placement="top" 
                            data-content="Clone"
                            onclick="addTestCase(${testCase.id})">
                                <i class="fas fa-clone"></i>
                        </span> 
                        <span 
                            class="ml-3" 
                            data-toggle="popover" 
                            data-placement="top" 
                            data-content="Delete"
                            onclick="deleteTestCase(${testCase.id})">
                                <i class="fas fa-trash-alt"></i>
                        </span>
                    </div>
                    
            </div>
            <div class="panel panel_${testCase.id} scroll scroll-dark">
                <div class="pt-3">
                    <div class="row form-group">
                        <div class="col-12 col-sm-6">
                            <div class="row">
                                <label class="col-sm-4 text-muted mt-2">
                                    Name
                                </label>
                                <div class="col-sm-8">
                                    <input 
                                        type="text" 
                                        class="form-control" 
                                        maxlength="15"
                                        name="tc${testCase.id}_name" 
                                        id="tc${testCase.id}_name" 
                                        value="${unescape(testCase.name)}"
                                        oninput="updateTestCaseTitle(${testCase.id},this)"
                                    >
                                    <div class="invalid-feedback">
                                        Please provide a name.
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="row justify-content-end">
                                <label class="col-sm-4 text-muted mt-2">
                                    Pass/Fail
                                </label>
                                <div class="col-sm-4">
                                    <input 
                                        class="mt-4" 
                                        type="checkbox" 
                                        name="tc${testCase.id}_pass_or_fail" 
                                        id="tc${testCase.id}_pass_or_fail" 
                                        data-on="PASS" 
                                        data-off="FAIL" 
                                        data-onstyle="success" 
                                        data-offstyle="danger" 
                                        ${checked} 
                                        data-toggle="toggle">
                                </div>
                            </div>
                        </div>
                    </div>

                   
                    <div class="form-group row">
                        <label 
                            class="col-form-label col-sm-2 text-muted" 
                            for="tc${testCase.id}_description">
                                Description
                        </label>
                        <div class="col-sm-10">
                            <textarea 
                                id="tc${testCase.id}_description" 
                                name="tc${testCase.id}_description" 
                                class="form-control scroll scroll-dark">
                                    ${ unescape(testCase.description) }
                            </textarea>
                        </div>
                    </div>
                        
                    <div class="form-group row">
                        <label 
                            class="col-form-label col-sm-2 text-muted" 
                            for="tc${testCase.id}_steps">
                                Steps
                        </label>
                        <div class="col-sm-10">
                            <textarea 
                                id="tc${testCase.id}_steps" 
                                name="tc${testCase.id}_steps" 
                                class="form-control scroll scroll-dark">
                                    ${ unescape(testCase.steps) }
                            </textarea>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label 
                            class="col-form-label col-sm-2 text-muted" 
                            for="tc${testCase.id}_expected_result">
                                Expected Result
                        </label>
                        <div class="col-sm-10">
                            <textarea 
                                id="tc${testCase.id}_expected_result" 
                                name="tc${testCase.id}_expected_result" 
                                class="form-control scroll scroll-dark">
                                    ${ unescape(testCase.expected_result) }
                            </textarea>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label 
                            class="col-form-label col-sm-2 text-muted" 
                            for="tc${testCase.id}_actual_result">
                                Actual Result
                        </label>
                        <div class="col-sm-10">
                            <textarea 
                                id="tc${testCase.id}_actual_result" 
                                name="tc${testCase.id}_actual_result" 
                                class="form-control scroll scroll-dark">
                                    ${ unescape(testCase.actual_result) }
                            </textarea>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label 
                            class="col-form-label col-sm-2 text-muted" 
                            for="tc${testCase.id}_notes">
                                Notes
                        </label>
                        <div class="col-sm-10">
                            <textarea 
                                id="tc${testCase.id}_notes" 
                                name="tc${testCase.id}_notes" 
                                class="form-control scroll scroll-dark">
                                    ${ unescape(testCase.notes) }
                            </textarea>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    function cleanUpText() {
        const allTextArea = $('textarea');
        for (let i = 0; i < allTextArea.length; i++) {
            allTextArea[i].value = allTextArea[i].value.trim();
        }
    }

    function autoResizeTextarea(id) {
        const textareas = $(`.panel_${id} textarea`);
        for (let i = 0; i < textareas.length; i++) {
            autosize(textareas[i]);
        }
    }

    function updatedTestCaseNav(id) {
        $('.breadcrumb-item span').removeClass('breadcrumb-active');
        $('.breadcrumb-item').addClass('text-muted');
        $(`.breadcrumb_${id}`).removeClass('text-muted')
        $(`.breadcrumb_${id} span`).addClass('breadcrumb-active')
    }

    function updateTestCaseTitle(id, e) {
        const title = e.value;
        $(`.breadcrumb_${id}_title`).text(title);
        $(`.accordion_${id}_title`).text(title);
    }
    
    function addTestCase(cloneId = 0) {
        let newTestCase;

        if (cloneId != 0) {
            newTestCase = cloneTestCase(cloneId);
        } else {
            newTestCase = new TestCase();
            newTestCase.id = getNewTestCaseId();
            const testCaseNo = template.testcases.length + 1;
            newTestCase.name = `Test Case ${testCaseNo}`;
        }

        template.testcases.push(newTestCase);

        addTestCaseToUI(newTestCase);
    }

    function onAccordionClick(id) {
        $(".accordion").removeClass("active-accordion");
        var allAccordians = $(".accordion");

        for (i = 0; i < allAccordians.length; i++) {
            var acc = allAccordians[i];
            var panel = acc.nextElementSibling;
            panel.style.maxHeight = null;
        }
        const activeAccordian = $(`.accordion_${id}`);
        if (activeAccordian.length == 0) {
            return
        }
        activeAccordian.addClass("active-accordion");

        const activePanel = activeAccordian[0].nextElementSibling;
        activePanel.style.maxHeight = activePanel.scrollHeight + "px";

        updatedTestCaseNav(id);

        setTimeout(function() {
            activeAccordian[0].scrollIntoView({
                behavior: 'smooth'
            });
        }, 500);
    }

    function cloneTestCase(existingTestCaseId) {
        updateTemplate();
        const testCaseNo = template.testcases.length + 1;
        let cloneTestCase = new TestCase();

        cloneTestCase.id = getNewTestCaseId();
        cloneTestCase.name = `Test Case ${testCaseNo}`;

        const existingTestCaseIndex = getTestCaseIndex(existingTestCaseId);
        let existingTestCase = template.testcases[existingTestCaseIndex];

        cloneTestCase.description = existingTestCase.description;
        cloneTestCase.steps = existingTestCase.steps;
        cloneTestCase.expected_result = existingTestCase.expected_result;
        cloneTestCase.actual_result = existingTestCase.actual_result;
        cloneTestCase.pass_or_fail = existingTestCase.pass_or_fail;
        cloneTestCase.notes = existingTestCase.notes;
        return cloneTestCase;
    }

    function deleteTestCase(id) {
        const index = getTestCaseIndex(id);
        template.testcases.splice(index, 1)

        $(`.panel_${id}`).remove();
        $(`.accordion_${id}`).remove();
        $(`.breadcrumb_${id}`).remove();

        const totalTestCases = template.testcases.length;
        if (totalTestCases > 0) {
            const activeTestCase = template.testcases[totalTestCases - 1]
            updatedTestCaseNav(activeTestCase.id);
            onAccordionClick(activeTestCase.id);
        } else {
            addTestCaseNav();
        }

        $(".popover").hide();
    }

    function getTestCaseIndex(id) {
        return template.testcases.map(function(e) {
            return parseInt(e.id);
        }).indexOf(id);
    }

    function updateTemplate() {
        const totalTestCases = template.testcases.length;
        for (let i = 0; i < totalTestCases; i++) {
            updateTestCaseDataFromUI(i, template.testcases[i]);
        }

        template.meta.name = $("#name").val();
        template.meta.author = $("#author").val();
        template.meta.build_env = escapeString($("#build_env").val());
        template.meta.verified_on = $("#verified_on").val();
        template.meta.additional_info = escapeString($("#additional_info").val());
    }

    function updateTestCaseDataFromUI(index, testCase) {
        testCase.name = escapeString($(`#tc${testCase.id}_name`).val());
        testCase.description = escapeString($(`#tc${testCase.id}_description`).val());
        testCase.steps = escapeString($(`#tc${testCase.id}_steps`).val());
        testCase.expected_result = escapeString($(`#tc${testCase.id}_expected_result`).val());
        testCase.actual_result = escapeString($(`#tc${testCase.id}_actual_result`).val());
        testCase.pass_or_fail = $(`#tc${testCase.id}_pass_or_fail`)[0].checked ? "PASS" : "FAIL";
        testCase.notes = escapeString($(`#tc${testCase.id}_notes`).val());

        template.testcases[index] = testCase;
    }

    function validation() {
        const totalTestCases = template.testcases.length;
        if (totalTestCases == 0) {
            showFloatingAlert("Error: Please add atleast one test case.", "bg-danger");
            return false;
        }

        const unitTestName = $("#name").val();
        if (unitTestName == "") {
            $("#name").addClass('is-invalid');
            return false;
        } else {
            $("#name").removeClass('is-invalid');
        }

        for (let i = 0; i < totalTestCases; i++) {
            const testCase = template.testcases[i];
            if (testCase.name == "") {
                $(`#tc${testCase.id}_name`).addClass('is-invalid');
                onAccordionClick(testCase.id);
                return false;
            } else {
                $(`#tc${testCase.id}_name`).removeClass('is-invalid');
            }
        }

        return true;
    }

    function saveUnitTest() {
        updateTemplate();

        if (!validation()) {
            return;
        }

        const id = $("#id").val();
        const unitTestName = $("#name").val();
        const projectId = $("#project_id").val();
        const authorId = $("#author_id").val();

        var data = {
            "id": id,
            "name": unitTestName,
            "project_id": projectId,
            "author_id": authorId,
            "json": template
        }

        makePOSTRequest("/unit-tests/save", data)
            .then((response) => {
                if (response.success == true) {
                    if (data.id == "") {
                        window.location.href = window.location.origin + `/unit-tests/edit/${response.id}`;
                    } else {
                        showFloatingAlert("Success: Unit Test saved!");
                    }

                } else {
                    showFloatingAlert("Error: Something went wrong.", "bg-danger");
                }

            })
            .catch((err) => {
                console.log(err);
                showPopUp('Error', "An unexpected error occured on server.");
            })

        return data;
    }

    function getNewTestCaseId() {
        const totalTestCases = template.testcases.length;
        if (totalTestCases == 0) {
            return 1;
        } else {
            return parseInt(template.testcases[totalTestCases - 1].id) + 1;
        }
    }
</script>
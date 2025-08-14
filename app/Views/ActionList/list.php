<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>

<style>
    body {
        background: #C9D6FF;
        background: -webkit-linear-gradient(to bottom, #E2E2E2, #C9D6FF);
        background: linear-gradient(to right, #E2E2E2, #C9D6FF);
        height: 100vh;
    }

    .toggle-menu {
        border: 5px solid #e0e1e5;
    }

    .breadcrumb {
        background-color: #fff;
    }

    .breadcrumb-item {
        font-size: 24px;
    }

    .my-border {
        border: 0.5px solid #a5c0dc;
        border-radius: 5px;
    }

    .truncate {
        width: 116px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .card {
        box-shadow: 0px 1px 22px -12px #607D8B;
        background-color: #fff;
        padding: 5px;
        border-radius: 8px;
        width: 95%;
        margin-left: auto;
        margin-right: auto;
    }

    .card-body {
        padding: 0.55rem;
        border-bottom: 0.1px solid #6c757d2e;
    }

    .revision-modal {
        width: 400px;
        z-index: 2;
        position: fixed;
        left: -33em;
        top: 65px;
        height: 616px;
        backdrop-filter: blur(5px);
        background: rgba(255, 255, 255, 0.4);
        box-shadow: 5px 10px 22px -12px #607D8B;
        border-radius: 8px;
        transition: all 0.6s ease-in-out;
    }

    .revision-modal-close {
        position: absolute;
        right: 3px;
        top: 2px;
        color: #6c757d;
        font-size: 18px;
        cursor: pointer;
        padding: 12px;
    }

    .revision-modal-close:hover:before {
        color: black;
    }

    .edit-modal {
        width: 400px;
        z-index: 2;
        position: fixed;
        right: -33em;
        top: 65px;
        height: auto;
        backdrop-filter: blur(5px);
        background: rgba(255, 255, 255, 0.4);
        box-shadow: 5px 10px 22px -12px #607D8B;
        border-radius: 8px;
        transition: all 0.6s ease-in-out;
    }

    header{
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .item-header {
        font-size: 18px;
        border-bottom: 0.1px solid #6c757d;
    }

    .item-body {
        overflow-y: auto;
        max-height: 74vh;
        overflow-x: hidden;
        padding: 15px;
        margin-bottom: 62px;
    }

    .item-priority {
        font-size: 14px;
        cursor: default;
        margin-left: 5px;
    }

    .item-description {
        font-size: 14px;
        line-height: 1.5;
        white-space: pre-line;
    }

    .item-footer-font {
        font-size: 14px;
    }

    .item-footer {
        position: absolute;
        margin-left: auto;
        margin-right: auto;
        left: 0;
        right: 0;
        text-align: center;
        bottom: 12px;
    }

    .hide-right {
        transform: translate(0em, 0);
    }

    .hide-left {
        transform: translate(0em, 0);
    }

    .show-right {
        transform: translate(-34em, 0);
    }

    .show-left {
        transform: translate(40em, 0);
    }

    .select2-selection__rendered {
        line-height: 39px !important;
    }

    .select2-container .select2-selection--single {
        height: 39px !important;
    }

    .select2-container .select2-selection--multiple {
        max-height: 110px !important;
        overflow-y: auto !important;
    }

    .select2-selection__arrow {
        height: 39px !important;
    }

    .ack-icon {
        color: #6c757d;
        margin-top: -5px;
        cursor: pointer;
        padding: 7px;
        margin-left: 8px;
        font-size: 20px;
        transition: all 0.5s ease-in;
    }

    .ack-icon:hover {
        color: #28a745;
    }

    .acknowledged {
        color: #28a745 !important;
    }

    /* Timeline styles*/
    .timeline {
        width: 100%;
        height: 92%;
        border-radius: 8px;
        color: #fff;
        padding: 10px 1px 10px 10px;
        overflow-y: auto;
    }

    .timeline ul {
        list-style-type: none;
        border-left: 2px solid lightgray;
        padding: 10px 5px;
    }

    .timeline ul li {
        padding: 2px 8px 2px 8px;
        position: relative;
        cursor: pointer;
        transition: .5s;
    }

    .revision-time {
        font-size: 11px;
        text-align: right;
        float: right;
        width: 100%;
    }

    .timeline ul li .content h3 {
        font-size: 16px;
        padding-top: 5px;
    }

    .timeline ul li .content p {
        padding: 5px 0px 15px 0px;
        font-size: 14px;
        margin-bottom: 0px;
    }

    .timeline ul li:before {
        position: absolute;
        content: '';
        width: 10px;
        height: 10px;
        background-color: #6c757d;
        border-radius: 50%;
        left: -11px;
        top: 28px;
        transition: .5s;
    }

    .timeline ul li:hover {
        background-color: #dee2e6;
        border-radius: 8px;
    }

    .timeline ul li:hover:before {
        background-color: #007bff;
        box-shadow: 0px 0px 10px 2px #007bff;
    }
</style>

<div class="fluid-container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb  arr-right" style="margin:0px;">
            <li class="breadcrumb-item text-secondary" aria-current="page"> Action List </li>
            <li class="ml-auto">
                <button class="btn btn-primary " onclick="addItem()">
                    <i class="fa fa-plus"></i> Add
                </button>
            </li>
        </ol>

    </nav>
    <div class="container mt-3">
        <ul class="nav nav-pills nav-fill nav-justified bg-white my-border" id="myTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="todo-tab" data-toggle="tab" href="#todo" role="tab" aria-controls="todo" aria-selected="true">To Do</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="onHold-tab" data-toggle="tab" href="#onHold" role="tab" aria-controls="onHold" aria-selected="false">On Hold</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="completed-tab" data-toggle="tab" href="#completed" role="tab" aria-controls="completed" aria-selected="false">Completed</a>
            </li>
        </ul>
        <div class="tab-content scroll scroll-primary" id="myTabContent" style=" overflow-y: auto; max-height: 80vh; ">
            <div class="tab-pane fade show active" id="todo" role="tabpanel" aria-labelledby="todo-tab">
                <div class="todo_items"></div>
            </div>
            <div class="tab-pane fade" id="onHold" role="tabpanel" aria-labelledby="onHold-tab">
                <div class="onhold_items"></div>
            </div>
            <div class="tab-pane fade" id="completed" role="tabpanel" aria-labelledby="completed-tab">
                <div class="completed_items"></div>
            </div>
        </div>


    </div>

    <div class="edit-modal">
        <form novalidate>
            <header class="text-center p-2">
                <span id="editModalTitle" class="item-header font-weight-bold text-secondary">Edit Item</span>
            </header>
            <div class="item-body scroll scroll-primary ">
                <input type="hidden" id="item_id" value="">
                <input type="hidden" id="item_state" value="">
                <input type="hidden" id="item_owner" value="">
                <div class="row ">
                    <div class="col-4 pr-0">
                        <div class="form-group">
                            <label class="text-muted" for="priority">Priority</label>
                            <select class="form-control select-box" name="priority" id="priority">
                                <option value="Low">Low</option>
                                <option value="Medium">Medium</option>
                                <option value="High">High</option>
                            </select>
                        </div>
                    </div>


                    <div class="col-5 pr-0" style="max-width: 45.666667%;">
                        <div class="form-group">
                            <label required class="text-muted" for="due_date">Due Date</label>
                            <input type="date" class="form-control" name="due_date" id="due_date" value="">
                            <div class="invalid-feedback">
                                Please choose a valid due date.
                            </div>
                        </div>
                    </div>

                    <div class="col-2">
                        <div class="form-group">
                            <label class="text-muted" for="sharing">Sharing</label><br />
                            <input type="checkbox" id="sharing" checked data-toggle="toggle">
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="form-group">
                            <label class="text-muted" for="title">Title</label>
                            <input type="text" required class="form-control scroll scroll-dark" name="tilte" id="title" maxlength="200"></textarea>
                            <div class="invalid-feedback">
                                Please provide a title.
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="form-group">
                            <label class="text-muted" for="description">Description</label>
                            <textarea required class="form-control scroll scroll-dark" placeholder="Describe action here..." style="height: 165px;" name="description" id="description"></textarea>
                            <div class="invalid-feedback">
                                Please provide some description.
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="form-group">
                            <label class="text-muted" for="completion" id="percentLabel">%
                                completed</label>
                            <input type="range" value="0" max="100" min="0" class="form-control-range custom-range" id="completion">
                        </div>
                    </div>


                    <div class="col-12">
                        <div class="form-group">
                            <label class="text-muted" for="responsible_id">Responsible</label>
                            <select required class="form-control select-box" multiple="multiple" name="responsible_id" id="responsible_id">
                                <?php foreach ($teamMembers as $key => $name) : ?>
                                    <option value="<?= $key ?>"><?= $name ?></option>;
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">
                                Please choose atleast one user.
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <footer class="item-footer">
                <div class="row">
                    <div class="col-12 text-center">
                        <button type="submit" id="btnSave" class="btn btn-primary">Save</button>
                        <a class="btn btn-danger ml-2 edit-cancel text-white" onclick="hideItemModal()">Cancel</a>
                    </div>
                </div>
            </footer>
        </form>
    </div>
    <div class="revision-modal"> </div>
</div>

<script>
    var actionItems = [];

    var owner_id = <?= session()->get('id') ?>,
        teamMembers;
    var slider = document.getElementById("completion");
    var output = document.getElementById("percentLabel");

    slider.oninput = function() {
        output.innerHTML = this.value + " % completed";
    }

    class ActionItem {
        constructor() {
            this.id = null;
            this.owner_id = null;
            this.responsible_id = null;
            this.sharing = false;
            this.update_date = null;
            this.action = {
                    "title": '',
                    "description": '',
                    "priority": 'High',
                    "completion": '0',
                    "state": 'todo',
                    "due_date": '',
                    "created_date": '',
                    "completion_date": '',
                    "ack": ''
                },
                this.revision_history = [];

        }
    }

    $(document).ready(function() {
        $('#description').on('input', function() {
            $(this).outerHeight(138).outerHeight(this.scrollHeight + 3);
        });

        $('#priority').select2();
        $('#responsible_id').select2({
            dropdownAdapter: $.fn.select2.amd.require('select2/selectAllAdapter')
        });
        $(".select2-selection--multiple").addClass('scroll scroll-dark');

        teamMembers = <?= json_encode($teamMembers) ?>;
        actionItems =
            <?= json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>;
        for (var i = 0; i < actionItems.length; i++) {
            actionItems[i].action = JSON.parse(actionItems[i].action);
            actionItems[i].revision_history = JSON.parse(actionItems[i].revision_history);
            actionItems[i].id = parseInt(actionItems[i].id);
            actionItems[i].sharing = parseInt(actionItems[i].sharing);
            actionItems[i].owner_id = parseInt(actionItems[i].owner_id);
            const actionHtml = getActionHtml(actionItems[i]);
            $("." + actionItems[i].action.state + "_items").append(actionHtml);
        }

        $('[data-toggle="popover"]').popover({
            trigger: "hover"
        });

        goToAction();
    });

    function goToAction(){
        const currentURL = window.location.href;
        const divIdExists = currentURL.lastIndexOf('#');
        if(divIdExists > -1){
            const actionItemId = currentURL.substring(divIdExists+1);
            var elem = document.getElementById(actionItemId);
            elem.scrollIntoView({
                behavior: 'smooth'
            });
        }
    }



    $.fn.select2.amd.define('select2/selectAllAdapter', [
        'select2/utils',
        'select2/dropdown',
        'select2/dropdown/attachBody'
    ], function(Utils, Dropdown, AttachBody) {

        function SelectAll() {}
        SelectAll.prototype.render = function(decorated) {
            var self = this,
                $rendered = decorated.call(this),
                $selectAll = $(
                    '<button class="btn btn-xm btn-default" type="button" style="margin-left:6px;"><i class="fa fa-check-square-o"></i> Select All</button>'
                ),
                $unselectAll = $(
                    '<button class="btn btn-xm btn-default" type="button" style="margin-left:6px;"><i class="fa fa-square-o"></i> Unselect All</button>'
                ),
                $btnContainer = $('<div style="padding:5px;text-align:center;">').append($selectAll).append(
                    $unselectAll);
            if (!this.$element.prop("multiple")) {
                // this isn't a multi-select -> don't add the buttons!
                return $rendered;
            }
            $rendered.find('.select2-dropdown').prepend($btnContainer);
            $selectAll.on('click', function(e) {
                self.$element.find('option').prop('selected', 'selected').end().select2();
                self.$element.select2({
                    dropdownAdapter: $.fn.select2.amd.require('select2/selectAllAdapter')
                });
            });
            $unselectAll.on('click', function(e) {
                self.$element.find('option').prop('selected', false).end().select2();
                self.$element.select2({
                    dropdownAdapter: $.fn.select2.amd.require('select2/selectAllAdapter')
                });
            });
            return $rendered;
        };

        return Utils.Decorate(
            Utils.Decorate(
                Dropdown,
                AttachBody
            ),
            SelectAll
        );

    });


    function hideItemModal() {
        $(".edit-modal").removeClass("show-right");
        $(".edit-modal").addClass("hide-right");
        hideRevisionModal();
    }

    function showItemModal() {
        $(".edit-modal").removeClass("hide-right");
        $(".edit-modal").addClass("show-right");
    }

    function hideRevisionModal() {
        $(".revision-modal").removeClass("show-left");
        $(".reviedit-modal").addClass("hide-left");
    }

    function addItem() {
        $("#editModalTitle").text('Add action item');
        $("#btnSave").text('Save');

        $("#item_id").val("");
        $("#item_state").val("todo");
        $("#item_owner").val(owner_id);
        $("#title").val("");
        $("#description").val("");
        $("#description").outerHeight(138).outerHeight($("#description")[0].scrollHeight + 3);

        $("#responsible_id").val(owner_id);
        $("#priority").val("High");
        $('#due_date').val(new Date().toISOString().slice(0, 10));
        $("#completion").val(0);
        output.innerHTML = "0% completed";
        $('#sharing').bootstrapToggle('off')
        $('.select-box').trigger('change');

        showItemModal();

    }

    function editItem(id) {
        // $("#editModalTitle").text('L-' + id);
        
        $("#btnSave").text('Update');

        $("#item_id").val(id);

        let [itemLoc, item] = getObjectFromArray(id, actionItems);
        $("#editModalTitle").text(item.action.title);
        $("#item_state").val(item.action.state);
        $("#item_owner").val(item.owner_id);
        $("#title").val(item.action.title);
        $("#description").val(item.action.description);
        $("#description").outerHeight(138).outerHeight($("#description")[0].scrollHeight + 3);
        $("#responsible_id").val(item.responsible_id.split(','));
        $("#priority").val(item.action.priority);
        $("#due_date").val(item.action.due_date);
        $("#completion").val(item.action.completion);
        output.innerHTML = item.action.completion + " % completed";
        item['sharing'] == 1 ? $('#sharing').bootstrapToggle('on') : $('#sharing').bootstrapToggle('off');

        $('.select-box').trigger('change');
        showItemModal();

        $(".reviedit-modal").removeClass("hide-left");
        $(".revision-modal").addClass("show-left");

        getRevisionHtml(item);
    }

    $('form').on('submit', function(e) {
        e.preventDefault();

        let form = $('form')[0];
        if (form.checkValidity() === true) {

            const item_id = $("#item_id").val();
            let actionItem = buildActionItem();
            let update = true;

            if (item_id == "") {
                actionItem.action.ack = `${owner_id}`;
                let created_date = getCurrentDateForDB();

                if (actionItem.action.completion == 100) {
                    actionItem.action.state = "completed";
                    actionItem.action.completion_date = created_date;
                }

                actionItem.action.created_date = created_date;

                actionItem.revision_history.push(buildRevisionLog(actionItem, 'create'));

                optionalMessage = 'created';
                const data = {
                    actionItem: JSON.stringify(actionItem)
                };
                updateInDB('/actionList/update', data, 'update', optionalMessage);

            } else {
                actionItem.id = item_id;
                const revisionLog = buildRevisionLog(actionItem, 'update');
                if (revisionLog == false) {
                    update = false;
                } else {
                    let [itemLoc, item] = getObjectFromArray(item_id, actionItems);
                    actionItem.action.ack = item.action.ack;
                    actionItem.action.completion_date = item.action.completion_date;
                    actionItem.action.created_date = item.action.created_date;
                    actionItem.revision_history = item.revision_history;
                    actionItem.revision_history.push(revisionLog);

                    if (item.action.state != "completed" && actionItem.action.completion == 100) {
                        actionItem.action.state = "completed";
                        let completed_date = getCurrentDateForDB();
                        actionItem.action.completion_date = completed_date;
                        actionItem.revision_history.push(buildRevisionLog(actionItem, 'move'));
                        optionalMessage = 'completed';
                    } else {
                        optionalMessage = 'updated';
                    }
                    const data = {
                        actionItem: JSON.stringify(actionItem)
                    };
                    updateInDB('/actionList/update', data, 'update', optionalMessage);
                }

            }

            hideItemModal();
        } else {
            form.classList.add('was-validated');
        }

    });

    function updateInDB(url, data, type, optionaMessage = "") {

        makePOSTRequest(url, data)
            .then((response) => {
                let message = "";
                if (type == 'delete') {

                    let [itemLoc, item] = getObjectFromArray(data.id, actionItems);
                    actionItems.splice(itemLoc, 1);

                    $("#actionItem_" + data.id).fadeOut(800, function() {
                        $(this).remove();
                    });

                    message = `Action deleted successfully!`;

                } else if (type == 'update') {
                    let actionItem = JSON.parse(data.actionItem);
                    actionItem.id = response.id;
                    message = `Action ${optionalMessage} successfully!`;

                    if (optionalMessage == 'created') {
                        actionItems.push(actionItem);
                    } else {
                        let [itemLoc, item] = getObjectFromArray(actionItem.id, actionItems);
                        actionItems[itemLoc] = actionItem;
                        $("#actionItem_" + actionItem.id).remove();

                    }

                    const actionHtml = getActionHtml(actionItem);
                    $("." + actionItem.action.state + "_items").prepend(actionHtml);

                    $('[data-toggle="popover"]').popover({
                        trigger: "hover"
                    });

                } else if (type == 'move') {
                    const actionItem = JSON.parse(data.actionItem);
                    const actionHtml = getActionHtml(actionItem);

                    $("#actionItem_" + actionItem.id).remove();

                    $("." + actionItem.action.state + "_items").prepend(actionHtml);

                    $('[data-toggle="popover"]').popover({
                        trigger: "hover"
                    });

                    message = `Action moved to ${response.stateLabel} successfully!`;
                } else if (type == "ack") {

                    const actionItem = JSON.parse(data.actionItem);
                    const actionHtml = getActionHtml(actionItem);

                    $("#actionItem_" + actionItem.id).remove();

                    $("." + actionItem.action.state + "_items").prepend(actionHtml);

                    $('[data-toggle="popover"]').popover({
                        trigger: "hover"
                    });

                    message = `Action acknowledged!`;
                }
                showFloatingAlert(message);
            })
            .catch((err) => {
                console.log(err);
                showPopUp('Error', "An unexpected error occured on server.");
            })
    }

    function buildActionItem() {

        let actionItem = new ActionItem();
        actionItem.id = $("#item_id").val();
        actionItem.action.title = $("#title").val();
        actionItem.action.description = $("#description").val();
        actionItem.owner_id = $("#item_owner").val();
        actionItem.responsible_id = $("#responsible_id").val();
        actionItem.responsible_id = actionItem.responsible_id.join(',');
        actionItem.action.priority = $("#priority").val();
        actionItem.action.due_date = $("#due_date").val();
        actionItem.action.completion = parseInt($("#completion").val());
        actionItem.sharing = $('#sharing').prop('checked') == true ? 1 : 0;
        actionItem.action.state = $("#item_state").val();
        actionItem.update_date = getCurrentDateForDB();

        return actionItem;
    }

    //Revision log can be for creation, updation, moving
    function buildRevisionLog(actionItem, type) {
        let update_date = getCurrentDateForDB();

        if (type == "create") {
            return {
                log: "Action created",
                who: owner_id,
                type: "created",
                dateTime: update_date
            };
        } else if (type == "move") {
            let state = actionItem.action.state;
            if (state == "completed") {
                type = "completed";
            } else {
                type = "moved";
            }
            state = state.charAt(0).toUpperCase() + state.slice(1)
            return {
                log: "Action moved to " + state + " list.",
                who: owner_id,
                type: type,
                dateTime: update_date
            };
        } else if (type == "update") {
            const updatedFields = getUpdatedFields(actionItem);
            if (updatedFields.length) {
                const label = updatedFields.length == 1 ? 'field' : 'fields';
                return {
                    log: `${updatedFields.join(', ')} ${label} updated.`,
                    who: owner_id,
                    type: "update",
                    dateTime: update_date
                };
            } else {
                return false;
            }
        } else if (type = "ack") {
            return {
                log: "Action acknowledged",
                who: owner_id,
                type: "ack",
                dateTime: update_date
            };
        }
    }

    function getUpdatedFields(updatedVersion) {
        const checkForKeys = ['responsible_id', 'sharing', 'title', 'description', 'priority', 'completion', 'due_date'];
        const labelForKeys = ['Responsible', 'Sharing', 'Title', 'Description', 'Priority', 'Completion', 'Due Date']
        let updatedFields = [];
        const [itemLoc, previousVersion] = getObjectFromArray(updatedVersion.id, actionItems);

        for (var key in previousVersion) {
            const keyIndex = checkForKeys.indexOf(key);
            var val = previousVersion[key];

            if (typeof val == "object") {
                for (var nestedKey in val) {
                    const nestedKeyIndex = checkForKeys.indexOf(nestedKey);
                    if (nestedKeyIndex > -1) {
                        var prevVal = previousVersion[key][nestedKey];
                        var updatedVal = updatedVersion[key][nestedKey];
                        if (prevVal != updatedVal) {
                            updatedFields.push(labelForKeys[nestedKeyIndex])
                        }
                    }
                }
            } else {
                if (keyIndex > -1) {
                    if (key == 'responsible_id') {
                        var prevVal = previousVersion[key].split(",").sort().join(",");
                        var updatedVal = updatedVersion[key].split(",").sort().join(",");
                        if (prevVal != updatedVal) {
                            updatedFields.push(labelForKeys[keyIndex])
                        }
                    } else {
                        var prevVal = previousVersion[key];
                        var updatedVal = updatedVersion[key];
                        if (prevVal != updatedVal) {
                            updatedFields.push(labelForKeys[keyIndex])
                        }
                    }
                }
            }


        }

        return updatedFields;
    }


    function getActionHtml(actionItem) {

        let priorityColor = "";
        if (actionItem.action.priority == "High") {
            priorityColor = "bg-gradient-red";
        } else if (actionItem.action.priority == "Medium") {
            priorityColor = "bg-gradient-yellow";
        } else {
            priorityColor = "bg-gradient-green";
        }

        let completionLabel = "";
        let progressColor = "bg-teal";


        if (actionItem.action.completion < 10) {
            completionLabel = actionItem.action.completion;
        } else if (actionItem.action.completion < 33) {
            completionLabel = actionItem.action.completion + "%";
        } else if (actionItem.action.completion < 100) {
            completionLabel = actionItem.action.completion + "% Completed";
        } else {
            progressColor = "bg-info"
            completionLabel = "Completed in " + secondsToDhms(actionItem.action.created_date, actionItem.action
                .completion_date);
        }

        let sharingImage = "";
        let acknowledgeIcon = "";

        if (actionItem.sharing == 1) {
            sharingImage = `
			<span data-toggle="popover" data-content="Sharing" class="badge ml-1 p-2 bg-gradient-gray">
		<i style="font-size:14px;" class="fa fa-share-alt " aria-hidden="true"></i>
	    </span>
	`;
            let item_ack = actionItem.action.ack;
            if (item_ack == "") {
                item_ack = [];
            } else {
                item_ack = item_ack.split(',').sort();
            }

            let ack_title = "Acknowledge";
            let ack_title_content = "Let people know you are working on it!";

            if (item_ack.length) {
                ack_title = `Acknowledged by ${item_ack.length} of ${actionItem.responsible_id.split(',').length}`;
                ack_title_content = "";
                item_ack.forEach((pid, index) => {
                    ack_title_content += teamMembers[pid];
                    if (index < item_ack.length - 1) {
                        ack_title_content += ', ';
                    }
                });
            }

            const ack_flag = item_ack.includes(`${owner_id}`) ? 'acknowledged' : "";

            acknowledgeIcon = `<i data-toggle="popover" id="btn_ack_${actionItem.id}" 
								onclick="acknowledgeAction(${actionItem.id})"
								title="${ack_title}" data-content="${ack_title_content}"
								class="fa fa-check-circle ack-icon ${ack_flag}" aria-hidden="true"></i>`;
        }

        const responsibleIds = actionItem.responsible_id;
        let responsibleArr;
        if (Array.isArray(responsibleIds)) {
            responsibleArr = responsibleIds;
        } else {
            responsibleArr = responsibleIds.split(',');
        }
        let responsiblePeople = "";

        responsibleArr.forEach((pid, index) => {
            responsiblePeople += teamMembers[pid];
            if (index < responsibleArr.length - 1) {
                responsiblePeople += ', ';
            }
        });

        let totalResponsible = "";

        if (responsibleArr.length > 1) {
            totalResponsible = `(${responsibleArr.length}) `;
        }

        let moveItemHtml = `<button class="btn btn-default ml-1 btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
	    					Move To
						</button>`;

        if (actionItem.action.state == "todo") {
            moveItemHtml += `
		<div class="dropdown-menu" aria-labelledby="dropdownMenu2">
			<button class="dropdown-item" type="button" onclick="moveItem(${actionItem.id},'onhold')">On Hold</button>
			<button class="dropdown-item" type="button" onclick="moveItem(${actionItem.id},'completed')">Completed</button>
		</div>`;
        } else if (actionItem.action.state == "onhold") {
            moveItemHtml += `
		<div class="dropdown-menu" aria-labelledby="dropdownMenu2">
			<button class="dropdown-item" type="button" onclick="moveItem(${actionItem.id},'todo')">To Do</button>
			<button class="dropdown-item" type="button" onclick="moveItem(${actionItem.id},'completed')">Completed</button>
		</div>`;
        }

        let deleteIcon = "";

        if (actionItem.owner_id == owner_id) {
            deleteIcon = `<button data-toggle="popover" data-placement="bottom" data-content="Delete Item"
					type="button" class="ml-1 btn btn-sm box-shadow-right btn-sm-danger btn-danger"
					onclick="deleteItem(${actionItem.id})" data-original-title="" title="">
					<i class="fa fa-trash" aria-hidden="true"></i>
			      </button>`;
        }

        let actionHtml = `
		    <div class="card mt-3 actionItem " id="actionItem_${actionItem.id}">
			    <div class="d-flex justify-content-between">
			        <div class="w-25 text-left">
				        <span 
                            data-toggle="popover" 
                            data-content="${actionItem.action.priority}" 
                            title="Priority" 
                            class="badge ${priorityColor} p-2 item-priority"
				        >${actionItem.action.priority}
                        </span>

				        ${sharingImage}
				        ${moveItemHtml}
                    </div>
                    <div class="w-75 text-center">
                        <span 
                            class="text-secondary" 
                            style="font-size:20px">${actionItem.action.title}
                        </span>
                        ${acknowledgeIcon}
                    </div>
			        <div class="w-25 text-right">
                        <div class="button-group">
                            <button 
                                data-toggle="popover" 
                                data-placement="bottom" 
                                data-content="Edit Item"
                                type="button" 
                                class="ml-1 btn btn-sm box-shadow-right btn-sm-primary btn-primary"
                                onclick="editItem(${actionItem.id})">
                                    <i class="fa fa-pencil-alt" aria-hidden="true"></i>
                            </button>
                            ${deleteIcon}
                        </div>
			        </div>
			    </div>
			<div class="card-body">
			    <p class="item-description" >${actionItem.action.description}</p>
			</div>
			<div class="row p-2 text-muted">
			    <div class="col-4">
				<span class="item-footer-font"><span class="font-weight-bold">Due date:</span> ${actionItem.action.due_date}</span>
			    </div>
			    <div class="col-4 text-center">
				<div class="progress mt-2">
				    <div class="progress-bar ${progressColor}" role="progressbar" style="width: ${actionItem.action.completion}%;"
					aria-valuenow="${actionItem.action.completion}" aria-valuemin="0" aria-valuemax="100">${completionLabel}</div>
				</div>
			    </div>
			    <div class="col-4 text-right truncate">
				<span class="item-footer-font" data-toggle="popover" data-content="${responsiblePeople}">
				    <span class="font-weight-bold">Responsible${totalResponsible}:</span> ${responsiblePeople}
				</span>
			    </div>
			</div>


		    </div>
		    `;
        return actionHtml;
    }

    function acknowledgeAction(itemId) {
        const ackBtn = "btn_ack_" + itemId;
        $("#" + ackBtn).addClass('acknowledged');

        let [itemLoc, item] = getObjectFromArray(itemId, actionItems);
        console.log(item.action.ack);

        let ack_list = item.action.ack;
        if (ack_list == "") {
            ack_list = [];
        } else {
            ack_list = item.action.ack.split(',');
        }

        if (!ack_list.includes("" + owner_id)) {
            ack_list.push(owner_id);
            item.action.ack = ack_list.join(',');
            item.update_date = getCurrentDateForDB();
            item.revision_history.push(buildRevisionLog(item, "ack"));
            const data = {
                actionItem: JSON.stringify(item)
            };
            updateInDB('/actionList/update', data, 'ack');
            actionItems[itemLoc] = item;
            $('.popover').remove();
        }

    }

    function getRevisionHtml(actionItem) {
        var revision_history = actionItem.revision_history;
        let revisionHtml = `
	            <header class="text-center font-weight-bold p-2">
	                <span class="item-header text-secondary">Revision Log</span>
			<i class="fas fa-times revision-modal-close" onclick="hideRevisionModal()"></i>
	            </header>
		<div class="timeline scroll scroll-primary"> 
		    <ul class="">`;
        let revisions = "";
        for (var i = (revision_history.length - 1); i >= 0; i--) {
            let type = revision_history[i].type;
            let headColor = '';
            if (type == "moved") {
                headColor = 'text-warning';
            } else if (type == "completed") {
                headColor = 'text-success';
            } else if (type == "created") {
                headColor = 'text-danger';
            } else if (type == "ack") {
                headColor = 'text-info';
            } else {
                headColor = 'text-primary';
            }
            revisions += `
           		<li>
           		   <div class="content">
           			<h3 class="${headColor}">${revision_history[i].log}</h3>
				<p class="blockquote-footer p-0 m-0">${teamMembers[revision_history[i].who]}
				<span class="revision-time text-muted">${formatDate(revision_history[i].dateTime)}</p>
				</p>
           		   </div>
           		</li>
           	`;
        }
        revisionHtml += revisions;
        revisionHtml += `</ul></div>`;
        $('.revision-modal').html('');
        $('.revision-modal').html(revisionHtml);
    }

    function moveItem(itemId, itemState) {
        let stateLabel = "";
        let [itemLoc, item] = getObjectFromArray(itemId, actionItems);
        item.action.state = itemState;

        if (itemState == "completed") {
            item.action.completion = 100;
            item.action.completion_date = getCurrentDateForDB();
            stateLabel = "Completed";
        } else if (itemState == "todo") {
            stateLabel = "To Do";
        } else {
            stateLabel = "On Hold";
        }

        item.revision_history.push(buildRevisionLog(item, "move"));

        item.update_date = getCurrentDateForDB();

        actionItems[itemLoc] = item;

        const data = {
            actionItem: JSON.stringify(item)
        }

        updateInDB('/actionList/update', data, 'move');

    }

    function deleteItem(itemId) {
        bootbox.confirm({
            title: 'Delete',
            message: `Are you sure you want to delete?`,
            buttons: {
                cancel: {
                    label: '<i class="fa fa-times"></i> Cancel'
                },
                confirm: {
                    label: '<i class="fa fa-check"></i> Confirm'
                }
            },
            callback: function(result) {
                if (result) {
                    updateInDB('/actionList/delete', {
                        id: itemId
                    }, 'delete');
                    $('.popover').remove();
                } else {
                    console.log('Delete Cancelled');
                }
            }
        });
    }

    function secondsToDhms(created, completed) {
        created = new Date(created);
        completed = new Date(completed);
        var seconds = (completed.getTime() - created.getTime()) / 1000;
        return secondsToDuration(seconds);

    }
</script>
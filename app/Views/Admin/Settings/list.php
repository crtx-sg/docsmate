<style>
    .tab-content ul {
        list-style: none;
        counter-reset: my-awesome-counter;
    }

    .tab-content ul li {
        counter-increment: my-awesome-counter;
    }

    .tab-content .add-serial-number ul li::before {
        content: counter(my-awesome-counter) ". ";
        color: "#6c757d";
        font-weight: bold;
    }

    .nav-link.active {
        color: #f8f9fa !important;
        background-color: #6c757d !important;
    }
</style>


<div class="row  justify-content-center  p-0 p-md-8">
    <div class="col-12  col-md-12 col-lg-9">
        <div class="card  mt-4">
            <ul class="nav nav-tabs nav-justified" id="myTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link lead" id="product-tab" data-toggle="tab" href="#product" role="tab"
                        aria-controls="product" aria-selected="false">Products</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active lead" id="enum-tab" data-toggle="tab" href="#enum" role="tab"
                        aria-controls="enum" aria-selected="true">Enums</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link lead" id="config-tab" data-toggle="tab" href="#config" role="tab"
                        aria-controls="config" aria-selected="false">Config</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link lead" id="document-tab" data-toggle="tab" href="#document" role="tab"
                        aria-controls="document" aria-selected="false">Document Properties</a>
                </li>

            </ul>
            <div class="tab-content  p-md-4 p-2" id="myTabContent">
                <div class="tab-pane fade  show active add-serial-number" id="enum" role="tabpanel" aria-labelledby="enum-tab">
                    <div class="row">
                        <div class="col-12" id="enumAlert">
                            <div class="alertDiv"></div>
                        </div>
                        <div class="col-12 col-sm-4 ">
                            <div class="list-group" id="list-tab" role="tablist">
                                <a class="list-group-item list-group-item-action active" id="document-status-list"
                                    data-toggle="list" href="#documentStatusList" role="tab"
                                    aria-controls="profile">Document Status</a>

                                <a class="list-group-item list-group-item-action" id="reference-category-list"
                                    data-toggle="list" href="#referenceCategoryList" role="tab"
                                    aria-controls="profile">Reference Types</a>

                                <a class="list-group-item list-group-item-action" id="review-category-list"
                                    data-toggle="list" href="#reviewCategoryList" role="tab"
                                    aria-controls="profile">Review Types</a>

                                <a class="list-group-item list-group-item-action" id="requirement-category-list"
                                    data-toggle="list" href="#requirementCategoryList" role="tab"
                                    aria-controls="profile">Requirement Types</a>
                                
                                    <a class="list-group-item list-group-item-action" id="assets-category-list"
                                    data-toggle="list" href="#assetsCategoryList" role="tab"
                                    aria-controls="profile">Assets Types</a>

                                <!-- <a class="list-group-item list-group-item-action" id="risk-category-list"
                                    data-toggle="list" href="#riskCategoryList" role="tab"
                                    aria-controls="profile">Risk Category</a> -->

                                <a class="list-group-item list-group-item-action" id="risk-methodology-list"
                                    data-toggle="list" href="#riskMethodologyList" role="tab"
                                    aria-controls="profile">Risk Methodology</a>

                                <a class="list-group-item list-group-item-action " id="template-category-list"
                                    data-toggle="list" href="#templateCategoryList" role="tab"
                                    aria-controls="home">Template Types</a>

                                <a class="list-group-item list-group-item-action" id="user-role-list"
                                    data-toggle="list" href="#userRoleList" role="tab"
                                    aria-controls="profile">User Role</a>

                                <a class="list-group-item list-group-item-action" id="task-category-list"
                                    data-toggle="list" href="#taskCategoryList" role="tab"
                                    aria-controls="profile">Task Types</a>

                                <a class="list-group-item list-group-item-action" id="task-type-list"
                                    data-toggle="list" href="#taskTypeList" role="tab"
                                    aria-controls="profile">Task Status</a>
                                

                                <a class="list-group-item list-group-item-action" id="time-tracker-list"
                                    data-toggle="list" href="#timeTrackerList" role="tab"
                                    aria-controls="profile">Timesheet Tasks</a>
                            </div>
                        </div>
                        <div class="col-12 col-sm-8 pt-4 pt-sm-0">
                            <div class="tab-content" id="nav-tabContent">

                                <div class="tab-pane fade show active" id="documentStatusList" role="tabpanel"
                                    aria-labelledby="document-status-list">
                                    
                                    <div class="row ">
                                        <div class="col-9">
                                            <ul class="list-group documentStatus"></ul>
                                        </div>
                                        <div class="col-3">
                                            <button class="btn btn-primary"
                                                onclick="addValue('Document Status', 'documentStatus')">Add</button>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="referenceCategoryList" role="tabpanel"
                                    aria-labelledby="reference-category-list">
                                    
                                    <div class="row ">
                                        <div class="col-9">
                                            <ul class="list-group referenceCategory"></ul>
                                        </div>
                                        <div class="col-3">
                                            <button class="btn btn-primary"
                                                onclick="addValue('Reference Category', 'referenceCategory')">Add</button>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="reviewCategoryList" role="tabpanel"
                                    aria-labelledby="review-category-list">
                                    
                                    <div class="row ">
                                        <div class="col-9">
                                            <ul class="list-group reviewCategory"></ul>
                                        </div>
                                        <div class="col-3">
                                            <button class="btn btn-primary"
                                                onclick="addValue('Review Category', 'reviewCategory')">Add</button>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="requirementCategoryList" role="tabpanel"
                                    aria-labelledby="requirement-category-list">
                                    
                                    <div class="row ">
                                        <div class="col-9">
                                            <ul class="list-group requirementsCategory"></ul>
                                        </div>
                                        <div class="col-3">
                                            <button class="btn btn-primary"
                                                onclick="addValue('Requirement Category', 'requirementsCategory')">Add</button>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="assetsCategoryList" role="tabpanel"
                                    aria-labelledby="assets-category-list">
                                    
                                    <div class="row ">
                                        <div class="col-9">
                                            <ul class="list-group assetsCategory"></ul>
                                        </div>
                                        <div class="col-3">
                                            <button class="btn btn-primary"
                                                onclick="addValue('Assets Category', 'assetsCategory')">Add</button>
                                        </div>
                                    </div>
                                </div>

                                <!-- <div class="tab-pane fade" id="riskCategoryList" role="tabpanel"
                                    aria-labelledby="risk-category-list">
                                    
                                    <div class="row ">
                                        <div class="col-9">
                                            <ul class="list-group riskCategory"></ul>
                                        </div>
                                        <div class="col-3">
                                            <button class="btn btn-primary"
                                                onclick="addValue('Risk Category', 'riskCategory')">Add</button>
                                        </div>
                                    </div>
                                </div> -->

                                <div class="tab-pane fade  " id="templateCategoryList" role="tabpanel"
                                    aria-labelledby="template-category-list">
                                    
                                    <div class="row ">
                                        <div class="col-9">
                                            <ul class="list-group templateCategory"></ul>
                                        </div>
                                        <div class="col-3">
                                            <button class="btn btn-primary"
                                                onclick="addValue('Template Category', 'templateCategory')">Add</button>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="userRoleList" role="tabpanel"
                                    aria-labelledby="review-category-list">
                                    
                                    <div class="row ">
                                        <div class="col-9">
                                            <ul class="list-group userRole"></ul>
                                        </div>
                                        <div class="col-3">
                                            <button class="btn btn-primary"
                                                onclick="addValue('User Role', 'userRole')">Add</button>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="taskCategoryList" role="tabpanel"
                                    aria-labelledby="task-category-list">
                                    
                                    <div class="row">
                                        <div class="col-9">
                                            <ul class="list-group taskCategory"></ul>
                                        </div>
                                        <div class="col-3">
                                            <button class="btn btn-primary"
                                                onclick="addValue('Task Category', 'taskCategory')">Add</button>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="taskTypeList" role="tabpanel"
                                    aria-labelledby="task-type-list">
                                    
                                    <div class="row">
                                        <div class="col-9">
                                            <ul class="list-group taskType"></ul>
                                        </div>
                                        <div class="col-3">
                                            <button class="btn btn-primary"
                                                onclick="addValue('Task Type', 'taskType')">Add</button>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="timeTrackerList" role="tabpanel"
                                    aria-labelledby="time-tracker-list">
                                    
                                    <div class="row ">
                                        <div class="col-9">
                                            <ul class="list-group timeTrackerCategory"></ul>
                                        </div>
                                        <div class="col-3">
                                            <button class="btn btn-primary"
                                                onclick="addValue('Time Tracker Category', 'timeTrackerCategory')">Add</button>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="riskMethodologyList" role="tabpanel"
                                    aria-labelledby="risk-methodology-list">
                                    
                                    <div class="row ">
                                        <div class="col-9">
                                            <ul class="list-group riskMethodologyCategory"></ul>
                                        </div>
                                        <div class="col-3">
                                            <button class="btn btn-primary"
                                                onclick="addValue('Risk Methodology Category', 'riskMethodologyCategory')">Add</button>
                                        </div>
                                    </div>
                                </div>


                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade add-serial-number" id="config" role="tabpanel" aria-labelledby="config-tab">
                    <div class="alertDiv"></div>
                    <div class="input-group-text" style="display:flow-root">Sonarqube</div>&nbsp;
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text" >URL</span>
                        </div>
                        <input type="text" class="form-control" id="sonar" >&nbsp;

                        <div class="input-group-prepend">
                            <span class="input-group-text" >Key</span>
                        </div>
                        <input type="text" class="form-control" id="apiKey-sonar" >                                              
                    </div>
                    <div class="input-group-text" style="display:flow-root">TestLink</div>&nbsp;
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text" >URL</span>
                        </div>
                        <input type="text" class="form-control" id="testLink" >&nbsp;

                        <div class="input-group-prepend">
                            <span class="input-group-text" >Key</span>
                        </div>
                        <input type="text" class="form-control" id="apiKey-testLink" >                                             
                    </div>
                    <div class="input-group-text" style="display:flow-root">Jenkins</div>&nbsp;
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text" >URL</span>
                        </div>
                        <input type="text" class="form-control" id="jenkins" >&nbsp;  

                        <div class="input-group-prepend">
                            <span class="input-group-text" >Job Name</span>
                        </div>
                        <input type="text" class="form-control" id="job-jenkins" >&nbsp;                                             
                    </div>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text" >Username</span>
                        </div>
                        <input type="text" class="form-control" id="user-jenkins" >&nbsp;  

                        <div class="input-group-prepend">
                            <span class="input-group-text">Key</span>
                        </div>
                        <input type="text" class="form-control" id="apiKey-jenkins" >&nbsp;
                        
                    </div>
                    <div class="input-group mb-3">
                    <div class="input-group-prepend">
                            <span class="input-group-text" >FileManagerUrl</span>
                        </div>
                        <input type="text" class="form-control" id="fileManagerUrl-jenkins" >&nbsp;
                    </div>
                    <div class="input-group-text" style="display:flow-root">General</div>&nbsp;
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text" >Daily Build Live Url </span>
                        </div>
                        <input type="text" class="form-control" id="liveUrl-generic" >&nbsp;  
                    </div>

                    <div class="row">
                        <div class="col text-center">
                            <button class="btn btn-primary" onclick="saveConfig()">Save</button>
                        </div>
                    </div>
                   
                </div>
                <div class="tab-pane fade" id="document" role="tabpanel" aria-labelledby="document-tab">
                    <div class="alertDiv"></div>
                    <div class="input-group-text" style="display:flow-root">Header Section</div>&nbsp;
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text" >Title</span>
                        </div>
                        <input type="text" class="form-control" id="docTitle" >
                    </div>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text" >Icon</span>
                        </div>
                        <input type="text" class="form-control" id="docIcon" >
                    </div>

                    <div class="input-group-text" style="display:flow-root">Footer Section</div>&nbsp;
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text" >Text</span>
                        </div>
                        <input type="text" class="form-control" id="docConfidential" >  
                        <i class="fa fa-info-circle" style="font-size:25px; padding-top:5px; color:#FFFFF" title="Add footer, version text differentiate with semicolon. ex: Company;QFT Rev C"></i>
                        <div class="input-group-prepend">
                            <span class="input-group-text" >Include Page Number</span>
                        </div>
                        <select class="form-control" id="docPageNums" data-style="btn-secondary">
                        <option value="" disabled>
                                        Select
                                    </option>
                                    <?php foreach ($pageNumbersToggle as $pageNos): ?>
                                    <option
                                        <?= json_decode($propertiesData[0]['options'], true)[3]["value"] != null ? (((json_decode($propertiesData[0]['options'], true)[3]["value"]) == $pageNos) ? 'selected': '') : '' ?>
                                        value="<?=  $pageNos ?>"><?=  $pageNos ?></option>
                                    <?php endforeach; ?>
                        </select>                                      
                    </div>

                    <div class="input-group-text" style="display:flow-root">Docs Download Permissions</div>&nbsp;
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Users</span>
                        </div>
                        <select class="form-control  selectpicker" data-live-search="true" multiple id="docsDownloadPermissions">
                            <?php foreach ($teamMembers as $key=>$value): ?>
                            <option 
				                <?= json_decode($propertiesData[0]['options'], true)[4]["value"] != null ? (in_array("$key",json_decode($propertiesData[0]['options'], true)[4]["value"]) ? 'selected' : '') : '' ?>
                                value="<?=  $key ?>"><?=  $value ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col text-center">
                            <button class="btn btn-primary" onclick="saveDocx()">Save</button>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="product" role="tabpanel" aria-labelledby="product-tab">
                <div class="row">
                <div class="col-md-6">
                    <a href="/settings/addProduct" id="add-product" class="btn btn-primary float-end">Add</a>
                </div>

                <div class="col-md-6">
                <label class="font-weight-bold text-muted">Status</label>
                <div class="btn-group btn-group-toggle">
                    <?php foreach ($productStatus as $proStatus): ?>
                    <?php
                        $statusId = str_replace(' ', '_', $proStatus);
                        $selected = ($selectedStatus == $proStatus) ? true : false;
                        //$statusCount = (isset($reviewsCount[$revStatus])) ? $reviewsCount[$revStatus] : 0;
                    ?>
                    <label class="lbl_<?= $statusId ?> btn <?=($selected ? " btn-primary" : "btn-secondary")?>">
                        <input type="radio" name="view" value="<?=$proStatus?>" autocomplete="off"
                            onclick="getTableRecords()" <?=($selected ? "checked" : "")?>> <?=$proStatus?>
                    </label>
                    <?php endforeach;?>
                </div>

            </div>
            </div>

                <br/>
                    <div class="row">
                        <div class="col-12" id="productAlert">
                            <div class="alertDiv"></div>
                        </div>
                        <div class="col-12">
                            <table class="table  table-hover" id="product-list">
                                <thead class="thead-dark">
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Name</th>
                                        <th scope="col">Display Name</th>
                                        <th scope="col">Description</th>
                                        <th scope="col">Status</th>
                                        <th scope="col" style="width:90px">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white " id="tbody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>


            </div>
            
        </div>

    </div>
</div>


<script>
    // Global Variables
    var dropdownData, configData, propertiesData;
    var productStatus, table = null, pageNumbersToggle = null;

    
    $(document).ready(function () {
        productStatus = <?= json_encode($productStatus) ?>;

        pageNumbersToggle = <?= json_encode($pageNumbersToggle) ?>;

        table = initializeDataTable('product-list');
        getTableRecords();

        dropdownData = <?= json_encode($dropdownData) ?>;
        configData = <?= json_encode($configData)?>;
        propertiesData = <?= json_encode($propertiesData)?>;
        
        for (var i = 0; i < dropdownData.length; i++) {
            const identifier = dropdownData[i].identifier;
            const options = JSON.parse(dropdownData[i].options);

            if (options == null) {
                showAlert("alert-warning", "No records found.", "enumAlert");
            } else {
                for (var j = 0; j < options.length; j++) {
                    if(identifier == 'requirementsCategory' || identifier == 'taskCategory' || identifier == 'taskType' || identifier == 'timeTrackerCategory' || identifier == 'riskMethodologyCategory'){
                        addItemToList(options[j], identifier);
                    }else{
                        addItemToList(options[j].value, identifier);
                    }
                }
            }
        }

        for (var i = 0; i < configData.length; i++) {
            const identifier = configData[i].identifier;
            const options = JSON.parse(configData[i].options);
                for (var j = 0; j < options.length; j++) {
                    if (options != null) {
                        $("#"+options[j].key).val(options[j].url);
                        $("#apiKey-"+options[j].key).val(options[j].apiKey);
                        if(options[j].key == "jenkins"){
                            $("#user-"+options[j].key).val(options[j].user);
                            $("#job-"+options[j].key).val(options[j].job);
                            $("#fileManagerUrl-"+options[j].key).val(options[j].fileManagerUrl);
                        }
                        if(options[j].key == "generic"){
                            $("#liveUrl-"+options[j].key).val(options[j].dailyBuildLiveUrl);
                        }
                    }
                }
        }

        for (var i = 0; i < propertiesData.length; i++) {
            const identifier = propertiesData[i].identifier;
            const options = JSON.parse(propertiesData[i].options);
            for (var j = 0; j < options.length; j++) {
                if (options != null) {
                    $("#"+options[j].key).val(options[j].value);
                }
            }
        }

    });


    function getTableRecords() {
    const selectedView = $("input[name='view']:checked").val();
    var url = `/settings/getProducts?view=${selectedView}`;
    //var url = `/settings/getProducts?view=Active`;

    $("#addButton").attr("href", `/settings/addProduct`);

    $(".btn-group label").removeClass("btn-primary").addClass("btn-secondary");
    $(`.lbl_${selectedView.replace(/\s/g, '_')}`).removeClass("btn-secondary").addClass("btn-primary");

    makeRequest(url)
        .then((response) => {
            const productsList = response.products;
            populateTable(productsList);
        })
        .catch((err) => {
            console.log(err);
            showPopUp('Error', "An unexpected error occured on server.");
        })

    }

    function populateTable(productList) {
        dataInfo = {
            "rowId": 'product-id',
            "requiredFields": ['name', 'display-name', 'description', 'status'],
           "dateFields": ['updated-at'],
            "action": [{
                    title: "Edit",
                    buttonClass: "btn btn-warning",
                    iconClass: "fa fa-edit",
                    clickTrigger: "edit",
                    clickParams: ['product-id']
                },
                {
                    title: "Delete",
                    buttonClass: "btn btn-danger",
                    iconClass: "fa fa-trash",
                    clickTrigger: "deleteProduct",
                    clickParams: ['product-id'],
                    // condition: {
                    //     on: 'assigned-to',
                    //     with: userId
                    // }
                }
            ]
        };

        if (productList.length) {
            table.destroy();
        }

        $('#tbody').html("");
        var data = getHTMLtable(productList, dataInfo);
        $('#tbody').append(data);

        if (productList.length) {
            table = initializeDataTable('product-list');
        }

    }

    function edit(id) {
        location.href = `/settings/addProduct/${id}`;
    }

    function deleteProduct(productId) {

        bootbox.confirm("Do you really want to delete the Product?", function(result) {
            if (result) {
                $.ajax({
                    url: '/settings/deleteProduct/' + productId,
                    type: 'GET',
                    success: function(response) {
                        response = JSON.parse(response);
                        if (response.success == "True") {
                            $("#" + productId).fadeOut(800)
                        } else {
                            bootbox.alert('Product not deleted.');
                        }
                    }
                });
            } else {
                console.log('Delete Cancelled');
            }

        });
    }

    function addRootRequirement(id, val) {
        var domId, isChecked,object;
        domId = '#isRoot'+id;
        isChecked = $(domId).prop('checked');
        object = {'key': id, 'value': val, 'isRoot': isChecked};
        $.ajax({
            type: 'POST',
            url: '/admin/settings/updateRequirementValues',
            data: object,
            dataType: 'json',
            success: function (response) {
                if (response.success == "True") {
                    console.log("added root requirement");
                } else {
                    showAlert("alert-danger", "Failed to add a new item.", "enumAlert");
                }
            },
            error: function (err) {
                console.log(err);
            }
        });
    }

    function addRootTask(id, val) {
        var domId, isChecked,object;
        domId = '#isRootTaskCat'+id;
        isChecked = $(domId).prop('checked');
        object = {'key': id, 'value': val, 'isRoot': isChecked};
        $.ajax({
            type: 'POST',
            url: '/admin/settings/updateTaskValues',
            data: object,
            dataType: 'json',
            success: function (response) {
                if (response.success == "True") {
                    console.log("added root Task Category");
                    showAlert("alert-success", "Successfully added Task Category to list", "enumAlert");
                } else {
                    showAlert("alert-danger", "Failed to add a new Task Category.", "enumAlert");
                }
            },
            error: function (err) {
                console.log(err);
            }
        });
    }

    function addRootTaskType(id, val) {
        var domId, isChecked,object;
        domId = '#isRootTaskType'+id;
        isChecked = $(domId).prop('checked');
        object = {'key': id, 'value': val, 'isRoot': isChecked};
        $.ajax({
            type: 'POST',
            url: '/admin/settings/updateTaskTypeValues',
            data: object,
            dataType: 'json',
            success: function (response) {
                if (response.success == "True") {
                    console.log("added root Task Type");
                    showAlert("alert-success", "Successfully added Task Type to list", "enumAlert");
                } else {
                    showAlert("alert-danger", "Failed to add a new Task type.", "enumAlert");
                }
            },
            error: function (err) {
                console.log(err);
            }
        });
    }

    function addRootTimeTrackerCategory(id, val) {
        var domId, isChecked,object;
        domId = '#isRootTimeTrackerCategory'+id;
        isChecked = $(domId).prop('checked');
        object = {'key': id, 'value': val, 'isRoot': isChecked};
        $.ajax({
            type: 'POST',
            url: '/admin/settings/updateTimeTrackerValues',
            data: object,
            dataType: 'json',
            success: function (response) {
                if (response.success == "True") {
                    console.log("added root Task Type");
                    showAlert("alert-success", "Successfully added Time Tracker to list", "enumAlert");
                } else {
                    showAlert("alert-danger", "Failed to add a new Time Tracker Category.", "enumAlert");
                }
            },
            error: function (err) {
                console.log(err);
            }
        });
    }

    function addRootRiskMethodologyCategory(id, val) {
        var domId, isChecked,object;
        domId = '#isRootRiskMethodologyCategory'+id;
        isChecked = $(domId).prop('checked');
        object = {'key': id, 'value': val, 'isRoot': isChecked};
        $.ajax({
            type: 'POST',
            url: '/admin/settings/updateRiskMethodologyValues',
            data: object,
            dataType: 'json',
            success: function (response) {
                if (response.success == "True") {
                    //console.log("added root Task Type");
                    showAlert("alert-success", "Successfully added Risk Methodology to list", "enumAlert");
                } else {
                    showAlert("alert-danger", "Failed to add a new Risk Method Category.", "enumAlert");
                }
            },
            error: function (err) {
                console.log(err);
            }
        });
    }

    function addItemToList(value, listClass) {
        var listCss = "list-group-item list-group-item-action", checked,val,newItem;
        if(listClass == 'requirementsCategory'){
            checked = (value.isRoot == true) ? "checked = true" : "";
            val = value.value.replace( /^\s+|\s+$/g, '' );
            newItem = '<li class="' + listCss + '">' + val + ' &nbsp;&nbsp;<input id="isRoot'+value.key+'" type="checkbox" '+ checked +' title="Add to root traceability" onclick="addRootRequirement('+value.key+",'"+val+"'"+')"/></li>';
        }
        else if(listClass == 'taskCategory'){
            checked = (value.isRoot == true) ? "checked = true" : "";
            val = value.value.replace( /^\s+|\s+$/g, '' );
            newItem = '<li class="' + listCss + '">' + val + ' &nbsp;&nbsp;<input id="isRootTaskCat'+value.key+'" type="checkbox" '+ checked +' title="Add to TaskBoard Category" onclick="addRootTask('+value.key+",'"+val+"'"+')"/></li>';
        }
        else if(listClass == 'taskType'){
            checked = (value.isRoot == true) ? "checked = true" : "";
            val = value.value.replace( /^\s+|\s+$/g, '' );
            newItem = '<li class="' + listCss + '">' + val + ' &nbsp;&nbsp;<input id="isRootTaskType'+value.key+'" type="checkbox" '+ checked +' title="Add to TaskBoard Type" onclick="addRootTaskType('+value.key+",'"+val+"'"+')"/></li>';
        }
        else if(listClass == 'timeTrackerCategory'){
            checked = (value.isRoot == true) ? "checked = true" : "";
            val = value.value.replace( /^\s+|\s+$/g, '' );
            newItem = '<li class="' + listCss + '">' + val + ' &nbsp;&nbsp;<input id="isRootTimeTrackerCategory'+value.key+'" type="checkbox" '+ checked +' title="Add to TimeTracker Category" onclick="addRootTimeTrackerCategory('+value.key+",'"+val+"'"+')"/></li>';
        }
        else if(listClass == 'riskMethodologyCategory'){
            checked = (value.isRoot == true) ? "checked = true" : "";
            val = value.value.replace( /^\s+|\s+$/g, '' );
            newItem = '<li class="' + listCss + '">' + val + ' &nbsp;&nbsp;<input id="isRootRiskMethodologyCategory'+value.key+'" type="checkbox" '+ checked +' title="Add to Risk Methodology Category" onclick="addRootRiskMethodologyCategory('+value.key+",'"+val+"'"+')"/></li>';
        }
        else{
            newItem = '<li class="' + listCss + '">' + value + '</li>';
        }
        $("." + listClass).append(newItem);
    }


    function addValue(listName, listClass) {
        var title = "Add " + listName;
        bootbox.prompt({
            title: title,
            centerVertical: true,
            callback: function (result) {
                if (result != null && result != "") {
                    result = result.trim();
                    var dataIndex = dropdownData.findIndex(x => x.identifier === listClass);
                    var existingOptions = JSON.parse(dropdownData[dataIndex]["options"])

                    var newKey = getMax(existingOptions, "key") + 1;
                    var newItem = {
                        key: newKey,
                        value: result.replace(" ","")
                    };
                    if(listClass == 'requirementsCategory' || listClass == 'taskCategory' || listClass == 'taskType' || listClass == 'timeTrackerCategory' || listClass == 'riskMethodologyCategory'){
                        newItem['isRoot'] =  false;
                    }

                    if (existingOptions == null) existingOptions = [];
                    existingOptions.push(newItem);
                    var updatedOptions = JSON.stringify(existingOptions);


                    var object = {
                        id: dropdownData[dataIndex].id,
                        identifier: listClass,
                        options: updatedOptions
                    };

                    saveToDB(object, listName, listClass, dataIndex, updatedOptions, result);
                }

            }
        });
    }

    function saveToDB(object, title, listClass, dataIndex, updatedOptions, newItem) {
        const successMsg = title + " added successfully.";
        $.ajax({
            type: 'POST',
            url: '/admin/settings/addEnums',
            data: object,
            dataType: 'json',
            success: function (response) {
                console.log(response);
                if (response.success == "True") {
                    dropdownData[dataIndex]["options"] = updatedOptions;
                    if(listClass == 'requirementsCategory' || listClass == 'taskCategory' || listClass == 'taskType' || listClass == 'timeTrackerCategory' || listClass == 'riskMethodologyCategory'){
                        var newData = JSON.parse(updatedOptions);
                        addItemToList(newData[newData.length-1], listClass);                        
                    }else{
                        addItemToList(newItem, listClass);
                    }
                    showAlert("alert-success", successMsg, "enumAlert");
                } else {
                    showAlert("alert-danger", "Failed to add a new item.", "enumAlert");
                }
            },
            error: function (err) {
                console.log(err);
            }
        });

    }

    function saveConfig(){
        var identifier = "third-party";
        var sonarUrl = $("#sonar").val();
        var testUrl = $("#testLink").val();
        var sonarUrlKey = $("#apiKey-sonar").val();
        var testUrlKey = $("#apiKey-testLink").val();
        var jenkinsUrl = $("#jenkins").val();
        var jenkinsKey = $("#apiKey-jenkins").val();
        var jenkinsUser = $("#user-jenkins").val();
        var jenkinsJob = $("#job-jenkins").val();
        var jenkinsFileManagerUrl = $("#fileManagerUrl-jenkins").val();
        var dailyBuildLiveUrl = $("#liveUrl-generic").val();

        if(sonarUrl  != "" || testUrl != ""){
            var configIndex = configData.findIndex(x => x.identifier === identifier);
            var existingOptions = JSON.parse(configData[configIndex]["options"]);

            for(var z = 0 ; z< existingOptions.length ; z++){
                if(existingOptions[z]["key"] == "sonar"){
                    existingOptions[z]["url"] = sonarUrl;
                    existingOptions[z]["apiKey"] = sonarUrlKey;
                }
                if(existingOptions[z]["key"] == "testLink"){
                    existingOptions[z]["url"] = testUrl;
                    existingOptions[z]["apiKey"] = testUrlKey;
                }
                if(existingOptions[z]["key"] == "jenkins"){
                    existingOptions[z]["url"] = jenkinsUrl;
                    existingOptions[z]["apiKey"] = jenkinsKey;
                    existingOptions[z]["user"] = jenkinsUser;
                    existingOptions[z]["job"] = jenkinsJob;
                    existingOptions[z]["fileManagerUrl"] = jenkinsFileManagerUrl;
                }

                if(existingOptions[z]["key"] == "generic"){
                    existingOptions[z]["dailyBuildLiveUrl"] = dailyBuildLiveUrl;
                }
            }
            
            var updatedOptions = JSON.stringify(existingOptions);
            configData[configIndex]["options"] = updatedOptions;
            
            var object = {
                id: configData[configIndex].id,
                identifier: identifier,
                options: updatedOptions
            };
            
            $.ajax({
                type: 'POST',
                url: '/admin/settings/addEnums',
                data: object,
                dataType: 'json',
                success: function (response) {
                    console.log(response);
                    if (response.success == "True") {
                        showAlert("alert-success", "Configuration saved successfully.", 'config');
                    } else {
                        showAlert("alert-danger", "Failed to save config.", 'config');
                    }
                },
                error: function (err) {
                    console.log(err);
                }
            });


        }
        
    }

    function saveDocx(){
        var identifier, docTitle, docIcon, docConfidential, docPageNums, propertiesIndex, existingOptions;
        identifier = "documentProperties";
        docTitle = $("#docTitle").val();
        docIcon = $("#docIcon").val();
        docConfidential = $("#docConfidential").val();
        docPageNums = $("#docPageNums").val();
        docsDownloadPermsData = $("#docsDownloadPermissions").val();
        propertiesIndex = propertiesData.findIndex(x => x.identifier === identifier);
        existingOptions = JSON.parse(propertiesData[propertiesIndex]["options"]);

        var isDocsDownloadPermsExist = false;
        for(var z = 0 ; z< existingOptions.length ; z++){
            if(existingOptions[z]["key"] == "docTitle"){
                existingOptions[z]["value"] = docTitle;
            }
            if(existingOptions[z]["key"] == "docIcon"){
                existingOptions[z]["value"] = docIcon;
            }
            if(existingOptions[z]["key"] == "docConfidential"){
                existingOptions[z]["value"] = docConfidential;
            }
            if(existingOptions[z]["key"] == "docPageNums"){
                existingOptions[z]["value"] = docPageNums;
            }
            if(existingOptions[z]["key"] == "docsDownloadPermissions"){
                existingOptions[z]["value"] = docsDownloadPermsData;
                isDocsDownloadPermsExist = true;
            }
        }
        if(! isDocsDownloadPermsExist){
            existingOptions[existingOptions.length] = {
                "key": "docsDownloadPermissions",
                "value": docsDownloadPermsData
            }
        }
        var updatedOptions = JSON.stringify(existingOptions);
        propertiesData[propertiesIndex]["options"] = updatedOptions;
        
        var object = {
            id: propertiesData[propertiesIndex].id,
            identifier: identifier,
            options: updatedOptions
        };
        
        $.ajax({
            type: 'POST',
            url: '/admin/settings/addEnums',
            data: object,
            dataType: 'json',
            success: function (response) {
                console.log(response);
                if (response.success == "True") {
                    showAlert("alert-success", "Properties saved successfully.", 'document');
                } else {
                    showAlert("alert-danger", "Failed to save Properties.", 'document');
                }
            },
            error: function (err) {
                console.log(err);
            }
        });
    }
    
    function showAlert(type, message, parentId) {
        // alert-warning or alert-success or alert-danger
        const html = `<div class="alert ${type} alert-dismissible fade show" role="alert">
                            ${message}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">×</span>
                        </button>
                      </div>`;
        
        $("#" + parentId + " .alertDiv").html(html);

    }

    function getMax(arr, prop) {
        if (arr == null) {
            return -1;
        }
        var max;
        for (var i = 0; i < arr.length; i++) {
            var compare = arr[i][prop];
            if (max == null || parseInt(compare) > parseInt(max))
                max = compare;
        }
        return max;
    }
</script>

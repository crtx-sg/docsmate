<style>
.breadcrumb {
    background-color: #fff;
}
.breadcrumb-item {
    font-size: 24px;
}

.arr-right .breadcrumb-item+.breadcrumb-item::before {
    content: "›";
    vertical-align: top;
    font-size: 45px;
    line-height: 18px;
    margin-top: 4px;
    color: #86befa;
}

button[data-id="selectedUserId"]{
    height: 32px;
    margin-top: -6px;
    padding-top: 4px;
    padding-bottom: 4px;
    padding-left: 8px;
    padding-right: 8px;
}

.tasks-column {
    padding-left: 5px;
    padding-right: 5px;
    min-width: 20% !important;
}

.task-parent {
    max-height: 83vh;
    overflow-y: auto;
    padding: 0.3rem;
    min-height: 83vh;
    background-color: #eff4f7;;
    border-color: 1px solid #eff4f7;;
}

/* task styles */
.newTask {
    box-shadow: 0px 1px 22px -12px #607D8B;
    border-radius: 8px;
}

.newTask:hover {
    box-shadow: rgba(19, 19, 19, 0.6) 0px 0.35em 1.175em, rgba(19, 19, 19, 0.2) 0px 0.175em 0.5em;
    transform: translateY(-3px) scale(1.05);
    transition: transform 1000ms;
}

.truncate {
    width: 116px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}


.newTask_textarea {
    height: 110px;
}

/* Draggable style */
.ui-helper {
    width: 100% !important;
}


/* Comment Styles */
.counter.counter-lg {
    font-size: 10px;
    color: #f8f9fa;
    font-weight: bold;
    text-align: center;
    padding-top: 1px;
}

.sec {
    position: relative;
    top: -14px;
    right: -4px;
}

.dot {
    height: 16px;
    width: 15px;
    background-color: #cc650f;
    border-radius: 50%;
    display: inline-block;
    margin-left: -15px;
}

.modal-lg {
    max-width: 80vw;
}


/* Chart styles */
.box {
    box-shadow: 0px 1px 22px -12px #607D8B;
    background-color: #fff;
    padding: 25px 35px 25px 30px;
    border-radius:8px;
}

.chart-title h5 {
    font-size: 18px;
    color: rgba(51, 51, 51, 1);
    margin-bottom: 38px;
}
.hidden{
    display: none;
}
</style>

<div class="fluid-container">
    <nav aria-label="breadcrumb" class="fixed-top" style="margin-left:80px;">
        <ol class="breadcrumb  arr-right" style="margin:0px;">
            <li class="breadcrumb-item text-primary" aria-current="page"> Taskboard </li>
            <li class="breadcrumb-item active" aria-current="page"><?= $title ?></li>
            <li class="breadcrumb-item active" aria-current="page">
                <input type="checkbox" id="selectedView" checked data-size="small"
                    data-on="<i class='fas fa-list-ul'></i>&nbsp;&nbsp;List" data-off="<i class='fas fa-chart-line'></i>&nbsp;&nbsp;Graph&nbsp;"
                    data-toggle="toggle" data-onstyle="outline-secondary"  data-offstyle="outline-primary">
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                <?php $userId = session()->get('id'); ?>
                <select class="selectpicker" id="selectedUserId" onchange="getTasks()" data-live-search="true" data-style="btn-outline-secondary"
                    data-size="8" data-width="fit" data-header="Select user">
                    <option data-icon="fas fa-users" value="ALL" selected>
                        All
                    </option>
                    <?php foreach ($teamMembers as $key=>$value): ?>
                    <?php $selected = ($userId == $key) ? "selected" : ""; ?>
                    <option data-icon="far fa-user" value='<?=  $key ?>' <?= $selected ?>>
                        <?=  $value ?> </option>
                    <?php endforeach; ?>
                </select>
            </li>
            <li class="breadcrumb-item active">
                <a class="btn btn-success text-light" href="/taskboard/createExcel/<?php echo $project_id;?>" title="Export Tasks">
                <i class="fa fa-file-excel"></i>
                 </a>

            </li>
            <li class="ml-auto">
                <a class="btn btn-secondary text-light" href="/projects">
                    <i class="fa fa-chevron-left"></i> Back
                </a>

            </li>
        </ol>

    </nav>
    <div class="pt-4 graphView hidden pb-4" style="background-color: #eff4f7;min-height:100vh">
        <div class="row justify-content-center mb-4">

            <div class="col-12 col-sm-5 ">
                <div class="box">
                    <div id="developerChart"><span>No data found.</span></div>
                </div>
            </div>

            <div class="col-12 col-sm-5">
                <div class="box">
                    <div id="verifierChart"><span>No data found.</span></div>
                </div>
            </div>
        </div>
        <div class="row justify-content-center mb-2">
            <div class="col-12 col-sm-5">
                <div class="box">
                    <div id="columnPie"><span>No data found.</span></div>
                </div>
            </div>
            <div class="col-12 col-sm-5">
                <div class="box">
                    <div id="categoryPie"><span>No data found.</span></div>
                </div>
            </div>
        </div>
    </div> 
    <div class="row listView" style="padding-left:1rem;padding-right:1rem; position:fixed; top:65px;">
    <?php 
    $bgColorsArr = ["bg-dark", "bg-info", "bg-primary", "bg-success", "bg-warning", "bg-secondary"];
    foreach($taskTypes as $key => $val) {
        $taskTyp =$val['value'];
        $bgColor = $bgColorsArr[$key];
   ?>
        <div class="col-12 col-md-2 tasks-column">

            <div class="card">
                <div class="card-header <?= $bgColor ?> text-light">
                    <?= $taskTyp; ?>
                    <span class="stats_<?= $taskTyp ?> badge badge-light float-right mt-1">0</span>
                </div>

                <div class="card-body task-parent scroll scroll-dark" id="column_<?=  $taskTyp ?>">
                    <div class="input-group">
                        <input type="text" class="form-control" name="column_<?=  $taskTyp ?>_title" id="column_<?=  $taskTyp ?>_title"
                            placeholder="Quick Add - Title Only" />
                        <div class="ml-2">
                            <button class="btn btn-outline-dark" data-toggle="popover" data-placement="bottom"
                                data-content="Add Task"
                                onclick="addTask('<?=  $taskTyp ?>', document.getElementById('column_<?=  $taskTyp ?>_title').value)">
                                <i class="fas fa-plus "></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>
        <!-- <div class="col-12 col-md-2 tasks-column">

            <div class="card ">
                <div class="card-header  bg-info  text-light">
                    In Progress
                    <span class="stats_InProgress badge badge-light float-right mt-1">0</span>
                </div>
                <div class="card-body task-parent scroll scroll-info " id="column_InProgress">
                    <div class="input-group">
                        <input type="text" class="form-control" name="column_InProgress_title"
                            id="column_InProgress_title" placeholder="Quick Add - Title Only" />
                        <div class="ml-2">
                            <button class="btn btn-outline-info" data-toggle="popover" data-placement="bottom"
                                data-content="Add Task"
                                onclick="addTask('In Progress', document.getElementById('column_InProgress_title').value)">
                                <i class="fas fa-plus "></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <div class="col-12 col-md-2 tasks-column">

            <div class="card">
                <div class="card-header bg-purple">
                    Under Verification
                    <span class="stats_UnderVerification badge badge-light float-right mt-1">0</span>
                </div>
                <div class="card-body  task-parent scroll scroll-purple " id="column_UnderVerification">
                    <div class="input-group">
                        <input type="text" class="form-control" name="column_UnderVerification_title"
                            id="column_UnderVerification_title" placeholder="Quick Add - Title Only" />
                        <div class="ml-2">
                            <button class="btn btn-outline-purple" data-toggle="popover" data-placement="bottom"
                                data-content="Add Task"
                                onclick="addTask('Under Verification', document.getElementById('column_UnderVerification_title').value)">
                                <i class="fas fa-plus "></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="col-12 col-md-2 tasks-column">

            <div class="card">
                <div class="card-header  bg-success text-light">
                    Complete
                    <span class="stats_Complete badge badge-light float-right mt-1">0</span>
                </div>
                <div class="card-body  task-parent scroll scroll-success " id="column_Complete">
                    <div class="input-group">
                        <input type="text" class="form-control" name="column_Complete_title" id="column_Complete_title"
                            placeholder="Quick Add - Title Only" />
                        <div class="ml-2">
                            <button class="btn btn-outline-success" data-toggle="popover" data-placement="bottom"
                                data-content="Add Task"
                                onclick="addTask('Complete', document.getElementById('column_Complete_title').value)">
                                <i class="fas fa-plus "></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="col-12 col-md-2 tasks-column">

            <div class="card">
                <div class="card-header  bg-warning text-dark">
                    Observations
                    <span class="stats_Observations badge badge-light float-right mt-1">0</span>
                </div>
                <div class="card-body  task-parent scroll scroll-warning " id="column_Observations">
                    <div class="input-group">
                        <input type="text" class="form-control" name="column_Observations_title"
                            id="column_Observations_title" placeholder="Quick Add - Title Only" />
                        <div class="ml-2">
                            <button class="btn btn-outline-warning" data-toggle="popover" data-placement="bottom"
                                data-content="Add Task"
                                onclick="addTask('Observations', document.getElementById('column_Observations_title').value)">
                                <i class="fas fa-plus "></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div> -->

    </div>

    
</div>


<script>
var teamMembers, activeProjects, projectOptions = "",
    project_id, teamMemberOptions = "",taskCategories, taskCategoryOptions = "",
    taskTypes, taskTypeOptions = "", tasksArr = [], taskTypesArr = [], taskCategoriesArr = [];
var defaultZIndex = 3;
var taskStats = <?= json_encode($taskStats) ?>;
//console.log(taskStats);

// // var taskStats = {
// //     'Todo': 0,
// //     'In Progress': 0,
// //     'Under Verification': 0,
// //     'Complete': 0,
// //     'Observations': 0
// };

$(document).ready(function() {
    $('[data-toggle="popover"]').popover({
        trigger: "hover"
    });
    $(".fluid-container").parents().css("overflow", "visible")
    $("body").css("overflow-x", "hidden");

    project_id = '<?= $project_id ?>';

    teamMembers = <?= json_encode($teamMembers) ?>;
    <?php foreach($teamMembers as $key => $name) : ?>
    teamMemberOptions += `<option value="<?= $key ?>"><?= $name ?></option>`;
    <?php endforeach; ?>

    activeProjects = <?= json_encode($activeProjects) ?>;

    <?php foreach($activeProjects as $key => $name) : ?>
    <?php $selected = ""; if($key == $project_id ) { $selected = "selected"; } ?>
    projectOptions += `<option <?= $selected ?> value="<?= $key ?>"><?= $name ?></option>`;
    <?php endforeach; ?>

    taskCategories = <?= json_encode($taskCategories) ?>;
    <?php foreach($taskCategories as $category) : ?>
        taskCategoriesArr.push("<?= $category["value"] ?>");
        taskCategoryOptions += `<option value="<?= $category["value"] ?>"><?= $category["value"] ?></option>`;
    <?php endforeach; ?>
    console.log(taskCategoriesArr);

    taskTypes = <?= json_encode($taskTypes) ?>;
    <?php foreach($taskTypes as $type) : ?>
        taskTypesArr.push("<?= $type["value"] ?>");
        taskTypeOptions += `<option value="<?= $type["value"] ?>"><?= $type["value"] ?></option>`;
    <?php endforeach; ?>

    makeColumnsDroppable();

    <?php if(isset($tasksArr)): ?>
    tasksArr = <?= json_encode($tasksArr) ?>;
    displayTasks();
    
    <?php endif ?>

    <?php if(isset($chartData)): ?>
    var chartData = <?= json_encode($chartData) ?>;
    displayChart(chartData);
    <?php endif ?>

});

function displayTasks(){
    $(".newTask").remove();

    // taskStats = {
    //     'Todo': 0,
    //     'In Progress': 0,
    //     'Under Verification': 0,
    //     'Complete': 0,
    //     'Observations': 0
    // };
    taskStats = <?= json_encode($taskStats) ?>;
    tasksArr.forEach((task, i) => {
        taskStats[task['task_column']] += 1;
        if (task.comments != null) {
            tasksArr[i].comments = JSON.parse(task.comments);
        }
        addTaskToDocument(task);
    });
    updateStats(taskStats);
}

function getTasks(){
    const selectedUserId = $("#selectedUserId").val();
    var url = `/taskboard/getTasks?project_id=${project_id}&user_id=${selectedUserId}`;
    makeRequest(url)
        .then((response) => {
            tasksArr = response.tasksArr;
            displayTasks();
            const chartData = response.chartData;
            removeChart('developerChart');
            removeChart('verifierChart');
            removeChart('columnPie');
            removeChart('categoryPie');
            displayChart(chartData)
                
        })
        .catch((err) => {
            console.log(err);
            showPopUp('Error', "An unexpected error occured on server.");
        })
}

function removeChart(id){
    try{
        ApexCharts.exec(id, 'destroy');
    }catch(err){
        console.log(err);
    }
}

$('#selectedView').change(function() {
    const showList = $(this).prop('checked');
    if(!showList){
        $(".listView").hide('slide', {direction: 'left'}, 1000);
        $(".graphView").show('slide', {direction: 'right'}, 1000);
    }else{
        $(".graphView").hide('slide', {direction: 'right'}, 1000);
        $(".listView").show('slide', {direction: 'left'}, 1000);
    }
})

function displayChart(chartData) {

    if(chartData.developerData.series.length){
        drawBar("developerChart", "Development Tasks Load", chartData.developerData);
    }else{
        $("#developerChart").html("");
        $("#developerChart").html("<span>No tasks assigned for development.</span>")
    }

    if(chartData.verificationData.series.length){
        drawBar("verifierChart", "Verification Tasks Load", chartData.verificationData)
    }else{
        $("#verifierChart").html("");
        $("#verifierChart").html("<span>No tasks assigned for verification.</span>")
    }
    
    if(chartData.columnData.series.length){
        drawPie("columnPie", "Task breakup - Column wise", chartData.columnData, returnColors("column", chartData.columnData
        .labels));
    }else{
        $("#columnPie").html("");
        $("#columnPie").html("<span>No data found.</span>")
    }
    
    if(chartData.categoryData.series.length){
        drawPie("categoryPie", "Task breakup - Category wise", chartData.categoryData, returnColors("category", chartData
        .categoryData.labels));
    }else{
        $("#categoryPie").html("");
        $("#categoryPie").html("<span>No data found.</span>")
    }
    

}

// // function returnColors(type, labels) {
// //     let colors = [];
// //     if (type == "column") {
// //         for (let i = 0; i < labels.length; i++) {
// //             if (labels[i] == "Complete") {
// //                 colors.push("#28a745");
// //             } else if (labels[i] == "In Progress") {
// //                 colors.push("#17a2b8");
// //             } else if (labels[i] == "Todo") {
// //                 colors.push("#343a40");
// //             } else if (labels[i] == "Under Verification") {
// //                 colors.push("#6f42c1");
// //             } else {
// //                 colors.push("#ffc107");
// //             }
// //         }
// //     } else {
// //         for (let i = 0; i < labels.length; i++) {
// //             if (labels[i] == "Bug") {
// //                 colors.push("#dc3545");
// //             } else if (labels[i] == "Improvement") {
// //                 colors.push("#28a745");
// //             } else if (labels[i] == "New Feature") {
// //                 colors.push("#e83e8c");
// //             } else if (labels[i] == "Task") {
// //                 colors.push("#343a40");
// //             }
// //         }
// //     }

//     return colors;

// }

function returnColors(type, labels) {
    let colors = [];
    let columnColorHexas = ["#28a745","#17a2b8","#343a40","#6f42c1","#ffc107"];
    let categoryColorHexas = ["#dc3545","#28a745","#e83e8c","#343a40","#ffc107"];
    if (type == "column") {
        for (let i = 0; i < labels.length; i++) {
            if (taskTypesArr.indexOf(labels[i])){
                colors.push(columnColorHexas[i]);
            } else {
                colors.push(columnColorHexas[i]);
            }
        }
    } else {
        for (let i = 0; i < labels.length; i++) {
            if (taskCategoriesArr.indexOf(labels[i])) {
                colors.push(categoryColorHexas[i]);
            } 
        }
    }
return colors;
}

function drawBar(chartContainerId, title, data) {
    $("#"+chartContainerId).html("");
    var chartHeight = 150;
    if(data.series.length > 10){
        chartHeight = 550;
    }else if(data.series.length > 5){
        chartHeight = 350;
    }else if(data.series.length > 1){
        chartHeight = 250;
    }
    var options = {
        title: {
            text: title,
        },
        series: [{
            name: "Tasks",
            data: data.series
        }],
        chart: {
            id: chartContainerId,
            type: "bar",
            height: chartHeight,
            fontFamily: 'Open Sans, sans-serif',
            redrawOnParentResize: true
        },
        legend: {
            show: false
        },
        plotOptions: {
            bar: {
                horizontal: true,
                distributed: true
            }
        },
        dataLabels: {
            enabled: true
        },
        xaxis: {
            categories: data.labels,
            title: {
                text: "No. of tasks assigned",
            }
        }
        

    }

    var chart = new ApexCharts(document.querySelector("#" + chartContainerId), options);
       
    chart.render();
    window.dispatchEvent(new Event('resize'))
}

function drawPie(chartContainerId, title, data, colors) {
    
    $("#"+chartContainerId).html("");
    var pieOptions = {
        title: {
            text: title
        },
        series: data.series,
        chart: {
            id: chartContainerId,
            width: "100%",
            height: 200,
            type: "donut",
            toolbar: {
                show: true
            },
            fontFamily: 'Open Sans, sans-serif'
        },
        colors: colors,
        labels: data.labels,
        dataLabels: {
            formatter: function(val, opts) {
                return opts.w.config.series[opts.seriesIndex]
            },
        },
        responsive: [{
            breakpoint: 480,
            options: {
                chart: {
                    width: 200
                },
                legend: {
                    position: "bottom"
                }
            }
        }],
        plotOptions: {
            pie: {
                donut: {
                    size: '65%',
                    background: 'transparent',
                    labels: {
                        show: true,
                        total: {
                            show: true,
                            label: 'Total',
                            fontSize: '16px',
                            fontFamily: 'Helvetica, Arial, sans-serif',
                            fontWeight: 600,
                            color: '#373d3f',
                            formatter: function(w) {
                                return w.globals.seriesTotals.reduce((a, b) => {
                                    return a + b
                                }, 0)
                            }
                        }
                    }
                },
            }
        }

    };

    var chart = new ApexCharts(document.querySelector("#" + chartContainerId), pieOptions);
    chart.render();
}

function updateStats() {
    const keys = Object.keys(taskStats);
    const values = Object.values(taskStats);

    for (var i = 0; i < keys.length; i++) {
        //const targetColumn = ".stats_" + keys[i].replace(" ", "");
        const targetColumn = ".stats_" + keys[i];
        $(targetColumn).text(values[i])
    }
}

$(document).on({
    ajaxStart: function() {
        $("#loading-overlay").show();
    },
    ajaxStop: function() {
        $("#loading-overlay").hide();
    }
});

function makeColumnsDroppable() {
    //const columns = ["Todo", "In Progress", "Under Verification", "Observations", "Complete"];
    const columns = taskTypesArr;
    columns.forEach((columnName) => {
        const columnId = "#column_" + columnName.replace(" ", "");
        var $column = $(columnId);

        $column.droppable({
            drop: function(event, ui) {
                $column.append(ui.draggable);
                const $task = $(ui.draggable)[0];
                const taskId = $task.id;
                updateTaskColumn(taskId, columnName);
            }
        });

    });
}

function addTask(column, title, taskId = "") {
    if (title == "") {
        var formTitle = "Add Task",
            buttonText = "Add";

        if (taskId != "") {
            formTitle = `T-${taskId}`, buttonText = "Update";
        }

        var dialog = bootbox.dialog({
            title: formTitle,
            message: `<form id="taskForm">
                        <input type="hidden" name="id" value='${taskId}' />
                        <div class="row justify-content-center">
                            <div class="col-12 col-md-6 d-none projectDropDown">
                                <div class="form-group">
                                    <label class = "font-weight-bold text-muted" for="project_id">Project</label>
                                    <select class="form-control selectpicker" data-live-search="true" data-size="8" name="project_id" id="project_id">
                                        <option value="" disabled selected>
                                            Select
                                        </option>
                                        ${projectOptions}
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label class = "font-weight-bold text-muted" for="newTask_category">Category</label>
                                    <select class="form-control selectpicker" name="newTask_category" id="newTask_category">                                    
                                    <option value="" disabled selected>
                                            Select
                                    </option>
                                    ${taskCategoryOptions}
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label class = "font-weight-bold text-muted" for="newTask_title">Title</label>
                                    <input type="text" class="form-control" name="newTask_title" id="newTask_title" placeholder="Task Title" />
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label class = "font-weight-bold text-muted" for="newTask_description">Description</label>
                                    <textarea class="form-control newTask_textarea" name="newTask_description" id="newTask_description" placeholder="What needs to get done?" ></textarea>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label class = "font-weight-bold text-muted" for="newTask_assignee">Assigned To</label>
                                    <select class="form-control selectpicker" data-live-search="true" data-size="8" name="newTask_assignee" id="newTask_assignee">
                                        <option value="" disabled selected>
                                            Select
                                        </option>
                                        ${teamMemberOptions}
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label class = "font-weight-bold text-muted" for="newTask_verifier">Verified By</label>
                                    <select class="form-control selectpicker" data-live-search="true" data-size="8" name="newTask_verifier" id="newTask_verifier">
                                        <option value="" disabled selected>
                                            Select
                                        </option>
                                        ${teamMemberOptions}
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label class = "font-weight-bold text-muted" for="newTask_column">Column</label>
                                    <select class="form-control selectpicker" name="newTask_column" id="newTask_column">
                                        <option value="" disabled selected>
                                                Select
                                        </option>
                                        ${taskTypeOptions}
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-12 col-md-6">

                                <div class="form-group">
                                    <label class = "font-weight-bold text-muted" for="newTask_attachments">Attachments</label>
                                    <div class="custom-file">
                                        <label class = "custom-file-label" for="newTask_attachments">Attachments</label>
                                        <input class="custom-file-input" type="file" id="newTask_attachments" name="attachments[]" accept="image/*,video/*" multiple>
                                    </div>
                                    
                                </div>
                               
                            </div>
                            
                        </div>
                        </form>
                        
                        `,
            buttons: {
                delete: {
                    label: "Delete",
                    className: 'btn-danger mr-auto d-none deleteTask',
                    callback: function() {
                        bootbox.confirm({
                            title: 'Delete',
                            message: `Are you sure you want to delete task T-${taskId} ?`,
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
                                    const object = {
                                        id: taskId
                                    };
                                    updateTaskInDB('/taskboard/deleteTask', "delete", object);

                                } else {
                                    console.log('Delete Cancelled');
                                }
                            }
                        });

                    }
                },
                cancel: {
                    label: "Cancel",
                    className: 'btn-secondary'
                },
                ok: {
                    label: buttonText,
                    className: "btn-primary",
                    callback: function() {

                        const taskTitle = $('#newTask_title').val();
                        let taskForm = new FormData(document.getElementById("taskForm"));

                        if (taskTitle != "") {
                            if (taskId == "") {
                                //Add Task
                                updateTaskInDB('/taskboard/addTask', "add", taskForm, true);
                            } else {
                                //Update Task
                                updateTaskInDB('/taskboard/addTask', "update", taskForm, true);
                            }
                        } else {
                            showPopUp("Validation Error", "Title of a task cannot be empty!")
                        }

                    }
                }

            }
        });

        if (taskId != "") {
            $(".deleteTask").removeClass("d-none");
            $(".projectDropDown").removeClass("d-none");
            const task = tasksArr.find(x => parseInt(x.id) === taskId);
            $('#newTask_title').val(task.title);
            $('#newTask_description').val(task.description);
            $('#newTask_assignee').val(task.assignee);
            $('#newTask_verifier').val(task.verifier);
            $('#newTask_category').val(task.task_category);
            $('#newTask_column').val(task.task_column);
            const formFooter =
                `<footer class="blockquote-footer text-right mt-3">Created by <cite>${teamMembers[task.creator]}</cite></footer>`;
            $('.modal-body').append(formFooter);
        } else {
            $("#newTask_column").val(column);
        }


        $(".custom-file-input").change(function() {
            var files = $("#newTask_attachments")[0].files;
            if (files.length < 1) {
                $(".custom-file-label").text("Attachments")
            } else if (files.length > 1) {
                $(".custom-file-label").text(files.length + " Files")
            } else {
                $(".custom-file-label").text(files[0].name)
            }
        })

        $('.selectpicker').selectpicker('refresh');

    } else {

        let taskForm = new FormData();
        taskForm.append('project_id', project_id);
        taskForm.append('newTask_title', title);
        taskForm.append('newTask_category', "Task");
        taskForm.append('newTask_column', column);

        updateTaskInDB('/taskboard/addTask', "add", taskForm, true);


    }

}

function getTaskFromArray(taskId) {
    var existingTask, existingTaskLoc;
    tasksArr.some((task, index) => {
        if (task.id == taskId) {
            existingTask = task;
            existingTaskLoc = index;

            return true;
        }
    });
    return [existingTaskLoc, existingTask];
}

function updateTaskColumn(taskId, updatedColumn) {
    const temp = getTaskFromArray(taskId);
    const existingTaskLoc = temp[0];
    const existingTask = temp[1];

    if (existingTask.task_column != updatedColumn) {
        const object = {
            id: taskId,
            task_column: updatedColumn
        };
        updateTaskInDB('/taskboard/updateTaskColumn', 'updateTaskColumn', object);
    }

}

function addComment(taskId) {
    const temp = getTaskFromArray(taskId);
    const existingTask = temp[1];

    if (existingTask.comments == null) {
        existingTask.comments = [];
    }else{
        existingTask.comments.reverse();
    }
    var commentsHtml = "";

    existingTask.comments.forEach((commentData, i) => {
        if (i == 0) {
            commentsHtml +=
                `<ul class="list-group scroll scroll-orange" style="max-height: 300px;overflow-y: auto;">`;
        }
        commentsHtml += `<li class="list-group-item list-group-item-action" style="font-size:14px;line-height:1.5rem">
                                       <span style="white-space: pre-line"> ${commentData.comment} </span>
                                    <footer class="blockquote-footer text-right">By <cite>${commentData.by}</cite> at ${formatDate(commentData.timestamp)}</footer>
                                </li>`;
        if (i == (existingTask.length - 1)) {
            commentsHtml += `</ul>`;
        }

    });

    var dialog = bootbox.dialog({
        title: "Add Comment",
        message: `<div class="row">
                            <div class="col-12">${commentsHtml}</div>
                            <div class="col-12 mt-2">
                                <div class="form-group">
                                    <label class = "font-weight-bold text-muted" for="newTask_comment">Comment</label>
                                    <textarea class="form-control newTask_textarea" name="newTask_comment" id="newTask_comment" placeholder="Task Update" ></textarea>
                                </div>
                            </div>
                        </div>
                        `,
        buttons: {
            cancel: {
                label: "Cancel",
                className: 'btn-secondary'
            },
            ok: {
                label: "Add",
                className: 'bg-orange',
                callback: function() {
                    const comment = $('#newTask_comment').val();

                    if (comment != "") {
                        const object = {
                            id: taskId,
                            comment
                        };
                        updateTaskInDB('/taskboard/addComment', 'addComment', object);
                    }


                }
            }
        }
    });

}

function getTaskHtml(newTask) {
    var categoryColor;
    if (newTask.task_category == "Bug") {
        categoryColor = "badge-danger";
    } else if (newTask.task_category == "Improvement") {
        categoryColor = "badge-success";
    } else if (newTask.task_category == "Task") {
        categoryColor = "badge-dark";
    } else {
        categoryColor = "bg-pink"
    }

    var commentsCount = "";
    var commentCountClass = "d-none";
    if (newTask.comments != null) {
        commentsCount = newTask.comments.length;
        commentCountClass = "";
    }

    var assignee = (newTask.task_column == "UnderVerification" ? newTask.verifier : newTask.assignee);
    if (assignee != "" && assignee != null) {
        assignee = teamMembers[assignee];
        if(assignee == undefined){
            assignee = "User Disabled";
        }
    } else {
        assignee = "Unassigned";
    }
    var attachmentsHtml = "";
    if (newTask.attachments != null) {
        jsonAttachments = newTask.attachments;
        attachmentsCount = JSON.parse(newTask.attachments).length;
        attachmentsHtml +=
            `<button data-toggle="popover" data-placement="bottom" data-content="View Attachments" type="button" 
                                        class="btn btn-sm btn-purple box-shadow-right" onclick="attachmentSlider('T-${newTask.id} Attachments', getCarouselHtml(${newTask.id}) )" >
                                    <i class="fas fa-paperclip"></i>
                                </button>
                                <span id="attachmentCount_${newTask.id}" class="dot sec counter counter-lg">${attachmentsCount}</span>`;
    }
    var descp = "";
    if(newTask.description != null){
        descp = safeString(newTask.description);
        if(descp.length > 400){
            descp = descp.substring(0,400);
            descp += " ...";
        }
    }

    var taskHtml = `
                        <div class=" text-muted">
                            <div class="float-left pl-2 pt-2">
                                <span class="badge bg-teal p-2 box-shadow-left" style="font-size:14px;cursor:default;">T-${newTask.id}</span>
                                
                            </div>
                            <div class="float-right pt-2 pr-2">
                                ${attachmentsHtml}
                                <button data-toggle="popover" data-placement="bottom" data-content="Add Comment" type="button" class="ml-1 btn btn-sm btn-orange box-shadow-right" onclick="addComment(${newTask.id})">
                                    <i class="fas fa-comment"></i>
                                </button>
                                <span id="commentCount_${newTask.id}" class="dot sec counter counter-lg ${commentCountClass}">${commentsCount}</span>
                                <button data-toggle="popover" data-placement="bottom" data-content="Edit Task" type="button" class="ml-1 btn btn-sm box-shadow-right btn-sm-primary btn-primary" onclick="addTask('', '', ${newTask.id})">
                                    <i class="fa fa-pencil-alt" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body" style="padding:0.75rem">
                            <p style="font-size:14px;line-height: 1.5;" data-toggle="popover" data-placement="top" data-content="${descp}" class="card-text task_title">${newTask.title}</p>
                        </div>
                        <div class="card-footer text-muted text-right" style="padding:4px">
                            <div class="float-left">
                                <span style="font-size:11px" class="task_category p-2 badge ${categoryColor}" style="cursor:default;">${newTask.task_category}</span>
                            </div>
                            <div class="float-right truncate">
                                <span style="font-size:14px" data-toggle="popover" data-placement="bottom" data-content="${assignee}" class="task_assignee ">${assignee}</span>
                            </div>
                            
                        </div>
                    `;

    return taskHtml;
}

function safeString(str) {
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function getCarouselHtml(taskId) {
    const temp = getTaskFromArray(taskId);
    const existingTask = temp[1];
    const attachments = JSON.parse(existingTask.attachments);
    let carouselIndicators = "";
    let carouselItems = "";
    attachments.forEach((attachment, index) => {

        const activeClass = (index == 0) ? 'active' : '';
        carouselIndicators +=
            `<li data-target="#carouselExampleIndicators" data-slide-to="${index}" class="${activeClass}"></li>`;
        if (attachment.type.includes('image')) {
            carouselItems += `<div class="carousel-item ${activeClass} ">
                                    <img class="d-block img-fluid rounded mx-auto d-block" style="max-height: 80vh;" src="${attachment.link}" >
                                </div>`;

        } else if (attachment.type.includes('video')) {
            carouselItems += `<div class="carousel-item ${activeClass}">
                                    <video class="video-fluid d-block w-100" style="max-height: 80vh;" autoplay controls >
                                        <source src="${attachment.link}" type="${attachment.type}" />
                                    </video>
                                </div>`;
        } else if (attachment.type.includes('pdf')) {
            carouselItems += `<div class="carousel-item ${activeClass}">
                                    <embed src="${attachment.link}" type="application/pdf" width="100%" style="min-height:80vh" />
                                </div>`;
        } else {
            carouselItems += `<div style="min-height: 200px;margin-top: 100px;" class="carousel-item text-center ${activeClass}">
                                    <a  href="${attachment.link}" download >Attachment</a>
                                </div>`;
        }
    });
    return [carouselIndicators, carouselItems];
}

function addTaskToDocument(newTask) {

    const html = `
                    <div class="card mt-3 newTask"  style="display:none;" id="${newTask.id}">
                        ${getTaskHtml(newTask)}
                    </div>`;

    const column = newTask.task_column.replace(" ", "");
    var $column = $("#column_" + column);
    $(html).appendTo($column).fadeIn('slow');
    $('[data-toggle="popover"]').popover({
        trigger: "hover"
    });

    // $column.append(html);
    $(".card", $column).draggable({
        cancel: "button",
        revert: "invalid",
        containment: "document",
        helper: "clone",
        cursor: "move",
        start: function(event, ui) {
            $(ui.helper).addClass("ui-helper");
            $(".ui-draggable").not(ui.helper.css("z-index", "1"))
                .css("z-index", "0");
        }
    });
}

function updateTaskInDB(url, type, taskObject, fileData = false) {

    makePOSTRequest(url, taskObject, fileData)
        .then((data) => {
            if (data.success == "True") {
                if (type == "add") {

                    // taskObject.id = data.task;
                    tasksArr.push(data.task);

                    taskStats[data.task['task_column']] += 1;
                    updateStats();

                    addTaskToDocument(data.task);
                    showFloatingAlert(`T-${data.task.id} task added successfully!`);
                    getTasks();

                } else if (type == "update") {

                    var task = new Task();
                    task = data.task;

                    const temp = getTaskFromArray(task.id);
                    const existingTaskLoc = temp[0];
                    const existingTask = temp[1];
                    if (existingTask.project_id != task.project_id) {
                        $("#" + task.id).fadeOut(800, function() {
                            $(this).remove();
                        });
                        taskStats[existingTask.task_column] -= 1;
                        updateStats();

                        tasksArr.splice(existingTaskLoc, 1);
                        
                        showFloatingAlert(
                            `T-${task.id} task moved to ${activeProjects[task.project_id]} successfully!`,
                            "bg-warning");
                        
                    } else {
                        task.comments = existingTask.comments;
                        tasksArr[existingTaskLoc] = task;
                        $(`#${task.id}`).html(getTaskHtml(task));
                        $('[data-toggle="popover"]').popover({
                            trigger: "hover"
                        });

                        if (existingTask.task_column != task.task_column) {
                            var div_column = task.task_column.replace(" ", "");
                            $(`#${task.id}`).appendTo($("#column_" + div_column));

                            taskStats[existingTask.task_column] -= 1;
                            taskStats[task.task_column] += 1;
                            updateStats();
                            
                        }
                        showFloatingAlert(`T-${task.id} task updated successfully!`);
                    }
                    getTasks();

                } else if (type == "addComment") {

                    const temp = getTaskFromArray(taskObject.id);
                    const existingTaskLoc = temp[0];
                    const existingTask = temp[1];

                    if (existingTask.comments == null) {
                        existingTask.comments = [];

                    }
                    existingTask.comments.push(JSON.parse(data.jsonComment));
                    tasksArr[existingTaskLoc] = existingTask;

                    $(`#commentCount_${taskObject.id}`).removeClass('d-none');
                    $(`#commentCount_${taskObject.id}`).text(existingTask.comments.length);

                    showFloatingAlert(`Comment added to T-${taskObject.id} successfully!`);
                } else if (type == "delete") {

                    $("#" + taskObject.id).fadeOut(800, function() {
                        $(this).remove();
                    });
                    const temp = getTaskFromArray(taskObject.id);
                    const existingTaskLoc = temp[0];
                    const existingTask = temp[1];

                    taskStats[existingTask.task_column] -= 1;
                    updateStats();

                    tasksArr.splice(existingTaskLoc, 1);
                    getTasks();

                } else if (type == "updateTaskColumn") {
                    const temp = getTaskFromArray(taskObject.id);
                    const existingTaskLoc = temp[0];
                    const existingTask = temp[1];

                    taskStats[existingTask.task_column] -= 1;
                    taskStats[taskObject.task_column] += 1;
                    updateStats();

                    existingTask.task_column = taskObject.task_column;
                    tasksArr[existingTaskLoc] = existingTask;
                    $(`#${taskObject.id}`).html(getTaskHtml(existingTask));
                    $('[data-toggle="popover"]').popover({
                        trigger: "hover"
                    });
                    getTasks();
                }
            } else {
                showPopUp('Error', data.errorMsg);
            }

        })
        .catch((err) => {
            console.log(err);
            showPopUp('Error', "An unexpected error occured on server.");
        })
}
</script>
<style>
    body {
        background-color: rgb(239, 244, 247);
        overflow: hidden;
    }

    .toggle-menu {
        border: 5px solid rgb(239, 244, 247);
    }

    .breadcrumb {
        background-color: #fff;
    }

    .breadcrumb-item {
        font-size: 24px;
    }

    .card {
        box-shadow: 0px 1px 22px -12px #607D8B;
        background-color: #fff;
        padding: 6px 12px;
        border-radius: 8px;
        width: 95%;
        margin-left: auto;
        margin-right: auto;
    }

    .card:hover {
        box-shadow: 0px 10px 32px -12px #607D8B;
    }

    .card-body {
        padding: 5px;
    }

    ul {
        list-style: none;
        counter-reset: my-awesome-counter;
    }

    ul li>p {
        counter-increment: my-awesome-counter;
    }

    ul li>p::before {
        content: counter(my-awesome-counter) ". ";
        color: "#6c757d";

    }

    .activeBackground {
        border: 1px solid #dee2e6;
        padding: 2px 12px;
        border-radius: 8px;
        margin-top: 5px;
        font-size: 14px;
    }

    .activeBackground:hover {
        background: #f8f9fa;
        border: 0.1rem solid #6c757d;
    }

    .statsHeading {
        padding-left: 10px;
        font-size: 16px;
        color: #5d606b;
        margin: 0px;
    }

    .para {
        font-size: 14px;
        margin: 0px;
    }

    .small-text {
        font-size: 12px;
    }

    .build-link-div {
        border: 0.1rem solid #6c757d;
        border-radius: 4px;
        padding: 5px;
        margin-left: 4px;
        margin-right: 4px;
        background: #6c757d;
    }

    .build-link:hover {
        text-decoration: none;
        font-weight: 600;
    }


    .changeListItems:hover {
        border: 0.1rem solid #6c757d;
        cursor: default;
    }

    .box {
        box-shadow: 0px 1px 22px -12px #607D8B;
        background-color: #fff;
        padding-bottom: 15px;
        border-radius: 8px;
    }

    .box-header {
        background: #007bff;
        border-bottom: 1px solid;
        font-size: 19px !important;
        padding: 8px 0px;
        color: white;
        border-top-left-radius: 8px;
        border-top-right-radius: 8px;
    }

    .label {
        color: #5d606b;
    }

    .task {
        display: flex;
        justify-content: space-between;
        position: relative;
        color: #777982;
        font-size: 14px;
        font-weight: 500;
    }

    .task:hover {
        transform: translatex(2px);
    }

    label {
        cursor: pointer;
        width: auto;
    }

    label .label-text {
        position: relative;
        word-break: break-all;
    }



    .tag {
        font-size: 10px;
        height: 25px;
        min-width: 45px;
        max-width: 110px;
        padding: 5px 8px;
        border-radius: 20px;
        text-align: center;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .tag-green {
        background-color: #e0fbf6;
        color: #58c2a9;
    }

    .tag-violet {
        background-color: #ece7fe;
        color: #8972f3;
    }

    .tag-red {
        background-color: #fde7ea;
        color: #e77f89;
    }

    .tag-yellow {
        background-color: #f9f8d8;
        color: #dede06;
    }

    .tag-gray {
        background-color: #f7f8fc;
        color: #a0a6b5;
    }

    .task-box {
        position: relative;
        border-radius: 8px;
        width: 92%;
        margin: 10px auto;
        padding: 9px;
        cursor: pointer;
        box-shadow: 2px 2px 4px 0px rgba(235, 235, 235, 1);
        background: aliceblue;
    }

    .task-box.active {
        background: #777982;
    }

    .task-box:hover {
        transform: scale(1.02);
    }

    .time {
        margin-bottom: 6px;
        color: #777982;
        font-size: 10px;
    }

    .time.active {
        color: whitesmoke;
    }

    .task-name {
        font-size: 14px;
        font-weight: 500;
        color: #777982;
        width: 70%;
    }

    .task-name.active {
        color: wheat;
    }

    .item-label {
        font-family: 'Roboto', sans-serif;
        border: 1px solid #ccc;
        padding: 2px 5px 2px 5px;
        border-radius: 8px;
        color: coral;
        font-size: 14px;
        height: 28px;
    }

    .item-label.active {
        color: whitesmoke;
        border: 2px solid coral;
    }

    .addDiv a {
        color: #fff;
    }

    .addDiv:hover a {
        cursor: pointer;
        background: white;
        border-radius: 8px;
        color: #007bff !important;
    }
</style>

<div class="fluid-container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb  arr-right" style="margin:0px;">
            <li class="breadcrumb-item text-primary" aria-current="page"> Dashboard </li>
        </ol>
    </nav>
    <main class="pt-3 pb-3">
        <div class="row p-0 pr-md-3 pl-md-3 pt-1 justify-content-center mb-2">
            <div class="col-12 col-sm-4 ">
                <div class="row">
                    <div class="col-12">
                        <div class="box">
                            <div class="text-center box-header"><span>Pending Items</span></div>
                            <div class="box-body">
                                <nav>
                                    <div class="nav nav-tabs justify-content-center" id="nav-tab" role="tablist">
                                        <a class="nav-item nav-link active" id="reviews-tab" data-toggle="tab" href="#reviews" role="tab" aria-controls="reviews" aria-selected="true">Reviews</a>
                                        <a class="nav-item nav-link" id="documents-tab" data-toggle="tab" href="#documents" role="tab" aria-controls="documents" aria-selected="false">Documents</a>
                                        <a class="nav-item nav-link" id="unitTests-tab" data-toggle="tab" href="#unitTests" role="tab" aria-controls="unitTests" aria-selected="false">Unit Tests</a>
                                    </div>
                                </nav>
                                <div class="tab-content scroll scroll-primary" style="height: 72vh;" id="nav-tabContent">
                                    <div class="tab-pane fade show active" id="reviews" role="tabpanel" aria-labelledby="reviews-tab">
                                        <div id="reviewsContainer"></div>
                                    </div>
                                    <div class="tab-pane fade" id="documents" role="tabpanel" aria-labelledby="documents-tab">
                                        <div id="documentsContainer"></div>
                                    </div>
                                    <div class="tab-pane fade" id="unitTests" role="tabpanel" aria-labelledby="unitTests-tab">
                                        <div id="unitTestsContainer"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- <div class="col-12 mt-3">
                        <div class="box">
                            <div class="box-header">
                                <div class="d-flex justify-content-between">
                                    <div class="w-100 text-center">
                                        <span style="margin-left: 45px;">To Do List</span>
                                    </div>
                                    <div class="mr-2 addDiv" data-toggle="popover" data-content="Add Action Item" data-placement="bottom">
                                        <a href="actionList">
                                            <span class="pl-2 pr-2">
                                                <i class="fa fa-plus" aria-hidden="true"></i>
                                            </span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div id="actionContainer" class="box-body scroll scroll-primary" style="height: 31vh;"></div>
                        </div>
                    </div> -->
                </div>

            </div>
            <div class="col-12 col-sm-4">
                <div class="row">
                    <div class="col-12 ">
                        <div class="box">
                            <div class="box-header text-center"><span>Tasks</span></div>
                            <div id="taskboardContainer" class="box-body scroll scroll-primary" style="height: 75vh;"></div>
                        </div>
                    </div>
                    <!-- <div class="col-12 mt-3">
                        <div class="box">
                            <div class="box-header">
                                <div class="d-flex justify-content-between">
                                    <div class="w-100 text-center">
                                        <span style="margin-left: 45px;">Schedule</span>
                                    </div>
                                    <div class="mr-2 addDiv" data-toggle="popover" data-content="Add Schedule" data-placement="bottom">
                                        <a href="timeTracker">
                                            <span class="pl-2 pr-2">
                                                <i class="fa fa-plus" aria-hidden="true"></i>
                                            </span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div id="scheduleContainer" class="box-body scroll scroll-primary" style="height: 33vh;"></div>
                        </div>
                    </div> -->

                </div>
            </div>
            <div class="col-12 col-sm-4 ">
                <div class="box">
                    <div class="box-header text-center"><span>Stats</span></div>
                    <div class="box-body mt-3 scroll scroll-primary" style="max-height: 75vh;">
                        <div class="stats-container row ml-0 mr-0"></div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
<footer class='website-footer' style="background:#e6e6ff">
        <p style="color:#4d4dff;">DocsGo <?php echo getenv('app.version')." "; ?><i class="fa fa-copyright"></i> 2023 VMI Software R&D. All rights reserved.</p>
</footer>
<script>
    $(document).on({
        ajaxStart: function() {
            $("#loading-overlay").show();
        },
        ajaxStop: function() {
            $("#loading-overlay").hide();
        }
    });

    getReviews();

    getDocuments();

    getUnitTests();

    getTasksHtml();

    //getSchedule();

    getTodoList();

    function getStats() {
        makeRequest("/dashboard/getStats")
            .then(response => {
                if (response.jenkins_stats != null) {
                    showJenkinsStats(response.jenkins_stats);
                }

                if (response.sonar_stats != null) {
                    showSonarStats(response.sonar_stats,response.sonar_stats_url);
                }

                $('[data-toggle="popover"]').popover({
                    trigger: "hover"
                });
            })
            .catch(err => console.log(err))
    }

    getStats();

    function showJenkinsStats(stats) {
        const jenkinsHtml = populateBuildStats(stats);
        $(".stats-container").append(jenkinsHtml);
    }

    function populateBuildStats(stats) {
        const changeLog = stats.changeLog;
        let changes = changeLog.length > 0 ? changeLog.length : "No changes";
        changes += (changeLog.length == 1 ? " commit" : (changeLog.length > 1 ? " commits" : ""))

        let changesList = '';
        if (changeLog.length) {
            for (let i = 0; i < changeLog.length; i++) {
                changesList += `
                <li>
                    ${changeLog[i]["msg"]}
                </li>
            `;
            }

        }

        let resultClass = "text-secondary";
        if (stats.result == "SUCCESS") {
            resultClass = "text-success";
            stats.result = "Success";
        } else if (stats.result == "FAILURE") {
            resultClass = "text-danger";
            stats.result = "Failure";
        } else {
            stats.result = "BUILDING";
            stats.result = "Building";
        }

        const buildSpecificLinks = getJenkinsLinks(stats.url, stats.fileManagerUrl, stats.dailyBuildLiveUrl);

        return `
            <h5 class="statsHeading text-left">Jenkins Build</h5>
            <div class="col-12 p-2">     
                <div class="pl-2 pr-2 activeBackground">                   
                    <div class="d-flex pb-2 pt-1 justify-content-center">
                        <div class="flex-row flex-wrap pr-2 text-center ">
                            <div class="label font-weight-light">Status</div>
                            <div class="p-1 ${resultClass}">${stats.result}</div>
                        </div>
                        <div class="flex-row flex-wrap pr-2 text-center" >
                            <div class="label font-weight-light">Commits</div>
                            <div class="p-1 " data-toggle="popover"
                            data-placement="bottom"
                            data-content="${changesList}" data-html="true">${changes}</div>
                        </div>
                        <div class="flex-row flex-wrap pr-2 text-center">
                            <div class="label font-weight-light">Duration</div>
                            <div class="p-1">${secondsToDuration(stats.duration/1000)}</div>
                        </div>
                        <div class="flex-row flex-wrap text-center">
                            <div class="label font-weight-light">Date</div>
                            <div class="p-1">${formatDate2(stats.timestamp)}</div>
                        </div>
                    </div>

                    <span class="label font-weight-light">Links</span><br />
                    <div class="d-flex flex-row justify-content-center align-content-start flex-wrap">
                    <div class="p-1">
                            <a target="_blank"
                                href="${stats.url}"
                                data-toggle="popover" data-content="Goto build location" data-placement="bottom"
                                class="btn btn-sm btn-light text-primary">Build</a>
                        </div>
                        <div class="p-1">
                            <a target="_blank"
                                href="${buildSpecificLinks.buildLocation}"
                                data-toggle="popover" data-content="Download binaries" data-placement="bottom"
                                class="btn btn-sm btn-light text-primary">Binaries</a>
                        </div>
                        <div class="p-1"><a target="_blank" href="${buildSpecificLinks.liveInstance}"
                                data-toggle="popover" data-content="Goto deployed instance" data-placement="bottom"
                                class="btn btn-sm btn-light text-primary">Live Instance</a></div>
                                
                    </div>
                    <span class="label font-weight-light">Build Reports</span>
                    <div class="d-flex flex-row justify-content-center align-content-start flex-wrap">
                        <div class="p-1"><a target="_blank"
                                href="${buildSpecificLinks.automationReport}"
                                data-toggle="popover" data-content="View automation report" data-placement="bottom"
                                class="btn btn-sm btn-light text-danger">Automation Report</a></div>
                </div>
            </div>
        `;
    }

    function getJenkinsLinks(url, fileManagerUrl, dailyBuildLiveUrl) {
        const jenkins_ip = url.substring(0, url.lastIndexOf(':'));
        //build date format yyyy-mm-dd
        const today = new Date();
        const year = today.getFullYear();
        const month = today.getMonth() < 10 ? '0' + (today.getMonth() + 1) : today.getMonth() + 1;
        const day = today.getDate() < 10 ? '0' + today.getDate() : today.getDate();

        const date = year + '-' + month + '-' + day;
        const buildLocation = fileManagerUrl + "/index.php?p=builds/" + date;

        return {
            buildLocation: buildLocation,
            //liveInstance: jenkins_ip.replace('http', 'https') + "/webservices",
            liveInstance: dailyBuildLiveUrl,
            automationReport: buildLocation + "/automation_report",
           // unitTestReport: buildLocation + "/ut_coverage"
        }
    }

    function showSonarStats(stats, statsUrl) {
        const sonarHtml = populateSonarStats(stats, statsUrl);
        $(".stats-container").append(sonarHtml);
    }

    function populateSonarStats(stats, statsUrl) {
        let sonarProjects = "";
        for (const projectKey in stats) {
            console.log("sonar url ",statsUrl);
            sonarProjects += `
            <div class="activeBackground">
                <div>
                    <a href="${statsUrl}/dashboard?id=${projectKey}" target="_blank" style="text-decoration:none;" class="text-primary">${projectKey}</a>
                </div>
                <div class="d-flex justify-content-around text-secondary">
                    <div class="text-center">
                        <span>${stats[projectKey]["bugs"]}</span>
                        <br />
                        <span class="font-weight-light" style="font-size:14px">
                            <i class="fas fa-bug"></i>
                             Bugs
                        </span>
                    </div>
                    <div class="text-center">
                        <span>${stats[projectKey]["vulnerabilities"]}</span>
                        <br />
                        <span class="font-weight-light" style="font-size:14px"> 
                            <i class="fas fa-lock-open"></i>
                             Vulnerabilities
                        </span>
                    </div>
                    <div class="text-center">
                        <span>${stats[projectKey]["code_smells"]}</span>
                        <br />
                        <span class="font-weight-light" style="font-size:14px">
                            <i class="fas fa-radiation-alt"></i>
                            Code Smells
                        </span>
                    </div>
                </div>
            </div>
        `;
        }


        let sonarHtml = `
        <h5 class="statsHeading text-left pt-2">Sonar Code Analysis</h5>
        <div class="col-12 p-2">       
            <div class="">
                ${sonarProjects}
            </div>
        </div>`;

        return sonarHtml;
    }

    function getTasksHtml() {
        const taskboardData = <?= $taskboardData ?>;
        let tasks = "";

        if (taskboardData.length) {
            for (let i = 0; i < taskboardData.length; i++) {
                tasks += `
                    <div class="task-box">
                        <div class="task">
                            <label for="item-${i}">
                                <a href="taskboard?project-id=${taskboardData[i].project_id}" style="text-decoration:none">
                                    <span class="label-text">${taskboardData[i].title}</span>
                                </a>
                            </label>
                            <span class="tag ${getTasksColumnClass(taskboardData[i].task_column)}">${taskboardData[i].task_column}</span>
                        </div>
                    </div>`;
            }
        } else {
            tasks = `<div class="task-box">
                        <div class="task justify-content-center">
                            <label for="item-1" class="text-center">
                                <span class="label-text">No tasks assigned.</span>
                            </label>
                        </div>
                    </div>`;
        }

        const html = `<div>
                        ${tasks}
                    </div>`;
        $("#taskboardContainer").html(html);
    }

    function getTasksColumnClass(columnName) {
        if (columnName == "Todo") {
            return "tag-gray";
        } else if (columnName == "In Progress") {
            return "tag-green";
        } else if (columnName == "Under Verification") {
            return "tag-violet";
        } else if (columnName == "Observations") {
            return "tag-yellow";
        } else {
            return "tag-gray"
        }
    }

    // function getSchedule() {
    //     const trackerList = <?php //$trackerList ?>;
    //     const activityCategory = <?php //$activityCategory ?>;
    //     const trackerKeys = Object.keys(trackerList);
    //     let itemsHtml = "";
    //     if (trackerKeys.length) {
    //         const currentSlot = getCurrentSlot(trackerKeys);
    //         const timeSlots = getTimeSlots(1);
    //         for (let i = 0; i < trackerKeys.length; i++) {
    //             let activeClass = "";
    //             if (currentSlot == trackerKeys[i]) {
    //                 activeClass = "active";
    //             }
    //             const slot = timeSlots[trackerKeys[i]];
    //             const description = trackerList[trackerKeys[i]].description;
    //             const category = activityCategory[trackerList[trackerKeys[i]].category];
    //             itemsHtml += `<div id="slot_${trackerKeys[i]}" class="task-box ${activeClass}">
    //                             <div class="time ${activeClass}">${slot}</div>
    //                             <div class="d-flex justify-content-between">
    //                                 <div class="task-name ${activeClass}">${description}</div>
    //                                 <div class="item-label ${activeClass}">${category}</div>
    //                             </div>
    //                         </div>`;
    //         }
    //         setTimeout(function() {
    //             var elem = document.getElementById("slot_" + getCurrentSlot(trackerKeys));

    //             elem.scrollIntoView({
    //                 behavior: 'smooth'
    //             });
    //         }, 500);
    //     } else {
    //         itemsHtml = `<div class="task-box">
    //                         <div class="task  justify-content-center">
    //                             <label for="item-1" class="text-center">
    //                                 <span class="label-text">Add activities in Time Tracker</span>
    //                             </label>
    //                         </div>
    //                     </div>`;
    //     }

    //     $("#scheduleContainer").html(itemsHtml);


    // }

    function getCurrentSlot(trackerKeys) {
        const date = new Date();
        let expectedSlot = 2 * date.getHours();
        expectedSlot += (date.getMinutes() >= 30) ? 1 : 0;
        return findClosest(trackerKeys, expectedSlot);
    }

    function findClosest(arr, num) {
        if (arr == null) {
            return
        }

        let closest = arr[0];
        for (let item of arr) {
            if (Math.abs(item - num) < Math.abs(closest - num)) {
                closest = item;
            }
        }
        return closest;
    }

    function getReviews() {
        const reviewsData = <?= $reviewsData ?>;

        let reviews = "";
        if (reviewsData.length) {
            for (let i = 0; i < reviewsData.length; i++) {
                const id = reviewsData[i].id;
                const status = reviewsData[i].status;
                const title = reviewsData[i].context;
                reviews += `
                    <div class="task-box">
                        <div class="task">
                            <label for="item-${i}">
                                <a href="reviews/add/${id}" style="text-decoration:none">
                                    <span class="label-text">${title}</span>
                                </a>
                            </label>
                            <span class="tag ${getStatusColor(status)}">${status}</span>
                        </div>
                    </div>`;
            }
        } else {
            reviews = `
                <div class="task-box">
                    <div class="task  justify-content-center">
                        <label for="item-1" class="text-center">
                            <span class="label-text">No data found.</span>
                        </label>
                    </div>
                </div>`;
        }

        const html = `<div>
                        ${reviews}
                    </div>`;
        $("#reviewsContainer").html(html);
    }

    function getDocuments() {
        const documentsData = <?= $documentsData ?>;

        let documents = "";
        if (documentsData.length) {
            for (let i = 0; i < documentsData.length; i++) {
                const id = documentsData[i].id;
                const status = documentsData[i].status;
                const title = documentsData[i].title;
                documents += `
                    <div class="task-box">
                        <div class="task">
                            <label for="item-${i}">
                                <a href="documents/add?id=${id}" style="text-decoration:none">
                                    <span class="label-text">${title}</span>
                                </a>
                            </label>
                            <span class="tag ${getStatusColor(status)}">${status}</span>
                        </div>
                    </div>`;
            }
        } else {
            documents = `
                <div class="task-box">
                    <div class="task  justify-content-center">
                        <label for="item-1" class="text-center">
                            <span class="label-text">No data found.</span>
                        </label>
                    </div>
                </div>`;
        }

        const html = `<div>
                        ${documents}
                    </div>`;
        $("#documentsContainer").html(html);
    }

    function getStatusColor(status) {
        if (status == "Request Change") {
            return "tag-red";
        } else if (status == "Request Review") {
            return "tag-violet";
        } else if (status == "Draft") {
            return "tag-yellow";
        } else {
            return "tag-gray"
        }
    }

    function getUnitTests() {
        const unitTestsData = <?= $unitTestsData ?>;

        let unitTests = "";
        if (unitTestsData.length) {
            for (let i = 0; i < unitTestsData.length; i++) {
                const id = unitTestsData[i].id;
                const name = unitTestsData[i].name;
                const project = unitTestsData[i].project;
                unitTests += `
                    <div class="task-box">
                        <div class="task">
                            <label for="item-${i}">
                                <a href="/unit-tests/edit/${id}" style="text-decoration:none">
                                    <span class="label-text">${name}</span>
                                </a>
                            </label>
                            <span class="tag tag-violet">${project}</span>
                        </div>
                    </div>`;
            }
        } else {
            unitTests = `
                <div class="task-box">
                    <div class="task  justify-content-center">
                        <label for="item-1" class="text-center">
                            <span class="label-text">No data found.</span>
                        </label>
                    </div>
                </div>`;
        }

        const html = `<div>
                        ${unitTests}
                    </div>`;
        $("#unitTestsContainer").html(html);
    }

    function getTodoList() {
        const toDoData = <?= $toDoData ?>;

        let actionItems = "";
        if (toDoData.length) {
            for (let i = 0; i < toDoData.length; i++) {
                const id = toDoData[i].id;
                const completion = toDoData[i].completion;
                const title = toDoData[i].title;
                const priority = toDoData[i].priority;
                actionItems += `
                    <div class="task-box">
                        <div class="task">
                            <label for="item-${i}">
                                <a href="actionList#actionItem_${id}" style="text-decoration:none">
                                    <span class="label-text">${title}</span>
                                </a>
                            </label>
                            <span class="tag ${getPriorityColor(priority)}">${priority}</span>
                        </div>
                        <div class="text-center" style="font-size:12px;color:#777982">${completion}%</div>
                        <div class="progress" style="height: 5px;">
                            <div 
                                class="progress-bar bg-success" 
                                role="progressbar" 
                                style="width: ${completion}%;" 
                                aria-valuenow="${completion}" 
                                aria-valuemin="0" 
                                aria-valuemax="100">
                            </div>
                        </div>
                    </div>`;
            }
        } else {
            actionItems = `
                <div class="task-box">
                    <div class="task  justify-content-center">
                        <label for="item-1" class="text-center">
                            <span class="label-text">Add activities in Action List.</span>
                        </label>
                    </div>
                </div>`;
        }

        const html = `<div>
                        ${actionItems}
                    </div>`;
        $("#actionContainer").html(html);
    }

    function getPriorityColor(priority) {
        if (priority == "High") {
            return "tag-red";
        } else if (priority == "Low") {
            return "tag-green";
        } else if (priority == "Medium") {
            return "tag-yellow";
        } else {
            return "tag-gray"
        }
    }
</script>
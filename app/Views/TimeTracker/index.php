<link rel="preconnect" href="https://fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/time_tracker.css?1.1" />

<div class="fluid-container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb  arr-right" style="margin:0px;">
            <li class="breadcrumb-item text-primary" aria-current="page"> Time Tracker </li>
        </ol>
    </nav>
    <main class="pb-3">
        <div class="jumbotron jumbotron-fluid p-2 m-0">
            <div class="container">
                <p class="lead m-0" id="headerText"></p>
            </div>
        </div>
        <div class=" mt-2">
            <div class="row justify-content-end">
                <div class="col-md-3 justify-content-end form-inline">
                    <div class="form-group mb-2 mr-4">
                        <input class="form-control form-control-sm" id="tracker_list_date" type="date"
                            value="<?php echo date("Y-m-d"); ?>" />
                        <button title="previous" onclick="updateDateCounter('minus')" class="btn-circle btn-sm ml-2"><i
                                class="fas fa-angle-left fa-2x"></i></button>
                        <button title="next" onclick="updateDateCounter('plus')" class="btn-circle btn-sm ml-1"><i
                                class="fas fa-angle-right fa-2x"></i></button>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-4 pl-4">
                    <div id="daySummary"></div>
                    <div class="mt-2" id="weeklySummary"></div>
                </div>
                <div class="col-8 pr-4">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th scope="col" style="width:110px" class="text-center">Time
                                    <span title="Time format" class="badge badge-pill badge-light ml-1 btnToggle" onclick="toggleTimeFormat()">12hrs</span>
                                </th>
                                <th scope="col" class="text-center">Activity </th>
                            </tr>
                        </thead>
                        <tbody id="activity_rows" class="scroll scroll-primary"></tbody>

                    </table>
                </div>
            </div>

        </div>

    </main>
    <div class="edit-modal">
        <form id="trackerForm" novalidate>
            <header class="text-center p-2">
                <span id="editModalTitle" class="item-header font-weight-bold text-secondary">Create Activity</span>
            </header>
            <div class="item-body scroll scroll-primary ">
                <input type="hidden" id="item_id" name="item_id">
                <input type="hidden" id="slot_id" name="slot_id">
                <input type="hidden" id="tracker_date" name="tracker_date">
                <div class="row ">
                    <div class="col-6">
                        <div class="form-group">
                            <label class="text-muted" for="priority">Category</label>
                            <select class="form-control select-box" name="category" id="category">
                                <?php foreach ($activityCategory as $key => $name): ?>
                                <option value="<?=$key?>"><?=$name?></option>;
                                <?php endforeach;?>
                            </select>
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="form-group">
                            <label class="text-muted" for="reoccuring">Reoccuring</label>
                            <select class="form-control select-box" name="reoccuring" id="reoccuring">
                                <option value="0">None</option>;
                                <option value="7">7 days</option>;
                                <option value="15">15 days</option>;
                                <option value="30">30 days</option>;
                            </select>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="form-group">
                            <label class="text-muted" for="description">Description</label>
                            <textarea required class="form-control scroll scroll-dark"
                                placeholder="Describe activity here..." style="height: 165px;" name="description"
                                id="description"></textarea>
                            <div class="invalid-feedback">
                                Please provide some description.
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <footer class="item-footer">
                <div class="row justify-content-between">
                    <div class="col-3">
                        <button type="button" title="Reset Activity" id="btnReset" class="btn btn-secondary" onclick="resetActivity()" style="display:none">
                            <i class="fas fa-sync"></i>
                        </button>
                    </div>
                    <div class="col-6">
                        <button type="submit" id="btnSave" class="btn btn-primary">Save</button>
                        <a class="btn btn-danger ml-2 edit-cancel text-white" onclick="hideItemModal()">Cancel</a>
                    </div>
                </div>
            </footer>
        </form>
    </div>
</div>

<script>
$(document).on({
    ajaxStart: function() {
        $("#loading-overlay").show();
    },
    ajaxStop: function() {
        $("#loading-overlay").hide();
    }
});
let TIME_SLOTS = [];
let TRACKER_LIST = {};
let DATE_COUNTER = 0;
let ACTIVITY_CATEGORY;
let USERNAME = "<?= session()->get('name') ?>";
let STACK_CHART, PIE_CHART;
let TIME_FORMAT = 1;

$(document).ready(function() {
    let existingFormat = window.localStorage.getItem('TIME_FORMAT');
    if(existingFormat == null){
        window.localStorage.setItem('TIME_FORMAT', 1);
    }else{
        TIME_FORMAT = existingFormat;
    }
    getTracker();
    ACTIVITY_CATEGORY = <?= json_encode($activityCategory) ?>;
    initializePieChart();
    initializeStackChart();
});

function getTracker() {
    const tracker_date = getTrackerDate();
    const url = "/timeTracker/show?tracker_date=" + tracker_date;
    makeRequest(url)
        .then((response) => {
            TRACKER_LIST = {};
            if (response.trackerList != null) {
                TRACKER_LIST = JSON.parse(response.trackerList);
            }
            createTracker();
            $("#tracker_list_date").val(response.tracker_date);
        })
        .catch((err) => {
            console.log(err);
            showPopUp('Error', "An unexpected error occured on server.");
        });
}

function getTrackerDate() {
    let tracker_date = new Date();
    tracker_date.setDate(tracker_date.getDate() + DATE_COUNTER);
    return formatDate(tracker_date, false).substring(0, 10);
}

function toggleTimeFormat(){
    if(TIME_FORMAT == 1){
        TIME_FORMAT = 2;
    }else{
        TIME_FORMAT = 1;   
    }
    window.localStorage.setItem('TIME_FORMAT', TIME_FORMAT);
    drawTrackerHtml(TIME_FORMAT);
}

function updateTimeLabel(){
    const label = TIME_FORMAT == 1 ? "12hrs" : "24hrs";
    $(".btnToggle").html(label);  
}

function createTracker() {
    drawTrackerHtml(TIME_FORMAT);
    createHeaderText();
    updatePieChartData(getGraphStats());
    getStackChartData();
}

function drawTrackerHtml(time_format = 1){
    updateTimeLabel();
    const timeSlots = getTimeSlots(time_format);
    TIME_SLOTS = timeSlots;
    $("#activity_rows").html("");

    for (var i = 0; i < timeSlots.length; i++) {
        $("#activity_rows").append(`<tr id="slot_${i}">
            <th scope="row" style="width:100px">${timeSlots[i]}</th>
            <td style="padding:0px">
                <div class="row">
                    <div class="col-11">
                        <p style="margin:4px 0px 0px 10px" id="slot_description_${i}">
                        <span style="color:#ccc">Empty</span>
                        </p>
                    </div>
                    <div class="col-1">
                        <button class="activity-button" title="Edit" onclick="showItemModal('${i}')"><i class="fa fa-edit"
                                aria-hidden="true"></i></button>
                    </div>
                </div>
            </td>
        </tr>`)
    }

    let firstSlot = 18;
    if (TRACKER_LIST != null) {
        if(Object.keys(TRACKER_LIST).length){
            for (const item in TRACKER_LIST) {
                updateSlotDescription(item, ACTIVITY_CATEGORY[TRACKER_LIST[item].category], TRACKER_LIST[item].description)
            }
            firstSlot = Object.keys(TRACKER_LIST)[0];
        }
        
    }
    
    setTimeout(function() {
        var elem = document.getElementById("slot_" + firstSlot);
        elem.scrollIntoView({
            behavior: 'smooth'
        });
    }, 500);

}

function updateSlotDescription(slot_id, category, description) {
    $(`#slot_description_${slot_id}`).html(
        `<span class="item-label">${category}</span> -  ${description}`);
}

function resetSlotDescription(slot_id){
    $(`#slot_description_${slot_id}`).html(
        `<span style="color:#ccc">Empty</span>`);
}

function hideItemModal() {
    $(".edit-modal").removeClass("show-right");
    $(".edit-modal").addClass("hide-right");
}

function showItemModal(slotId) {
    $(".edit-modal").removeClass("hide-right");
    $(".edit-modal").addClass("show-right");

    $("#slot_id").val(slotId);
    const trackerDate = getTrackerDate();
    $("#tracker_date").val(trackerDate);
    if (activityExists(slotId)) {
        $("#item_id").val(TRACKER_LIST[slotId].id);
        $(".item-header").text("Edit Activity for " + TIME_SLOTS[slotId]);
        $("#category").val(TRACKER_LIST[slotId].category);
        $("#reoccuring").val(TRACKER_LIST[slotId].reoccuring);
        $("#description").val(TRACKER_LIST[slotId].description);
        $("#reoccuring").attr('disabled', true);
        $("#btnReset").show();
    } else {
        $("#item_id").val("");
        $(".item-header").text("Create Activity for " + TIME_SLOTS[slotId]);
        $("#category").val(0);
        $("#reoccuring").val(0);
        $("#description").val("");
        $("#reoccuring").attr('disabled', false);
        $("#btnReset").hide();
    }
}

function updateDateCounter(type) {
    if (type == "plus") {
        DATE_COUNTER++;
    } else {
        DATE_COUNTER--;
    }
    getTracker();
}

function resetActivity(){
    const slot_id = $("#slot_id").val();
    let activityForm = {slot_id, tracker_date:  getTrackerDate()};

    makePOSTRequest('/timeTracker/delete', activityForm)
        .then((response) => {
            delete TRACKER_LIST[response.slot_id];
            resetSlotDescription(response.slot_id);
            createHeaderText();
            const data = getGraphStats();
            updatePieChartData(data);
        })
        .catch((err) => {
            console.log(err);
            showPopUp('Error', "An unexpected error occured on server.");
        })
        hideItemModal();
}

$('form').on('submit', function(e) {
    e.preventDefault();

    let form = $('form')[0];
    if (form.checkValidity() === true) {

        let trackerForm = $('form#trackerForm').serialize();
        makePOSTRequest('/timeTracker/create', trackerForm)
            .then((response) => {
                TRACKER_LIST[response.slot_id] = response.item;

                updateSlotDescription(response.slot_id, ACTIVITY_CATEGORY[response.item.category], response
                    .item.description);

                createHeaderText();
                const data = getGraphStats();
                updatePieChartData(data);

            })
            .catch((err) => {
                console.log(err);
                showPopUp('Error', "An unexpected error occured on server.");
            })
        hideItemModal();
    } else {
        form.classList.add('was-validated');
    }

});

function activityExists(slotId) {
    return Object.keys(TRACKER_LIST).includes(slotId);
}

function createHeaderText() {
    const slotsFilled = Object.keys(TRACKER_LIST);
    const totalActivities = slotsFilled.length;
    if (totalActivities) {
        const tracker_date = getTrackerDate();
        const today = formatDate(new Date(), false).substring(0, 10);
        const totalActivityUnit = totalActivities > 1 ? 'activites' : 'activity';
        let message = `Hi ${USERNAME}! You have logged in ${totalActivities} ${totalActivityUnit} for the day.`;
        if (tracker_date == today) {
            const currentDate = new Date();
            let currentHour = currentDate.getHours()*2;
            const currentMinutes = currentDate.getMinutes();
            if(currentMinutes>30){
                currentHour += 1;
            }
            
            const activitesLeftForTheDay = slotsFilled.filter(slotTime => slotTime >= currentHour).length;
            const activityLeftUnit = activitesLeftForTheDay > 1 ? 'are' : 'is';
            if (activitesLeftForTheDay) {
                message += ` Out of which ${activitesLeftForTheDay} ${activityLeftUnit} still pending.`;
            }
        }
        $("#headerText").text(message);
        $(".jumbotron").show();
    } else {
        $("#headerText").text("");
        $(".jumbotron").hide();

    }
}

function updatePieChartData(data) {
    PIE_CHART.updateOptions({
        series: data.series,
        labels: data.labels
    });
}

function getStackChartData() {
    const tracker_date = getTrackerDate();
    const url = `/timeTracker/get-weekly-stats?tracker_date=${tracker_date}`;
    makeRequest(url)
        .then((response) => {
            updateStackChartData(response.series, response.categories)
        })
        .catch((err) => {
            console.log(err);
            showPopUp('Error', "An unexpected error occured on server.");
        });
}

function updateStackChartData(series, categories) {
    STACK_CHART.updateOptions({
        series: series,
        xaxis: {
            categories: categories
        }
    });

}

function getGraphStats() {
    let categoryData = {};
    for (const item in TRACKER_LIST) {
        let slot = TRACKER_LIST[item];
        let category = ACTIVITY_CATEGORY[slot.category];
        if (!categoryData[category]) {
            categoryData[category] = 1;
        } else {
            categoryData[category] = categoryData[category] + 1;
        }
    }
    return {
        labels: Object.keys(categoryData),
        series: Object.values(categoryData)
    };
}

function initializePieChart() {
    var pieOptions = {
        title: {
            text: "Summary"
        },
        series: [],
        chart: {
            id: "daySummary",
            width: "100%",
            height: 200,
            type: "donut",
            toolbar: {
                show: true
            },
            fontFamily: 'Open Sans, sans-serif'
        },
        labels: [],
        dataLabels: {
            formatter: function(val, opts) {
                return opts.w.config.series[opts.seriesIndex]*0.5
            },
        },
        tooltip: {
            y: {
                formatter: function(val) {
                    const unit = val > 1 ? 'activites': 'activity';
                    return `${val} ${unit} ~ ${val*0.5} hrs`;
                }
            }
        },
        colors:['#008ffb', '#00e396', '#feb019', '#ff4560', '#775dd0', '#e044a7', '#12239e', "#545b62"],
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
                        value: {
                            show: true,
                            fontSize: '16px',
                            fontFamily: 'Helvetica, Arial, sans-serif',
                            fontWeight: 400,
                            color: undefined,
                            offsetY: 16,
                            formatter: function (val) {
                                return (val*0.5)+"hrs"
                            }
                        },
                        total: {
                            show: true,
                            label: 'Total',
                            fontSize: '16px',
                            fontFamily: 'Helvetica, Arial, sans-serif',
                            fontWeight: 600,
                            color: '#373d3f',
                            formatter: function(w) {
                                let total =  w.globals.seriesTotals.reduce((a, b) => {
                                    return a + b
                                }, 0);
                                return (total*0.5)+" hrs";
                            }
                        }
                    }
                },
            }
        }

    };

    PIE_CHART = new ApexCharts(document.querySelector("#daySummary"), pieOptions);
    PIE_CHART.render();
}

function initializeStackChart() {
    var options = {
        series: [],
        chart: {
            type: 'bar',
            height: 350,
            stacked: true,
            stackType: '100%'
        },
        plotOptions: {
            bar: {
                horizontal: true,
            },
        },
        stroke: {
            width: 1,
            colors: ['#fff']
        },
        title: {
            text: "Last Week Stats"
        },
        xaxis: {
            categories: [],
        },
        tooltip: {
            y: {
                formatter: function(val) {
                    return (val*0.5) + "hrs"
                }
            }
        },
        colors:['#008ffb', '#00e396', '#feb019', '#ff4560', '#775dd0', '#e044a7', '#12239e', "#545b62"],
        fill: {
            opacity: 1

        },
        legend: {
            position: 'top',
            horizontalAlign: 'left',
            offsetX: 40
        }
    };

    STACK_CHART = new ApexCharts(document.querySelector("#weeklySummary"), options);
    STACK_CHART.render();
}
</script>

<div class="container">
<a title="Summary Preview" onclick="generatePreview(this, <?php echo $project['project-id'];?>)"
                                    class="ml-2 btn bg-info text-dark" style="float:right">
                                    <i class="fa fa-eye" aria-hidden="true"></i>
                                </a>
<div class="row  justify-content-center">
    <!-- <div class="col-12  col-md-10 col-lg-7"> -->
        <!-- <div class="card  mt-2"> -->

                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text" style="font-weight: bold;">Name</span>
                        </div>
                        <input type="text" style="background-color:white;" class="form-control" id="name" value="<?= $project['name']?>" readonly>&nbsp;

                        <div class="input-group-prepend">
                            <span class="input-group-text" style="font-weight: bold;">Manager</span>
                        </div>
                        <input type="text" style="background-color:white;" class="form-control" id="manager_id" value="<?= $managerName; ?>" readonly>                                              
                    </div>

                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text" style="font-weight: bold;">Version</span>
                        </div>
                        <input type="text" style="background-color:white;" class="form-control" id="version" value="<?= isset($project['version']) ? $project['version'] : '' ?>" readonly>&nbsp;

                        <div class="input-group-prepend">
                            <span class="input-group-text" style="font-weight: bold;">Start Date</span>
                        </div>
                        <input type="text" style="background-color:white;" class="form-control" id="start-date" value="<?= isset($project['start-date']) ? $project['start-date'] : '' ?>" readonly>&nbsp;

                        <div class="input-group-prepend">
                            <span class="input-group-text" style="font-weight: bold;">End Date</span>
                        </div>
                        <input type="text" style="background-color:white;" class="form-control" id="end-date" value="<?= isset($project['end-date']) ? $project['end-date'] : '' ?>" readonly>                                              
                    </div>

                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text" style="font-weight: bold;">Reviews Count</span>
                        </div>
                        <input type="number" style="background-color:white;" class="form-control" id="reviews-count" value="<?= $reviewsCount; ?>" readonly>&nbsp;

                        <div class="input-group-prepend">
                            <span class="input-group-text" style="font-weight: bold;">Documents Count</span>
                        </div>
                        <input type="number" style="background-color:white;" class="form-control" id="documents-count" value="<?= $docsCount; ?>" readonly>&nbsp;

                        <div class="input-group-prepend">
                            <span class="input-group-text" style="font-weight: bold;">Resources Count</span>
                        </div>
                        <input type="number" style="background-color:white;" class="form-control" id="resources-count" value="<?= $resourcesCount; ?>" readonly>&nbsp;

                        <div class="input-group-prepend">
                            <span class="input-group-text" style="font-weight: bold;">Total Hours Spent</span>
                        </div>
                        <input type="number" style="background-color:white;" class="form-control" id="total-hrs-spent" value="<?= $totalHrsSpent; ?>" readonly>&nbsp;
                    </div>
<div class="col-10 justify-content-center">
<hr/>
<h3 style="text-align:center;"> Distribution of effort in Reviews </h3>
<hr/>

<div class="chart-container"  style="position: relative; height:100vh; width:80vw;">
    <div class="pie-chart-container">
      <canvas id="pie-chart"></canvas>
    </div>
</div>
</div>
<?php if (count($reviewsList) == 0): ?>

<div class="col-10">
  <div class="alert alert-warning" role="alert">
    No Reviews found for this Project.
  </div>
</div>
<?php else: ?>                    

<div class="col-10">
<hr/>
<h3 style="text-align:center;"> Reviews </h3>
<hr/>
<table class="table  table-hover" id="reviews-list">
  <thead class="thead-dark">
    <tr>
      <th scope="col">#</th>
      <th scope="col">Review Type</th>
      <th scope="col">Review</th>
    </tr>
  </thead>
  <tbody class="bg-white">
    <?php foreach ($reviewsList as $key => $row): ?>
        <tr scope="row" id="<?php echo $key;?>">
            <td><?php echo $key+1; ?></td>
            <td><?php echo $row['category']; ?></td>
            <td><?php echo $row['context'];?> </td>
            </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
</div>
<?php endif; ?>

<?php if (count($risksList) == 0): ?>

<div class="col-10">
  <div class="alert alert-warning" role="alert">
    No Risks found for this Project.
  </div>
</div>
<?php else: ?>     
<div class="col-10">
<hr/>
<h3 style="text-align:center;"> Risks </h3>
<hr/>
<table class="table  table-hover" id="risks-list">
  <thead class="thead-dark">
    <tr>
      <th scope="col">#</th>
      <th scope="col">Risk Type</th>
      <th scope="col">Risk</th>
    </tr>
  </thead>
  <tbody class="bg-white">
    <?php foreach ($risksList as $key => $row): ?>
        <tr scope="row" id="<?php echo $key;?>">
            <td><?php echo $key+1; ?></td>
            <td><?php echo $row['risk_type']; ?></td>
            <td><?php echo $row['risk'];?> </td>
            </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
</div>
<?php endif; ?>

<?php if (count($developerWiseHrsSpent) == 0): ?>

<div class="col-10">
  <div class="alert alert-warning" role="alert">
    No Developer hours found for this Project.
  </div>
</div>
<?php else: ?>                    

<div class="col-10">
<hr/>
<h3 style="text-align:center;"> Hours spent by team members </h3>
<hr/>
<table class="table  table-hover" id="developer-hours">
  <thead class="thead-dark">
    <tr>
      <th scope="col">#</th>
      <th scope="col">Name</th>
      <th scope="col">Hours Spent</th>
    </tr>
  </thead>
  <tbody class="bg-white">
    <?php foreach ($developerWiseHrsSpent as $key => $row): ?>
        <tr scope="row" id="<?php echo $key;?>">
            <td><?php echo $key+1; ?></td>
            <td><?php echo $row['name']; ?></td>
            <td><?php echo $row['timeSpent'];?> </td>
            </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
</div>
<?php endif; ?>

<?php //if (count($gapsList) == 0): ?>

<!-- <div class="col-10">
  <div class="alert alert-warning" role="alert">
    No Gaps found for this Project.
  </div>
</div>
<?php //else: ?>     
<div class="col-10">
<hr/>
<h3 style="text-align:center;"> Gaps </h3>
<hr/>
<table class="table  table-hover" id="gaps-list">
  <thead class="thead-dark">
    <tr>
      <th scope="col">#</th>
      <th scope="col">Requirement Type</th>
      <th scope="col">Requirement</th>
    </tr>
  </thead>
  <tbody class="bg-white">
    <?php //foreach ($gapsList as $key => $row): ?>
        <tr scope="row" id="<?php //echo $key;?>">
            <td><?php //echo $key+1; ?></td>
            <td><?php //echo $row['type']; ?></td>
            <td><?php //echo $row['requirement'];?> </td>
            </tr>
        <?php //endforeach; ?>
      </tbody>
    </table>
</div>-->
<?php //endif; ?> 



</div>
<!-- </div> -->
<!-- </div> -->
</div>
<script>
  $(document).ready( function () {

    /*------------------------------------------
      --------------------------------------------
      Get the Pie Chart Canvas 
      --------------------------------------------
      --------------------------------------------*/
      var cData = JSON.parse(`<?php echo $chart_data; ?>`);
      var ctx = $("#pie-chart");
 
      /*------------------------------------------
      --------------------------------------------
      Pie Chart Data 
      --------------------------------------------
      --------------------------------------------*/
      var data = {
        labels: cData.label,
        datasets: [
            {
                datalabels: {
                color: '#FFCE56'
            },
                label: "Reviews Count",
                data: cData.data,
                backgroundColor: [
                    "#FFE4C4",
                    "#FDB45C",
                    "#A9A9A9",
                    "#DB7093",
                    "#DAF7A6",
                    "#ADFF2F",
                    "#46BFBD",,
                    "#4D5360",
                    "#989898",
                    "#CB252B",
                    "#E39371"
                ],
               
                borderWidth: [2, 2, 2, 2, 2,2,2,2,2,2,2]
            },
            
        ]
        
    };

    // var options = {
    //     responsive: true,
    //     title: {
    //         display: true,
    //         position: "top",
    //         text: "Project Reviews -  Category Wise Count",
    //         fontSize: 18,
    //         fontColor: "#111"
    //     },

    //     legend: {
    //         display: true,
    //         position: "right",
    //         labels: {
    //             fontColor: "#333",
    //             fontSize: 16
    //         }
    //     }
    // };

var options = {
  events: false,
  animation: {
    duration: 500,
    easing: "easeOutQuart",
    onComplete: function () {
      var ctx = this.chart.ctx;
      ctx.font = Chart.helpers.fontString(Chart.defaults.global.defaultFontFamily, 'normal', Chart.defaults.global.defaultFontFamily);
      ctx.textAlign = 'center';
      ctx.textBaseline = 'bottom';

      this.data.datasets.forEach(function (dataset) {

        for (var i = 0; i < dataset.data.length; i++) {
          var model = dataset._meta[Object.keys(dataset._meta)[0]].data[i]._model,
              total = dataset._meta[Object.keys(dataset._meta)[0]].total,
              mid_radius = model.innerRadius + (model.outerRadius - model.innerRadius)/2,
              start_angle = model.startAngle,
              end_angle = model.endAngle,
              mid_angle = start_angle + (end_angle - start_angle)/2;

          var x = mid_radius * Math.cos(mid_angle);
          var y = mid_radius * Math.sin(mid_angle);

          ctx.fillStyle = '#000';
          if (i == 3){ // Darker text color for lighter background
            ctx.fillStyle = '#444';
          }
          var percent = String(Math.round(dataset.data[i]/total*100)) + "%";      
          //Don't Display If Legend is hide or value is 0
          if(dataset.data[i] != 0 && dataset._meta[0].data[i].hidden != true) {
            ctx.fillText(dataset.data[i], model.x + x, model.y + y);
            // Display percent in another line, line break doesn't work for fillText
            ctx.fillText(percent, model.x + x, model.y + y + 15);
          }
        }
      });               
    }
  }
};


    /*------------------------------------------
    --------------------------------------------
    create Pie Chart class object
    --------------------------------------------
    --------------------------------------------*/
    var chart1 = new Chart(ctx, {
        type: "pie",
        data: data,
        options: options
    });



    var table = $('#reviews-list').DataTable({
      "responsive": true,
      "autoWidth": false,
      "stateSave": true
    });
    // $('.l-navbar .nav__link, #footer-icons').on('click', function () {
    //   table.state.clear();
    // });

    var table1 = $('#risks-list').DataTable({
      "responsive": true,
      "autoWidth": false,
      "stateSave": true
    });

    var table2 = $('#gaps-list').DataTable({
      "responsive": true,
      "autoWidth": false,
      "stateSave": true
    });
  
  });

  function generatePreview(e, id) {
    var url = '/projects/downloadSummary/2/' + id;
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
                showPopUp("Project Summary", "No file is available to download");
            } 
            response = JSON.parse(response);
            if(response.success == 'True'){
                var host = 'https://'+location.hostname+'/'+response.fileName;
                window.open(host, '_blank');
            }else {
                showPopUp("Project Summary", "Unable to view the summary");
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

<?php $userId = session()->get('id');  ?>
<div class="row p-0 p-md-4 justify-content-center">

<div class="col-12 pt-3 mb-4 pt-md-0 pb-md-0">
</div>
</div>
    <div class="col-12">
      <table id="meetings-list" class="table  table-hover">
      <thead class="thead-dark">
        <tr>
          <th scope="col">#</th>
          <th scope="col" style="min-width:100px">Title</th>
          <th scope="col" style="min-width:80px">Entry Date</th>
          <th scope="col" style="min-width:100px;text-align:center;">Notes</th>
          <th scope="col">Created date</th>
          <th scope="col">Action</th>
        </tr>
      </thead>
      <tbody class="bg-white" id="tbody"></tbody>
        
    </table>
  </div>
  </div>

<script>

var userId, table = null;

$(document).ready(function() {
  userId = <?= $userId ?>;
    

    table = initializeDataTable('meetings-list');

    getTableRecords();

});


$(document).on({
    ajaxStart: function() {
        $("#loading-overlay").show();
    },
    ajaxStop: function() {
        $("#loading-overlay").hide();
    }
});




function getTableRecords(updateStats = false) {
    
    var url = `/meeting/getMeetings`;

    $("#addButton").attr("href", `/meeting/add`);


    makeRequest(url)
        .then((response) => {
            const meetingsList = response.meetings;
            populateTable(meetingsList);
        })
        .catch((err) => {
            console.log(err);
            showPopUp('Error', "An unexpected error occured on server.");
        })

}

function populateTable(meetingsList) {
    dataInfo = {
        "rowId": 'meeting-id',
        "requiredFields": ['title', 'entry-date', 'notes','created_at'],
        "dateFields": ["created_at"],
        "action": [
            {
                title: "View",
                buttonClass: "btn btn-primary",
                iconClass: "fa fa-eye",
                clickTrigger: "view",
                clickParams: ['meeting-id']
            },
        ]
    };

    if (meetingsList.length) {
        table.destroy();
    }

    $('#tbody').html("");
    var data = getHTMLtable(meetingsList, dataInfo);
    $('#tbody').append(data);

    if (meetingsList.length) {
        table = initializeDataTable('meetings-list');
    }

}

function view(id) {
    location.href = `/meeting/add/${id}`;
}

</script>
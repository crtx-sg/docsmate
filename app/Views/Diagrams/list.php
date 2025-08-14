<div class="row p-0 p-md-4 justify-content-center">

    <div class="col-12 pt-3 mb-4 pt-md-0 pb-md-0">
        <div class="btn-group btn-group-toggle ">
            <button onclick="getTableRecords('my')" class="btn btn-primary myDiagrams">My</button>
            <button onclick="getTableRecords('all')" class="btn btn-secondary allDiagrams">All</button>
        </div>
    </div>

    <div class="col-12">
        <table class="table  table-hover" id="diagrams-list">
            <thead class="thead-dark">
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Name</th>
                    <th scope="col">Author</th>
                    <th scope="col">Update date</th>
                    <th scope="col" style="width:175px">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white " id ="tbody"></tbody>
        </table>
    </div>
  
</div>

<script>

    var prevUrlType = "", userId, table;

    $(document).ready( function () {
        userId = <?= session()->get('id') ?>;
        // $('#diagrams-list').DataTable({
        //     "responsive": true,
        //     "autoWidth": false,
        // });
        table = initializeDataTable('diagrams-list');

        <?php //if(isset($_SESSION['PREV_URL'])): 
            helper('Helpers\utils');
		    $prev_url = getPrevUrl(); ?>
            prevUrl = JSON.parse(`<?= json_encode($prev_url) ?>`);
            if(prevUrl){
                if(prevUrl.name == "diagramsList"){
                    prevUrlType = prevUrl.vars.type;
                }
            }
            
        <?php //endif; ?>
        getTableRecords(prevUrlType);
        
    });

    $(document).on({
        ajaxStart: function(){
            $("#loading-overlay").show();
        },
        ajaxStop: function(){ 
            $("#loading-overlay").hide();
        }    
    });

    function getTableRecords(type = ""){
        let url = '/diagrams/getDiagrams?type=';
        if(type != ""){
            url += type; 
            $(".btn-group button").removeClass("btn-primary").addClass("btn-secondary");
            $(`.${type}Diagrams`).removeClass("btn-secondary").addClass("btn-primary");
        }else{
            url += "my"; 
        }

        makeRequest(url)
            .then((response) => {
                var diagramsList = response.diagrams;
                populateTable(diagramsList);
            })
            .catch((err) => {
                console.log(err);
                showPopUp('Error', "An unexpected error occured on server.");
            })
    }

    function populateTable(diagramsList){
        dataInfo = {
            "rowId": 'id',
            "requiredFields": ['diagram_name', 'author', 'updated_at'],
            "dateFields": ["updated_at"],
            "action": [{
                    title: "Preview",
                    buttonClass: "btn btn-info",
                    iconClass: "fa fa-eye",
                    clickTrigger: "previewImage",
                    clickParams: ['diagram_name', 'link']
                },
                {
                    title: "Edit",
                    buttonClass: "btn btn-warning",
                    iconClass: "fa fa-edit",
                    clickTrigger: "edit",
                    clickParams: ['id']
                },
                {
                    title: "Delete",
                    buttonClass: "btn btn-danger",
                    iconClass: "fa fa-trash",
                    clickTrigger: "deleteDiagram",
                    clickParams: ['id'],
                    condition: {
                        on: 'author_id',
                        with: userId
                    }
                }
            ]
        };

        if(diagramsList.length){
            table.destroy();
        }
        
        $('#tbody').html("");
        var data = getHTMLtable(diagramsList, dataInfo);
        $('#tbody').append(data);
       
        if(diagramsList.length){
            table = initializeDataTable('diagrams-list');
        }

        // $('#tbody').html("");
        // $('#tbody').append(getHTMLtable(diagramsList, dataInfo));
    }

    function edit(id){
        location.href = `/diagrams/draw?id=${id}`;
    }

    function deleteDiagram(id){
        bootbox.confirm("Do you really want to delete the diagram?", function(result) {
            if(result){
                const object = {id};
                makePOSTRequest('/diagrams/delete', object)
                    .then((response)=>{
                        $("#"+object.id).fadeOut(800, function() { $(this).remove(); });
                    })
                    .catch((err) => {
                        console.log(err);
                        showPopUp('Error', "An unexpected error occured on server.");
                    })
            }            
        });
        
    }

</script>


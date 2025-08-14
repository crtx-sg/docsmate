<style>
    .truncate {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>
<div class="row p-0 p-md-4 justify-content-center">

    <div class="col-12 pt-3 mb-4 pt-md-0 pb-md-0">

        <div class="row pl-2 pr-2 pb-3 pt-3  mx-auto" style="background: #e9ecef;width: 83%;border-radius: 8px;">
            <div class="col-sm-6 col-md-4 col-lg-auto">
                <div class="form-group mb-0">
                    <label class="font-weight-bold text-muted" for="projects">Project</label>
                    <br />
                    <select class="form-control selectpicker" onchange="getTableRecords()" id="projects" data-style="btn-secondary" data-live-search="true" data-size="8">
                        <option value="" disabled>
                            Select Project
                        </option>
                        <?php foreach ($projects as $key => $value) : ?>
                            <option <?= (($selectedProject == $key) ? "selected" : "") ?> value="<?= $key ?>"><?= $value ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

            </div>

            <div class="col-sm-3 col-md-2 ">
                <div class="form-group mb-0">
                    <label class="font-weight-bold text-muted">Author</label>
                    <br />
                    <div class="btn-group btn-group-toggle ">
                        <label class="myUT btn <?= ($selectedType == "my" ? " btn-primary" : "btn-secondary") ?>">
                            <input type="radio" name="type" value="my" autocomplete="off" onclick="getTableRecords()" <?= ($selectedType == "my" ? "checked" : "") ?>> My
                        </label>
                        <label class="allUT btn <?= ($selectedType == "all" ? " btn-primary" : "btn-secondary") ?>">
                            <input type="radio" name="type" value="all" autocomplete="off" onclick="getTableRecords()" <?= ($selectedType == "all" ? "checked" : "") ?>> All
                        </label>
                    </div>
                </div>
            </div>

        </div>
        <div class="row mt-4 justify-content-center">
            <div class="col-12 col-xl-10">
                <table class="table  table-hover" id="unit-tests-list">
                    <thead class="thead-dark">
                        <tr>
                            <th scope="col" style="width:30px">#</th>
                            <th scope="col" style="width:90px">ID</th>
                            <th scope="col" style="width:150px">Name</th>
                            <th scope="col" style="width:150px">Author</th>
                            <th scope="col" style="width:130px">Created</th>
                            <th scope="col" style="width:90px">Updated</th>
                            <th scope="col" style="width:135px">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white " id="tbody"></tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script>
    var userId, tableName = 'unit-tests-list',
        table = null;

    $(document).on({
        ajaxStart: function() {
            $("#loading-overlay").show();
        },
        ajaxStop: function() {
            $("#loading-overlay").hide();
        }
    });

    $(document).ready(function() {
        userId = <?= session()->get('id') ?>;
        table = initializeDataTable(tableName);

        getTableRecords();
    });

    function getTableRecords() {
        const selectedProjectId = $("#projects").val();
        const type = $("input[name='type']:checked").val();

        $(".btn-group label").removeClass("btn-primary").addClass("btn-secondary");
        $(`.${type}UT`).removeClass("btn-secondary").addClass("btn-primary");

        var url = `/unit-tests/list/project/${selectedProjectId}/type/${type}`;

        $("#addButton").attr("href", `/unit-tests/add/project/${selectedProjectId}`);

        makeRequest(url)
            .then((response) => {
                populateTable(response);
            })
            .catch((err) => {
                console.log(err);
                showPopUp('Error', "An unexpected error occured on server.");
            })

    }

    function populateTable(list) {
        dataInfo = {
            "rowId": 'id',
            "requiredFields": ['utId', 'name', 'author', 'created_at', 'updated_at'],
            "dateFields": [],
            "action": [{
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
                    clickTrigger: "deleteUT",
                    clickParams: ['id', 'name'],
                    condition: {
                        on: 'author_id',
                        with: userId
                    }
                }
            ]
        };

        if (list.length) {
            table.destroy();
        }

        $('#tbody').html("");
        var data = getHTMLtable(list, dataInfo);
        $('#tbody').append(data);

        if (list.length) {
            table = initializeDataTable(tableName);
        }

    }

    function edit(id) {
        location.href = `/unit-tests/edit/${id}`;
    }

    function deleteUT(id, name) {

        bootbox.confirm(`Do you really want to delete ${name}?`, function(result) {
            if (result) {
                $.ajax({
                    url: '/unit-tests/delete/' + id,
                    type: 'DELETE',
                    success: function(response) {
                        response = JSON.parse(response);
                        if (response.success == true) {
                            $("#" + id).fadeOut(800)
                        } else {
                            bootbox.alert('Review not deleted.');
                        }
                    }
                });
            } else {
                console.log('Delete Cancelled');
            }

        });

    }
</script>
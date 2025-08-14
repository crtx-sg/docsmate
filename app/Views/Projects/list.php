<?php
$uri = service('uri');
?>
<div class="row p-0 p-md-4 justify-content-center">
<div class="col-12 pt-3 mb-4 pt-md-0 pb-md-0">
<div class="row">

<div class="col-md-2">
    <div class="form-group mb-0">
    <label class="font-weight-bold text-muted">Product</label><br />
      <select class="form-control selectpicker" onchange="getSelectedStatusData(3)" id="products" name="products" data-style="btn-secondary" data-live-search="true" data-size="8" >
        <option value="ALL">
          All
        </option>
        <?php foreach ($products as $key=>$value): ?>
          <option  <?= (($selectedProduct == $key) ? "selected" : "") ?> value="<?=  $key ?>"><?=  $value ?></option>
        <?php endforeach; ?>
      </select>
    </div>
</div>
<div class="col-md-2">
    <div class="form-group mb-0">
    <label class="font-weight-bold text-muted">Status</label><br />
    <div class="btn-group btn-group-toggle ">
      
      <div class="btn-group btn-group-toggle btn-security-toggle" id="activeblock">
              <div class="btn <?= ((!strpos($uri, '?') || (strpos($uri, 'view=Active'))) ? "btn-primary" : "btn-secondary") ?>" id="1" onclick="getSelectedStatusData(1)">
                <input type="radio" name="status-type" value="Active" id="status-type1" /> Active
              </div>
              <div class="btn <?= (strpos($uri, 'view=Completed') ? "btn-primary" : "btn-secondary") ?>" id="2" onclick="getSelectedStatusData(2)">
                <input type="radio" name="status-type" value="Completed" id="status-type2" /> Completed
              </div>
            </div>
    </div>
</div>
</div>


  </div>
  </div>
<?php if (count($data) == 0): ?>

  <div class="col-12">
    <div class="alert alert-warning" role="alert">
      No records found.
    </div>
  </div>
  <?php else: ?>
    <div class="col-12">
      <table id="project-list" class="table  table-hover">
      <thead >
        <tr>
          <th scope="col">#</th>
          <th scope="col">Project Name</th>
          <th scope="col">Project Version</th>
          <th scope="col">Product</th>
          <th scope="col" style="min-width:300px;">Description</th>
          <th scope="col">Start Date</th>
          <th scope="col">End Date</th>           
          <th scope="col" style="min-width:200px;text-align:center;">Action</th>
          <th scope="col" style="min-width:90px">Update</th>
        </tr>
      </thead>
      <tbody class="bg-white">
        <?php foreach ($data as $key=>$row): ?>
            <tr scope="row" id="<?php echo $row['project-id'];?>">
                <td><?php echo $key+1; ?></td>
                <td><?php echo $row['name'];?></td>
                <td><?php echo $row['version'];?></td>
                <td><?php echo $row['product'];?></td>
                <td style="width:150px !important"><?php echo $row['description'];?></td>
                <td><?php echo $row['start-date'];?></td>
                <td><?php echo $row['end-date'];?></td>
                <td>
                    <a href="/taskboard?project-id=<?php echo $row['project-id'];?>" title="Taskboard" class="btn btn-info">
                      <i class="fas fa-tasks"></i>
                    </a>
                    <a title="Download" href="#" onclick="checkGenerateDocuments(this, <?php echo $row['project-id'];?>)" 
                      class="btn btn-primary ml-2">
                        <i class="fa fa-download"></i>
                    </a>
                    <a title="Docx" <?php echo ($isAllowedToDownload == "True") ? '' : 'hidden';?> href="#" onclick="getWordDocumentFileList(this, <?php echo $row['project-id'];?>)" 
                      class="btn btn-primary ml-2">
                        <i class="fa fa-file-word"></i>
                    </a>
                    <a title="Summary"  href="/projects/summary/<?php echo $row['project-id'];?>" class="btn btn-primary ml-2">
                        <i class="fa fa-bars"></i>
                    </a>
                </td>
                <td>
                    <a href="/projects/add/<?php echo $row['project-id'];?>" class="btn btn-warning ml-2">
                        <i class="fa fa-edit"></i>
                    </a>
                    <?php if (session()->get('is-admin')): ?>
                    <!-- <a onclick="deleteProject(<?php //echo $row['project-id'];?>)" class="btn btn-danger ml-2">
                        <i class="fa fa-trash text-light"></i>
                    </a> -->
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

<?php endif; ?>
</div>



<script>
$(document).ready( function () {
  var table = $('#project-list').DataTable({
    "responsive": true,
    "stateSave": true,
    "autoWidth": false
  });

  $('.l-navbar .nav__link, #footer-icons').on('click', function () {
    table.state.clear();
  });

  var dt = $('#project-list').DataTable();
  //hide the product column
  if($("#products").val() == "ALL")
        dt.column(3).visible(true);
  else
        dt.column(3).visible(false);
  
});

function deleteProject(id){
    bootbox.confirm("Do you really want to delete the project?", function(result) {
      if(result){
        $.ajax({
          url: '/projects/delete/'+id,
          type: 'GET',
          success: function(response){
              response = JSON.parse(response);
              if(response.success == "True"){
                  $("#"+id).fadeOut(800)
              }else{
                bootbox.alert('Project not deleted.');
              }
            }
        });
      }else{
        console.log('Delete Cancelled');
      }

    });

}

function checkGenerateDocuments(e, id){
  var anchor = $(e);
  var iTag  = anchor.find('i');
  url = '/generate-documents/checkGenerateDocuments/'+id;
  $.ajax({
    url: url,
    type: 'GET',
    beforeSend: function() {
      $(anchor).addClass('disabled');
      $(iTag).removeClass('fa-download')
      $(iTag).addClass('fa-spinner fa-spin')
      console.log("before checkGenerateDocuments");
    },
    complete: function(){
      console.log("complete checkGenerateDocuments");
    },
    success: function(response, textStatus, jqXHR){
      if((jqXHR.responseText).indexOf('success') >= 0){
        response = JSON.parse(response);
        if(response.success == 'False'){
          if((response.description == 'Download is deprecated') || (response.description == 'Download path is not available') ){
            generateDocuments(e,id);
          }
          if(response.description == 'No downloads available'){
            showPopUp("Projects", "There are no documents to download");
            $(anchor).removeClass('disabled');
            $(iTag).removeClass('fa-spinner fa-spin');
            $(iTag).addClass('fa-download');
          }
        }
      } else{
        var a = document.createElement('a');
        var binaryData = [];
        binaryData.push(response);
        window.URL.createObjectURL(new Blob(binaryData, {type: "application/zip"}))
        a.href = url;
        document.body.append(a);
        a.click();
        a.remove();
        window.URL.revokeObjectURL(url);
        showPopUp("Project Documents", "File downloaded successfully");  

        $(anchor).removeClass('disabled');
        $(iTag).removeClass('fa-spinner fa-spin');
        $(iTag).addClass('fa-download');
      }
    },
    ajaxError: function (error) {
      showPopUp("Error", error);  
    }
  });
}

function getWordDocumentFileList(e, id) {
  var anchor = $(e);
  var iTag  = anchor.find('i');
  url = '/generate-documents/getWordDocumentFileList/'+id;
  $.ajax({
      url: url,
      type: 'GET',
      beforeSend: function() {
        $(anchor).addClass('disabled');
        $(iTag).removeClass('fa-file-word');
        $(iTag).addClass('fa-spinner fa-spin');
        console.log("before getWordDocumentFileList");
      },
      complete: function(){
        console.log("complete getWordDocumentFileList");
      },
      success: function(response){
        var data = JSON.parse(response);
        if(data.success == 'False'){
          if(data.description == 'No downloads available' || data.description == 'Download path is not available' || data.description == 'Download is deprecated'){
            showPopUp("Project Word Documents", "Latest PDF files are not available to download the word documents, Please download the PDF files.");  
            //remove loader
            $(anchor).removeClass('disabled');
            $(iTag).removeClass('fa-spinner fa-spin');
            $(iTag).addClass('fa-file-word');
          }
        }
        if(data.success == 'True'){
          if(data.data.fileNames){
            var fileNames = data.data.list;
            //Download fresh files using file-name and id
            startPDFDocxConvertion(e, id, fileNames);
          }else{
            var downloadPaths = data.data.list;
            //Download existing files using download path
            var { [Object.keys(downloadPaths).pop()]: lastItem } = downloadPaths;

            Object.keys(downloadPaths).forEach(function(key, index) {
              var path = downloadPaths[key]['download-path'];
              var name = downloadPaths[key]['file-name'];
              var id = downloadPaths[key]['id'];
              var projectId = downloadPaths[key]['project-id'];
              updateDownloadUrl(projectId, id, path, name, false, lastItem['file-name'], downloadPaths, e);
            });
          }
        }

      },
      ajaxError: function (error) {
        showPopUp("Error", error);
      }
    });
}

function startPDFDocxConvertion(e, id, fileNames){
  var anchor = $(e);
  var iTag  = anchor.find('i');

  var { [Object.keys(fileNames).pop()]: lastItem } = fileNames;
  var lastItem = Object.keys(fileNames).filter(function(key) {return fileNames[key] === lastItem})[0];
  
  Object.keys(fileNames).forEach(function(key) {
    url = '/generate-documents/startPDFDocxConvertion/'+id+'/'+key;
    $.ajax({
        url: url,
        type: 'GET',
        beforeSend: function() {
          $(anchor).addClass('disabled');
          $(iTag).removeClass('fa-file-word')
          $(iTag).addClass('fa-spinner fa-spin')
          console.log("before startPDFDocxConvertion");
        },
        complete: function(){
          console.log("complete startPDFDocxConvertion");
        },
        success: function(response){
          var data = JSON.parse(response);
          if(data.success == 'True'){
            originalFile = data.fileName;
            downloadUrl = data.fileDownloadUrl;
            updateDownloadUrl(id, fileNames[originalFile], downloadUrl, originalFile, true, lastItem, fileNames, e);
          } else {
            $(anchor).removeClass('disabled');
            $(iTag).removeClass('fa-spinner fa-spin');
            $(iTag).addClass('fa-file-word');
            showPopUp("Error", data.status + " : " + data.fileName);
          }
        
        },
        ajaxError: function (error) {
          $(anchor).removeClass('disabled');
          $(iTag).removeClass('fa-spinner fa-spin');
          $(iTag).addClass('fa-file-word');
          showPopUp("Error", error);
        }
      });
    
  });
  

}


function updateDownloadUrl(projectId, id, path, name, check, lastItem, fileNames, e) {
  var url = 'generate-documents/updateDownloadUrl';
  var formData = {
    'id': id, 'project-id': projectId, 'path': path, 'name': name, 'isDBUpdate' : check, 'lastItem': lastItem, 'fileNames': fileNames
  }
  var anchor = $(e);
  var iTag  = anchor.find('i');
  $.ajax({
      type: 'POST',
      url: 'generate-documents/updateDownloadUrl',
      data: formData,
      success: function(response, textStatus, jqXHR){
        data = JSON.parse(response);
        if(data.success == "True"){
          if(data.status == "Download-zip-file"){
            console.log("case to zip file:");
            $(anchor).removeClass('disabled');
            $(iTag).removeClass('fa-spinner fa-spin');
            $(iTag).addClass('fa-file-word');
            console.log("completed updateDownloadUrl");
            window.location = data.downloadFile;
            showPopUp("Project Word Documents", "File downloaded successfully");  
          }
        } else {
          $(anchor).removeClass('disabled');
          $(iTag).removeClass('fa-spinner fa-spin');
          $(iTag).addClass('fa-file-word');
          console.log("Unalbe to create zip file due to some docx files missing.");
          showPopUp("Error", "Unable create word documents zip file: Some document files are not coverted. Please try again.");     
        }
      },
      error: function(err) {
          showPopUp("Error", "Error occured on server.");
          $(anchor).removeClass('disabled');
          $(iTag).removeClass('fa-spinner fa-spin');
          $(iTag).addClass('fa-file-word');
      }
  });
}

function generateDocuments(e, id){
  var anchor = $(e);
  var iTag  = anchor.find('i');
  url = '/generate-documents/downloadDocuments/2/'+id;
  $.ajax({
      url: url,
      type: 'GET',
      beforeSend: function() {
        $(anchor).addClass('disabled');
        $(iTag).removeClass('fa-download')
        $(iTag).addClass('fa-spinner fa-spin')
        console.log("before generateDocuments");
      },
      complete: function(){
        $(anchor).removeClass('disabled');
        $(iTag).removeClass('fa-spinner fa-spin');
        $(iTag).addClass('fa-download');
        console.log("complete generateDocuments");
      },
      success: function(response){
        if(response == 'no data'){
          showPopUp("Projects", "There are no documents to download");
        }else if(response == 'unable to create zip file'){
          showPopUp("Projects", "Unable to create a zip folder");
        }else{
          var a = document.createElement('a');
          var binaryData = [];
          binaryData.push(response);
          window.URL.createObjectURL(new Blob(binaryData, {type: "application/zip"}))
          a.href = url;
          document.body.append(a);
          a.click();
          a.remove();
          window.URL.revokeObjectURL(url);
          setTimeout(() => {
            showPopUp("Project Documents", "File downloaded successfully"); 
          }, 1000);
          //insert the new record for the json 
          updateGenerateDocumentPath(id);
        }
      },
      ajaxError: function (error) {
        showPopUp("Error", error);
      }
    });
}

function updateGenerateDocumentPath(id){
  url = '/generate-documents/updateGenerateDocumentPath/'+id;
  $.ajax({
    url: url,
    type: 'GET',
    beforeSend: function() {
      console.log("before updateGenerateDocumentPath");
    },
    complete: function(){
      console.log("complete updateGenerateDocumentPath");
    },
    success: function(response){
      console.log("updated updateGenerateDocumentPath");  
    },
    ajaxError: function (error) {
      showPopUp("Error", error);
    }
  });
}

function getSelectedStatusData(id) {
    var idVal, obj, status, url;
    var selectedProductId = $("#products").val();
    if (id == 3) {
      id = $('#activeblock .btn-primary').attr('id');
    }
    if (id != '') {
      $('#activeblock  div').removeClass('btn-primary').addClass('btn-secondary');
      idVal = "#status-type" + id;
      $(idVal).parent().removeClass("btn-secondary").addClass('btn-primary');
      obj = {
        1: "Active",
        2: "Completed"
      };
      status = obj[id];
      url = `projects?view=${status}&product-id=${selectedProductId}`;
    } else {
      url = `projects?view=Active&product-id=${selectedProductId}`;
    }
       window.location = url;
}

</script>


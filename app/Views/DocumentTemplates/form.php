<style>
  .nav-link.active {
    color: #f8f9fa !important;
    background-color: #6c757d !important;
  }

  .CodeMirror{
    width: 100%;
  }
  .modal-body { 
   max-height: 500px; 
   overflow-y: auto; 
}
</style>
<div class="">
  <div class="row justify-content-center">
    <div class="col-12 col-md-9 mt-3 pt-3 pb-3 ">
      <div class="container">
        
        <div class="row form-color p-3">
          <div class="col-12 col-sm-6">
            <h3><?= $formTitle ?></h3>
          </div>
          <div class="col-12 col-sm-6 ">
            <button type="button" onclick="submitForm()" class="btn btn-primary float-right">Submit</button>
          </div>
        </div>
        <hr>
        <div class="alert-div">
         
        </div>
        <?php if (session()->get('success')): ?>
        <div class="alert alert-success" role="alert">
          <?= session()->get('success') ?>
        </div>
        <?php endif; ?>
        <form class="" action="/documents-templates/<?= $action ?>" method="post">
          <div class="row">
            <?php if (isset($validation)): ?>
            <div class="col-12">
              <div class="alert alert-danger" role="alert">
                <?= $validation->listErrors() ?>
              </div>
            </div>
            <?php endif; ?>
          </div>

          <div class="card form-color">
            <ul class="nav nav-tabs nav-justified mb-3" id="myTab" role="tablist">
              <li class="nav-item">
                <a class="nav-link active lead" id="header-tab" data-toggle="tab" href="#header" role="tab"
                  aria-controls="header" aria-selected="true">Header</a>
              </li>
              <li class="nav-item">
                <a class="nav-link lead" id="section-tab" data-toggle="tab" href="#section" role="tab"
                  aria-controls="section" aria-selected="false">Sections</a>
              </li>
            </ul>

            <div class="tab-content p-4" id="myTabContent">
              <div class="tab-pane fade show active mt-3" id="header" role="tabpanel" aria-labelledby="header-tab">
                <div class="row justify-content-center">
                  <div class="col-12 col-sm-5 ">
                    <div class="form-group">
                      <label class="font-weight-bold text-muted" for="name">Name</label>
                      <input type="text" class="form-control" name="name" id="name" required
                        value="<?= isset($documentTemplate['name']) ? $documentTemplate['name'] : '' ?>">
                    </div>
                  </div>
                </div>
                <div class="row justify-content-center">

                  

                  <div class="col-12 col-sm-6">
                    <div class="form-group">
                      <label class="font-weight-bold text-muted" for="cp-line1">Header 1</label>
                      <input type="text" class="form-control" name="cp-line1" id="cp-line1"
                        value="<?= isset($template["cp-line1"]) ? $template["cp-line1"] : '' ?>">
                    </div>
                  </div>
                  <div class="col-12 col-sm-6">
                    <div class="form-group">
                      <label class="font-weight-bold text-muted" for="cp-line2">Header 2</label>
                      <input type="text" class="form-control" name="cp-line2" id="cp-line2"
                        value="<?= isset($template["cp-line2"]) ? $template["cp-line2"] : '' ?>">
                    </div>
                  </div>

                  

                  <div class="col-12 col-sm-8">
                    <div class="form-group">
                      <label class="font-weight-bold text-muted" for="cp-line3">Title</label>
                      <input type="text" class="form-control" name="cp-line3" id="cp-line3"
                        value="<?= isset($template["cp-line3"]) ? $template["cp-line3"] : '' ?>">
                    </div>
                  </div>

                  <div class="col-12 col-sm-4">
                    <div class="form-group">
                      <label class="font-weight-bold text-muted" for="template-category">Category</label>
                      <select class="form-control selectpicker" id="template-category"  name="template-category" >
                        <option value="" disabled <?= isset($template["template-category"]) ? '' : 'selected' ?>>
                          Select Category
                        </option>
                        <?php foreach ($templateCategory as $category): ?>
                        <option
                          <?= isset($template["template-category"]) ? (($template["template-category"] == $category["value"]) ? 'selected': '') : '' ?>
                          value="<?=  $category["value"] ?>"><?=  $category["value"] ?></option>
                        <?php endforeach; ?>
                      </select>
                    
                    </div>
                  </div>
                  

                  <div class="col-12 col-sm-3">
                    <div class="form-group">
                      <label class="font-weight-bold text-muted" for="cp-line4">Document ID</label>
                      <input type="text" class="form-control" name="cp-line4" id="cp-line4"
                        value="<?= isset($template["cp-line4"]) ? $template["cp-line4"] : '11XXXXX' ?>">
                    </div>
                  </div>

                  <div class="col-12 col-sm-3">
                    <div class="form-group">
                      <label class="font-weight-bold text-muted" for="cp-line5">Revision</label>
                      <input type="text" class="form-control" name="cp-line5" id="cp-line5"
                        value="<?= isset($template["cp-line5"]) ? $template["cp-line5"] : 'Revision X' ?>">
                    </div>
                  </div>

                  <div class="col-12 col-sm-3">
                    <div class="form-group">
                      <label class="font-weight-bold text-muted" for="section-font">Section Font</label>
                      <input type="text" class="form-control" name="section-font" id="section-font"
                        value="<?= isset($template["section-font"]) ? $template["section-font"] : 'Arial' ?>">
                    </div>
                  </div>

                  <div class="col-12 col-sm-3">
                    <div class="form-group">
                      <label class="font-weight-bold text-muted" for="section-font-size">Section Font Size</label>
                      <input type="text" class="form-control" name="section-font-size" id="section-font-size"
                        value="<?= isset($template["section-font-size"]) ? $template["section-font-size"] : '11' ?>">
                    </div>
                  </div>
                  

                  <div class="col-12">
                    <div class="form-group">
                      <label class="font-weight-bold text-muted" for="cp-approval-matrix">Note on First Page</label>
                      <input type="text" class="form-control" name="cp-approval-matrix" id="cp-approval-matrix"
                        value="<?= isset($template["cp-approval-matrix"]) ? $template["cp-approval-matrix"] : 'Refer to APR-MTX-01 in IMSXpress for the latest approval matrix' ?>">
                    </div>
                  </div>

                  <div class="col-12">
                    <div class="form-group">
                      <label class="font-weight-bold text-muted" for="cp-change-history">Change History</label>
                      <textarea class="form-control" name="cp-change-history"
                        id="cp-change-history"><?= isset($template["cp-change-history"]) ? $template["cp-change-history"] : '' ?></textarea>
                    </div>
                  </div>
                </div>
               

              </div>

              <div class="tab-pane fade mt-3" id="section" role="tabpanel" aria-labelledby="section-tab">
                <div class="row justify-content-md-between">
                  <div class="col col-lg-2">
                    <button type="button" class="btn btn-dark">
                      Total <span class="badge badge-light" id="totalSections">0</span>
                    </button>
                  </div>
                  <div class="col col-lg-2">
                    <a onclick="addSection('NEW')" title="Add Section" class="btn btn-success float-right">
                      <i class="fa fa-plus text-light"></i>
                    </a>
                  </div>
                </div>

                <div class="row sections-container"></div>
              </div>
            </div>


          </div>




        </form>
      </div>
    </div>
  </div>
</div>

<script>
var sectionCount = 0;
var sectionID = 0;
var tablesLayout;
var existingTypes;
var templateId = "";
var templateType = "";
var sections;
class Section {
  constructor(){
    this.id = "";
    this.title =  '';
    this.type =  '';
    this.content =  '';
    this.tableName = '';
    this.headerColumns = '';
    this.contentColumns = ''
  }
}

$(document).ready(function(){
  tablesLayout = <?= $tablesLayout ?>;
  
  existingTypes = '<?= $existingTypes ?>';
  <?php if (isset($template)): ?>
    var template = <?=  json_encode($template) ?>;
    templateId = "<?= $documentTemplate['id'] ?>";
    templateType = "<?= $documentTemplate['type'] ?>";
    
    sections = template.sections;
    
    for(var i=0 ; i<sections.length; i++){
      var sectionTitle = sections[i].title;
      var sectionType = sections[i].type;
      var sectionContent = sections[i].content;
      var sectionTableName = sections[i].tableName;
      var sectionHeaderColumns = sections[i].headerColumns;
      var sectionContentColumns = sections[i].contentColumns;
      

      addSection("EXISTING");

      $(`#title_${sectionID}`).val(sectionTitle);
      $(`#type_${sectionID}`).val(sectionType);
      $(`#content_${sectionID}`).val(sectionContent);

      if(sectionType == "database"){
        toggleSectionType(`type_${sectionID}`);
        $(`#db_${sectionID}`).find("select.section_table").selectpicker('val', sectionTableName);
        //$("#table-preview").show();
        var column_text = $(`#db_${sectionID}`).find("select.section_column_text");
        var column_value = $(`#db_${sectionID}`).find("select.section_column_value");
        for(var key in tablesLayout) {
     
          if(tablesLayout[key]["name"] == sectionTableName){
            
            var columns = tablesLayout[key].columns.split(',');
            var options = "";
            for(var j=0; j< columns.length; j++){
              options += `<option value="${columns[j]}"> 
                              ${columns[j]} 
                          </option>`;
            }
            if(key == "UnitTest"){
              column_text.append(`<option value="name"> 
                                    name 
                                </option>`);
            }else{
              column_text.append(options);
            }
            column_value.append(options);
            
            column_text.selectpicker('refresh');
            column_value.selectpicker('refresh');
            $(column_text).selectpicker('val', sectionHeaderColumns.split(','));
            $(column_value).selectpicker('val', sectionContentColumns.split(','));
            
          }
        }



      }
      
    }
    
  <?php endif; ?>

  
});

$("#section-tab").click(function(){
    if(sections != undefined){
      setTimeout(function(){ 
        for(var z =1; z<=sections.length; z++){
          var contentName = 'content_'+z;
          var $cm = $('textarea[name="'+contentName+'"]').nextAll('.CodeMirror')[0].CodeMirror;
          $cm.refresh();
        }
      }, 500);
    }
  });

function submitForm(){
  var name = $("#name").val().trim();
  var name_arr = name.toLowerCase().split(',');
  var type = "";
  var templateCategory = $("#template-category").val();

  if(templateType == ""){
    if(name_arr.length){
      for(var i = name_arr.length-1; i>0 ; i--){
        type += name_arr[i]+"-"
      }
      type += name_arr[0];
    }else{
      type = name;
    }
    if(existingTypes.includes(type)){
        $('.alert-div').html(returnAlert('Name already exists.'));
      return;
    }
  }else{
    type = templateType;
  }
  

  if(name == ""){
    $('.alert-div').html(returnAlert('Name cannot be empty.'));
  }else if(spaceCheck(name))
  {
   $('.alert-div').html(returnAlert('Name should not contain spaces.'));
  }else if(templateCategory == null){
    $('.alert-div').html(returnAlert('Please assign a "Category" to the template.'));
  }else{
    var cp_icon = "";
    var cp_line1 = $("#cp-line1").val();
    var cp_line2 = $("#cp-line2").val();
    var cp_line3 = $("#cp-line3").val();
    var cp_line4 = $("#cp-line4").val();
    var cp_line5 = $("#cp-line5").val();
    var cp_approval_matrix = $("#cp-approval-matrix").val();
    
    var section_font = $("#section-font").val();
    var section_font_size = $("#section-font-size").val();

    const $codemirror = $('textarea[name="cp-change-history"]').nextAll('.CodeMirror')[0].CodeMirror;
    var cp_change_history = $codemirror.getValue();

    var sectionArray = getSectionsData();
    if(sectionArray.length){
      var sectionsJson = sectionArray;
      
      var template_json_object = {"cp-icon":cp_icon, "template-category": templateCategory, "cp-line1": cp_line1, "cp-line2": cp_line2, "cp-line3": cp_line3,
        "cp-line4": cp_line4, "cp-line5": cp_line5, "cp-approval-matrix": cp_approval_matrix, "cp-change-history": cp_change_history,
        "section-font": section_font, "section-font-size": section_font_size, "sections": sectionsJson };
      var mainJson = {};
      mainJson[type] = template_json_object;
      // console.log(mainJson);

      addTemplate(name, type, JSON.stringify(mainJson));
    }
    
  }


}

function spaceCheck(name){
    if (name.match(/\s/g)){
        return true;
    } else {
        return false;
    }
}

function createSectionHeader(){
  sectionCount++;
  sectionID++;
  var header = "";
  header += `<div class="card-header">
                <div class="row" style="margin-bottom:-10px">
                  <div class="col-6">
                    <p class="lead">Section</p>
                  </div>
                  <div class="col-6">
                    <a onclick="deleteSection('sec${sectionID}')" title="Delete Section" class="btn btn-sm btn-danger float-right">
                      <i class="fa fa-trash text-light"></i>
                    </a>
                  </div>
                </div>
              </div>`;
  return header;
}

function createSectionBody(){
  var body = "";
  tableOptions = returnOptions("tables");
  body += `<div class="card-body">
              <div class="form-row">
                <label class="col-xl-3 col-form-label font-weight-bold text-muted">Title</label>
                <input type="text" id='title_${sectionID}' class="form-control  col-xl-9 section_input  ">
              </div>
              <div class="text_fields">
                <div class="form-row mt-3">
                  <label class="col-xl-3 col-form-label font-weight-bold text-muted">Type</label>
                  <select id='type_${sectionID}' onchange="toggleSectionType('type_${sectionID}')" class="form-control col-xl-9 section_type" name="type" id="type">
                    <option value="text">Text</option>
                    <option value="database">Database</option>
                  </select>
                </div>

                <div class="database_fields d-none" id='db_${sectionID}'>
                  <div class="form-row mt-3">
                    <label class="col-xl-3 col-form-label font-weight-bold text-muted">Table Name</label>
                    <select class="form-control col-xl-9 section_table selectpicker"  name="table_name" id="templates-select-id" onchange="tableChange('db_${sectionID}', this)">
                      ${tableOptions}
                    </select>
                  </div>
                  <div class="form-row mt-3">
                  <a href="#" id="table-preview"  onclick="getTablePreview('db_${sectionID}');">Preview</a>
                  </div>
                  <div class="form-row mt-3">
                    <label class="col-xl-3 col-form-label font-weight-bold text-muted">Columns Text</label>
                    <select class="form-control col-xl-9 section_column_text selectpicker" name="table_columns_text"></select>
                  </div>
                  <div class="form-row mt-3">
                    <label class="col-xl-3 col-form-label font-weight-bold text-muted">Columns Value</label>
                    <select class="form-control col-xl-9 section_column_value selectpicker" data-actions-box="true" multiple name="table_columns_value"></select>
                  </div>
                </div>

                <div class="form-row mt-3">
                  <label class="col-xl-3 col-form-label font-weight-bold text-muted">Content</label>
                  <textarea id='content_${sectionID}' name='content_${sectionID}' class="form-control section_content"></textarea>
                </div>
              </div>
             
            </div>`;
  return body;
}

function addSection(flag){
 
  var header = createSectionHeader();
  var body = createSectionBody();

  var card = `<div class="col-12 col-xl-6 mt-3" id="sec${sectionID}">
                  <div class="card">`;
  card += header;
  card += body;
  card += ` </div>
          </div>`;

  $(".sections-container").append(card);
  $(".section_table").selectpicker();
  $("#totalSections").text(sectionCount.toString());
  // var $cm = $('textarea[name="content_'+sectionID+'"]').nextAll('.CodeMirror')[0];
  // if ($cm == undefined){
    
  // }
  if(flag == "NEW"){
    var simplemde = new SimpleMDE({
            element: $(`#content_${sectionID}`)[0],
            status: [{
                     className: "characters",
                     defaultValue: function(el) {
                        el.innerHTML = "0";
                     },
                     onUpdate: function(el) {
                        el.innerHTML = simplemde.value().length;
                     }
                  }],
            showIcons: ["code", "table"],
      });
    
  }

    
}

function deleteSection(id){
  $("#"+id).remove();
  sectionCount--;
  $("#totalSections").text(sectionCount.toString());
}

function returnOptions(type){
  var options = "";
 
  if(type == 'tables'){
    options += `<option value="" disabled selected>Select</option>`;
    for(var key in tablesLayout) {
      options += `<option value="${tablesLayout[key].name}"> 
                      ${key} 
                  </option>`;
    }
    return options; 
  }else{
    for(var key in tablesLayout) {
     
      if(key == type){
        
        var columns = tablesLayout[key].columns.split(',');
        for(var i=0; i< columns.length; i++){
          options += `<option value="${columns[i]}"> 
                          ${columns[i]} 
                      </option>`;
        }
        return options; 
      }
      
    }
    return options; 
  }
}

function tableChange(parentID, tableNameDD){
  var column_text = $("#"+parentID).find("select.section_column_text");
  var column_value = $("#"+parentID).find("select.section_column_value");
  var tableName = tableNameDD.selectedOptions[0].text;
  
  var options = returnOptions(tableName);

  column_text.empty();
  column_value.empty();
  if(tableName == "UnitTest"){
    column_text.append(`<option value="name"> 
                          name 
                      </option>`);
    
  }else{
    column_text.append(options);
  }
 
  column_value.append(options);
  
  column_text.selectpicker('refresh');
  column_value.selectpicker('refresh');

  var defaultSelectedColumnText = tablesLayout[tableName]["columns"].split(',')[0];
  if(tableName == "UnitTest"){
    $(column_text).selectpicker('val', 'name');
  }else{
    $(column_text).selectpicker('val', defaultSelectedColumnText);
  }
}

function toggleSectionType(id){
  var type = $("#"+id).val();
  var arr = id.split('_');
  
  if(type == "database"){
    $("#db_"+arr[1]).removeClass('d-none');
  }else{
    $("#db_"+arr[1]).addClass('d-none');
  }
}

function getSectionsData(){
  var allTitles = $(".section_input");
  var allTypes = $(".section_type");
  var allContents = $(".section_content");
  var tables = $("select.section_table");
  var columns_text = $("select.section_column_text");
  var columns_value = $("select.section_column_value");
  var sectionArray = [];
  
  if(allTitles.length > 0){
    $('.alert-div').html("");
    for(var i=0; i<allTitles.length; i++){
      var title = allTitles[i].value;
      var type = allTypes[i].value;
      // var content = allContents[i].value;
      var $codemirror = $(allContents[i]).nextAll('.CodeMirror')[0].CodeMirror;
      var content = $codemirror.getValue();
      var parent = allTitles[i].parentElement.parentElement;
      $(parent.getElementsByClassName('alert')).remove();
      if(title == ""){
        sectionArray = [];
        var msg = 'Section title cannot be empty';
        $(parent).prepend(returnAlert(msg));
        showPopUp("Validation Error", msg);
        break;
      }
      if(type == "database"){
        var table = tables[i].value;
        var column_text = $(columns_text[i]).val();
        var column_value = $(columns_value[i]).val();
        if(table == "" || column_text == "" || column_value == ""){
          sectionArray = [];
          var msg = 'Table name, column text and column values of a section cannot be empty'
          $(parent).prepend(returnAlert(msg));
          showPopUp("Validation Error", msg);
          break;
        }

        var section = new Section();
        section.id = "section"+(i+1);
        section.title =  title;
        section.type =  type;
        section.content = content;
        section.tableName = table;
        section.headerColumns = column_text;
        section.contentColumns = column_value.join();

        sectionArray.push(section);
      }else{

        var section = new Section();
        section.id = "section"+(i+1);
        section.title =  title
        section.type =  type;
        section.content = content;

        sectionArray.push(section);
      }
      
    }
    return sectionArray;
  }else{
    $('.alert-div').html(returnAlert('Please add atleast 1 section.'));
  }
}

function returnAlert(message){
  return '<div class="alert alert-danger" role="alert">'+message+'</div>';
}

function addTemplate (name, type, json) {
  var successMessage = "Template added successfully!."
  if(templateId != ""){
    successMessage = "Template updated successfully!."
  }
  var formData = {
            'id'   : templateId,
            'name' : name,
            'type' : type,
            'template-json-object' : json
  };
   
   $.ajax({
     type: 'POST',
     url: '/documents-templates/addTemplate',
     data: formData,
     success: function (response) {
       
       response = JSON.parse(response);
       if (response.success == "True") {
        templateId = response.id;
        showPopUp("Success", successMessage);
       } else {
        showPopUp("Failure", "Failed to add a new template!.");
       }
     },
     error: function (err) {
       console.log(err);
     }
   })

 }

function getTablePreview(id) {
  var tableName = $("#"+id).find("select.section_table").val();
  console.log("selected table: ", tableName);
  if(tableName == "" || tableName == null){
    bootbox.alert({
      message: "Please select a table to preview.",
      size: 'small'
    });
  }
  else{
  $.ajax({
  type: 'GET',
  url: '/documents-templates/getTableContent/' + tableName,
  success: function (response) {
    var data = JSON.parse(response);
    var tabContent = data.result;
      // Extract value from table header. 
      let col = [];
      for (let i = 0; i < tabContent.length; i++) {
        for (let key in tabContent[i]) {
          if (col.indexOf(key) === -1) {
            col.push(key);
          }
        }
      }
      // Create table.
      const table = document.createElement("table");

      // Create table header row using the extracted headers above.
      let tr = table.insertRow(-1);                   // table row.

      for (let i = 0; i < col.length; i++) {
        let th = document.createElement("th");      // table header.
        th.innerHTML = col[i];
        tr.appendChild(th);
      }

      // add json data to the table as rows.
      for (let i = 0; i < tabContent.length; i++) {

        tr = table.insertRow(-1);

        for (let j = 0; j < col.length; j++) {
          let tabCell = tr.insertCell(-1);
          tabCell.innerHTML = tabContent[i][col[j]];
        }
      }

      // Now, add the newly created table with json data, to a popup.
      bootbox.alert({
        title: tableName +" table", 
        message: table,
        size: 'large'
      });

    }});
  }
}

</script>
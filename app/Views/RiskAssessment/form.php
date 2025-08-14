<style>
.box {
  box-shadow: 0px 1px 22px -12px #607D8B;
  background-color: #fff;
  padding: 10px 35px 10px 30px;
  border-radius: 8px;
}

.box-header {
  border-bottom: 1px solid;
  font-size: 19px !important;
  height:35px;
}

.activeDiv{
  border-left: 1px solid #ddd;
  border-right: 1px solid #ddd;
  border-bottom: 1px solid #ddd;
  padding:10px;
  border-radius: 8px;
  background: #e9ecef;
  word-wrap: break-word;
  /* white-space:pre-wrap; */
}

br + br { display: none; }

</style>

<form class="" action="/risk-assessment/<?= $action ?>" method="post">

    <div class="row pt-2 justify-content-center">
        <div class="col-6">

            <!-- <div class="alert alert-success" role="alert">
            Something
            </div> -->
            <?php if (session()->get('success')): ?>
            <div class="alert alert-success" role="alert">
                <?= session()->get('success') ?>
            </div>
            <?php endif; ?>
            <?php if (isset($validation)): ?>
            <div class="alert alert-danger" role="alert">
                <?= $validation->listErrors() ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="row p-0 pr-md-4 pl-md-4 pt-1 mb-2">
        <div class="col-12 col-md-6">
            <div class="row">
                <div class="col-12 col-sm-12">
                    <div class="box">
                        <div class="text-center box-header"><span><?= $id != ""? "RA-".$id: "Add Risk"?></span></div>
                        <div class="mt-3 box-body">
                            <div class="row">
                                <div class="col-12 p-1">
                                    <div class="form-group">
                                        <label class="font-weight-bold text-muted" for="risk">Risk</label>
                                        <input type="text" class="form-control" name="risk" id="risk"
                                            value="<?= isset($member['risk']) ? htmlentities($member['risk']) : '' ?>">
                                    </div>
                                </div>

                                <div class="col-12 col-md-6 p-1">
                                    <div class="form-group" readonly="readonly" id="risk_type_selection">
                                        <label class="font-weight-bold text-muted" for="project">Project</label>
                                        <select class="form-control  selectpicker" data-live-search="true" data-size="8"
                                            name="project" id="project">
                                            <option value="" disabled
                                                <?= (isset($member['project_id']) && ($member['project_id'] != 0) ) ? '' : 'selected' ?>>
                                                Select Project
                                            </option>
                                            <?php foreach ($projects as $key=>$value): ?>
                                            <option
                                                <?= isset($member['project_id']) ? (($member['project_id'] == $key) ? 'selected': '') : '' ?>
                                                value="<?=  $key ?>"><?=  $value ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6 p-1">
                                    <div class="form-group" id="risk_name">
                                        <label class="font-weight-bold text-muted" for="risk_type">Risk Type</label>
                                        <select class="form-control  selectpicker" data-live-search="true" data-size="8"
                                            name="risk_type" id="risk_type" onchange="toggleVulnerability()">
                                            <option value="" disabled
                                                <?= isset($member['risk_type']) ? '' : 'selected' ?>>
                                                Select Risk
                                            </option>
                                            <?php foreach ($riskCategory as $key=>$value): ?>
                                            <option
                                                <?= isset($member['risk_type']) ? (($member['risk_type'] == $value) ? 'selected': '') : '' ?>
                                                value="<?=  $value ?>"><?=  $value ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="font-weight-bold text-muted" for="risk-methodology">Risk Methodology</label><br />
                                        <select readOnly class="form-control" data-live-search="true" data-size="8"
                                            name="risk-methodology" id="riskMethod">
                                            <option value="" disabled <?= isset($member['risk_type']) ? '' : 'selected' ?>>
                                                Select Risk Methodology
                                            </option>
                                            <?php foreach ($riskMethodologies as $key=>$value): ?>
                                            <option
                                                <?= isset($member['risk_type']) ? (($member['risk_type'] == $value) ? 'selected': '') : '' ?>
                                                value="<?=  $value ?>"><?=  $value ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-12 mt-3">
                    <div class="box">
                        <div class="text-center box-header">
                          <span>Risk Details</span>
                          <a class="btn btn-sm btn-dark float-right text-light" onclick="toggleDetailsView()"><i class="fas fa-exchange-alt"></i></a>
                        </div>
                        <div class="risk-details-html"></div>
                        <div class="risk-details-fields mt-3 box-body">
                            <div class="row">

                                <div><input type="hidden" id="form-status" value="<?= $isEdit; ?>" /></div>
                                <div class="col-12" id="software_name">
                                    <div class="form-group">
                                        <label class="font-weight-bold text-muted" for="software_name">Software Name</label>
                                        <input type="text" class="form-control" name="software_name" id="software_name"
                                            value="<?= isset($member['software_name']) ? htmlentities($member['software_name']) : '' ?>">
                                    </div>
                                </div>

                                <div class="col-12" id="type">
                                    <div class="form-group">
                                        <label class="font-weight-bold text-muted" for="type">Type</label>
                                        <input type="text" class="form-control" name="type" id="type"
                                            value="<?= isset($member['type']) ? htmlentities($member['type']) : '' ?>">
                                    </div>
                                </div>

                                <div class="col-12" id="version">
                                    <div class="form-group">
                                        <label class="font-weight-bold text-muted" for="version">Version</label>
                                        <input type="text" class="form-control" name="version" id="version"
                                            value="<?= isset($member['version']) ? htmlentities($member['version']) : '' ?>">
                                    </div>
                                </div>

                                <div class="col-12" id="latest_version">
                                    <div class="form-group">
                                        <label class="font-weight-bold text-muted" for="latest_version">Latest Version</label>
                                        <input type="text" class="form-control" name="latest_version" id="latest_version"
                                            value="<?= isset($member['latest_version']) ? htmlentities($member['latest_version']) : '' ?>">
                                    </div>
                                </div>
                                
                                <div class="col-12" id="vulnerability">
                                    <div class="form-group">
                                        <label class="font-weight-bold text-muted" for="vulnerability">Vulnerability</label>
                                        <input type="text" class="form-control" name="vulnerability" id="vulnerability"
                                            value="<?= isset($member['vulnerability']) ? htmlentities($member['vulnerability']) : '' ?>">
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="font-weight-bold text-muted" for="initial_risk_priority_number">Initial Risk Priority Number</label>
                                        <input type="text" class="form-control" name="initial_risk_priority_number" id="initial_risk_priority_number"
                                            value="<?= isset($member['initial_risk_priority_number']) ? htmlentities($member['initial_risk_priority_number']) : '' ?>">
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="font-weight-bold text-muted" for="residual_risk_priority_number">Residual Risk Priority Number</label>
                                        <input type="text" class="form-control" name="residual_risk_priority_number" id="residual_risk_priority_number"
                                            value="<?= isset($member['residual_risk_priority_number']) ? htmlentities($member['residual_risk_priority_number']) : '' ?>">
                                    </div>
                                </div>



                                <div class="col-12">
                                    <div class="form-group" id='risk_description'>
                                        <label class="font-weight-bold text-muted" for="description">Risk Description</label>
                                        <textarea style="min-height: 165px;" class="form-control scroll scroll-dark" name="risk_description" id="risk_description" title="Identification of known or foreseeable hazards43(and their causes) associated with the device based on the intended use; reasonably foreseeable misuse whether intentional or unintentional; and the impacts to safety, treatment, and/or diagnosis."><?=
                  isset($member['risk_description']) ? trim($member['risk_description']) : ''
                  ?></textarea>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group" id='initial_risk_evaluation'>
                                        <label class="font-weight-bold text-muted"
                                            for="initial_risk_evaluation">Initial Risk Evaluation</label>
                                        <textarea style="min-height: 165px;" class="form-control scroll scroll-dark" name="initial_risk_evaluation" id="initial_risk_evaluation" title="This includes assessment of acceptability (e.g., acceptable, not acceptable) and need for risk reduction (control) measures as defined in the risk management plan."><?=
                  isset($member['initial_risk_evaluation']) ? trim($member['initial_risk_evaluation']) : ''
                  ?></textarea>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group" id='benefit_risk_analysis'>
                                        <label class="font-weight-bold text-muted" for="benefit_risk_analysis">Benefit Risk Analysis</label>
                                        <textarea style="min-height: 165px;" class="form-control scroll scroll-dark" name="benefit_risk_analysis" id="benefit_risk_analysis" title="If a residual risk is deemed not acceptable according to the acceptability criteria in the risk management plan and further risk control is not possible, the sponsor should provide documented benefit-risk analysis to demonstrate that the benefits of the intended use outweigh the residual risk."><?=
                  isset($member['benefit_risk_analysis']) ? trim($member['benefit_risk_analysis']) : ''
                  ?></textarea>
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <div class="form-group" id='residual_risk_evaluation'>
                                        <label class="font-weight-bold text-muted" for="residual_risk_evaluation">Residual Risk Evaluation</label>
                                        <textarea style="min-height: 165px;" class="form-control scroll scroll-dark" name="residual_risk_evaluation" id="residual_risk_evaluation" title="This includes assessment of acceptability (e.g., acceptable, not acceptable) as defined in the risk management plan."><?=
                  isset($member['residual_risk_evaluation']) ? trim($member['residual_risk_evaluation']) : ''
                  ?></textarea>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group" id='risk_analysis'>
                                        <label class="font-weight-bold text-muted" for="risk_analysis">Risk Analysis</label>
                                        <textarea style="min-height: 165px;" class="form-control scroll scroll-dark" name="risk_analysis" id="risk_analysis"><?=
                  isset($member['risk_analysis']) ? trim($member['risk_analysis']) : ''
                  ?></textarea>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group" id='risk_control_measures'>
                                        <label class="font-weight-bold text-muted" for="risk_control_measures">Risk Control Measures</label>
                                        <textarea style="min-height: 165px;" class="form-control scroll scroll-dark" name="risk_control_measures"
                                            id="risk_control_measures" title="This should include the risk control measures in Design, Protective measures, Information for safety and also the verification of the implementation of the risk control measures and verification of the effectiveness of the implemented risk control measures."><?=
                  isset($member['risk_control_measures']) ? trim($member['risk_control_measures']) : ''
                  ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="col-12 col-md-6">
            <div class="box">
                <div class="text-center box-header"><span id="rpnHeading">Assign Risk Acceptability</span></div>
                <div class="mt-3 box-body">
                    <div class="row">

                        <div class="col-12 text-center" id="data-open-issue-soup-matrix">
                            <?php foreach ($fmeaList as $key=>$value): ?>
                            <div>
                                    <?php if (($value['id']) < 3) : ?>
                                <div class="form-group">
                                    <label class="font-weight-bold text-muted"><?php echo $value['category'];?></label>
                                    <br />
                                            <div class="btn-group btn-group-toggle btn-security-toggle" id="listblock<?php echo $key; ?>">
                                                <?php foreach ($value['options'] as $key1 => $value1) : ?>
                                                    <div class="btn btn-sm <?php echo (($value['value']) ==  $value1['title']) ? "btn-primary" : "btn-secondary"; ?> " id="RDanchor<?php echo $key;
                                                                                                                                                                                    echo $key1; ?>" data-toggle="popover" data-placement="left" data-content="<?php echo $value1['description']; ?>" title="<?php echo $value['category'] . " - " . $value1['title']; ?>" onclick="calculateRAValue(<?php echo $key; ?> ,<?php echo $key1; ?>)">
                                                        <input type="radio" name="<?php echo $value['category']; ?>-status-type" value="<?php echo $value1['value'] . '/' . $value1['title']; ?>" <?php echo (($value['value']) ==  $value1['title']) ? "checked" : ""; ?> />
                                                        <?php echo $value1['title']; ?>
                                        </div>
                                        &nbsp;
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="col-12 col-sm-6 mt-3" id="data-open-issue-soup-rpn-matrix">
                            <div class="form-group">
                                <label class="font-weight-bold text-muted" for="rav">Risk Acceptability Level(RAL)</label>
                                <input type="text" class="form-control" name="rav" id="rav" readonly value="<?= isset($fmeaList[2]["value"]) ? $fmeaList[2]["value"] : '' ?>">
                                <input type="text" class="form-control" name="rpn" id="rpn" hidden value="<?= isset($member['CVSS_3_1_base_risk_assessment']) ? $member['CVSS_3_1_base_risk_assessment'] : '' ?>">
                            </div>
                        </div>

                        <div class="col-12" id="data-vulnerability-matrix">
                            <div class="row">
                                <?php $count=0; foreach ($cvssList as $key=>$value): $count++;?>
                                <div class="<?= ($count == 1) ? 'col-7 pl-0' : 'col-5 pl-4 pr-0 text-right' ?>">
                                    <div class="form-group">
                                        <?php if($key !='Score'): ?>
                                        <div class="row">
                                            <div class="col-12">
                                                <label class="font-weight-bold text-muted">
                                                    <h6><?php echo $key; ?></h6>
                                                </label>
                                            </div>
                                        </div>
                                        <?php foreach ($value as $key1=>$value1): ?>
                                        <div class="col-12">
                                            <div class="form-group" style="height:100%">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <label
                                                            class="font-weight-bold text-muted"><?php echo $value1['category']; ?></label>
                                                    </div>
                                                </div>

                                                <div class="btn-group btn-group-toggle btn-vulnerability-toggle"
                                                    id="vulnerability<?php echo str_replace(' ', '', $value1['category']);?>">
                                                    <?php foreach ($value1['options'] as $key2=>$value2):?>
                                                    <div class="btn btn-sm <?php echo (($value1['value']) ==  $value2['title'])? "btn-primary" : "btn-secondary"; ?> "
                                                        <?php echo $key2;?>
                                                        id="matrixAnchor<?php echo str_replace(' ', '', $value1['category']);echo $key2;?>"
                                                        title="<?php echo $value1['category']." - ".$value2['title'];?>"
                                                        data-toggle="popover" data-placement="left" 
                                                        data-content="<?php echo $value2['description']; ?>"
                                                        onclick="toggleVulnerabilityTabs('<?php echo str_replace(' ', '', $value1['category']);?>', <?php echo $key2;?>)">
                                                        <input type="radio"
                                                            name="<?php echo str_replace(' ', '', $value1['category']);?>-status-type"
                                                            class="<?php echo str_replace(' ', '', $value1['category']);?>-status-type"
                                                            value="<?php echo $value2['value'].'/'.$value2['title'];?>"
                                                            <?php echo (($value1['value']) ==  $value2['title'])? "checked" : ""; ?> />
                                                        <?php echo $value2['title'];?>
                                                    </div>
                                                    &nbsp;
                                                    <?php endforeach?>
                                                </div>
                                            </div>
                                        </div>

                                        <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 mt-3" id="data-vulnerability-baseScore-matrix">
                            <div class="form-group">
                                <label class="font-weight-bold text-muted" for="baseScore">CVSS 3.1 Base Risk Assessment</label>
                                <input type="text" class="form-control" name="baseScore" id="baseScore" readonly
                                    value="<?= (isset($member['CVSS_3_1_base_risk_assessment']) && $member['CVSS_3_1_base_risk_assessment'] !=0 ) ? $member['CVSS_3_1_base_risk_assessment'] : '' ?>">
                            </div>
                        </div>



                        <div class="col-12 col-sm-6 mt-3">
                            <div class="form-group">
                                <label class="font-weight-bold text-muted" for="status">Status</label>
                                <select class="form-control  selectpicker" data-live-search="true" data-size="8"
                                    name="status" id="status">
                                    <option value="" disabled <?= isset($member['status']) ? '' : 'selected' ?>>
                                        Select
                                    </option>
                                    <?php foreach ($riskStatus as $value): ?>
                                    <option
                                        <?= isset($member['status']) ? (($member['status'] == $value) ? 'selected': '') : '' ?>
                                        value="<?=  $value ?>"><?=  $value ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>


                        <div class="col-12  mt-3">
                            <div class="row justify-content-center">
                                <div class="col-2">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>

</form>

<script>
$(document).ready(function() {
    toggleVulnerability();
    $('[data-toggle="popover"]').popover({
        trigger: "hover"
    })
});

let htmlView = false
function toggleDetailsView(){
  if(!htmlView){
    const detailsFields = $(".risk-details-fields textarea:visible");
    const detailsLabels = $(".risk-details-fields label:visible");
    $(".risk-details-fields").hide();
    $(detailsFields).each(function(index, el ){
      const label = "<label style='background:#fff;padding: 0px 8px 6px 8px; border-radius: 10px;' class='font-weight-bold text-muted pt-2' > "+$(detailsLabels[index]).text() + "</label>" ;
      const detail = $(el).val();
      const html = SimpleMDE.prototype.markdown(detail);
      $(".risk-details-html").append(label+html);
    });
      $(".risk-details-html").addClass("activeDiv");
  }else{
    $(".risk-details-html").removeClass("activeDiv");
    $(".risk-details-html").html("");
    $(".risk-details-fields").show();
  }
  htmlView = !htmlView;
}

    function calculateRAValue(id, id1) {
    //removeing the all primary class and checked type and  added secondary class
    $('#listblock' + id + ' div').removeClass('btn-primary').addClass('btn-secondary');
    $('#listblock' + id + ' input').removeAttr('checked');
    //adding primary class to selected one
    var idVal = "#RDanchor" + id + id1;
    $(idVal).removeClass("btn-secondary").addClass('btn-primary');
    //calculating the rpn and adding checked attribute to get the values in controller
    var activeList = $('.btn-security-toggle .btn-primary input');
        var risk = "Medium";
    var rpn = 1;
        const activeRisk = [];
    for (var i = 0; i < activeList.length; i++) {
        $(activeList[i]).attr('checked', true);
        rpn = rpn * ($(activeList[i]).val()).split('/')[0];
            if (activeList.length == 2) {
                activeRisk.push(($(activeList[i]).val()).split('/')[0]);
                if(i==1){
                    if(activeRisk[0] == 4 && activeRisk[1] > 1){
                        risk = "High";
                    } else if(activeRisk[0] == 3 && activeRisk[1] > 2){
                        risk = "High";
                    } else if(activeRisk[0] == 3 && activeRisk[1] == 1){
                        risk = "Low";
                    } else if(activeRisk[0] == 2 && activeRisk[1] < 2){
                        risk = "Low";
                    } else if(activeRisk[0] == 1){
                        risk = "Low";
                    }
                }
            }
        }
        $('#rav').val(risk);
        $('#rpn').val(rpn);
}

function toggleVulnerabilityTabs(id, id1) {
    $('#vulnerability' + id + ' div').removeClass('btn-primary').addClass('btn-secondary');
    $('#vulnerability' + id + ' input').removeAttr('checked');
    var idVal = "#matrixAnchor" + id + id1;
    $(idVal).removeClass("btn-secondary").addClass('btn-primary');
    var activeList = $('.btn-vulnerability-toggle .btn-primary input');
    var rpn = 1;
    var postDataClaMatrix = {
        'AttackVector': '',
        'AttackComplexity': '',
        'PrivilegesRequired': '',
        'UserInteraction': '',
        'Scope': '',
        'ConfidentialityImpact': '',
        'IntegrityImpact': '',
        'AvailabilityImpact': ''
    };
    var PR_Changed_Data = {
        'None': 0.85,
        "Low": 0.68,
        "High": 0.5
    };
    for (var i = 0; i < activeList.length; i++) {
        $(activeList[i]).attr('checked', true);
        var scopeName = ($(activeList[i]).val()).split('/')[1]

        var selName = ($(activeList[i]).attr('name')).replace('-status-type', '');
        var selNameVal = ($(activeList[i]).val()).split('/')[0];
        postDataClaMatrix[selName] = selNameVal;

        //#Checking the PR values based on the selected SCOPE
        if ($(activeList[i]).attr('name') == 'Scope-status-type' && scopeName == 'Changed') {
            var PRV = $('input[name=PrivilegesRequired-status-type]:checked').val();
            var NLW = (PRV != '' && PRV != undefined) ? (PRV.split('/')[1]) : '';
            postDataClaMatrix['PrivilegesRequired'] = PR_Changed_Data[NLW];
        }
    }
    if ($('input[name=Scope-status-type]:checked').val() != undefined) {
        var scopeAttr = ($('input[name=Scope-status-type]:checked').val()).split('/')[1];
        calculateBaseScore(postDataClaMatrix, scopeAttr);
    }
}

function toggleVulnerability() {
    var selVal = $("#risk_type").val();
    console.log("selVal:", selVal);
    if(selVal != null){
      if(selVal == "Vulnerability" || selVal == 'Software Of Unknown Provenance'){
        $("#riskMethod").val('CVSS 3.1');
        $("#rpnHeading").text("CVSS 3.1 Base Risk Assessment");
      } else {
        $("#riskMethod").val('Risk Acceptability Matrix');
        $("#rpnHeading").text("Assign Risk Acceptability");
      }
    }
    //Toggle text-area boxes based on the category selection
    $('#software_name, #type, #version, #latest_version, #vulnerability').css('display','none');
    if (selVal == 'Vulnerability' || selVal == 'Software Of Unknown Provenance') {
        //If its Edit-form no need to change the description changes, Else display soup boxes..
        $('#software_name, #type, #version, #latest_version, #vulnerability').css('display','block');
    }
        
    if (selVal != 'Vulnerability' && selVal != 'Software Of Unknown Provenance') {
        $('#data-open-issue-soup-matrix, #data-open-issue-soup-rpn-matrix').css('display', 'block');
        $('#data-vulnerability-matrix, #data-vulnerability-baseScore-matrix').css('display', 'none');
    } else if (selVal == 'Vulnerability' || selVal == 'Software Of Unknown Provenance') {
        $('#data-open-issue-soup-matrix, #data-open-issue-soup-rpn-matrix').css('display', 'none');
        $('#data-vulnerability-matrix, #data-vulnerability-baseScore-matrix').css('display', 'block');
    } else {
        $('#data-vulnerability-matrix, #data-vulnerability-baseScore-matrix').css('display', 'none');
    }
}

function calculateBaseScore(data, scopeAt) {
    var CVSS_exploitabilityCoefficient = 8.22;
    var CVSS_scopeCoefficient = 1.08;
    var baseScore;
    var impactSubScore;
    var exploitabalitySubScore = CVSS_exploitabilityCoefficient * data['AttackVector'] * data['AttackComplexity'] *
        data['PrivilegesRequired'] * data['UserInteraction'];
    var impactSubScoreMultiplier = (1 - ((1 - data['ConfidentialityImpact']) * (1 - data['IntegrityImpact']) * (1 -
        data['AvailabilityImpact'])));
    if (scopeAt === 'Unchanged') {
        impactSubScore = data['Scope'] * impactSubScoreMultiplier;
    } else {
        impactSubScore = data['Scope'] * (impactSubScoreMultiplier - 0.029) - 3.25 * Math.pow(impactSubScoreMultiplier -
            0.02, 15);
    }
    if (impactSubScore <= 0) {
        baseScore = 0;
    } else {
        if (scopeAt === 'Unchanged') {
            baseScore = CVSSroundUp1(Math.min((exploitabalitySubScore + impactSubScore), 10));
        } else {
            baseScore = CVSSroundUp1(Math.min((exploitabalitySubScore + impactSubScore) * CVSS_scopeCoefficient, 10));
        }
    }
    $('#baseScore').val(baseScore);
}

function CVSSroundUp1(d) {
    return Math.ceil(d * 10) / 10;
}
</script>
<?php namespace App\Controllers;

use App\Models\ProjectModel;
use App\Models\TeamModel;
use App\Models\RiskAssessmentModel;
use App\Models\SettingsModel;
use CodeIgniter\I18n\Time;
use TP\Tools\Pandoc;
use TP\Tools\PandocExtra;
use App\Models\RiskCategoryModel;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class RiskAssessment extends BaseController
{
	public function index()
    {
		$data = [];

		$data['pageTitle'] = 'Risk Assessment';
		$data['addBtn'] = true;
		$data['addUrl'] = "/risk-assessment/add";
		// $data['AddMoreBtn'] = true;
		// $data['AddMoreBtnText'] = "Get Risks";
		$data['backUrl'] = '/risk-assessment';

		$status = $this->request->getVar('status');
		$type = $this->request->getVar('type');
		$data['isSyncEnabled'] = false;
		$data['riskCategorySelected'] = 'Vulnerability';

		$model = new RiskAssessmentModel();
		// if($status == 'sync'){
		// 	$status = '';
		// 	$project_id = $this->request->getVar('project_id');
		// 	$res = $this->syncRecords($project_id);
		// 	$data['isSyncEnabled'] = true;
		// 	$data["data"] = $model->getRisks('All', 'Vulnerability');
		// }else{
			if($status == '' && $type == '') {
				$data["data"] = $model->getRisks('All', 'Vulnerability');
			}else {
				$data["data"] = $model->getRisks($status, $type);
				$data['riskCategorySelected'] = $type;
			}	
		//}
		// $pandoc = new Pandoc();
		// foreach($data['data'] as $key=>$item){
		// 	$convertData = $pandoc->convert($data['data'][$key]['hazard-analysis'], "gfm", "html5");
		// 	$data['data'][$key]['hazard-analysis'] = $convertData;
		// }
		session()->set('prevUrl', '');
		$projectModel = new ProjectModel();
        $data['projects'] = $projectModel->getProjects(); 
		helper('Helpers\utils');
		$activeProject = getActiveProjectId();	
		if($activeProject != ""){
			$selectedProject = $activeProject;
			$data['selectedProject'] = $selectedProject;
			$data['riskCategory'] = $this->getRiskTypecategories();
		}else{
			$data['riskCategory'] = [];
		}
		
		echo view('templates/header');
		echo view('templates/pageTitle', $data);
		echo view('RiskAssessment/list',$data);
		echo view('templates/footer');
	}

	// function syncRecords($id){
	// 	$model = new RiskAssessmentModel();

	// 	$sonarRecords = $model->getSonarRecords();
	// 	$vulnerabilitiesList = $model->getVulnerabilitiesList();
	// 	if( $sonarRecords ){
	// 		$recordsList = array();
	// 		//filter records, remove duplicate risknames and append details in description
	// 		foreach( $sonarRecords as $vulnRecord){
				
	// 			$isDuplicateMesaage = false;

	// 			foreach( $recordsList as $x => $val ){
	// 				if( $vulnRecord->message == $val['risk'] ){
	// 					$isDuplicateMesaage = true;
	// 					$description['filename'] = substr($vulnRecord->component, stripos($vulnRecord->component, ":") + 1, strlen($vulnRecord->component));
	// 					$description['textRange'] = $vulnRecord->textRange;
	// 					$description['tags'] = $vulnRecord->tags;

	// 					$descriptionObj = (object) $description;
	// 					array_push($recordsList[$x]['description'], $descriptionObj); 

	// 					break;
	// 				} 
	// 			}

	// 			if( ! $isDuplicateMesaage ){
	// 				$descriptionArray = array();
	// 				$description['filename'] = substr($vulnRecord->component, stripos($vulnRecord->component, ":") + 1, strlen($vulnRecord->component));
	// 				$description['textRange'] = $vulnRecord->textRange;
	// 				$description['tags'] = $vulnRecord->tags;
	// 				$descriptionObj = (object) $description;
	// 				array_push($descriptionArray, $descriptionObj);
	// 				$data = [
	// 					// 'project' => $this->request->getVar('project'),
	// 					'risk_type' => $vulnRecord->type,
	// 					'risk' => $vulnRecord->message,
	// 					'component' => substr($vulnRecord->component, 0, stripos($vulnRecord->component, ":")),
	// 					'risk_description' => $descriptionArray
	// 				];
	// 				array_push($recordsList, $data);
	// 			}
	// 		}
	// 		foreach( $recordsList as $record){
	// 			$isRecordExist = false;
	// 			foreach( $vulnerabilitiesList as $key=>$vul ){
	// 				if ($record['risk'] == $vul['risk']) {
	// 					$isRecordExist = true;
	// 					$newDescArray = [];
	// 					$existingDescArray =  json_decode($vul['risk_description']);
	// 					foreach( $record['risk_description'] as $a ){
	// 						$isExist = false;
	// 							foreach( $existingDescArray as $b ){
	// 								if ($a->filename == $b->filename && $a->textRange == $b->textRange) {
	// 									$isExist = true;
	// 								}	
	// 							}
	// 						if( ! $isExist ){
	// 							array_push($newDescArray, $a);
	// 						}
	// 					}
	// 					if( count($newDescArray) > 0 ){
	// 						try {
	// 							$updatedDescArray = $existingDescArray;
	// 							foreach( $newDescArray as $new ){
	// 								array_push($updatedDescArray, $new);
	// 							}
	// 							$res = $model->updateVulnerabilityDescription($vul['id'], json_encode($updatedDescArray));
	// 						} catch(Exception $e){
	// 							error_log("[Docsgo] [RiskAssessment.syncRecords] [ERROR] Error on updating vulnerabilities description.");
	// 						}	
	// 					}
	// 					break;
	// 				}		
	// 			}
	// 			if( ! $isRecordExist ){
	// 				try {
	// 					$newData = [
	// 						'project_id' => $id,
	// 						'risk_type' => $record['risk_type'],
	// 						'risk' => $record['risk'],
	// 						'component' => $record['component'],
	// 						'risk_description' => json_encode($record['risk_description']),
	// 						'status' => 'Open'
	// 					];
	// 					$model->save($newData);
	// 				} catch(Exception $e) {
	// 					error_log($e);
	// 				}	
	// 			}
	// 		}

	// 		return true;
	// 	} else {
	// 		error_log("[Docsgo] [RiskAssessment.syncRecords] [INFO] Sonarqube vulnerabilities list is empty.");
	// 		return true;
	// 	}	
	// }

	function add(){
		$id = $this->request->getVar('id');
		helper(['form']);
		$model = new RiskAssessmentModel();
		$data = [];
		$data['pageTitle'] = 'Risk Assessment';
		$data['addBtn'] = False;
		$dataList = [];
		$data['riskCategory'] = $this->getRiskTypecategories();
		$data['riskStatus'] = ['Open', 'Close'];

		$projectModel = new ProjectModel();
		$data['projects'] = $projectModel->getProjects(); 

		$settingsModel = new SettingsModel();
        $riskMethodOptions = $settingsModel->getConfig("riskMethodologyCategory");
        $data["riskMethodologies"] = $riskMethodOptions; //Status Radio Buttons
		
		// if ($riskMethodOptions != null) {
        // 	if ($this->request->getVar('risk_type') == 'Vulnerability') {
        //         $data["selectedRiskMethod"] = $riskMethodOptions[1]; //Default status
        //     } else {
        //         $data["selectedRiskMethod"] = $riskMethodOptions[0];
        //     }
        // }
		//Handling the back page navigation url
		if(isset($_SERVER['HTTP_REFERER'])){
			$urlStr = $_SERVER['HTTP_REFERER'];
			if (strpos($urlStr, 'status')) {
				$urlAr = explode("status", $urlStr);
				$backUrl = '/risk-assessment?status'.$urlAr[count($urlAr)-1];
				session()->set('prevUrl', $backUrl);
			}else{
				if(session()->get('prevUrl') == ''){
					session()->set('prevUrl', '/risk-assessment');
				}
			}
		}else{
			session()->set('prevUrl', '/risk-assessment');
		}
		$data['backUrl'] =  session()->get('prevUrl');
		

		$rules = [
			'project'=> 'required',
			'risk_type'=> 'required',
			'risk' => 'required|min_length[3]|max_length[255]'
		];	

		$initialJsonObj = file_get_contents(APPPATH."Templates/riskAssesment.json");

// 		$SOUP_description = '
// {
// 	"version": " version",
// 	"purpose": "Purpose of SOUP module",
// 	"validation": ""
// 	}
	
// **Reference for the OTS **
// https://www.fda.gov/regulatory-information/search-fda-guidance-documents/shelf-software-use-medical-devices
// Guidance Document Id: FDA-2019-D-3598
// Guidance Document issued on September 27, 2019. 
// As per FDA Guidance document "Off-The-Shelf Software Use in Medical Devices" section A. Basic Documentation for OTS Software, the below details are provided

// **What is it?**
// For each component of OTS Software used, the following should be specified:
// * Title and Manufacturer of the OTS Software.
// * Version Level, Release Date, Patch Number, and Upgrade Designation, as appropriate.
// * Any OTS Software documentation that will be provided to the end user.
// * Why is this OTS Software appropriate for this medical device?
// * What are the expected design limitations of the OTS Software?

// **What are the Computer System Specifications for the OTS Software?**

// For what configuration will the OTS Software be validated? The following should be specified:
// * Hardware specifications: processor (manufacturer, speed, and features), RAM (memory size), hard disk size, other storage, communications, display, etc.
// * Software specifications: operating system, drivers, utilities, etc. The software requirements specification (SRS) listing for each item should contain the name (e.g., Windows 10, Excel, Sun OS, etc.), specific version levels (e.g., 4.1, 5.0, etc.) and a complete list of any patches that have been provided by the OTS Software manufacturer.

// **How will you assure appropriate actions are taken by the End User?**

// * What aspects of the OTS Software and system can (and/or must) be installed/configured?
// * What steps are permitted (or must be taken) to install and/or configure the product?
// * How often will the configuration need to be changed?
// * What education and training are suggested or required for the user of the OTS
// * Software?
// * What measures have been designed into the medical device to prevent the operation of any non-specified OTS Software, e.g., word processors, games? Operation of nonspecified OTS Software may be prevented by system design, preventive measures, or labeling. Introduction may be prevented by disabling input (USB, CD, modems).

// **What does the OTS Software do?**
// What function does this OTS Software provide in this device? This is equivalent to the software requirements in the Guidance for the Content of Premarket Submissions for Software Contained in Medical Devices for this OTS Software. The following should be specified:

// * What is the OTS Software intended to do? The sponsor’s design documentation should specify exactly which OTS components will be included in the design of the medical device and to what extent OTS Software is involved in error control and messaging in device error control.

// * What are the links with other software including software outside the medical device (not reviewed as part of this or another application)? The links to outside software should be completely defined for each medical device/module. The design documentation should include a complete description of the linkage between the medical device software and any outside software (e.g., networks).

// **How do you know it works?**

// * Based on the Level of Concern:
// * Describe testing, verification, and validation of the OTS Software and ensure it is appropriate for the device hazards associated with the OTS Software. (See Note 1.)
// * Provide the results of the testing. (See Note 2.)
// * Is there a current list of OTS Software problems (bugs) and access to updates?

// **How will you keep track of (control) the OTS Software?**

// * What measures have been designed into the medical device to prevent the introduction of incorrect versions? On startup, ideally, the medical device should check to verify that all software is the correct title, version level, and configuration. If the correct software is not loaded, the medical device should warn the operator and shut down to a safe state.
// * How will you maintain the OTS Software configuration?
// * Where and how will you store the OTS Software?
// * How will you ensure proper installation of the OTS Software?
// * How will you ensure proper maintenance and life cycle support for the OTS Software?';
// $SOUP_hazard_analysis = '
// **OTS Software Hazard Analysis**
					
// List of all potential hazards identified, estimated severity of each identified hazard and list of all potential causes of each identified hazard.

// | Hazard Id | Hazard description | Severity  | Causes
// | -------- | -------- | -------- |-------- |
// | Text     | Text     | Text     | Text     |
// | Text     | Text     | Text     | Text     |

// **OTS Software Hazard Mitigation**

// Hazard mitigation activities may seek to reduce the severity of the hazard, the likelihood of the occurrence, or both. Hazard mitigation interventions may be considered in three categories with the following order of precedence:
// * Design (or redesign)
// * Protective measures (passive measures)
// * Warning the user (labeling)

// **Residual Risk**
// The residual risk assessment after mitigation is given below.';

		$data["id"] = $id;
		if($id == ""){
			$data['action'] = "add";
			$data['formTitle'] = "Add Risk Assessment";
			$data['member']['status'] = 'Open';
			$data['jsonObj'] = json_decode($initialJsonObj, true);
			// $data['member']['hazard-analysis-soup'] = $SOUP_hazard_analysis;
			// $data['member']['description-soup'] = $SOUP_description;
			$data['isEdit'] = false;
		}else{
			$data['action'] = "add?id=".$id;
			$data['member'] = $model->where('id',$id)->first();
			$data['formTitle'] = 'Update Risk Assessment (RA-'.$data['member']['id'].')';
			$data['jsonObj'] = json_decode($data['member']['assessment'], true);
			if($data['jsonObj'] == ''){
				$data['jsonObj'] = json_decode($initialJsonObj, true);
			}
			$data['isEdit'] = true;
		}

		$data['fmeaList'] = $data['jsonObj']['risk-assessment']['fmea'];
		$data['cvssList'] = $data['jsonObj']['risk-assessment']['cvss'][0];
		$rules = [
			'project'=> 'required',
			'risk_type'=> 'required',
			'risk' => 'required|min_length[3]|max_length[255]',
		];	
		if ($this->request->getMethod() == 'post') {
			$newData = [
				'project_id' => $this->request->getVar('project'),
				'risk_type' => $this->request->getVar('risk_type'),
				'risk' => $this->request->getVar('risk'),
				'status' => $this->request->getVar('status')
			];
			//if($data['isEdit']) {
				//if($this->request->getVar('risk_type') == 'Scope-Items'){
					//failure_mode, harm, cascade_effect
					if($this->request->getVar('risk_type') == 'Vulnerability' || $this->request->getVar('risk_type') == 'Software Of Unknown Provenance'){
						$newData['software_name'] = $this->request->getVar('software_name');  
						$newData['type'] = $this->request->getVar('type');
						$newData['version'] = $this->request->getVar('version');
						$newData['latest_version'] = $this->request->getVar('latest_version');
						$newData['vulnerability'] = $this->request->getVar('vulnerability');
					}
					$newData['risk_description'] = $this->request->getVar('risk_description');  
					$newData['initial_risk_evaluation'] = $this->request->getVar('initial_risk_evaluation');
					$newData['risk_analysis'] = $this->request->getVar('risk_analysis');
					$newData['risk_control_measures'] = $this->request->getVar('risk_control_measures');
					$newData['residual_risk_evaluation'] = $this->request->getVar('residual_risk_evaluation');
					$newData['benefit_risk_analysis'] = $this->request->getVar('benefit_risk_analysis');
					$newData['initial_risk_priority_number'] = $this->request->getVar('initial_risk_priority_number');
					$newData['residual_risk_priority_number'] = $this->request->getVar('residual_risk_priority_number');
			//}
			// }else{
			// 	//here we are differing the contents for SOUP or Vulnarebility & open-issues
			// 	if($this->request->getVar('risk_type') == 'Scope-Items' || $this->request->getVar('risk_type') == 'Open-Issue'){
			// 		//failure_mode, harm, cascade_effect
			// 		$newData['failure_mode'] = $this->request->getVar('failure_mode');  
			// 		$newData['harm'] = $this->request->getVar('harm');
			// 		$newData['cascade_effect'] = $this->request->getVar('cascade_effect');
			// 		$newData['hazard-analysis'] = $this->request->getVar('hazard-analysis');
			// 		$newData['description'] = ($this->request->getVar('risk_type') == 'Open-Issue')? $this->request->getVar('description'):'';
			// 	}else if($this->request->getVar('risk_type') == 'SOUP'){
			// 		$newData['description'] = $this->request->getVar('description-soup');
			// 		$newData['hazard-analysis'] = $this->request->getVar('hazard-analysis-soup');
			// 	}else{
			// 		$newData['description'] = $this->request->getVar('description');
			// 		$newData['hazard-analysis'] = $this->request->getVar('hazard-analysis');
			// 	}
			// }
			//if description and hazard text is removed and try to push as empty, then fill those data automatically
			// if($this->request->getVar('risk_type') == 'SOUP'){
			// 	if(trim($newData['description']) == '')
			// 		$newData['description'] = $SOUP_description;
			// 	if(trim($newData['hazard-analysis']) == '')
			// 		$newData['hazard-analysis'] = $SOUP_hazard_analysis;
			// }

			$riskType = $this->request->getVar('risk_type');
			$postDataMatrix = array(
				'Severity'=>'','Occurrence' =>'','Detectability' => '','RAL' => '',
				'Attack Vector' => '','Attack Complexity' => '','Privileges Required' => '','User Interaction' =>'', 'Scope' => '',
				'Confidentiality Impact' => '', 'Integrity Impact' => '','Availability Impact' => '','base_score' => ''
			);
			if($riskType != 'Vulnerability'){
				$postDataMatrix['Severity'] = ($this->request->getVar('Severity-status-type')) ? explode('/', $this->request->getVar('Severity-status-type'))[1] : '';			
				$postDataMatrix['Occurrence'] = ($this->request->getVar('Occurrence-status-type')) ? explode('/', $this->request->getVar('Occurrence-status-type'))[1] : '';	
				$postDataMatrix['Detectability'] = ($this->request->getVar('Detectability-status-type')) ? explode('/', $this->request->getVar('Detectability-status-type'))[1] : '';
				$postDataMatrix['RAL'] = $this->request->getVar('rav');
				$newData['CVSS_3_1_base_risk_assessment'] = $this->request->getVar('rpn');
			}
			if($riskType == 'Vulnerability'){
				$postDataMatrix['Attack Vector'] = ($this->request->getVar('AttackVector-status-type')) ? explode('/', $this->request->getVar('AttackVector-status-type'))[1] : '';
				$postDataMatrix['Attack Complexity'] = ($this->request->getVar('AttackComplexity-status-type')) ? explode('/', $this->request->getVar('AttackComplexity-status-type'))[1] : '';
				$postDataMatrix['Privileges Required'] = ($this->request->getVar('PrivilegesRequired-status-type')) ? explode('/', $this->request->getVar('PrivilegesRequired-status-type'))[1] : '';
				$postDataMatrix['User Interaction'] = ($this->request->getVar('UserInteraction-status-type')) ? explode('/', $this->request->getVar('UserInteraction-status-type'))[1] : '';
				$postDataMatrix['Scope'] = ($this->request->getVar('Scope-status-type')) ? explode('/', $this->request->getVar('Scope-status-type'))[1] : '';
				$postDataMatrix['Confidentiality Impact'] = ($this->request->getVar('ConfidentialityImpact-status-type')) ? explode('/', $this->request->getVar('ConfidentialityImpact-status-type'))[1] : '';
				$postDataMatrix['Integrity Impact'] = ($this->request->getVar('IntegrityImpact-status-type')) ? explode('/', $this->request->getVar('IntegrityImpact-status-type'))[1] : '';
				$postDataMatrix['Availability Impact'] = ($this->request->getVar('AvailabilityImpact-status-type')) ? explode('/', $this->request->getVar('AvailabilityImpact-status-type'))[1] : '';
				$postDataMatrix['base_score'] = $this->request->getVar('baseScore');
				$newData['CVSS_3_1_base_risk_assessment'] = $this->request->getVar('baseScore');
			}
			foreach($data['jsonObj']['risk-assessment']['fmea'] as $key=>$value){
				$data['jsonObj']['risk-assessment']['fmea'][$key]['value'] = $postDataMatrix[$value['category']];
			}
			foreach($data['jsonObj']['risk-assessment']['cvss'] as $key=>$value){
				foreach($value as $key1=>$value1){
					foreach($value1 as $key2=>$value2){
						$data['jsonObj']['risk-assessment']['cvss'][$key][$key1][$key2]['value'] = $postDataMatrix[$value2['category']];
					}
				}
			}
			$newData['assessment'] = json_encode($data['jsonObj']);

			$data['member'] = $newData;
			$data['fmeaList'] = $data['jsonObj']['risk-assessment']['fmea'];
			$data['cvssList'] = $data['jsonObj']['risk-assessment']['cvss'][0];

			if (! $this->validate($rules)) {
				$data['validation'] = $this->validator;
				// $data['member']['hazard-analysis'] = '';
				// $data['member']['description'] = '';	
				// $data['member']['hazard-analysis-soup'] = $SOUP_hazard_analysis;
				// $data['member']['description-soup'] = $SOUP_description;	
			}else{
				if($id > 0){
					$list = [];
					$currentTime = gmdate("Y-m-d H:i:s");
					$newData['id'] = $id;
					$newData['update_date'] = $currentTime;
					$message = 'Risk Assessment successfully updated.';
				}else{
					$data['member'] = [];
					// $data['member']['hazard-analysis-soup'] = $SOUP_hazard_analysis;
					// $data['member']['description-soup'] = $SOUP_description;		
					$data['member']['status'] = 'Open';		
					$data['jsonObj'] = json_decode($initialJsonObj, true);
					$data['fmeaList'] = $data['jsonObj']['risk-assessment']['fmea'];
					$data['cvssList'] = $data['jsonObj']['risk-assessment']['cvss'][0];			
					$message = 'Risk Assessment successfully added.';
				}

				$model->save($newData);
				$session = session();
				$session->setFlashdata('success', $message);
			}
		}

		echo view('templates/header');
		echo view('templates/pageTitle', $data);
		echo view('RiskAssessment/form', $data);
		// echo view('templates/footer');
	}

	private function getRiskTypecategories() {
		$model = new RiskCategoryModel();
		$riskCategory = $model->getRiskCategories();
		return $riskCategory;
	}

	private function getRiskTypecategories1() {
		$settingsModel = new SettingsModel();
		$riskCategory = $settingsModel->where("identifier","riskCategory")->first();
		if($riskCategory["options"] != null){
			$data = json_decode( $riskCategory["options"], true );
		}else{
			$data = [];
		}
		return $data;
	}

	public function delete(){
		if (session()->get('is-admin')){
			$id = $this->request->getVar('id');
			$model = new RiskAssessmentModel();
			$model->delete($id);
			$response = array('success' => "True");
			echo json_encode( $response );
		}
		else{
			$response = array('success' => "False");
			echo json_encode( $response );
		}
	}

	public function import() {
		try{
			$filePath = getenv('app.basePath');
			$attachmentsDir = "uploads/risk";
			$fileLinks = $this->uploadFiles($attachmentsDir);
			if(count($fileLinks)){
					$file_name = $fileLinks['link'];
					$arr_file 	= explode('.', $file_name);
					$extension 	= end($arr_file);
			}
		
			if('csv' == $extension) {
				$reader 	= new \PhpOffice\PhpSpreadsheet\Reader\Csv();
			} else {
				$reader 	= new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
			}
			$spreadsheet 	= $reader->load($filePath.$file_name);
			$sheet_data 	= $spreadsheet->getActiveSheet()->toArray();
			$list 			= [];
			$projectModel = new ProjectModel();
			foreach($sheet_data as $key => $val) {
				if($key != 0) {
						$list [] = [
							'project_id' =>  $projectModel->getProjectId(trim($val[0])),
							'risk'		 => $val[1],
							'software_name'	=> $val[2],
							'type'	=> $val[3],
							'version' => $val[4],
							'latest_version' => $val[5],
							'risk_analysis'			=> $val[6],
							'risk_control_measures' 			=> $val[7],
							'residual_risk_evaluation'				=> $val[8],
							'benefit_risk_analysis'				=> $val[9],
							'vulnerability'				=> $val[10],
							'CVSS_3_1_base_risk_assessment'				=> $val[11],
							'risk_type' =>"Software Of Unknown Provenance",
							'status' => "Open"
						];
					//}
				}
			}
			if(file_exists($filePath.$file_name))
				unlink($filePath.$file_name);
			if(count($list) > 0) {
				$model = new RiskAssessmentModel();
				$result 	= $model->bulkInsert($list);
				$session = session();
				if($result) {
					$json = [
						'success_message' 	=> "All Entries are imported successfully."
					];
				} else {
					$json = [
						'error_message' 	=> "Something went wrong. Please try again."
					];
				}
			} else {
				$json = [
					'error_message' => "No new record is found."
				];
			}
		echo json_encode($json);
		}
		catch(Exception $e){
            error_log($e);
            return false;
        }
	}

	private function uploadFiles($attachmentsDir){
        $fileLinks = array();
        if($files = $this->request->getFiles())
        {
            if (!file_exists($attachmentsDir)) {
                mkdir($attachmentsDir, 0777, true);
            }

			$attachment = $files['attachments'];
			if ($attachment->isValid() && ! $attachment->hasMoved())
			{           
				$newName = $attachment->getRandomName();
				$attachment->move($attachmentsDir, $newName);
				$type = $attachment->getClientMimeType();
				$link = "/".$attachmentsDir."/".$newName;

				$object['link'] = $link;
				$object['type'] = $type;
			}
        }
        return $object;
    }

	public function createRiskAssessmentExcelTemplate() {
		$fileName = 'riskAssessmentTemplate.xlsx';
		
		$spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
       	$sheet->setCellValue('A1', 'Project Name');
        $sheet->setCellValue('B1', 'Name');//risk
        $sheet->setCellValue('C1', 'Software Name');//software_name
        $sheet->setCellValue('D1', 'Type');//type
		$sheet->setCellValue('E1', 'Version');  
		$sheet->setCellValue('F1', 'Latest Version'); 
		$sheet->setCellValue('G1', 'Risk Analysis');
        $sheet->setCellValue('H1', 'Risk Control Measures');       
		$sheet->setCellValue('I1', 'Residual Risk Evaluation');  
		$sheet->setCellValue('J1', 'Benefit Risk Analysis');  
		$sheet->setCellValue('K1', 'Vulnerability');
		$sheet->setCellValue('L1', 'Highest CVSS');
		 
		
		$spreadsheet
		->getActiveSheet()
		->getStyle('A1:J1')
		->getFill()
		->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
		->getStartColor()
		->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLACK);

		$spreadsheet->getActiveSheet()->getStyle('A1:J1')
->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
for ($i = 'A'; $i !=  $spreadsheet->getActiveSheet()->getHighestColumn(); $i++) {
    $spreadsheet->getActiveSheet()->getColumnDimension($i)->setAutoSize(TRUE);
}
         
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
		$projectDocsRootDir = getenv('app.basePath');
		
		$outputFilePath = $projectDocsRootDir."/".$fileName;
		$writer->save($outputFilePath);

        header('Content-Type: application/vnd.ms-excel'); // generate excel file
        header('Content-Disposition: attachment;filename="'. $fileName.'"'); 
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');	// download file 
    }

}
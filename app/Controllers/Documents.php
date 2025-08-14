<?php

namespace App\Controllers;

use App\Models\DocumentModel;
use App\Models\ProjectModel;
use App\Models\TeamModel;
use App\Models\DocumentTemplateModel;
use App\Models\ReviewModel;
use App\Models\DocumentsMasterModel;
use App\Models\RequirementsModel;
use App\Models\TraceabilityMatrixModel;
use App\Models\RiskAssessmentModel;
use App\Models\AcronymsModel;
use App\Models\SettingsModel;
use App\Models\UnitTestModel;

class Documents extends BaseController
{
	public function index()
	{
		$data = [];
		$data['pageTitle'] = 'Documents';
		$data['addBtn'] = False;
		$data['titleDD'] = True;
		$data['addUrl'] = "/documents/add";

		$selectedStatus = null;
		$selectedProject = null;
		$selectedUser = null;

		helper('Helpers\utils');
		$prev_url = getPrevUrl();
		if(isset($prev_url)){
			if ($prev_url["name"] == "documentsList") {
				$vars = $prev_url["vars"];
				$selectedStatus = $vars['view'];
				$selectedProject = $vars['project_id'];
				$selectedUser = isset($vars['user_id']) ? $vars['user_id'] : null;
			}
		}

		if ($selectedUser == null) {
			$selectedUser = session()->get('id');
		}

		$teamModel = new TeamModel();
		$data['teamMembers'] = $teamModel->getMembers();

		//Document Type List for adding new documents
		$templateModel = new DocumentTemplateModel();
		$existingTypes = $templateModel->getTypes();
		$data['documentType'] = $existingTypes;

		$settingsModel = new SettingsModel();
		$documentStatusOptions = $settingsModel->getConfig("documentStatus");
		$data["documentStatus"] = $documentStatusOptions; //Status Radio Buttons

		if ($selectedStatus == null) {
			if ($documentStatusOptions != null) {
				$selectedStatus = $documentStatusOptions[0]; //Default status
			} else {
				$selectedStatus = null;
			}
		}

		$projectModel = new ProjectModel();
		$data['projects'] = $projectModel->getProjects(); //Projects Dropdown
		$data['completedProjects'] = $projectModel->getCompletedProjects(); //Completed Projects Dropdown

		if ($selectedProject == null) {
			helper('Helpers\utils');
			$selectedProject = getActiveProjectId(); //Default project
		}

		if ($selectedProject != null) {
			$documentModel = new DocumentModel();
			$data['documentsCount'] = $documentModel->getDocumentsCount($selectedProject, $selectedUser);
		}


		$data['selectedProject'] = $selectedProject;
		$data['selectedStatus'] = $selectedStatus;
		$data['selectedUser'] = $selectedUser;
		// $this->updateReviewDescription();

		echo view('templates/header');
		echo view('templates/pageTitle', $data);
		echo view('ProjectDocuments/list', $data);
		echo view('templates/footer');
	}

	public function getDocumentStats()
	{
		$project_id = $this->request->getVar('project_id');
		$selectedUser =  $this->request->getVar('user_id');

		$documentModel = new DocumentModel();
		$documentStats = $documentModel->getDocumentsCount($project_id, $selectedUser);
		$response["success"] = "True";
		$response["documentStats"] = $documentStats;

		echo json_encode($response);
	}

	public function getDocuments()
	{
		$view = $this->request->getVar('view');
		$project_id = $this->request->getVar('project_id');
		$user_id = $this->request->getVar('user_id');
		$logged_in_user_id = $this->request->getVar('loggedInUserId');

		$vars['view'] = $view;
		$vars['project_id'] = $project_id;
		$vars['user_id'] = $user_id;

		helper('Helpers\utils');
		setPrevUrl('documentsList', $vars);

		$userCondition = "";
		if ($user_id != "ALL") {
			$userCondition = " AND ( FIND_IN_SET($user_id,`reviewer-id`) > 0 OR docs.`author-id` = " . $user_id . " ) ";
		}

		$documentModel = new DocumentModel();
		$whereCondition = ' WHERE docs.`status` = "' . $view . '" AND docs.`project-id` = ' . $project_id . $userCondition;

		$data = $documentModel->getDocuments($whereCondition, "", $logged_in_user_id);

		$response["success"] = "True";
		$response["documents"] = $data;

		echo json_encode($response);
	}


	private function getTablesData($tableName, $project_id)
	{
		if ($tableName == 'teams') {
			$teamModel = new TeamModel();
			$data = $teamModel->orderBy('name', 'asc')->findAll();
			return $data;
		} else if ($tableName == 'reviews') {
			$condition = " WHERE rev.`project-id` = " . $project_id;
			$reviewModel = new ReviewModel();
			//$data = $reviewModel->getMappedRecords($condition);
			$data = $reviewModel->getMappedRecordsForDocs($condition);
			return $data;
		} else if ($tableName == 'documentMaster') {
			$references = new DocumentsMasterModel();
			$data = $references->getRefrences();
			return $data;
		} else if ($tableName == 'requirements') {
			$requirements = new RequirementsModel();
			$data = $requirements->findAll();
			return $data;
		} else if ($tableName == 'traceabilityMatrix') {
			$traceabilityMatrix = new TraceabilityMatrixModel();
			$data = $traceabilityMatrix->getTraceabilityData();
			return $data;
		} else if ($tableName == 'documents') {
			$condition = " WHERE docs.`project-id` = " . $project_id;
			$documents = new DocumentModel();
			$data = $documents->getDocuments($condition);
			return $data;
		} else if ($tableName == 'riskAssessment') {
			//Allow to display all open risk irrespective of project
			$condition = " WHERE `status` = 'Open'";
			$riskAssessment = new RiskAssessmentModel();
			$data = $riskAssessment->getRisksForDocuments($condition);
			return $data;
		} else if ($tableName == 'acronyms') {
			$acronymsModel = new AcronymsModel();
			$data = $acronymsModel->orderBy('acronym')->findAll();
			return $data;
		} else if($tableName == 'unitTest'){
			$unitTestModel = new UnitTestModel();
			$data = $unitTestModel->getAllForDocs(array('project_id' => $project_id));
			// return json_decode($data, true);
			return $data;
		} else {
			return [];
		}
	}

	private function getJsonTemplate($type)
	{
		$templateModel = new DocumentTemplateModel();
		$data = $templateModel->where('type', $type)->first();
		$template["name"] = $data["name"];
		$template["jsonObject"] = json_decode($data['template-json-object'], true);
		return $template;
	}

	private function getExistingDocs($type = "")
	{
		$documentModel = new DocumentModel();
		$data = [];

		// Get 10 latest exisiting Docs related to the type
		//All Documents
		$limit = "LIMIT 10";
		$whereCondition = "WHERE docs.`type` = '" . $type . "' ";
		$existingDocs = $documentModel->getDocuments($whereCondition, $limit);
		if (!count($existingDocs)) $data["all"] = [];
		for ($i = 0; $i < count($existingDocs); $i++) {
			$data["all"][$i]["id"] = $existingDocs[$i]["id"];
			$data["all"][$i]["title"] = $existingDocs[$i]["title"];
		}

		//Logged In User Docs
		$whereCondition .= "AND docs.`author-id` = " . session()->get('id') . " ";
		$existingDocs = $documentModel->getDocuments($whereCondition, $limit);
		if (!count($existingDocs)) $data["my"] = [];
		for ($i = 0; $i < count($existingDocs); $i++) {
			$data["my"][$i]["id"] = $existingDocs[$i]["id"];
			$data["my"][$i]["title"] = $existingDocs[$i]["title"];
		}

		return $data;
	}


	public function add()
	{
		helper(['form']);
		$documentModel = new DocumentModel();
		$data = [];
		$data['pageTitle'] = 'Documents';
		$data['addBtn'] = False;

		$teamModel = new TeamModel();
		$data['teams'] = $teamModel->getMembers();

		$data['backUrl'] = "/documents";

		$settingsModel = new SettingsModel();

		//$data["documentStatus"] =  $settingsModel->getConfig("documentStatus");
		$settingsModel = new SettingsModel();
        $documentStatus = $settingsModel->where("identifier", "documentStatus")->first();
        if ($documentStatus["options"] != null) {
            $documentStatusOptionsArr = json_decode($documentStatus["options"], true);
            unset($documentStatusOptionsArr[1]);
            unset($documentStatusOptionsArr[2]);
            $data["documentStatus"] = $documentStatusOptionsArr;
        } else {
            $data["documentStatus"] = [];
        }

		$data["reviewCategory"] =  $settingsModel->getConfig("reviewCategory");

		if ($this->request->getMethod() == 'get') {
			$id = $this->request->getVar('id');
			if ($id == "") {

				// Request for new document
				$type = $this->request->getVar('type');
				$project_id = $this->request->getVar('project_id');
				$existing_id = $this->request->getVar('existing_id');

				if ($project_id == "null") {
					$session = session();
					$session->setFlashdata('alert', "danger");
					$session->setFlashdata('message', "Create a project first!");
					return redirect()->to('/documents');
				}
				// Populating Existing Docs Dropdown
				$existingDocs = $this->request->getVar('allExistingDocs');
				if ($existingDocs == "" || $existingDocs == "no") {
					$data['allExistingDocs'] = "FALSE";
				} else {
					$data['allExistingDocs'] = "TRUE";
				}

				if ($existing_id != "" && $project_id != "") {
					// Return requested template
					$existingDoc = $documentModel->where('id', $existing_id)->first();
					$data['selectedExistingDocId'] = $existing_id;
					if ($existingDoc["author-id"] != session()->get('id')) {
						$data['allExistingDocs'] = "TRUE";
					}
					$type = $existingDoc["type"];
					// $existingDoc = $this->getExistingDocs("",$existing_id);
					// $type = $existingDoc["type"];
					$template = $this->getJsonTemplate($type);
					$templateName = $template["name"];
					$existingDoc['json-object'] = json_decode($existingDoc['json-object'], true);
					$data['jsonObject'] = $existingDoc['json-object'][$type];
				} else if ($type != "" && $project_id != "") {

					$template = $this->getJsonTemplate($type);
					$templateName = $template["name"];

					$data['jsonObject'] = $template['jsonObject'][$type];
				} else {

					return redirect()->to('/documents');
				}

				$data["type"] = $type;

				$projectModel = new ProjectModel();
				$projects = $projectModel->getProjects();
				$data['completedProjects'] = $projectModel->getCompletedProjects(); //Completed Projects Dropdown

				$data['project_name'] = (isset($projects[$project_id]) && $projects[$project_id])!=""? $projects[$project_id]:$data['completedProjects'][$project_id];
				$data['project_id'] = $project_id;
				$data['formTitle'] = $templateName;
				$data['docTitle'] = $templateName . "," . $data['project_name'];
				// Get Lookup tables as per template sections
				$sections = $data['jsonObject']["sections"];
				$data["lookUpTables"] = [];
				foreach ($sections as $section) {
					if (array_key_exists('type', $section)) {
						if ($section['type'] == 'database') {
							if (!array_key_exists($section['tableName'], $data["lookUpTables"])) {
								$data["lookUpTables"][$section['tableName']] = $this->getTablesData($section['tableName'], $project_id);
							}
						}
					}
				}


				$data['existingDocs'] = $this->getExistingDocs($type);
			} else {
				// Return an existing document
				$data['projectDocument'] = $documentModel->where('id', $id)->first();
				$settingsModel = new SettingsModel();
				$documentStatus = $settingsModel->where("identifier", "documentStatus")->first();
				$reviewer = explode(",",$data['projectDocument']['reviewer-id']);
				if ($documentStatus["options"] != null) {
					$documentStatusOptionsArr = json_decode($documentStatus["options"], true);
					if(session()->get('id') == $data['projectDocument']['author-id'] && session()->get('is-manager')){
						//unset($documentStatusOptionsArr[0]);
						unset($documentStatusOptionsArr[1]);
						$data["documentStatus"] = $documentStatusOptionsArr;
					}else if(session()->get('id') == $data['projectDocument']['author-id']){
						unset($documentStatusOptionsArr[1]);
						unset($documentStatusOptionsArr[2]);
						$data["documentStatus"] = $documentStatusOptionsArr;
					}
					else if(in_array(session()->get('id'), $reviewer)){
						unset($documentStatusOptionsArr[0]);
						unset($documentStatusOptionsArr[3]);
						$data["documentStatus"] = $documentStatusOptionsArr;
					}
				} else {
					$data["documentStatus"] = [];
				}
	  
				//$data['projectDocument']['reviewer-id'] = explode(",",$data['projectDocument']['reviewer-id']);
				$data['projectDocument']['reviewer-id'] = $reviewer;
				$type = $data['projectDocument']["type"];
				$project_id = $data['projectDocument']["project-id"];

				$projectModel = new ProjectModel();
				$projects = $projectModel->getProjects();
				$data['completedProjects'] = $projectModel->getCompletedProjects(); //Completed Projects Dropdown

				$data['project_name'] = (isset($projects[$project_id]) && $projects[$project_id])!=""? $projects[$project_id]:$data['completedProjects'][$project_id];
				//$data['project_name'] = $projects[$project_id];
				$data['project_id'] = $project_id;


				$data['jsonObject'] = json_decode($data['projectDocument']['json-object'], true);
				$data['jsonObject'] = $data['jsonObject'][$type];

				// Get Lookup tables as per template sections
				$sections = $data['jsonObject']["sections"];
				$data["lookUpTables"] = [];
				foreach ($sections as $section) {
					if (array_key_exists('type', $section)) {
						if ($section['type'] == 'database') {
							if (!array_key_exists($section['tableName'], $data["lookUpTables"])) {
								$data["lookUpTables"][$section['tableName']] = $this->getTablesData($section['tableName'], $project_id);
							}
						}
					}
				}

				//Get Review Comment
				$reviewModel = new ReviewModel();
				$data['documentReview'] = $reviewModel->where('id', $data['projectDocument']['review-id'])->first();
				$data['nearByDocuments'] = $this->getNearByDocuments($data['projectDocument']['update-date']);
			}
		}

		echo view('templates/header');
		echo view('templates/pageTitle', $data);
		echo view('ProjectDocuments/form', $data);
		echo view('templates/footer');
	}

	private function getNearByDocuments($id)
	{
		$prevId = null;
		$nextId = null;

		helper('Helpers\utils');
		$prev_url = getPrevUrl();
		if(isset($prev_url)){
			if ($prev_url["name"] == "documentsList") {
				$vars = $prev_url["vars"];
				$status = $vars['view'];
				$project_id = $vars['project_id'];
				$user_id = isset($vars['user_id']) ? (($vars['user_id'] != 'ALL') ? $vars['user_id'] : '') : '';
				$documentModel = new DocumentModel();
				$prevId = $documentModel->getPrevDocId($id, $project_id, $status, $user_id);
				$nextId = $documentModel->getNextDocId($id, $project_id, $status, $user_id);
			}
		}

		$rev["prevId"] = $prevId;
		$rev["nextId"] = $nextId;

		return $rev;
	}

	public function save()
	{
		$response = array();

		$fileName =  $this->request->getVar('cp-line3');
		$fileName =  str_replace(' ', '', $fileName);
		$fileName =  str_replace(',', '_', $fileName);
		$currentTime = gmdate("Y-m-d H:i:s");

		//Updating JSON template
		$type = $this->request->getVar('type');
		$template = $this->getJsonTemplate($type);
		$jsonObject = $template['jsonObject'];

		$jsonObject[$type]['cp-line3'] = $this->request->getVar('cp-line3');
		$jsonObject[$type]['cp-line4'] = $this->request->getVar('cp-line4');
		$jsonObject[$type]['cp-line5'] = $this->request->getVar('cp-line5');
		$jsonObject[$type]['cp-change-history'] = $this->request->getVar('cp-change-history');
		$jsonObject[$type]['cp-approval-matrix'] = $this->request->getVar('cp-approval-matrix');
		$sections = $jsonObject[$type]['sections'];


		foreach ($sections as $key => $section) {
			$sectionId = $sections[$key]["id"];
			$sections[$key]["content"] = $this->request->getVar($sectionId);
		}
		$jsonObject[$type]["sections"] = $sections;

		$postData = [
			'project-id' => $this->request->getVar('project-id'),
			'type' => $type,
			'category' => $jsonObject[$type]["template-category"],
			//'author-id' => session()->get('id'),
			'author-id' => $this->request->getVar('author-id'),
			'reviewer-id' => implode(",",$this->request->getVar('reviewer-id')),
			'file-name' => $fileName,
			'status' => $this->request->getVar('status'),
			'update-date' => $currentTime,
			'json-object' => json_encode($jsonObject),
		];

		$documentModel = new DocumentModel();
		$id = $this->request->getVar('id');
		$revision["dateTime"] = $currentTime;
		$userName = session()->get("name");
		$revision["who"] = $userName;

		if ($id == "") {
			//Creating Document Revision JSON
			$revision["type"] = "Created";
			$revision["log"] = "Document created.";
			$revisionHistory["revision-history"] = array();

			array_push($revisionHistory["revision-history"], $revision);

			$postData["revision-history"] = json_encode($revisionHistory);

			//Insert
			$id = $documentModel->insert($postData);
			$session = session();
			$session->setFlashdata('success', "Document Created successfully!");
		} else {
			//Updating Document Revision
			$approver = "";
			$doc = $documentModel->where('id', $id)->first();
			if($doc['approved-by'] != "" && $doc['approved-by'] != null){
                $approver = $doc['approved-by'];
            }
			$oldJsonObject = json_decode($doc['json-object'], true);
			$diff = $this->arrayDiff($jsonObject[$type], $oldJsonObject[$type]);

			if ($doc["reviewer-id"] != $postData["reviewer-id"]) {
				if (strlen($diff)) {
					$diff .= ", ";
				}
				$diff .= "Reviewer";
			}

			if ($doc["status"] != $postData["status"]) {
				if (strlen($diff)) {
					$diff .= ", ";
				}
				$diff .= "Status";
			}

			$revisionHistory = $doc["revision-history"];
			if ($revisionHistory != null) {
				$revisionHistory = json_decode($revisionHistory, true);
			} else {
				$revisionHistory["revision-history"] = array();
			}
			if (strlen($diff)) {
				$revision["type"] = "Edited";
				$revision["log"] = $diff . " was updated.";
				array_push($revisionHistory["revision-history"], $revision);
			}


			$postData["revision-history"] = json_encode($revisionHistory);
			$response["revisionHistory"] = json_encode($revisionHistory);

			if ($postData['status'] == 'Approved') {
				$postData["approved-by"] = ($approver != "") ? ($approver.",".session()->get("id")):session()->get("id");
				$postData['approved-at'] = $currentTime;
				$postData["approved-by"] = implode(',', array_unique(explode(',', $postData["approved-by"])));
			}

			//Update Document
			$documentModel->update($id, $postData);
		}

		if ($postData['status'] == 'Request Review') {
			$baseUrl = getenv('app.baseURL');
			$notificationMessage = "$userName has requested review of D-$id.";
			$referenceLink = $baseUrl . "/documents/add?id=" . $id;

			helper('Helpers\utils');
			$reviewerIDs = explode(",",$postData["reviewer-id"]);
			foreach($reviewerIDs as $reviewerId){
				sendNotification($reviewerId, "D-$id", "Review Requested", $referenceLink, $notificationMessage);
			}
		}

		$response["success"] = "True";
		$response["id"] = $id;
		$response["fileName"] = $fileName;

		echo json_encode($response);
	}

	public function arrayDiff($aArray1, $aArray2)
	{
		$aReturn = array();

		foreach ($aArray1 as $mKey => $mValue) {
			if (array_key_exists($mKey, $aArray2)) {
				if (is_array($mValue)) {
					foreach ($mValue as $sKey => $sValue) {
						if ($sValue != $aArray2[$mKey][$sKey]) {
							array_push($aReturn, $aArray2[$mKey][$sKey]["title"] . " Section");
						}
					}
				} else {
					if ($mValue != $aArray2[$mKey]) {
						array_push($aReturn, $mKey);
					}
				}
			} else {
				array_push($aReturn, $mKey);
			}
		}

		if (count($aReturn)) {
			$diff = implode(", ", $aReturn);
			$diff = str_replace("cp-line3", "Title", $diff);
			$diff = str_replace("cp-line4", "Document ID", $diff);
			$diff = str_replace("cp-line5", "Revision", $diff);
			$diff = str_replace("cp-approval-matrix", "Approval Matrix", $diff);
			$diff = str_replace("cp-change-history", "Change History", $diff);
			return $diff;
		} else {
			return "";
		}
	}

	public function delete()
	{

		$uri = $this->request->uri;
		$id = $uri->getSegment(3);

		$model = new DocumentModel();
		$model->delete($id);
		$response = array('success' => "True");
		echo json_encode($response);
	}
}

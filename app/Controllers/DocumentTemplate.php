<?php

namespace App\Controllers;

use App\Models\DocumentTemplateModel;
use App\Models\SettingsModel;

class DocumentTemplate extends BaseController
{

	public function index()
	{
		$data = [];
		$data['pageTitle'] = 'Templates';
		$data['addBtn'] = True;
		$data['addUrl'] = "/documents-templates/add";


		$model = new DocumentTemplateModel();
		$data['data'] = $model->orderBy('name')->findAll();

		echo view('templates/header');
		echo view('templates/pageTitle', $data);
		echo view('DocumentTemplates/list', $data);

		echo view('templates/footer');
	}

	private function returnParams()
	{
		$uri = $this->request->uri;
		$id = $uri->getSegment(3);
		if ($id != "") {
			$id = intval($id);
		}
		return $id;
	}

	public function addTemplate()
	{
		if ($this->request->getMethod() == 'post') {
			$id = $this->request->getVar('id');
			$name = $this->request->getVar('name');
			$type = $this->request->getVar('type');
			$json = $this->request->getVar('template-json-object');

			$newData = [
				'name' => $name,
				'type' => $type,
				'template-json-object' => $json,
			];

			if ($id != "") {
				$newData["id"] = $id;
			}

			$model = new DocumentTemplateModel();
			$model->save($newData);

			$newRecord = $model->where('type', $type)->first();

			$response = array('success' => "True");
			$response['id'] = $newRecord['id'];

			echo json_encode($response);
		}
	}

	public function add()
	{
		$id = $this->returnParams();
		helper(['form']);
		$model = new DocumentTemplateModel();
		$data = [];
		$data['pageTitle'] = 'Templates';
		$data['addBtn'] = False;
		$data['backUrl'] = "/documents-templates";
		// $data['existingTypes'] =  join(",",$model->getTypes());
		$existingTypes = $model->getTypes();
		$data['existingTypes'] = implode(",", array_keys($existingTypes));

		$settingsModel = new SettingsModel();
		$templateCategory = $settingsModel->where("identifier", "templateCategory")->first();

		if ($templateCategory["options"] != null) {
			$data["templateCategory"] = json_decode($templateCategory["options"], true);
		} else {
			$data["templateCategory"] = [];
		}

		if ($id == "") {
			$data['action'] = "add";
			$data['formTitle'] = "Add Template";
		} else {
			$data['action'] = "add/" . $id;

			$documentTemplate = $model->where('id', $id)->first();
			$data['documentTemplate'] = $documentTemplate;
			$data['formTitle'] = $documentTemplate['name'];
			$template = json_decode($data['documentTemplate']["template-json-object"], true);
			$data['template'] = $template[$documentTemplate['type']];
		}

		$data['tablesLayout'] = json_encode($this->returnTablesLayout());
		echo view('templates/header');
		echo view('templates/pageTitle', $data);
		echo view('DocumentTemplates/form', $data);
		echo view('templates/footer');
	}

	private function returnTablesLayout()
	{
		$tables = array();
		// There should be no spaces between column value names
		$tables['Acronyms']['name'] = "acronyms";
		$tables['Acronyms']['columns'] = "acronym,description";
		$tables['Documents']['name'] = "documents";
		$tables['Documents']['columns'] = "file-name,author";
		$tables['References']['name'] = "documentMaster";
		$tables['References']['columns'] = "reference,name,category,description,location,status,version";
		$tables['Requirements']['name'] = "requirements";
		$tables['Requirements']['columns'] = "description,requirement,type,update_date";
		$tables['Reviews']['name'] = "reviews";
		$tables['Reviews']['columns'] = "review-name,context,description,review-ref,status,project-name,reviewer,author";
		$tables['RiskAssessment']['name'] = "riskAssessment";
		$tables['RiskAssessment']['columns'] = "risk_type,risk,risk_description,component,benefit_risk_analysis,initial_risk_evaluation,residual_risk_evaluation,risk_analysis,assessment,CVSS_3_1_base_risk_assessment,status";
		$tables['Teams']['name'] = "teams";
		$tables['Teams']['columns'] = "name,email,responsibility,role";
		$tables['TraceabilityMatrix']['name'] = "traceabilityMatrix";
		$tables['TraceabilityMatrix']['columns'] = "cncr,system,subsysreq,design,code,testcase";
		$tables['UnitTest']['name'] = "unitTest";
		$tables['UnitTest']['columns'] = "meta_name,meta_author,meta_build_env,meta_verified_on,meta_additional_info,testcases_name,testcases_description,testcases_steps,testcases_expected_result,testcases_actual_result,testcases_pass_or_fail,testcases_notes";


		return $tables;
	}


	public function delete()
	{
		if (session()->get('is-admin')) {
			$uri = $this->request->uri;
			$id = $uri->getSegment(3);

			$model = new DocumentTemplateModel();
			$model->delete($id);
			$response = array('success' => "True");
			echo json_encode($response);
		} else {
			$response = array('success' => "False");
			echo json_encode($response);
		}
	}

	public function getTableContent(){
		$uri = $this->request->uri;
		$tabName = $uri->getSegment(3);
		switch($tabName){
			case "acronyms":{
				$model = new \App\Models\AcronymsModel();
				break;
			}
			case "documents":{
				$model = new \App\Models\DocumentModel();
				break;
			}
			case "documentMaster":{
				$model = new \App\Models\DocumentsMasterModel();
				break;
			}
			case "requirements":{
				$model = new \App\Models\RequirementsModel();
				break;
			}
			case "reviews":{
				$model = new \App\Models\ReviewModel();
				break;
			}
			case "riskAssessment":{
				$model = new \App\Models\RiskAssessmentModel();
				break;
			}
			case "teams":{
				$model = new \App\Models\TeamModel();
				break;
			}
			case "traceabilityMatrix":{
				$model = new \App\Models\TraceabilityMatrixModel();
				break;
			}
			case "unitTest":{
				$model = new \App\Models\UnitTestModel();
				break;
			}
		}
		if($tabName != ""){
			$data = $model->limit(2)->orderBy('id', 'desc')->find();
			$response = array('success' => "True",'result' => $data);
		}else{
			$response = array('success' => "False");
		}
		echo json_encode( $response );
	}
}


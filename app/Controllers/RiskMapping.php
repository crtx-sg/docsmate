<?php 
namespace App\Controllers;

use App\Models\RiskCategoryModel;
use App\Models\SettingsModel;

class RiskMapping extends BaseController
{
    public function index()
    {
        $data = [];
		$data['pageTitle'] = 'Risk Mapping';
		$data['addBtn'] = True;
		$data['addUrl'] = "/risk-mapping/add";
        $view = $this->request->getVar('view');

		if($view == ''){
			$view = 'Active';			
		}

		$model = new RiskCategoryModel();
		$data['data'] = $model->where('status', $view)->findAll();	
        $data['view'] = $view;
		
		echo view('templates/header');
		echo view('templates/pageTitle', $data);
		echo view('RiskMapping/list',$data);
		echo view('templates/footer');
	}


    public function add()
    {
        $id = $this->returnParams();
        helper(['form']);
		$model = new RiskCategoryModel();
		$data = [];
		$data['pageTitle'] = 'RiskMapping';
		$data['addBtn'] = False;
		$data['backUrl'] = "/risk-mapping";
		$data['statusList'] = ['Active','InActive'];

        $settingsModel = new SettingsModel();
        $riskMethodology = $settingsModel->where("identifier", "riskMethodologyCategory")->first();
        if ($riskMethodology["options"] != null) {
            $data["riskMethodology"] = json_decode($riskMethodology["options"], true);
        } else {
            $data["riskMethodology"] = [];
        }

		if($id == ""){
			$data['action'] = "add";
			$data['formTitle'] = "Add RiskCategory";
		}else{
			$data['action'] = "add/".$id;

			$data['riskCategory'] = $model->where('id',$id)->first();		
			$data['formTitle'] = "Edit ".$data['riskCategory']["name"];	
		}

		if ($this->request->getMethod() == 'post') {
			
			$rules = [
				'name' => 'required|min_length[3]|max_length[50]',
				'risk-methodology' => 'required|min_length[3]|max_length[60]',
				'status' => 'required'
			];	

			$newData = [
				'name' => $this->request->getVar('name'),
				'risk-methodology' => $this->request->getVar('risk-methodology'),
				'status' => $this->request->getVar('status')
			];

			$data['riskCategory'] = $newData;

			if (! $this->validate($rules)) {
				$data['validation'] = $this->validator;
			}else{
				if($id > 0){
					$newData['id'] = $id;
					$message = 'RiskCategory successfully updated.';
				}else{
					$message = 'RiskCategory successfully added.';
				}
				$model->save($newData);
				$session = session();
				$session->setFlashdata('success', $message);
				
			}
		}
		
		echo view('templates/header');
		echo view('templates/pageTitle', $data);
		echo view('RiskMapping/form', $data);
		echo view('templates/footer');
    }

    private function returnParams(){
		$uri = $this->request->uri;
		$id = $uri->getSegment(3);
		if($id != ""){
			$id = intval($id);
		}
		return $id;
	}

    public function getRiskCategories()
    {
        // $view = $this->request->getVar('view');

        // $vars['view'] = $view;

        // helper('Helpers\utils');
        // setPrevUrl('productList', $vars);


        $model = new RiskCategoryModel();
        $data = $model->getRiskCategoriesData();

        $response["success"] = "True";
        $response["risks"] = $data;

        echo json_encode($response);
    }

    public function deleteRiskCategory()
    {
        $id = $this->returnParams();
        $model = new RiskCategoryModel();
        $model->delete($id);
        $response = array('success' => "True");
        echo json_encode($response);
    }

}
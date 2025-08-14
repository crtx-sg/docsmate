<?php namespace App\Controllers;

use App\Models\SettingsModel;
use App\Models\TeamModel;
use App\Models\ProductModel;


class Settings extends BaseController
{
    public function index()
    {
        $data = [];

        $data['pageTitle'] = 'Settings';
        $data['addBtn'] = false;
        $data['backUrl'] = '/admin/settings';

        $model = new SettingsModel();
        $data['dropdownData'] = $model->where('type', 'dropdown')->findAll();		
        $data['configData'] = $model->where('type', 'url')->findAll();	
        $data['propertiesData'] = $model->where('type', 'properties')->findAll();

        $data["selectedStatus"] = 'Active';
        $data["productStatus"] = ['Active', 'Completed','InActive'];
        $data["pageNumbersToggle"] = ['Enable', 'Disable'];

        $teamModel = new TeamModel();
        $data['teamMembers'] = $teamModel->getMembers();

        echo view('templates/header');
        echo view('templates/pageTitle', $data);
        echo view('Admin/Settings/list', $data);
        echo view('templates/footer');

    }

    public function addEnums()
    {
        if (session()->get('is-admin')) {
            if ($this->request->getMethod() == 'post') {
                $id = $this->request->getVar('id');
                $identifier = $this->request->getVar('identifier');
                $options =  $this->request->getVar('options');

                $rules = [
                    "id" => "required",
                    "identifier" => 'required',
                    "options" => 'required',
                ];
                $validation =  \Config\Services::validation();
                $validation->setRules($rules);

                if (! $this->validate($rules)) {
                    echo json_encode($validation->getErrors());
                }else{
                    $newData = ["id" => $id, "identifier" => $identifier, "options" => $options];
                    $model = new SettingsModel();
                    $model->save($newData);
                    $response = array('success' => "True");
                    echo json_encode($response);
                }
            }
        } else {
            $response = array('success' => "False");
            $response["error"] = "You are not authorized to perform this task.";
            echo json_encode($response);
        }
    
    }
    
    public function updateRequirementValues(){
        if (session()->get('is-admin')) {
            if ($this->request->getMethod() == 'post') {
                $keyId = $this->request->getVar('key');
                $keyValue = $this->request->getVar('value');
                $keyisRoot = ($this->request->getVar('isRoot') == 'false') ? false : true;
                
                $model = new SettingsModel();
                $data = $model->where('identifier', 'requirementsCategory')->findAll();	
                $dataList = $data; 	
                $data = json_decode($dataList[0]['options'], true);
                foreach($data as $key=>$val){
                    if($val['key'] == $keyId && $val['value'] == $keyValue) {
                        $data[$key]['isRoot'] = $keyisRoot;
                    }
                }
                $updateOptions = json_encode($data);
                $newData = ["id" => $dataList[0]['id'], "identifier" => $dataList[0]['identifier'], "options" => $updateOptions];
                $model->save($newData);
                $response = array('success' => "True");
                echo json_encode($response);
            }
        } else {
            $response = array('success' => "False");
            $response["error"] = "You are not authorized to perform this task.";
            echo json_encode($response);
        }

    }

    public function updateTaskValues(){
        if (session()->get('is-admin')) {
            if ($this->request->getMethod() == 'post') {
                $keyId = $this->request->getVar('key');
                $keyValue = $this->request->getVar('value');
                $keyisRoot = ($this->request->getVar('isRoot') == 'false') ? false : true;
                
                $model = new SettingsModel();
                $data = $model->where('identifier', 'taskCategory')->findAll();	
                $dataList = $data; 	
                $data = json_decode($dataList[0]['options'], true);
                foreach($data as $key=>$val){
                    if($val['key'] == $keyId && $val['value'] == $keyValue) {
                        $data[$key]['isRoot'] = $keyisRoot;
                    }
                }
                $updateOptions = json_encode($data);
                $newData = ["id" => $dataList[0]['id'], "identifier" => $dataList[0]['identifier'], "options" => $updateOptions];
                $model->save($newData);
                $response = array('success' => "True");
                echo json_encode($response);
            }
        } else {
            $response = array('success' => "False");
            $response["error"] = "You are not authorized to perform this task.";
            echo json_encode($response);
        }

    }

    public function updateTaskTypeValues(){
        if (session()->get('is-admin')) {
            if ($this->request->getMethod() == 'post') {
                $keyId = $this->request->getVar('key');
                $keyValue = $this->request->getVar('value');
                $keyisRoot = ($this->request->getVar('isRoot') == 'false') ? false : true;
                
                $model = new SettingsModel();
                $data = $model->where('identifier', 'taskType')->findAll();	
                $dataList = $data; 	
                $data = json_decode($dataList[0]['options'], true);
                foreach($data as $key=>$val){
                    if($val['key'] == $keyId && $val['value'] == $keyValue) {
                        $data[$key]['isRoot'] = $keyisRoot;
                    }
                }
                $updateOptions = json_encode($data);
                $newData = ["id" => $dataList[0]['id'], "identifier" => $dataList[0]['identifier'], "options" => $updateOptions];
                $model->save($newData);
                $response = array('success' => "True");
                echo json_encode($response);
            }
        } else {
            $response = array('success' => "False");
            $response["error"] = "You are not authorized to perform this task.";
            echo json_encode($response);
        }

    }

    public function updateTimeTrackerValues(){
        if (session()->get('is-admin')) {
            if ($this->request->getMethod() == 'post') {
                $keyId = $this->request->getVar('key');
                $keyValue = $this->request->getVar('value');
                $keyisRoot = ($this->request->getVar('isRoot') == 'false') ? false : true;
                
                $model = new SettingsModel();
                $data = $model->where('identifier', 'timeTrackerCategory')->findAll();	
                $dataList = $data; 	
                $data = json_decode($dataList[0]['options'], true);
                foreach($data as $key=>$val){
                    if($val['key'] == $keyId && $val['value'] == $keyValue) {
                        $data[$key]['isRoot'] = $keyisRoot;
                    }
                }
                $updateOptions = json_encode($data);
                $newData = ["id" => $dataList[0]['id'], "identifier" => $dataList[0]['identifier'], "options" => $updateOptions];
                $model->save($newData);
                $response = array('success' => "True");
                echo json_encode($response);
            }
        } else {
            $response = array('success' => "False");
            $response["error"] = "You are not authorized to perform this task.";
            echo json_encode($response);
        }

    }

    public function updateRiskMethodologyValues(){
        if (session()->get('is-admin')) {
            if ($this->request->getMethod() == 'post') {
                $keyId = $this->request->getVar('key');
                $keyValue = $this->request->getVar('value');
                $keyisRoot = ($this->request->getVar('isRoot') == 'false') ? false : true;
                
                $model = new SettingsModel();
                $data = $model->where('identifier', 'riskMethodologyCategory')->findAll();	
                $dataList = $data; 	
                $data = json_decode($dataList[0]['options'], true);
                foreach($data as $key=>$val){
                    if($val['key'] == $keyId && $val['value'] == $keyValue) {
                        $data[$key]['isRoot'] = $keyisRoot;
                    }
                }
                $updateOptions = json_encode($data);
                $newData = ["id" => $dataList[0]['id'], "identifier" => $dataList[0]['identifier'], "options" => $updateOptions];
                $model->save($newData);
                $response = array('success' => "True");
                echo json_encode($response);
            }
        } else {
            $response = array('success' => "False");
            $response["error"] = "You are not authorized to perform this task.";
            echo json_encode($response);
        }

    }


    public function addProduct($id = 0)
    {
        helper(['form']);
		$model = new ProductModel();
		$data = [];
		$data['pageTitle'] = 'Products';
		$data['addBtn'] = False;
		$data['backUrl'] = "/admin/settings";
		$data['statusList'] = ['Active', 'Completed','InActive'];

		if($id == 0){
			$data['action'] = "addProduct";
			$data['formTitle'] = "Add Product";
		}else{
			$data['action'] = "addProduct/".$id;

			$data['product'] = $model->where('product-id',$id)->first();		
			$data['formTitle'] = "Edit ".$data['product']["name"];	
		}

		if ($this->request->getMethod() == 'post') {
			
			$rules = [
				'name' => 'required|min_length[3]|max_length[50]',
				'description' => 'max_length[200]',
				'display-name' => 'required|min_length[3]|max_length[60]',
				'status' => 'required'
			];	

			$newData = [
				'name' => $this->request->getVar('name'),
				'display-name' => $this->request->getVar('display-name'),
				'description' => trim($this->request->getVar('description')),
				'status' => $this->request->getVar('status')
			];

			$data['product'] = $newData;

			if (! $this->validate($rules)) {
				$data['validation'] = $this->validator;
			}else{
				if($id > 0){
					$newData['product-id'] = $id;
					$message = 'Product successfully updated.';
				}else{
					$message = 'Product successfully added.';
				}
                //print_r($newData);exit;
				$model->save($newData);
				$session = session();
				$session->setFlashdata('success', $message);
				
			}
		}
		
		echo view('templates/header');
		echo view('templates/pageTitle', $data);
		echo view('Admin/Settings/form', $data);
		echo view('templates/footer');
    }

    public function getProducts()
    {
        $view = $this->request->getVar('view');

        $vars['view'] = $view;

        helper('Helpers\utils');
        setPrevUrl('productList', $vars);


        $model = new ProductModel();
        $data = $model->getProductsData($view);

        $response["success"] = "True";
        $response["products"] = $data;

        echo json_encode($response);
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

    public function deleteProduct($id)
    {
        $model = new ProductModel();
        $model->delete($id);
        $response = array('success' => "True");
        echo json_encode($response);
    }
}

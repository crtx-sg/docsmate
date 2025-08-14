<?php namespace App\Controllers;

use App\Models\DocumentsMasterModel;
use App\Models\SettingsModel;

class DocumentsMaster extends BaseController
{
	
	public function index()
    {
        $data = [];
		$data['pageTitle'] = 'References';
		$data['addBtn'] = True;
		$data['addUrl'] = "/documents-master/add";

		$model = new DocumentsMasterModel();
		$data['data'] = $model->findAll();	

		echo view('templates/header');
		echo view('templates/pageTitle', $data);
		echo view('DocumentMaster/list',$data);
		echo view('templates/footer');
	}

	public function add(){
		$id = $this->request->getVar('id');
		helper(['form']);
		$model = new DocumentsMasterModel();
		$data = [];
		$data['pageTitle'] = 'References';
		$data['addBtn'] = False;
		$data['backUrl'] = "/documents-master";
		$data['statusList'] = ['Draft', 'Approved', 'Obsolete'];
		
		$settingsModel = new SettingsModel();
		$referenceCategory = $settingsModel->where("identifier","referenceCategory")->first();
		if($referenceCategory["options"] != null){
			$data["referenceCategory"] = json_decode( $referenceCategory["options"], true );
		}else{
			$data["referenceCategory"] = [];
		}

		if($id == ""){
			$data['action'] = "add";
			$data['formTitle'] = "Add Reference";
		}else{
			$data['action'] = "add?id=".$id;
			$data['document'] = $model->where('id',$id)->first();		
			
			$data['formTitle'] = $data['document']["name"];

			
		}


		if ($this->request->getMethod() == 'post') {
			
			$rules = [
				'name' => 'required|min_length[3]|max_length[60]',
				'category' => 'required',
				'version' => 'required',
				'description' => 'max_length[100]',
				'ref' => 'max_length[100]',
				'location' => 'max_length[50]',
				'status' => 'required',				
			];	

			$newData = [
				'name' => $this->request->getVar('name'),
				'category' => $this->request->getVar('category'),
				'version' => $this->request->getVar('version'),
				'description' => trim($this->request->getVar('description')),
				'ref' => $this->request->getVar('ref'),
				'location' => $this->request->getVar('location'),
				'status' => $this->request->getVar('status'),
			];

			$data['document'] = $newData;

			if (! $this->validate($rules)) {
				$data['validation'] = $this->validator;
			}else{
				if($id > 0){
					$newData['id'] = $id;
					$message = 'Document successfully updated.';
				}else{
					$message = 'Document successfully added.';
				}
				$model->save($newData);
				$session = session();
				$session->setFlashdata('success',$message);
				
			}
		}
		
		echo view('templates/header');
		echo view('templates/pageTitle', $data);
		echo view('DocumentMaster/form', $data);
		echo view('templates/footer');
	}

	
	public function delete(){
		$id = $this->request->getVar('id');
		if($id != ""){
			$model = new DocumentsMasterModel();
			$model->delete($id);
			$response = array('success' => "True");
		}else{
			$response = array('success' => "False");
		}
		echo json_encode( $response );
	}

}
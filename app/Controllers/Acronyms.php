<?php namespace App\Controllers;

use App\Models\AcronymsModel;
use CodeIgniter\I18n\Time;
class Acronyms extends BaseController
{
	public function index()
    {
		$data = [];

		$data['pageTitle'] = 'Acronyms';
		$data['addBtn'] = true;
		$data['addUrl'] = "/documents-acronyms/add";
		$data['backUrl'] = '/documents-acronyms';

		$model = new AcronymsModel();
		$data['data'] = $model->orderBy('id', 'desc')->findAll();	

		echo view('templates/header');
		echo view('templates/pageTitle', $data);
		echo view('Acronyms/list',$data);
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

	public function add(){
		$id = $this->returnParams();
		helper(['form']);
		$model = new AcronymsModel();
		$data = [];
		$data['pageTitle'] = 'Acronyms';
		$data['addBtn'] = False;
		$data['backUrl'] = "/documents-acronyms";
		$dataList = [];

		$rules = [
			'acronym' => 'required',
			// 'description' => 'min_length[3]|max_length[500]'
		];	

		if($id == ""){
			$data['action'] = "add";
			$data['formTitle'] = "Add Acronym";
		}else{
			$data['action'] = "add/".$id;
			$data['formTitle'] = "Update Acronym";

			$data['member'] = $model->where('id',$id)->first();		
		}

		if ($this->request->getMethod() == 'post') {
			$newData = [
				'acronym' => $this->request->getVar('acronym'),
				'description' => $this->request->getVar('description'),
				'update_date' => gmdate("Y-m-d H:i:s")
			];
			$data['member'] = $newData;
	
			if (! $this->validate($rules)) {
				$data['validation'] = $this->validator;
			}else{
				if($id > 0){
					$newData['id'] = $id;
					$newData['update_date'] = gmdate("Y-m-d H:i:s");
					$message = 'Acronym successfully updated.';
				}else{
					$message = 'Acronym successfully added.';
				}
				$model->save($newData);
				$session = session();
				$session->setFlashdata('success', $message);
			}
		}

		echo view('templates/header');
		echo view('templates/pageTitle', $data);
		echo view('Acronyms/form', $data);
		echo view('templates/footer');
	}
	
	public function delete(){
		if (session()->get('is-admin')){
			$id = $this->returnParams();
			$model = new AcronymsModel();
			$model->delete($id);
			$response = array('success' => "True");
			echo json_encode( $response );
		}
		else{
			$response = array('success' => "False");
			echo json_encode( $response );
		}
	}


}
<?php namespace App\Controllers;

use App\Models\CourseModel;
use App\Models\TeamModel;
use CodeIgniter\I18n\Time;

class Courses extends BaseController
{
	public function index()
    {
        $data = [];
		$data['pageTitle'] = 'Courses';
		$data['addBtn'] = True;
		$data['addUrl'] = "/courses/add";

		$model = new CourseModel();
		$view = $this->request->getVar('view');

		if($view == ''){
			$view = 'Active';			
		}

		$data['data'] = $model->where('status', $view)->orderBy('created_at', 'desc')->findAll();	
		$data['view'] = $view;
		
		echo view('templates/header');
		echo view('templates/pageTitle', $data);
		echo view('Courses/list',$data);
		echo view('templates/footer');
	}


	public function add($id = 0){

		helper(['form']);
		$model = new CourseModel();
		$teamModel = new TeamModel();
		
		$data = [];
		$data['pageTitle'] = 'Courses';
		$data['addBtn'] = False;
		$data['backUrl'] = "/courses";
		$data['statusList'] = ['Active', 'InActive'];

		$initialJsonObj = file_get_contents(APPPATH."Templates/kPoints.json");
		
		//print_r($initialJsonObj);exit;
		if($id == 0){
			$data['action'] = "add";
			$data['formTitle'] = "Add Course";
			$data['jsonObj'] = json_decode($initialJsonObj, true);
		}else{
			$data['action'] = "add/".$id;
			$data['course'] = $model->where('course_id',$id)->first();
			$data['formTitle'] = $data['course']["title"];	
			$data['jsonObj'] = json_decode($data['course']['assessment'], true);
			if($data['jsonObj'] == ''){
				$data['jsonObj'] = json_decode($initialJsonObj, true);
			}
		}
		//print_r($data['jsonObj']);exit;
		$data['kPointsList'] = $data['jsonObj']['courses']['k-points'];
		if ($this->request->getMethod() == 'post') {
			
			$rules = [
				'title' => 'required|min_length[3]|max_length[100]',
				'description' => 'max_length[500]',
				'url' => 'required',
				'k-points' => 'required',
				'status' => 'required'
			];	
			$is_certified_text = $this->request->getVar('is_certified');
            $is_certified = 0;
            if($is_certified_text == 'on'){
                $is_certified = 1;
            }

			$postDataMatrix = array(
				'Difficulty'=>'','Duration' =>'','Type' => '','Upskill Type' => '','KPoints' => ''
			);
			//print_r($this->request->getVar('UpskillType-status-type'));exit;
			$postDataMatrix['Difficulty'] = ($this->request->getVar('Difficulty-status-type')) ? explode('/', $this->request->getVar('Difficulty-status-type'))[1] : '';			
			$postDataMatrix['Duration'] = ($this->request->getVar('Duration-status-type')) ? explode('/', $this->request->getVar('Duration-status-type'))[1] : '';	
			$postDataMatrix['Type'] = ($this->request->getVar('Type-status-type')) ? explode('/', $this->request->getVar('Type-status-type'))[1] : '';
			$postDataMatrix['Upskill Type'] = ($this->request->getVar('UpskillType-status-type')) ? explode('/', $this->request->getVar('UpskillType-status-type'))[1] : '';
			$postDataMatrix['KPoints'] = $this->request->getVar('k-points');
				//$newData['CVSS_3_1_base_risk_assessment'] = $this->request->getVar('rpn');

			foreach($data['jsonObj']['courses']['k-points'] as $key=>$value){
				$data['jsonObj']['courses']['k-points'][$key]['value'] = $postDataMatrix[$value['category']];
			}

			//$newData['assessment'] = json_encode($data['jsonObj']);
			$data['kPointsList'] = $data['jsonObj']['courses']['k-points'];

			$newData = [
				'title' => $this->request->getVar('title'),
				'url' => $this->request->getVar('url'),
				'description' => trim($this->request->getVar('description')),
				'k-points' => $this->request->getVar('k-points'),
				'is_certified' => $this->request->getVar('is_certified'),
				'status' => $this->request->getVar('status'),
				'assessment' => json_encode($data['jsonObj'])
			];

			$data['course'] = $newData;
			//print_r($newData);exit;
			
			if (! $this->validate($rules)) {
				$data['validation'] = $this->validator;
			}else{
				$newData["is_certified"] = $is_certified;
				if($id > 0){
					$newData['course_id'] = $id;
					$message = 'Course successfully updated.';
				}else{
					$data['jsonObj'] = json_decode($initialJsonObj, true);
					$data['kPointsList'] = $data['jsonObj']['courses']['k-points'];
					$message = 'Course successfully added.';
				}
				$model->save($newData);
				
				$session = session();
				$session->setFlashdata('success', $message);
			}
		}
		
		echo view('templates/header');
		echo view('templates/pageTitle', $data);
		echo view('Courses/form', $data);
		echo view('templates/footer');
	}

	
	public function delete(){
		if (session()->get('is-admin')){
			$id = $this->returnParams();
			$model = new CourseModel();
			$model->delete($id);
			$response = array('success' => "True");
			echo json_encode( $response );
		}else{
			$response = array('success' => "False");
			echo json_encode( $response );
		}
	}

	private function returnParams(){
		$uri = $this->request->uri;
		$id = $uri->getSegment(3);
		if($id != ""){
			$id = intval($id);
		}
		return $id;
	}
}

<?php

namespace App\Controllers;

use App\Models\MeetingModel;
use App\Models\TeamModel;
use CodeIgniter\I18n\Time;

class Meeting extends BaseController
{
    private $meetingModel;

    public function __construct()
    {
        $this->meetingModel = new MeetingModel();
    }

    public function index()
    {
        $data = [];
		$data['pageTitle'] = 'Meeting Notes';
		$data['addBtn'] = True;
		$data['addUrl'] = "/meeting/add";

		$model = new MeetingModel();

		echo view('templates/header');
		echo view('templates/pageTitle', $data);
		echo view('Meeting/list',$data);
		echo view('templates/footer');
	}

    public function getMeetings()
    {
        $model = new MeetingModel();
        
        $data = $model->getMeetings();
        
        $response["success"] = "True";
        $response["meetings"] = $data;

        echo json_encode($response);

    }



    public function add(){
        $id = $this->returnParams();

		helper(['form']);
		$model = new MeetingModel();
		$teamModel = new TeamModel();
		$data = [];
		$data['pageTitle'] = 'Meeting Notes';
		$data['addBtn'] = False;
		$data['backUrl'] = "/meeting";

		if($id == ""){
            $data['action'] = "add";
			$data['formTitle'] = "Add Meeting Notes";
            
		}else{
			$data['action'] = "add/".$id;
			$data['meeting'] = $model->where('meeting-id',$id)->first();
           
			$data['formTitle'] = $data['meeting']["entry-date"];
		}

		if ($this->request->getMethod() == 'post') {
			$rules = [
                'title' => 'required',
				'entry-date' => 'required',
                'notes' => 'required'
			];	
			$newData = [
				'entry-date' => $this->request->getVar('entry-date'),
                'title' => $this->request->getVar('title'),
				'notes' => trim($this->request->getVar('notes'))
			];
                       
			$data['meeting'] = $newData;

			if (! $this->validate($rules)) {
				$data['validation'] = $this->validator;
			}else{
				if($id > 0){
                        $newData['meeting-id'] = $id;
                        $model->update($id, $newData);
                        $message = 'Meeting notes successfully updated.';
                    }
				else{
                    $model->insert($newData);
					$message = 'Meeting notes successfully added.';
				}
				$session = session();
				$session->setFlashdata('success', $message);
			}
            $data['meeting']['meeting-id'] = $id;
		}
		
		echo view('templates/header');
		echo view('templates/pageTitle', $data);
		echo view('Meeting/form', $data);
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
}
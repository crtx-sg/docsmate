<?php namespace App\Controllers;

use App\Models\TeamModel;
use App\Models\ActionListModel;

class ActionList extends BaseController
{
	public function index()
	{
		$data = [];

		$teamModel = new TeamModel();
		$data['teamMembers'] = $teamModel->getMembers();
		
		$userId = session()->get('id');
		$actionListModel = new ActionListModel();
		$data['data'] = $actionListModel->getActions($userId);

		echo view('templates/header');
		echo view('ActionList/list',$data);
	}

	public function update()
	{
		$json_item = $this->request->getVar('actionItem');
		$actionItem = json_decode($json_item, true);
		$id = $actionItem['id'];
		$stateLabel = $actionItem['action']['state'];

		$actionItem['action'] = json_encode($actionItem['action']);
		usort($actionItem['revision_history'], function($a, $b) {
			  return new \DateTime($a['dateTime']) <=> new \DateTime($b['dateTime']);
		});

		$actionItem['revision_history'] = json_encode($actionItem['revision_history']);


		$model = new ActionListModel();
		if($id != ""){
			$model->update($id, $actionItem);
			$response['id'] = $id;
			$response['stateLabel'] = $this->returnStateLabel($stateLabel);
		}else{
			$id = $model->insert($actionItem);
			$response['id'] = $id;
		}
		
		$response['sucess'] = 'True';

		return json_encode($response);
	}

	private function returnStateLabel($stateLabel){
		if($stateLabel == 'todo'){
			return 'To Do';
		}else if($stateLabel == 'completed'){
			return 'Completed';
		}else{
			return 'On Hold';
		}
	}

	public function delete()
	{
		$id = $this->request->getVar('id');
		$model = new ActionListModel();

		$model->delete($id);
		$response = array('success' => 'True');
		$response['id'] = $id;
		echo json_encode($response);
	}

}

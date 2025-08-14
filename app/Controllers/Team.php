<?php namespace App\Controllers;

use App\Models\TeamModel;
use App\Models\SettingsModel;
use TP\Tools\Openfire;

class Team extends BaseController
{
	public function index()
    {
		$data = [];
		$data['pageTitle'] = 'Team';
		$data['addBtn'] = True;
		$data['addUrl'] = "/team/add";

		$model = new TeamModel();
		$data['data'] = $model->orderBy('is-manager', 'desc')->orderBy('name', 'asc')->findAll();	

		echo view('templates/header');
		echo view('templates/pageTitle', $data);
		echo view('Team/list',$data);
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
		$data = [];
		$data['pageTitle'] = 'Team';
		$data['addBtn'] = False;
		$data['backUrl'] = "/team";

		if (session()->get('is-admin')){
			$id = $this->returnParams();

			helper(['form']);
			$model = new TeamModel();

			$settingsModel = new SettingsModel();
			$userRole = $settingsModel->where("identifier","userRole")->first();
			if($userRole["options"] != null){
				$data["userRole"] = json_decode( $userRole["options"], true );
			}else{
				$data["userRole"] = [];
			}

			if($id == ""){
				$data['action'] = "add";
				$data['formTitle'] = "Add Member";

				$rules = [
					'name' => 'required|min_length[3]|max_length[64]',
					'email' => 'required|min_length[6]|max_length[50]|valid_email|is_unique[docsgo-team-master.email]',
				];

			}else{
				$data['action'] = "add/".$id;
				

				$rules = [
					'name' => 'required|min_length[3]|max_length[64]',
				];	

				$data['member'] = $model->where('id',$id)->first();		
				$data['formTitle'] = $data['member']["name"];
			}
			

			if ($this->request->getMethod() == 'post') {
				
				$is_manager_text = $this->request->getVar('is-manager');
				$is_manager = 0;
				if($is_manager_text == 'on'){
					$is_manager = 1;
				}

				$is_admin_text = $this->request->getVar('is-admin');
				$is_admin = 0;
				if($is_admin_text == 'on'){
					$is_admin = 1;
				}

				$is_active_text = $this->request->getVar('is-active');
				$is_active = 0;
				if($is_active_text == 'on'){
					$is_active = 1;
				}

				$defaultPassword = getenv('PASS_CODE');

				$newData = [
					'name' => $this->request->getVar('name'),
					'email' => $this->request->getVar('email'),
					'role' => $this->request->getVar('role'),
					'responsibility' => $this->request->getVar('responsibility'),
					'is-manager' => $is_manager,
					'is-admin' => $is_admin,
					'password' => $defaultPassword,
					'is-active' => $is_active,
				];

				$data['member'] = $newData;

				if (! $this->validate($rules)) {
					$data['validation'] = $this->validator;
				}else{

					if($id > 0){
						$newData['id'] = $id;
						$message = 'Team member successfully updated.';
					}else{
						$message = 'Team member successfully added.';
						$this->addStorageUser($newData);
						if(getenv('OF_ENABLED') == "true"){
							$this->addOpenfireUser($newData);
						}
					}

					$model->save($newData);
					$session = session();
					$session->setFlashdata('success', $message);
				}
			}
			echo view('templates/header');
			echo view('templates/pageTitle', $data);
			echo view('Team/form', $data);
			echo view('templates/footer');
		}else{
			$data['pageTitle'] = 'You are not authorized to perform this task.';
			echo view('templates/header');
			echo view('templates/pageTitle', $data);
			echo view('templates/footer');
		}
	
	}

	private function addStorageUser($docsgoUser){
		$jsonFile = file_get_contents('storage/private/users.json');
		$storageUsers = json_decode($jsonFile, true);

		$storageUser = array();
		$storageUser["username"] = $docsgoUser["email"];
        $storageUser["name"] = $docsgoUser["name"];
        $storageUser["role"] = "user";
        $storageUser["homedir"] = "/";
        $storageUser["permissions"] = "read|write|upload|download|batchdownload|zip";
		$storageUser["password"] = password_hash($docsgoUser['password'], PASSWORD_DEFAULT);

		array_push($storageUsers, $storageUser);
		
		$storageUsers = json_encode($storageUsers);
		file_put_contents('storage/private/users.json', $storageUsers);
	}

	private function addOpenfireUser($user){
		$username = substr($user['email'], 0, strrpos($user['email'], '@'));
		$userDetails = [
			"email" => $user['email'],
			"name" => $user['name'], 
			"password" => getenv('PASS_CODE'), 
			"username" => $username, 
		];
		
		$openfire = new Openfire();
		$openfire->createUser($userDetails);
		$openfire->addUserToGroup($username, getenv('OF_DEFAULT_GROUP'));
		
	}
	
	public function delete(){
		if (session()->get('is-admin')){
			$id = $this->returnParams();
			$model = new TeamModel();
			$user = $model->find($id);

			$username = substr($user['email'], 0, strrpos($user['email'], '@'));
			$openfire = new Openfire();
			$openfire->deleteUser($username);	
			$model->delete($id);

			$response = array('success' => "True");
			echo json_encode( $response );
		}
		else{
			$response = array('success' => "False");
			echo json_encode( $response );
		}
	}

	private function moveUsersToStorage($data){
		$storageUsers = array();
		$count = 1;
		foreach($data as $users){
			$storageUser = array();
			$storageUser["username"] = $users["email"];
			$storageUser["name"] = $users["name"];
			$storageUser["role"] = "user";
			$storageUser["homedir"] = "/";
			$storageUser["permissions"] = "read|write|upload|download|batchdownload|zip";
			$storageUser["password"] = $users['password'];
			$storageUsers[$count] = $storageUser;
			$count++;
		}

		$storageUser = array();
		$storageUser["username"] = 'guest';
		$storageUser["name"] = 'Guest';
		$storageUser["role"] = "guest";
		$storageUser["homedir"] = "/";
		$storageUser["permissions"] = "";
		$storageUser["password"] = "";
		$storageUsers[$count] = $storageUser;
		
		file_put_contents('storage/private/users.json', json_encode($storageUsers));
	}

}
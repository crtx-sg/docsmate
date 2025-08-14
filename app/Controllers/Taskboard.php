<?php namespace App\Controllers;

use App\Models\TaskboardModel;
use App\Models\ProjectModel;
use App\Models\TeamModel;
use App\Models\SettingsModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Taskboard extends BaseController
{
	public function index()
    {
        $data = [];
        $project_id = $this->request->getVar('project-id');
        if($project_id != ""){
            $projectModel = new ProjectModel();
            $project = $projectModel->where('project-id', $project_id)->first();
            $activeProjects = $projectModel->getProjects();
            $data['activeProjects'] =  $activeProjects;
            $title = $project['name'];
            $data['title'] = $title;	
            $data['project_id'] =  $project_id;

            $teamModel = new TeamModel();
            $data['teamMembers'] = $teamModel->getMembers();

            $data['taskCategories'] = $this->getTaskCategoryEnums();

            $data['taskTypes'] = $this->getTaskTypeEnums();

            $taskStats = [];
            foreach($data['taskTypes'] as $key => $val){
                $taskStats[$val["value"]] = 0;
            }
            //$data["taskStats"] = json_encode($taskStats);
            $data["taskStats"] = $taskStats;
            $taskModel = new TaskboardModel();
            $userId = session()->get('id');
            $data['tasksArr'] = $taskModel->getTasks("WHERE project_id = $project_id AND (tasks.assignee = $userId OR tasks.verifier = $userId) ");
            $data['chartData'] = $taskModel->getTasksCount($project_id);
        }
		echo view('templates/header');
		echo view('taskboard',$data);
		echo view('templates/footer');

    }

    private function getTaskCategoryEnums() {
		$settingsModel = new SettingsModel;
		$taskCategory = $settingsModel->where("identifier","taskCategory")->first();
		if($taskCategory["options"] != null){
			$dataList = json_decode( $taskCategory["options"], true );
			$taskCategory = [];
			foreach($dataList as $key=>$list){
				if($list['isRoot']){
					$taskCategory[] = $dataList[$key];
				}
			}
		}else{
			$taskCategory = [];
		}
		return $taskCategory;
	}

    private function getTaskTypeEnums() {
		$settingsModel = new SettingsModel;
		$taskType = $settingsModel->where("identifier","taskType")->first();
		if($taskType["options"] != null){
			$dataList = json_decode( $taskType["options"], true );
			$taskType = [];
			foreach($dataList as $key=>$list){
				if($list['isRoot']){
					$taskType[] = $dataList[$key];
				}
			}
		}else{
			$taskType = [];
		}
		return $taskType;
	}

    public function getTasks(){
        $project_id = $this->request->getVar('project_id');
        $user_id = $this->request->getVar('user_id');

        $userCondition = "";
        if($user_id != "ALL"){
            $userCondition = " AND (tasks.assignee = ".$user_id." OR tasks.verifier = ".$user_id.") ";
        }
        $taskModel = new TaskboardModel();
        $tasksArr = $taskModel->getTasks("WHERE project_id = ".$project_id.$userCondition );
        $chartData= $taskModel->getTasksCount($project_id, $user_id);

        $response["success"] = "True";
        $response["tasksArr"] = $tasksArr;
        $response["chartData"] = $chartData;

        echo json_encode($response);
    }

    private function isAuthorized($id){
        if(session()->get('is-manager')){
            return true;
        }else{
            $taskModel = new TaskboardModel();
            $task = $taskModel->find($id);
            if($task["creator"] == session()->get('id')){
                return true;
            }else{
                $response = array();
                $response["success"] = "False";
                $response["errorMsg"] = "You are not the owner of this task. Your changes will not be saved.";
                echo json_encode($response);
                exit(0);
            }
        }
    }

    public function addTask(){
        
        $id = $this->request->getVar('id');
        $project_id = $this->request->getVar('project_id');
        if($id != ""){
            $this->isAuthorized($id);
        }
        if($project_id == ""){
            $response = array();
            $response["success"] = "False";
            $response["errorMsg"] = "Project cannot be empty!";
            echo json_encode($response);
            exit(0);
        }
        $project_id = $this->request->getVar('project_id');
        $task = [
            "assignee" => $this->request->getVar('newTask_assignee'),
            "description" => $this->request->getVar('newTask_description'),
            "project_id" => $project_id,
            "verifier" => $this->request->getVar('newTask_verifier') ,
            "task_category" => $this->request->getVar('newTask_category'),
            "task_column" => $this->request->getVar('newTask_column'),
            "title" => $this->request->getVar('newTask_title'),
        ];

        $attachmentsDir = "uploads/taskboard/".$project_id;
        $fileLinks = $this->uploadFiles($attachmentsDir);

        if(count($fileLinks)){
            
            $attachments = $this->returnJsonField($id, 'attachments');
            $attachmentsCount = count($attachments);
            $attachmentsCount += 1;
            
            foreach($fileLinks as $key=>$object){
                $attachment["id"] = $attachmentsCount;
                $attachment["link"] = $object['link'];
                $attachment["type"] = $object['type'];
                array_push($attachments, $attachment);
                $attachmentsCount++;
            }
            $task["attachments"] = json_encode($attachments);
            
        }
        
        $model = new TaskboardModel();
        if($id != ""){
            $model->update($id, $task);
            $task = $model->find($id);
        }else{
            $task['creator'] = session()->get('id');
            $id = $model->insert($task);
            $task["id"] = $id;
        }

        
        $response["success"] = "True";
        $response["id"] = $id;
        
        $response["task"] = $task;

        echo json_encode($response);
    }

    private function uploadFiles($attachmentsDir){
        $fileLinks = array();
        if($files = $this->request->getFiles())
        {
            if (!file_exists($attachmentsDir)) {
                mkdir($attachmentsDir, 0777, true);
            }

            foreach($files['attachments'] as $attachment)
            {
                if ($attachment->isValid() && ! $attachment->hasMoved())
                {                   
                    $newName = $attachment->getRandomName();
                    $attachment->move($attachmentsDir, $newName);
                    $type = $attachment->getClientMimeType();
                    $link = "/".$attachmentsDir."/".$newName;

                    $object['link'] = $link;
                    $object['type'] = $type;
                    array_push($fileLinks,  $object);
                }
            }
        }
        return $fileLinks;
    }

    public function updateTaskColumn(){
        
        $id = $this->request->getVar('id');
        $this->isAuthorized($id);

        $task = [
            "task_column" => $this->request->getVar('task_column')
        ];

        $model = new TaskboardModel();
        $model->update($id, $task);
        
        $response = array();
        $response["success"] = "True";
        
        echo json_encode($response);
    }

    private function returnJsonField($id, $fieldName){
        if($id == ""){
            return array();
        }else{
            $model = new TaskboardModel();
            $task = $model->find($id);
            $existingValue = $task[$fieldName];
    
            if($existingValue == null){
                $existingValue = array();
            }else{
                $existingValue = json_decode($existingValue, true);
            }
    
            return $existingValue;
        }
        
    }

    public function addComment(){
        
        $jsonComment["comment"] = $this->request->getVar('comment');
        $jsonComment["timestamp"] = gmdate("Y-m-d H:i:s");
        $jsonComment["by"] = session()->get('name');

        $id = $this->request->getVar('id');
        $model = new TaskboardModel();
        $task = $model->find($id);
        $existingComments = $task['comments'];

        if($existingComments == null){
            $existingComments = array();
        }else{
            $existingComments = json_decode($existingComments, true);
        }
        array_push($existingComments, $jsonComment);

        $updatedTask = [
            "comments" => json_encode($existingComments)
        ];

        $model->update($id, $updatedTask);

        $response = array();
        $response["success"] = "True";
        $response['jsonComment'] = json_encode($jsonComment);

        echo json_encode($response);
    }

    public function deleteTask(){
        
        $id = $this->request->getVar('id');
        $this->isAuthorized($id);
        $model = new TaskboardModel();
        $task = $model->find($id);

        if($task["attachments"] != null){
            $attachments = json_decode($task["attachments"], true);
            foreach($attachments as $attachment){
                $this->deleteAttachments($attachment["link"]);
            }

        }

        $model->delete($id);

        $response = array('success' => "True");
        
        echo json_encode($response);       
    }

    private function deleteAttachments($imagePath){
        $existingFile = ltrim($imagePath, '/'); 
                
        if(file_exists($existingFile)){
            unlink($existingFile);
        }
    }

    public function createExcel() 
    {
        $projectModel = new ProjectModel();
        $projectId = $this->returnParams();
        $projectName = str_replace(" ","",$projectModel->getProjectName($projectId));
        $projectName = str_replace("/","-",$projectName);
		$fileName = $projectName.'_tasks_'.date("Y-m-d_H:i:s").'.xlsx';
        $model = new TaskboardModel();
		
		$spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("TasksList");

        $sheet->setCellValue('A1', 'SNo.');
        $sheet->setCellValue('B1', 'Task Column');
        $sheet->setCellValue('C1', 'Task Category');
        $sheet->setCellValue('D1', 'Title');
        $sheet->setCellValue('E1', 'Assignee');
        $sheet->setCellValue('F1', 'Verifier'); 
       
        $spreadsheet
		->getActiveSheet()
		->getStyle('A1:F1')
		->getFill()
		->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
		->getStartColor()
		->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLACK);

                $spreadsheet->getActiveSheet()->getStyle('A1:G1')
        ->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
        for ($i = 'A'; $i !=  $spreadsheet->getActiveSheet()->getHighestColumn(); $i++) {
            $spreadsheet->getActiveSheet()->getColumnDimension($i)->setAutoSize(TRUE);
        }
        //$spreadsheet->getActiveSheet()->getStyle('A1')->getAlignment()->setWrapText(true);
        $tasksData = $model->where('project_id', $projectId)->orderBy('id', 'desc')->findAll();

        $rows = 2;
        $k=1;
        foreach ($tasksData as $val){
            $sheet->setCellValue('A' . $rows, $k);
            $sheet->setCellValue('B' . $rows, $val['task_column']);
            $sheet->setCellValue('C' . $rows, $val['task_category']);
            $sheet->setCellValue('D' . $rows, $val['title']);
            $sheet->setCellValue('E' . $rows, $projectModel->getName($val['assignee']));
            $sheet->setCellValue('F' . $rows, $projectModel->getName($val['verifier']));
            $rows++;
            $k++;
        }

        $writer = new Xlsx($spreadsheet);
		$projectDocsRootDir = getenv('app.basePath');
		$directoryName = "Project_Tasks";
		if (!is_dir($directoryName)) {
			mkdir($directoryName, 0777);
		}
		$outputFilePath = $projectDocsRootDir . "/" . $directoryName . "/" .$fileName;
		$writer->save($outputFilePath);

        header('Content-Type: application/vnd.ms-excel'); // generate excel file
        header('Content-Disposition: attachment;filename="'. $fileName.'"'); 
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');	// download file 
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

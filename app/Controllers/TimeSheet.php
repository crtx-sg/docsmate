<?php

namespace App\Controllers;

use App\Models\TimeSheetModel;
use App\Models\SettingsModel;
use App\Models\TeamModel;
use App\Models\ProjectModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use CodeIgniter\I18n\Time;
//use \Aidid\BladeView\BladeView;

class TimeSheet extends BaseController
{
    private $settingsModel;
    private $timeSheetModel;
   // public $bladeview;

    public function __construct()
    {
         //$this->bladeview = new BladeView();
        $this->settingsModel = new SettingsModel();
        $this->timeSheetModel = new TimeSheetModel();
    }

    public function index()
    {
        $data = [];
		$data['pageTitle'] = 'TimeSheets';
		$data['addBtn'] = True;
		$data['addUrl'] = "/timesheet/add";

		$model = new TimeSheetModel();

        $selectedProject = null;
        $selectedStatus = null;
        $selectedUser = null;

        helper('Helpers\utils');
		$prev_url = getPrevUrl();
        if(isset($prev_url)){
        if ($prev_url["name"] == "timesheetList") {
            $vars = $prev_url["vars"];
            $selectedStatus = $vars['view'];
            $selectedProject = $vars['project_id'];
            $selectedUser = isset($vars['user_id']) ? $vars['user_id'] : null;
        }
    }

        if($selectedUser == null ){
            $selectedUser = session()->get('id');
        }

        //Projects Dropdown
        $projectModel = new ProjectModel();
        $data['projects'] = $projectModel->getProjects(); 

        $teamModel = new TeamModel();
        $data['teamMembers'] = $teamModel->getMembers();

        $settingsModel = new SettingsModel();
        $timesheetStatusOptions = $settingsModel->getConfig("timesheetStatus");
        array_unshift($timesheetStatusOptions,"All");
        $data["timesheetStatus"] = $timesheetStatusOptions; //Status Radio Buttons

        if ($selectedStatus == null) {
            if ($timesheetStatusOptions != null) {
                $selectedStatus = $timesheetStatusOptions[0]; //Default status
            } else {
                $selectedStatus = null;
            }
        }
            

        if ($selectedProject == null) {
            // helper('Helpers\utils');
            // $selectedProject = getActiveProjectId(); //Default project
            $selectedProject = "ALL";
        }

        $data['selectedProject'] = $selectedProject;
        $data['selectedStatus'] = $selectedStatus;
        $data['selectedUser'] = $selectedUser;

		
		echo view('templates/header');
		echo view('templates/pageTitle', $data);
		echo view('Timesheet/list',$data);
		echo view('templates/footer');
	}

    public function getTimesheets()
    {
        $view = $this->request->getVar('view');
        $project_id = $this->request->getVar('project_id');
        if (session()->get('is-admin')){
            $userId = $this->request->getVar('user_id');
        }else{
            $userId = session()->get('id');
        }

        $vars['user_id'] = $userId;
        $vars['view'] = $view;
        $vars['project_id'] = $project_id;

        helper('Helpers\utils');
        setPrevUrl('timesheetList', $vars);

        $userCondition = "";
        if($userId != "ALL"){
            $userCondition = " ( `user-id` = ".$userId.") ";
        }

        $viewCondition = "";
        if($view != "All"){
            $viewCondition = 'tms.`status` = "'.$view.'"';
            if($project_id != "ALL" || $userId != "ALL")
                $viewCondition = 'tms.`status` = "'.$view.'" AND ';
        }

        $projectCondition = "";
        if($project_id != "ALL"){
            $projectCondition = 'tms.`project-id` = "'.$project_id.'"';
            if($userId != "ALL")
            $projectCondition = 'tms.`project-id` = "'.$project_id.'" AND ';
        }
        

        $model = new TimeSheetModel();
        
        if($projectCondition == "" && $viewCondition == "" && $userCondition == "")
            $whereCondition = "";
        else
            $whereCondition = ' WHERE '.$viewCondition.$projectCondition.$userCondition;
        $data = $model->getTimesheets($whereCondition);
        
        $response["success"] = "True";
        $response["timesheets"] = $data;

        echo json_encode($response);

    }



    public function add(){
        $id = $this->returnParams();

		helper(['form']);
		$model = new TimeSheetModel();
		$teamModel = new TeamModel();
		$data = [];
		$data['pageTitle'] = 'Timesheets';
		$data['addBtn'] = False;
		$data['backUrl'] = "/timesheet";
        $projectModel = new ProjectModel();
        $data['projects'] = $projectModel->getProjects();

        $settingsModel = new SettingsModel();
        $timesheetStatus = $settingsModel->where("identifier", "timesheetStatus")->first();
        if ($timesheetStatus["options"] != null) {
            $data["timesheetStatus"] = json_decode($timesheetStatus["options"], true);
        } else {
            $data["timesheetStatus"] = [];
        }

        $isTimesheetExists = FALSE;
            //verifying whether task alreay exists in that project for same user in open state
            if ($this->request->getMethod() == 'post') {
                $isTimesheetAlreadyExists = $model->where('project-id',$this->request->getVar('project-id'))->where('type',$this->request->getVar('type'))->where('status',"Open")->where('user-id',$this->request->getVar('user-id'))->first();
                if($isTimesheetAlreadyExists['status'] == "Open"){
                    $isTimesheetExists = TRUE;
                    $id = $isTimesheetAlreadyExists['timesheet-id'];
                    // $data['formTitle'] = $data['timesheet']["entry-date"];
                    // //Update form, auto fill the project field
                    // $project_id = $data['timesheet']['project-id'];
                    // $data['project_id'] = $project_id;
                    // $data['project_name'] = $data['projects'][$project_id];
                    // $totalLoggedHours = 0;
                    // if($isTimesheetExists){
                    //     $totalLoggedHours = $data['timesheet']["total-logged-hours"];
                    // }
                }
            }

		if($id == ""){
            $project_id = $this->request->getVar('project_id');
            if ($project_id == "ALL") {
                helper('Helpers\utils');
                $project_id = getActiveProjectId();
            }
            $data['project_id'] = $project_id;
            $data['project_name'] = $data['projects'][$project_id];
            $data['action'] = "add?project_id=${project_id}";
			$data['formTitle'] = "Add TimeSheet";
            $totalLoggedHours = 0;
            
		}else{
			$data['action'] = "add/".$id;
            
			$data['timesheet'] = $model->where('timesheet-id',$id)->first();
            //$isTimesheetExists = FALSE;
            
            // if ($this->request->getMethod() == 'post') {
            //     if(($data['timesheet']['project-id'] == $this->request->getVar('project-id')) && ($data['timesheet']['user-id'] == $this->request->getVar('user-id')) && ($data['timesheet']['type'] == $this->request->getVar('type')) && ($data['timesheet']['status'] == "Open")){
            //         $isTimesheetExists = TRUE;
            //     }
//            }
			$data['formTitle'] = $data['timesheet']["entry-date"];
            //Update form, auto fill the project field
            $project_id = $data['timesheet']['project-id'];
            $data['project_id'] = $project_id;
            $data['project_name'] = $data['projects'][$project_id];
            $totalLoggedHours = 0;
            if($isTimesheetExists){
                $totalLoggedHours = $data['timesheet']["total-logged-hours"];
            }
		}

        //type dropdown
        $settingsModel = new SettingsModel();
        $timeTrackerType = $settingsModel->where("identifier", "timeTrackerCategory")->first();
        
        if ($timeTrackerType["options"] != null) {
            $data["timeTrackerType"] = json_decode($timeTrackerType["options"], true);
        } else {
            $data["timeTrackerType"] = [];
        }

		if ($this->request->getMethod() == 'post') {
			$rules = [
				'project-id' => 'required',
                'log' => 'required',
				//'type' => 'required',
				'entry-date' => 'required',
                'log-date' => 'required',
				'status' => 'required',
                'day-log-hours' => 'required'
			];	
			$newData = [
				'project-id' => $this->request->getVar('project-id'),
				//'log' => $this->request->getVar('log'),
				'type' => $this->request->getVar('type'),
				'entry-date' => $this->request->getVar('entry-date'),
                'log-date' => $this->request->getVar('log-date'),
				'dependencies' => trim($this->request->getVar('dependencies')),
				'day-log-hours' => $this->request->getVar('day-log-hours'),
				'status' => $this->request->getVar('status'),
				'user-id' => session()->get('id')
			];
            if($this->request->getVar('edit-log-hours') == "Changed"){
                $newData['total-logged-hours'] = $totalLoggedHours + $this->request->getVar('day-log-hours');
            }

            if($this->request->getVar('log') != "" && $this->request->getVar('log')!= null){
                if($isTimesheetExists)
                    $logs = $this->returnJsonField($id, 'log');
                else
                    $logs = array();
                $jsonMsg["message"] = $this->request->getVar('log');
                $jsonMsg["logHrs"] = $this->request->getVar('day-log-hours');
                $jsonMsg["timestamp"] = $this->request->getVar('log-date')." ".gmdate("H:i:s");
                
                array_push($logs, $jsonMsg);
                $newData['log'] = json_encode($logs);
            }
            if($this->request->getVar('status') == "Close"){
                $newData['day-log-hours'] = 0;
            }
            
			$data['timesheet'] = $newData;

			if (! $this->validate($rules)) {
				$data['validation'] = $this->validator;
			}else{
				if($id > 0){
                    //$isTimesheetExists = $model->where('timesheet-id',$id)->first();
                    if($isTimesheetExists){
                        $newData['timesheet-id'] = $id;
                        $model->update($id, $newData);
                        $message = 'Timesheet successfully updated.';
                    }else{
                        $model->insert($newData);
					    $message = 'Timesheet successfully added.';
                    }
                    // $newData['timesheet-id'] = $id;
                    // $model->update($id, $newData);
					// $message = 'Timesheet successfully updated.';
				}else{
                    $model->insert($newData);
					$message = 'Timesheet successfully added.';
				}
				// $model->insert($newData);
				$session = session();
				$session->setFlashdata('success', $message);
			}
            $data['timesheet']['day-log-hours'] = 0;
            $data['timesheet']['timesheet-id'] = $id;
		}
		
		echo view('templates/header');
		echo view('templates/pageTitle', $data);
		echo view('Timesheet/form', $data);
		echo view('templates/footer');
	}

    private function returnJsonField($id, $fieldName){
        if($id == ""){
            return array();
        }else{
            $model = new TimesheetModel();
            $timesheet = $model->find($id);
            $existingValue = $timesheet[$fieldName];
    
            if($existingValue == null){
                $existingValue = array();
            }else{
                $existingValue = json_decode($existingValue, true);
            }
    
            return $existingValue;
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

    public function delete()
    {
        $id = $this->returnParams();
        if($id){
            
			$model = new TimeSheetModel();
			$timesheet = $model->find($id);
			
			$model->delete($id);

			$response = array('success' => "True");
			echo json_encode( $response );
		}
		else{
			$response = array('success' => "False");
			echo json_encode( $response );
		}
    }

    public function sendStatusMail()
    {
        $teamModel = new TeamModel();
        $recipients = $teamModel->getManagerEmails();
        $toUsers = '"' . implode ( '","', $recipients ) . '"';

        $data["timesheets"] = $this->timeSheetModel->getCurrentDateTimesheets();

        if(isset($data["timesheets"]) && count($data["timesheets"]) > 0){
            $message = view('Emails/timesheet',$data);
            $email = \Config\Services::email();
            $from = getenv('email.SMTPUser');
            $email->setFrom($from, "DocsGo");
            //$email->setTo('docsgotest@yopmail.com');
            $email->setTo($toUsers);
            //$email->setCC($toUsers);
            $email->setSubject("[VIOS]: Timesheet report for ".date("jS \of F Y"));
            $email->setMessage($message);
            if($email->send()){
                log_message("info","mail sent successfully");
                return true;
            }else{
                $data = $email->printDebugger(['headers']);
                print_r($data);
                log_message("info","failed to sent mail");
                return false;
            }
        }
	}

    public function getLogMessages(){
		$uri = $this->request->uri;
		$timesheetId = $uri->getSegment(3);
		if($timesheetId != ""){
			$data = $this->timeSheetModel->select('log')->where("timesheet-id", $timesheetId)->first();
			$response = array('success' => "True",'result' => $data["log"]);
		}else{
			$response = array('success' => "False");
		}
		echo json_encode($response);
	}

    public function getReports()
    {
        $data = [];
        $data['pageTitle'] = 'Reports';
        $data['addBtn'] = false;
        $data['backUrl'] = "/timesheet";

        $selectedFromDate = null;
        $selectedToDate = null;

        helper('Helpers\utils');
		$prev_url = getPrevUrl();
        if(isset($prev_url)){
            if ($prev_url["name"] == "timesheetReports") {
                $vars = $prev_url["vars"];
                $selectedFromDate = $vars['from_date'];
                $selectedToDate = $vars['to_date'];
            }
        }

        if($selectedFromDate == null){
            $selectedFromDate = date("Y-m-d", strtotime("yesterday"));
        }
                
        if($selectedToDate == null){
            $selectedToDate = date("Y-m-d");
        }

        $data["selectedFromDate"] = $selectedFromDate;
        $data["selectedToDate"] = $selectedToDate;

        echo view('templates/header');
        echo view('templates/pageTitle', $data);
        echo view('Timesheet/Reports/list', $data);
        echo view('templates/footer');
    }

    public function getTimesheetReports()
    {
        $from_date = $this->request->getVar('from_date');
        $to_date = $this->request->getVar('to_date');

        $vars['from_date'] = $from_date;
        $vars['to_date'] = $to_date;

        helper('Helpers\utils');
        setPrevUrl('timesheetReports', $vars);

        $data = $this->timeSheetModel->getTimesheetReportRecords($from_date, $to_date);
        //print_r($data);die;
        $response["success"] = "True";
        $response["timesheetReportsData"] = $data;

        echo json_encode($response);
    }

    public function createExcel() {
		$fileName = 'timesheet_report_'.date("Y-m-d_H:i:s").'.xlsx';
		//$fileName = 'asset.xlsx';
		$from_date = $this->request->getVar('from_date');
        $to_date = $this->request->getVar('to_date');

		$timesheetData = $this->timeSheetModel->getTimesheetExportRecords($from_date, $to_date);
        if(!empty($timesheetData)){
		$spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
       	$sheet->setCellValue('A1', 'SNo.');
        $sheet->setCellValue('B1', 'Task');
        $sheet->setCellValue('C1', 'Log');
        $sheet->setCellValue('D1', 'Total hours');
		$sheet->setCellValue('E1', 'Status');
        $sheet->setCellValue('F1', 'Member');       
		
		$spreadsheet
		->getActiveSheet()
		->getStyle('A1:F1')
		->getFill()
		->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
		->getStartColor()
		->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLACK);
	//$spreadsheet->getActiveSheet()->freezePane('A1');

		$spreadsheet->getActiveSheet()->getStyle('A1:G1')
->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);

for ($i = 'A'; $i !=  $spreadsheet->getActiveSheet()->getHighestColumn(); $i++) {
    $spreadsheet->getActiveSheet()->getColumnDimension($i)->setAutoSize(TRUE);
    //$spreadsheet->getActiveSheet()->getStyle('C')->getAlignment()->setWrapText(TRUE);
}
$spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(50);
$spreadsheet->getActiveSheet()->getStyle('C1')->getAlignment()->setWrapText(true);
        $rows = 2;
        $j = 1;
        foreach ($timesheetData as $val){
            $spreadsheet->getActiveSheet()->getRowDimension($rows)->setRowHeight(70);
            $sheet->setCellValue('A' . $rows, $j);
            $sheet->setCellValue('B' . $rows, $val['task']);
            $sheet->setCellValue('C' . $rows, $val['log']);
            $sheet->setCellValue('D' . $rows, $val['tHours']);
	    	$sheet->setCellValue('E' . $rows, $val['status']);
            $sheet->setCellValue('F' . $rows, $val['name']);
            $rows++;
            $j++;
        } 
        
        $writer = new Xlsx($spreadsheet);
        $projectDocsRootDir = getenv('app.basePath');
		$directoryName = "Timesheet_Reports";
		if (!is_dir($directoryName)) {
			mkdir($directoryName, 0777);
		}else{
            //To delete all old files from existing directory
           // exec("rm ".$projectDocsRootDir . "/" . $directoryName . "/*");
        }
		$outputFilePath = $projectDocsRootDir . "/" . $directoryName . "/" .$fileName;
		$writer->save($outputFilePath);

        $response = array('success' => "True", "status"=>"Downloaded Timesheet excel successfully", "fileName" => $fileName, "fileDownloadUrl" => $directoryName . "/" . $fileName);
        echo json_encode( $response );
    }
    else{
        $response = array('success' => "False", "status"=>"No timesheets to download");
        echo json_encode( $response );

    }
    }

}
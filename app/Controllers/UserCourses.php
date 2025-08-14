<?php namespace App\Controllers;

use App\Models\UserCourseModel;
use App\Models\SettingsModel;
use App\Models\TeamModel;
use App\Models\CourseModel;
use App\Models\ProjectModel;

class UserCourses extends BaseController
{
    public function index()
    {
        $data = [];
        $data['pageTitle'] = 'User Courses';

        $selectedStatus = null;
        $selectedUser = null;

                
        helper('Helpers\utils');
		$prev_url = getPrevUrl();
        if(isset($prev_url)){
            if ($prev_url["name"] == "userCoursesList") {
                $vars = $prev_url["vars"];
                $selectedStatus = $vars['view'];
                $selectedUser = isset($vars['user_id']) ? $vars['user_id'] : null;
            }
        }
        $data['userTotKPoints'] = 0;
        if($selectedUser == null ){
            $selectedUser = "ALL";
        }

        $teamModel = new TeamModel();
        $data['teamMembers'] = $teamModel->getMembers();

        $settingsModel = new SettingsModel();
        $courseStatusOptions = $settingsModel->getConfig("userCourseStatus");
        $data["userCourseStatus"] = $courseStatusOptions; //Status Radio Buttons

        if ($selectedStatus == null) {
            if ($courseStatusOptions != null) {
                $selectedStatus = $courseStatusOptions[0]; //Default status
            } else {
                $selectedStatus = null;
            }
        }


        $userCourseModel = new UserCourseModel();
        $data['userCoursesCount'] = $userCourseModel->getUserCourseStatsCount($selectedUser);

        $data['userTotKPoints'] = $userCourseModel->getUserTotalKPoints($selectedUser);

        $data['userAchievedKPoints'] = $userCourseModel->getUserAchievedKPoints($selectedUser);

        $data['selectedStatus'] = $selectedStatus;
        $data['selectedUser'] = $selectedUser;
       
        echo view('templates/header');
        echo view('templates/pageTitle', $data);
        echo view('UserCourses/list', $data);
        echo view('templates/footer');
    }

    public function getUserCourses()
    {
        $view = $this->request->getVar('view');
        $user_id = $this->request->getVar('user_id');

        $vars['view'] = $view;
        $vars['user_id'] = $user_id;

        helper('Helpers\utils');
        setPrevUrl('userCoursesList', $vars);

        $userCondition = "";
        if($user_id != "ALL"){
            $userCondition = " AND (  ucour.`user_id` = ".$user_id." ) ";
        }

        $model = new UserCourseModel();
        $whereCondition = ' WHERE ucour.`status` = "' .$view. '"'.$userCondition;
        $data = $model->getMappedRecords($whereCondition);

        $response["success"] = "True";
        $response["userCourses"] = $data;

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

    public function add()
    {
        $id = $this->returnParams();

        helper(['form']);
        $model = new UserCourseModel();
        $data = [];
        $data['pageTitle'] = 'Course Register';
        $data['addBtn'] = false;
        $data['backUrl'] = "/userCourses";

        $courseModel = new CourseModel();
        $data['courses'] = $courseModel->getCourses();

        $teamModel = new TeamModel();
        $data['teamMembers'] = $teamModel->getMembers();
        $settingsModel = new SettingsModel();
        $userCourseStatus = $settingsModel->where("identifier", "userCourseStatus")->first();
        if ($userCourseStatus["options"] != null) {
                $userCourseOptionsArr = json_decode($userCourseStatus["options"], true);
            if(session()->get('is-manager') || session()->get('is-admin')){
                $data["userCourseStatus"] = $userCourseOptionsArr;
            }else{
                unset($userCourseOptionsArr[3]);
                $data["userCourseStatus"] = $userCourseOptionsArr;
            }
        } else {
            $data["userCourseStatus"] = [];
        }

        if ($id == "") {
            $data['formTitle'] = "Course Registration";
            //Add new form, auto fill the Course Title fields
            $data['isEditForm'] = false;
            $data['action'] = "add";
            $TeamModel = new TeamModel();
            $data['user'] = $TeamModel->where('id', session()->get('id'))->first();
            $data['user_id'] = $data['user']['id'];
            $data['userCourses']['user_id'] = $data['user']['id'];
        } else {
            $projectModel = new ProjectModel();
            $data['action'] = "add/".$id;
            $data['isEditForm'] = true;
            $data['userCourses'] = $model->where('user_course_id', $id)->first();
            
            //Update form, auto fill the project field
            $course_id = $data['userCourses']['course_id'];
            $data['course_id'] = $course_id;
            $data['course_title'] = $data['courses'][$course_id];
            $data['formTitle'] =  "Edit ".$data['courses'][$course_id]. "-" . $projectModel->getName($data['userCourses']['user_id']);
        }
        $currentTime = gmdate("Y-m-d H:i:s");
        if ($this->request->getMethod() == 'post') {
            $rules = [
                "course_id" => 'required',
                "user_id" => 'required',
                "planned_date" => 'required',
                //"completed_date" => 'required',
                "status" => 'required'
            ];

            $userCourseStatus =  $this->request->getVar('status');
           
            $newData = [
                "user_id" => $this->request->getVar('user_id'),
                "course_id" => $this->request->getVar('course_id'),
                "planned_date" => $this->request->getVar('planned_date'),
                "completed_date" => $this->request->getVar('completed_date'),
                "status" => $userCourseStatus, 
                'updated_at' => $currentTime
            ];

            $session = session();

            if (!$this->validate($rules)) {
                $data['validation'] = $this->validator;
            } else {
                $session = session();
                if ($id > 0) {
                    $newRecordFlag = false;
                    $newData['user_course_id'] = $id;
                    $model->save($newData);
                    $data['userCourses'] = $model->where('user_course_id', $id)->first();
                    $message = 'UserCourse successfully updated.';
                } else {
                    $newRecordFlag = true;
                    $id = $model->save($newData);
                    $message = 'UserCourse successfully added.';
                }
                $session->setFlashdata('success', $message);
            }
        }

        echo view('templates/header');
        echo view('templates/pageTitle', $data);
        echo view('UserCourses/form', $data);
        echo view('templates/footer');
    }

    public function addCourse()
    {
        $id = $this->returnParams();
        $userId = session()->get('id');
        $model = new UserCourseModel();
        $currentTime = gmdate("Y-m-d H:i:s");
        $isExists = $model->where('course_id',$id)->where('user_id',$userId)->first();
        if(isset($isExists) && $isExists != null){
            $response = array('success' => "False");
        }
        else{
            $newData = [
                "user_id" => $userId,
                "course_id" => $id,
                "planned_date" => gmdate("Y-m-d"),
                "status" => "Not-Started",
                'updated_at' => $currentTime
            ];
            $session = session();
            $id = $model->save($newData);
            $response = array('success' => "True");
        }
        echo json_encode($response);
    }

    public function delete()
    {
        $id = $this->returnParams();

        $model = new UserCourseModel();
        $model->delete($id);
        $response = array('success' => "True");
        echo json_encode($response);
    }

    public function getUserCoursesStats()
    {
        $selectedUser =  $this->request->getVar('user_id');

        $model = new UserCourseModel();
        $userCourseStats = $model->getUserCourseStatsCount($selectedUser);
        $response["success"] = "True";
        $response["userCourseStats"] = $userCourseStats;

        echo json_encode($response);
    }

    public function getUserTotalKPoints()
    {
        $selectedUser =  $this->request->getVar('user_id');

        $model = new UserCourseModel();
        $userTotKPoints = $model->getUserTotalKPoints($selectedUser);
        $response["success"] = "True";
        $response["points"] = $userTotKPoints;

        echo json_encode($response);
    }

    public function getUserAchievedKPoints()
    {
        $selectedUser =  $this->request->getVar('user_id');

        $model = new UserCourseModel();
        $userAchievedKPoints = $model->getUserAchievedKPoints($selectedUser);
        $response["success"] = "True";
        $response["points"] = $userAchievedKPoints;

        echo json_encode($response);
    }

    public function getReports()
    {
        $data = [];
        $data['pageTitle'] = 'Reports';
        $data['addBtn'] = false;
        $data['backUrl'] = "/userCourses";

        $selectedFromDate = null;
        $selectedToDate = null;

        helper('Helpers\utils');
		$prev_url = getPrevUrl();
        if(isset($prev_url)){
            if ($prev_url["name"] == "userCoursesReports") {
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

        // $model = new UserCourseModel();
        // $data["coursesReports"] = $model->getUserCourseReportRecords($selectedFromDate, $selectedToDate);

        $data["selectedFromDate"] = $selectedFromDate;
        $data["selectedToDate"] = $selectedToDate;

        echo view('templates/header');
        echo view('templates/pageTitle', $data);
        echo view('UserCourses/Reports/list', $data);
        echo view('templates/footer');
    }

    public function getUserCoursesReports()
    {
        $from_date = $this->request->getVar('from_date');
        $to_date = $this->request->getVar('to_date');

        $vars['from_date'] = $from_date;
        $vars['to_date'] = $to_date;

        helper('Helpers\utils');
        setPrevUrl('userCoursesReports', $vars);


        $model = new UserCourseModel();
        //$whereCondition = ' WHERE ucour.`status` = "' . $view . '" AND cour.`course_id` = ' . $course_id.$userCondition;

        $data = $model->getUserCourseReportRecords($from_date, $to_date);
        //print_r($data);die;
        $response["success"] = "True";
        $response["coursesReports"] = $data;

        echo json_encode($response);

    }


}

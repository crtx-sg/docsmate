<?php namespace App\Controllers;

use App\Models\DocumentModel;
use App\Models\ProjectModel;
use App\Models\ReviewModel;
use App\Models\SettingsModel;
use App\Models\TeamModel;
use TP\Tools\Openfire;

class Reviews extends BaseController
{
    public function index()
    {
        $data = [];
        $data['pageTitle'] = 'Review Register';
        $data['addBtn'] = true;
        $data['addUrl'] = "/reviews/add";

        $selectedStatus = null;
        $selectedProject = null;
        $selectedUser = null;
        $category = null;

                
        helper('Helpers\utils');
		$prev_url = getPrevUrl();
        if(isset($prev_url)){
        if ($prev_url["name"] == "reviewsList") {
            $vars = $prev_url["vars"];
            $selectedStatus = $vars['view'];
            $selectedProject = $vars['project_id'];
            $selectedUser = isset($vars['user_id']) ? $vars['user_id'] : null;
            $category = $vars['category'];
        }
    }

        if($selectedUser == null ){
            $selectedUser = session()->get('id');
        }

        $teamModel = new TeamModel();
        $data['teamMembers'] = $teamModel->getMembers();

        $settingsModel = new SettingsModel();
        $documentStatusOptions = $settingsModel->getConfig("documentStatus");
        $data["reviewStatus"] = $documentStatusOptions; //Status Radio Buttons

        if ($selectedStatus == null) {
            if ($documentStatusOptions != null) {
                $selectedStatus = $documentStatusOptions[0]; //Default status
            } else {
                $selectedStatus = null;
            }
        }

        $projectModel = new ProjectModel();
        $data['projects'] = $projectModel->getProjects(); //Projects Dropdown
        $data['completedProjects'] = $projectModel->getCompletedProjects(); //Completed Projects Dropdown
        
        $reviewCategory = $settingsModel->where("identifier", "reviewCategory")->first();
        if ($reviewCategory["options"] != null) {
            $data["reviewCategory"] = json_decode($reviewCategory["options"], true);
        } else {
            $data["reviewCategory"] = [];
        }

        if ($selectedProject == null) {
            helper('Helpers\utils');
            $selectedProject = getActiveProjectId(); //Default project
        }

        if($selectedProject != null){
            $reviewModel = new ReviewModel();
            $data['reviewsCount'] = $reviewModel->getReviewsCount($selectedProject, $selectedUser, $category);
        }

        $data['selectedProject'] = $selectedProject;
        $data['selectedStatus'] = $selectedStatus;
        $data['selectedUser'] = $selectedUser;
        $data['category'] = $category;
       
        echo view('templates/header');
        echo view('templates/pageTitle', $data);
        echo view('Reviews/list', $data);
        echo view('templates/footer');
    }

    public function getReviewStats()
    {
        $project_id = $this->request->getVar('project_id');
        $selectedUser =  $this->request->getVar('user_id');
        $category = $this->request->getVar('category');

        $reviewModel = new ReviewModel();
        $reviewStats = $reviewModel->getReviewsCount($project_id, $selectedUser, $category);
        $response["success"] = "True";
        $response["reviewStats"] = $reviewStats;

        echo json_encode($response);
    }

    public function getReviews()
    {
        $view = $this->request->getVar('view');
        $project_id = $this->request->getVar('project_id');
        $user_id = $this->request->getVar('user_id');
        $category = $this->request->getVar('category');

        $vars['view'] = $view;
        $vars['project_id'] = $project_id;
        $vars['user_id'] = $user_id;
        $vars['category'] = $category;

        helper('Helpers\utils');
        setPrevUrl('reviewsList', $vars);

        $userCondition = "";
        if($user_id != "ALL"){
            //$userCondition = " AND ( `review-by` = ".$user_id." OR `assigned-to` = ".$user_id." ) ";
            $userCondition = " AND ( FIND_IN_SET($user_id,`review-by`) > 0 OR `assigned-to` = ".$user_id." ) ";

        }

        $categoryCondition = "";
        if($category != "ALL"){
            $categoryCondition = ' AND rev.`category` like "'. $category.'%"';
        }

        $model = new ReviewModel();
        //$whereCondition = ' WHERE rev.`status` = "' . $view . '" AND proj.`project-id` = ' . $project_id.$userCondition .' AND rev.`category` like "'. $category.'%"';
        $whereCondition = ' WHERE rev.`status` = "' . $view . '" AND proj.`project-id` = ' . $project_id.$userCondition.$categoryCondition;

        $data = $model->getMappedRecords($whereCondition);

        $response["success"] = "True";
        $response["reviews"] = $data;

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

    public function addDocReview()
    {
        log_message('info', "Document review");
        if ($this->request->getMethod() == 'post') {
            $response = array();
            $response["success"] = "True";
            $currentTime = gmdate("Y-m-d H:i:s");

            $docId = $this->request->getVar('docId');
            $reviewId = $this->request->getVar('id');
            $status = $this->request->getVar('status');
            $commentId = $this->request->getVar('commentId');
            $message = $this->request->getVar('description');
            $reviewerName = session()->get('name');

            $notificationMessage = ""; 
        
            if($commentId == ""){
                $commentId = uniqid();
                $response["message"] = "Success! Comment added.";
                $notificationMessage = "$reviewerName just added review comments on your document D-$docId";
            }else{
                $response["message"] = "Success! Comment updated.";
                $notificationMessage = "$reviewerName has updated review comments on your document D-$docId";
            }
            
            $reviewModel = new ReviewModel();

            if ($reviewId == "") {
                $data = [
                    "project-id" => $this->request->getVar('projectId'),
                    "review-name" => $this->request->getVar('reviewName'),
                    "category" => $this->request->getVar('category'),
                    "context" => $this->request->getVar('context'),
                    "description" => '',
                    "review-by" => implode(",",$this->request->getVar('reviewBy')),
                    "assigned-to" => $this->request->getVar('assignedTo'),
                    "review-ref" => $this->request->getVar('reviewRef'),
                    'updated-at' => $currentTime,
                    "status" => $status,
                ];
                $reviewId = $reviewModel->insert($data);
            }

            list($comment, $comments) = $this->addToComments($reviewId, $commentId, $message);
                
            $response["comment"] = $comment;

            $commentsData = [
                "status" => $status,
                "description" => json_encode($comments),
            ];

            $isUpdated = $reviewModel->update($reviewId, $commentsData);
            log_message('info', "$isUpdated");
            //Updating Document Revision

            $documentModel = new DocumentModel();

            $doc = $documentModel->where('id', $docId)->first();
            $approver = "";
            if($doc['approved-by'] != "" && $doc['approved-by'] != null){
                $approver = $doc['approved-by'];
            }

            $revision["dateTime"] = $currentTime;
            $revision["who"] = $reviewerName;
            $revision["type"] = "Reviewed";
            $revision["log"] = "Review comments added.";
        
            $baseUrl = getenv('app.baseURL');
            $referenceLink = $baseUrl . "/documents/add?id=" . $docId;
            
            if ($doc['status'] != $status) {
                $revision["log"] .= " Document status updated to " . $status;
                $notificationMessage .= " And also, updated its status to  $status.";
            }
            
            
            //$notificationMessage .= "\nName - ".$doc["file-name"]." \nComment - ".$message." \nLink - ".$baseUrl."/documents/add?id=".$docId;
            
            $revisionHistory = $doc["revision-history"];
            if ($revisionHistory != null) {
                $revisionHistory = json_decode($revisionHistory, true);
            } else {
                $revisionHistory["revision-history"] = array();
            }
            array_push($revisionHistory["revision-history"], $revision);

            //Adding review id to the document
            $docData = [
                "review-id" => $reviewId,
                "status" => $status,
                'update-date' => $currentTime,
                'revision-history' => json_encode($revisionHistory),
            ];

            if($status == "Approved"){
                $docData["approved-by"] = ($approver != "") ? ($approver.",".session()->get("id")):session()->get("id");
				$docData['approved-at'] = $currentTime;
                $docData["approved-by"] = implode(',', array_unique(explode(',', $docData["approved-by"])));
            }

            //print_r($docData);exit;
            $documentModel->update($docId, $docData);

            $notificationMessage  .= " Comments - $message";
            helper('Helpers\utils');            	
			sendNotification($doc['author-id'], "D-$docId", "Review Comments Added", $referenceLink, $notificationMessage);

            $response['reviewId'] = $reviewId;
            $response["revisionHistory"] = json_encode($revisionHistory);
            $response['status'] = $status;

            echo json_encode($response);
        }
    }

    public function add()
    {
        $id = $this->returnParams();

        helper(['form']);
        $model = new ReviewModel();
        $data = [];
        $data['pageTitle'] = 'Review Register';
        $data['addBtn'] = false;
        $data['backUrl'] = "/reviews";

        $projectModel = new ProjectModel();
        $data['projects'] = $projectModel->getProjects();
        $data['completedProjects'] = $projectModel->getCompletedProjects(); //Completed Projects Dropdown

        $teamModel = new TeamModel();
        $data['teamMembers'] = $teamModel->getMembers();
        // $data['reviewStatus'] = ['Request Change', 'Ready For Review', 'Accepted'];
        $settingsModel = new SettingsModel();
        $reviewStatus = $settingsModel->where("identifier", "documentStatus")->first();
        if ($reviewStatus["options"] != null) {
            $reviewStatusOptionsArr = json_decode($reviewStatus["options"], true);
            unset($reviewStatusOptionsArr[1]);
            unset($reviewStatusOptionsArr[2]);
            $data["reviewStatus"] = $reviewStatusOptionsArr;
        } else {
            $data["reviewStatus"] = [];
        }

        $reviewCategory = $settingsModel->where("identifier", "reviewCategory")->first();
        if ($reviewCategory["options"] != null) {
            $data["reviewCategory"] = json_decode($reviewCategory["options"], true);
        } else {
            $data["reviewCategory"] = [];
        }

        // $data['categoryList'] = ["User Needs", "Plan", "Requirements", "Design",
        //  "Code", "Verification", "Validation", "Release", "Risk Management", "Traceability"];

        if ($id == "") {
            $data['formTitle'] = "Add Review";
            //Add new form, auto fill the project,Name,Author fields
            $project_id = $this->request->getVar('project_id');
            if ($project_id == "null") {
                $session = session();
                $session->setFlashdata('alert', "danger");
                $session->setFlashdata('message', "Create a project first!");
                return redirect()->to('/reviews');
            }
            $data['project_id'] = $project_id;
            $data['project_name'] = ($data['projects'][$project_id] != ""? $data['projects'][$project_id]:$data['completedProjects'][$project_id]);
            $data['action'] = "add?project_id=${project_id}";
            $TeamModel = new TeamModel();
            $data['user'] = $TeamModel->where('id', session()->get('id'))->first();
            $data['review']['assigned-to'] = $data['user']['id'];
        } else {
            $data['action'] = "add/" . $id;
            $approver = "";
            $data['review'] = $model->where('id', $id)->first();
            if($data['review']['approved-by'] != "" && $data['review']['approved-by'] != null){
                $approver = $data['review']['approved-by'];
            }
            $settingsModel = new SettingsModel();
            $reviewStatus = $settingsModel->where("identifier", "documentStatus")->first();
            $reviewer = explode(",", $data['review']['review-by']);
            if ($reviewStatus["options"] != null) {
                $reviewStatusOptionsArr = json_decode($reviewStatus["options"], true);
                if(session()->get('id') == $data['review']['assigned-to']){
                    unset($reviewStatusOptionsArr[1]);
                    unset($reviewStatusOptionsArr[2]);
                    $data["reviewStatus"] = $reviewStatusOptionsArr;
                }else if(in_array(session()->get('id'), $reviewer)){
                    unset($reviewStatusOptionsArr[0]);
                    unset($reviewStatusOptionsArr[3]);
                    $data["reviewStatus"] = $reviewStatusOptionsArr;
                }
            } else {
                $data["reviewStatus"] = [];
            }
  
            //$data['review']['review-by'] = explode(",",$data['review']['review-by']);
            $data['review']['review-by'] = $reviewer;
            $data['formTitle'] = 'R-' . $data['review']['id'] . " " . $data['review']['context'];
            //Update form, auto fill the project field
            $project_id = $data['review']['project-id'];
            $data['project_id'] = $project_id;
            $data['project_name'] = ($data['projects'][$project_id] != ""? $data['projects'][$project_id]:$data['completedProjects'][$project_id]);

            $data['nearByReviews'] = $this->getNearByReviews($data['review']['updated-at']);

        }
        $currentTime = gmdate("Y-m-d H:i:s");
        if ($this->request->getMethod() == 'post') {
            $rules = [
                "project-id" => 'required',
                "review-name" => 'required|max_length[64]',
                "assigned-to" => 'required',
                "context" => 'required|max_length[60]',
                "review-by" => 'required',
                "status" => 'required',
                "category" => 'required',
            ];

            $errors = [
                'review-by' => [
                    'required' => 'Select a reviewer',
                ],
            ];

            $reviewStatus =  $this->request->getVar('status');
            $newData = [
                "assigned-to" => $this->request->getVar('assigned-to'),
                "context" => $this->request->getVar('context'),
                "code-diff" => $this->request->getVar('code-diff'),
                "project-id" => $this->request->getVar('project-id'),
                "review-by" => implode(",",$this->request->getVar('review-by')),
                "review-name" => $this->request->getVar('review-name'),
                "review-ref" => $this->request->getVar('review-ref'),
                "status" => $reviewStatus, 
                'updated-at' => $currentTime,
                "category" => $this->request->getVar('category'),
            ];

            

            if (!$this->validate($rules, $errors)) {
                $data['validation'] = $this->validator;
            } else {
                $session = session();
                if ($id > 0) {
                    $newRecordFlag = false;
                    $newData['id'] = $id;
                    if($newData["status"] == "Approved"){
                        $newData["approved-by"] = ($approver != "") ? ($approver.",".session()->get("id")):session()->get("id");
                        $newData['approved-at'] = $currentTime;
                        $newData["approved-by"] = implode(',', array_unique(explode(',', $newData["approved-by"])));
                    }
                    $model->save($newData);
                    $data['review'] = $model->where('id', $id)->first();
                    $data['review']['review-by'] = explode(",",$data['review']['review-by']);
                    $message = 'Review successfully updated.';
                } else {
                    $newRecordFlag = true;
                    $id = $model->insert($newData);
                    $message = 'Review successfully added.';
                }

                $userName = session()->get('name');
                $notificationMessage = "$userName has requested review of R-$id.";
                $baseUrl = getenv('app.baseURL');
                $referenceLink = $baseUrl . "/reviews/add/" . $id;

                helper('Helpers\utils');
                $reviewerIDs = explode(",",$newData["review-by"]);
                foreach($reviewerIDs as $reviewerId){
                    sendNotification( $reviewerId, "R-$id", "Review Requested", $referenceLink, $notificationMessage);
                }
        
                $session->setFlashdata('success', $message);
                if($newRecordFlag){
                    return redirect()->to('/reviews/add/' . $id);
                }

            }
        }

        echo view('templates/header');
        echo view('templates/pageTitle', $data);
        echo view('Reviews/form', $data);
        echo view('templates/footer');
    }

    public function saveComment()
    {
		$response = array('success' => "True");

        $reviewId = $this->request->getVar('reviewId');
        $commentId = $this->request->getVar('commentId');
		$message = $this->request->getVar('message');
        $reviewStatus = $this->request->getVar('reviewStatus');
        $reviewerName = session()->get('name');

        $notificationMessage = ""; 

        if ($commentId != "") {
            $responseMessage = "Success! Comment updated.";
            $notificationMessage = "$reviewerName has updated review comments on your review R-$reviewId.";
        } else {
            $commentId = uniqid();
            $responseMessage = "Success! Comment added.";
            $notificationMessage = "$reviewerName just added review comments on your review R-$reviewId.";
        }
        list($comment, $comments) = $this->addToComments($reviewId, $commentId, $message);

        $data = [
            "description" => json_encode($comments),
		];

        $reviewModel = new ReviewModel();
        $review = $reviewModel->find($reviewId);
        $approver = "";
        if($review['approved-by'] != "" && $review['approved-by'] != null){
            $approver = $review['approved-by'];
        }
        

		if($reviewStatus != ""){
			$data["status"] = $reviewStatus;
            if($reviewStatus == "Approved"){
                $data["approved-by"] = ($approver != "") ? ($approver.",".session()->get("id")):session()->get("id");
                $data["approved-at"] = gmdate("Y-m-d H:i:s");
                $data["approved-by"] = implode(',', array_unique(explode(',', $data["approved-by"])));
            }
            $response["updateStatus"] = "True";
            $notificationMessage .= " And also, updated its status to $reviewStatus.";
		}else{
			$response["updateStatus"] = "False";
		}
        $reviewModel->update($reviewId, $data);
        
        $baseUrl = getenv('app.baseURL');
        $referenceLink = "$baseUrl/reviews/add/$reviewId";
            
        $notificationMessage  .= " Comments - $message";
        helper('Helpers\utils');            	
        sendNotification($review['assigned-to'], "R-$reviewId", "Review Comments Added", $referenceLink, $notificationMessage);

        $response["comment"] = $comment;
        $response["message"] = $responseMessage;

        echo json_encode($response);

    }

    private function addToComments($reviewId, $commentId, $message){

        $comment = array();
        $comment["id"] = $commentId;
        $comment["by"] = session()->get('name');
        $comment["message"] = $message;
        $comment["timestamp"] = gmdate("Y-m-d H:i:s");

        $reviewModel = new ReviewModel();
        $record = $reviewModel->where('id', $reviewId)->first();

        $comments = $record["description"];

        if ($comments == null) {
            $comments = array();
        } else {
            $comments = json_decode($comments, true);
        }

        $comments[$commentId] = $comment;

        uasort($comments, function ($a, $b) {
            $ad = new \DateTime($a['timestamp']);
            $bd = new \DateTime($b['timestamp']);

            if ($ad == $bd) {
                return 0;
            }

            return $ad < $bd ? -1 : 1;
        });

        return array($comment, $comments);
    }

    public function deleteComment()
    {
        $reviewId = $this->request->getVar('reviewId');
        $commentId = $this->request->getVar('commentId');

        $reviewModel = new ReviewModel();
        $record = $reviewModel->where('id', $reviewId)->first();

        $comments = $record["description"];
        $comments = json_decode($comments, true);
        unset($comments[$commentId]);

        if (count($comments)) {
            $comments = json_encode($comments);
        } else {
            $comments = null;
        }

        $data = [
            "description" => $comments,
        ];

        $reviewModel->update($reviewId, $data);

        $response = array('success' => "True");
        $response["commentId"] = $commentId;
        $response["message"] = "Success! Comment deleted.";

        echo json_encode($response);
    }

//One time use to convert text fields to json for review comments
    public function updateReviewDescription()
    {
        $reviewModel = new ReviewModel();
        $reviews = $reviewModel->getMappedRecords();
        foreach ($reviews as $index => $review) {
            $id = $review["id"];
            $message = $review["description"];
            $reviewer = $review["reviewer"];
            $timestamp = $review["updated-at"];
            if ($message != null) {
                $comment["id"] = uniqid();
                $comment["by"] = $reviewer;
                $comment["message"] = $message;
                $comment["timestamp"] = $timestamp;

                $comments = array();
                $comments[$comment["id"]] = $comment;
                $reviews[$index]["description"] = json_encode($comments);
                $reviewModel->save($reviews[$index]);
            }
        }
        return $reviews;
    }

    private function getNearByReviews($id)
    {
        $prevId = null;
        $nextId = null;

        helper('Helpers\utils');
		$prev_url = getPrevUrl();
        if(isset($prev_url)){
            if ($prev_url["name"] == "reviewsList") {
                $vars = $prev_url["vars"];
                $status = $vars['view'];
                $project_id = $vars['project_id'];
                $user_id = isset($vars['user_id']) ? ( ($vars['user_id'] != 'ALL') ? $vars['user_id'] : '' ): '';
                $category = $vars['category'];
                $model = new ReviewModel();
                $prevId = $model->getPrevReviewId($id, $project_id, $status, $user_id);
                $nextId = $model->getNextReviewId($id, $project_id, $status, $user_id);
            }
        }
        $rev["prevId"] = $prevId;
        $rev["nextId"] = $nextId;

        return $rev;
    }

    public function delete()
    {
        $id = $this->returnParams();

        $model = new ReviewModel();
        $model->delete($id);
        $response = array('success' => "True");
        echo json_encode($response);
    }

}

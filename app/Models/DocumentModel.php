<?php  namespace App\Models;

use CodeIgniter\Model;

class DocumentModel extends Model{
    protected $table = 'docsgo-documents';
    protected $allowedFields = ["project-id", "review-id", "type", "category", "author-id", "reviewer-id", 
                                "update-date", "json-object", "file-name", "status", "revision-history","approved-by", "approved-at"];
    
    public function getProjects($type = "", $status = "", $project_id = ""){
        $db      = \Config\Database::connect();
        
        $whereCondition = "";
        if($type != ""){
            $whereCondition = "WHERE docs.`type` = '".$type."' ";
        }

        if($status != "" && $project_id  != ""){
            $whereCondition = "WHERE docs.`status` = '".$status."' and docs.`project-id` = ".$project_id." ";
        }

        //$sql = "SELECT docs.`id`,docs.`project-id`,docs.`review-id`,docs.`type`,docs.`author-id`, author.`name` as `author`, reviewer.`name` as `reviewer`, docs.`update-date`,docs.`json-object`,docs.`file-name`,docs.`status`
        $sql = "SELECT docs.`id`,docs.`project-id`,docs.`review-id`,docs.`type`,docs.`author-id`, author.`name` as `author`, `reviewer-id` as `reviewer`, docs.`update-date`,docs.`json-object`,docs.`file-name`,docs.`status`
         FROM `docsgo-documents` AS docs
        INNER JOIN `docsgo-team-master` AS author ON docs.`author-id` = author.`id` 
        INNER JOIN `docsgo-team-master` AS reviewer ON docs.`reviewer-id` = reviewer.`id` 
        ".$whereCondition."
        ORDER BY docs.`update-date` DESC;";

        $query = $db->query($sql);

        $data = $query->getResult('array');
        $teamModel = new TeamModel();

        for($i=0; $i<count($data );$i++){
            $reviewerIDs = explode(",",$data[$i]['reviewer']);
            $reviewersArr = $teamModel->getMemberNames($reviewerIDs);
            $reviewers = implode(",",$reviewersArr);
            $data[$i]['reviewer'] = $reviewers;
        }
        return $data;
    }

    public function getDocumentsData($type = '', $id = '') {
        $db = \config\Database::connect();

        $whereCondition = "";
        if($type == 'project'){
            $sql = "SELECT * FROM `docsgo-documents` WHERE `project-id` = '".$id."' AND `status` = 'Approved' ";
        }else{
            $whereCondition = "WHERE `id` = '".$id."' ";
            $sql = "SELECT * FROM `docsgo-documents` ". $whereCondition;
        }
        $query = $db->query($sql);
        $data = $query->getResult('array');
        return $data;
    }

        public function getDocuments($whereCondition = "", $limit = "", $loggedInUserId = ""){    
        $db      = \Config\Database::connect();
        
        //$sql = "SELECT  CONCAT('D','-',docs.`id`) as documentId, docs.`id`,docs.`project-id`,docs.`review-id`,docs.`type`,docs.`author-id`, author.`name` as `author`, reviewer.`name` as `reviewer`, docs.`update-date`,docs.`json-object`,docs.`file-name`,docs.`status`
        $sql = "SELECT  CONCAT('D','-',docs.`id`) as documentId, docs.`id`,docs.`project-id`,docs.`review-id`,docs.`type`,docs.`author-id`, author.`name` as `author`, `reviewer-id` as `reviewer`, docs.`update-date`,docs.`json-object`,docs.`file-name`,docs.`status`, docs.`approved-by`, team.`name` as `approver`,docs.`approved-at`
        FROM `docsgo-documents` AS docs
        INNER JOIN `docsgo-team-master` AS author ON docs.`author-id` = author.`id` 
        INNER JOIN `docsgo-team-master` AS reviewer ON docs.`reviewer-id` = reviewer.`id` 
        LEFT JOIN `docsgo-team-master` AS team ON docs.`approved-by` = team.`id` 
        ".$whereCondition."
        ORDER BY docs.`update-date` DESC ".$limit." ;";

        $query = $db->query($sql);

        $data = $query->getResult('array');
        $teamModel = new TeamModel();

        if( $loggedInUserId != "") {
            $isUserAllowed = $this->getUserPermissions($loggedInUserId);
        }

        for($i=0; $i<count($data);$i++){
			$decodedJson = json_decode($data[$i]['json-object'], true);
			$data[$i]['title'] = $decodedJson[$data[$i]['type']]['cp-line3'];
            if( $loggedInUserId != "") {
                $data[$i]['is-allowed'] = $isUserAllowed ? "True" : "False";
            }
            $reviewerIDs = explode(",",$data[$i]['reviewer']);
            $reviewersArr = $teamModel->getMemberNames($reviewerIDs);
            $reviewers = implode(",",$reviewersArr);
            $data[$i]['reviewer'] = $reviewers;

            $approverIDs = explode(",",$data[$i]['approved-by']);
            $approversArr = $teamModel->getMemberNames($approverIDs);
            $approvers = implode(",",$approversArr);
            $data[$i]['approver'] = $approvers;
        }
        
        return $data;

    }

    public function getDocumentsCount($project_id, $user_id){
        $db      = \Config\Database::connect();
        $userCondition = "";
        if($user_id != "ALL"){
            $userCondition = " AND ( FIND_IN_SET($user_id,`reviewer-id`) > 0 OR `author-id` = ".$user_id." ) ";
        }
        $sql = "select count(*) as count ,status from `docsgo-documents` where `project-id` = ".$project_id.$userCondition." group by status";

        $query = $db->query($sql);

        $result = $query->getResult('array');
        if(count($result)){
            for($i=0; $i<count($result);$i++){
                $data[$result[$i]['status']] = $result[$i]['count'];
            }
            return $data;
        }else{
            return null;
        }
    
    }

    public function getPrevDocId($updateDate, $project_id, $status, $user_id){
        $userCondition = "";
        if($user_id != ""){
            //$userCondition = " AND ( `reviewer-id` = ".$user_id." OR `author-id` = ".$user_id." ) ";
            $userCondition = " AND ( FIND_IN_SET($user_id,`reviewer-id`) > 0 OR `author-id` = ".$user_id." ) ";
        }

        $db      = \Config\Database::connect();
        $sql = "SELECT id from `docsgo-documents` where `update-date` < '".$updateDate."' and `project-id` = ".$project_id." and status = '".$status."' ".$userCondition."  ORDER BY `update-date` desc LIMIT 1;";
       
        $query = $db->query($sql);

        $data = $query->getResult('array');
        if(count($data)){
            $data = $data[0]['id'];
        }else{
            $data = null;
        }
        return $data;
    }
     
    public function getNextDocId($updateDate, $project_id, $status, $user_id){
        $userCondition = "";
        if($user_id != ""){
            //$userCondition = " AND ( `reviewer-id` = ".$user_id." OR `author-id` = ".$user_id." ) ";
            $userCondition = " AND ( FIND_IN_SET($user_id,`reviewer-id`) > 0 OR `author-id` = ".$user_id." ) ";
        }
        
        $db      = \Config\Database::connect();
        $sql = "SELECT id from `docsgo-documents` where `update-date` > '".$updateDate."' and `project-id` = ".$project_id." and status = '".$status."' ".$userCondition." ORDER BY `update-date`,id desc LIMIT 1;";
       
        $query = $db->query($sql);

        $data = $query->getResult('array');
        if(count($data)){
            $data = $data[0]['id'];
        }else{
            $data = null;
        }
        return $data;
    }

    public function getApprovedFilesCount($id) {
        $db      = \Config\Database::connect();
        $sql = "SELECT count(*) as count FROM `docsgo-documents` WHERE `project-id`=".$id." AND status='Approved'";
        $query = $db->query($sql);
        $data = $query->getResult('array');
        if(count($data)){
            $data = $data[0]['count'];
        }else{
            $data = null;
        }
        return $data;
    }

    public function getDownloadPaths($id){
        $db = \config\Database::connect();
        $sql = " SELECT count(*) as count from `docsgo-documents` WHERE `project-id`= '".$id."' AND status ='Approved' AND `download-path` IS NULL;";
        $query = $db->query($sql);
        $data = $query->getResult('array');
        if(count($data)){
            $data = $data[0]['count'];
            if($data > 0){
                //grab the file names
                $sql = "SELECT id, `file-name`, `download-path` FROM `docsgo-documents` WHERE `project-id`='".$id."' and status='Approved'";
                $query = $db->query($sql);
                $data = $query->getResult('array');
                if(count($data)){
                    $fileNames = [];
                    $ar =[];
                    for($i=0; $i<count($data);$i++){
                        $name = preg_replace('/[^A-Za-z0-9\-]/', '_', $data[$i]['file-name']);
                        $name = $name.'.pdf';
                        $fileNames[$name] = $data[$i]['id'];
                    }
                    $dataList = [];
                    $dataList['fileNames'] = true;
                    $dataList['list'] = $fileNames;
                    return $dataList;
                }else{
                    return null;
                }
            }else{
                //grap the downloadpaths
                $sql1 = "SELECT `file-name`,id, `project-id`,`download-path` from `docsgo-documents` WHERE `project-id`= '".$id."' AND status='Approved'";
                $query = $db->query($sql1);
                $data = $query->getResult('array');
                $dataList = [];
                $dataList['fileNames'] = false;
                $dataList['list'] = $data;
                return $dataList;
            }
        }else{
            $data = null;
        }
    }

    public function updateGenerateDownloadPath($projectId) {
        $db = \Config\Database::connect();
        $sql = "UPDATE `docsgo-documents` SET `download-path` = NULL WHERE `project-id` = '".$projectId."' ";
        $query = $db->query($sql);
        $data = $query->getResult('array');
        return $data;
    }

    public function updateDownloadUrl($projectId, $id, $path) {
        $db = \Config\Database::connect();
        if( $path != NULL ){
            $sql = "UPDATE `docsgo-documents` SET `download-path` = '".$path."' WHERE `id` = '".$id."' AND `project-id` = '".$projectId."' ";
        } else {
            $sql = "UPDATE `docsgo-documents` SET `download-path` = NULL WHERE `id` = '".$id."' AND `project-id` = '".$projectId."' ";
        }

        $query = $db->query($sql);
        $data = $query->getResult('array');
        return $data;
    }

    public function getUserPermissions($userId){
        $db = \Config\Database::connect();
        $sql = "SELECT `is-manager`, `is-admin` FROM `docsgo-team-master` WHERE `id` = '".$userId."'";
        $query = $db->query($sql);
        $data = $query->getResult('array');
        if(count($data)>0){
            if($data[0]['is-admin'] == "1" || $data[0]['is-manager'] == "1"){
                return true;
            };
        }

        $whereCondition = " WHERE settings.identifier = 'documentProperties' ";
        $sql = "SELECT settings.options FROM `docsgo-settings` settings ".$whereCondition.";";
        $query = $db->query($sql);
        $docProperties = json_decode($query->getResult('array')[0]['options'], true);
        $userIds = [];
        foreach($docProperties as $key => $val){
            if($val["key"] == "docsDownloadPermissions"){
                $userIds = $val["value"];
            }
        }

        if(in_array("$userId", $userIds)){
            return true;
        } else {
            return false;
        }
    }
}
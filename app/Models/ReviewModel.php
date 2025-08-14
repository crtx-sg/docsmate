<?php  namespace App\Models;

use CodeIgniter\Model;
use App\Models\ProjectModel;

class ReviewModel extends Model{
    protected $table = 'docsgo-reviews';
    protected $allowedFields = ["assigned-to","context","description", "code-diff", "id","project-id",
                                "review-by","review-name","review-ref","status","updated-at", "category", "approved-by",
                                "approved-at"];


    public function getMappedRecords($whereCondition = ""){
        $db      = \Config\Database::connect();
        //$sql = "SELECT CONCAT('R','-',rev.`id`) as reviewId, rev.`id`, rev.`review-by`, team.`name` as `reviewer`, rev.`assigned-to`, team2.name as `author`,
        $sql = "SELECT CONCAT('R','-',rev.`id`) as reviewId, rev.`id`, rev.`review-by`, rev.`review-by` as reviewer, rev.`assigned-to`, team2.name as `author`,
        rev.`context`,rev.`description`,proj.`name` as `project-name`,rev.`review-name`,rev.`review-ref`,rev.`status`, rev.`updated-at`, rev.`approved-by`, team3.name as `approver`,rev.`approved-at`
         FROM `docsgo-reviews` AS rev 
        INNER JOIN `docsgo-projects` AS proj ON proj.`project-id` = rev.`project-id`
        INNER JOIN `docsgo-team-master` AS team ON rev.`review-by` = team.`id` 
        INNER JOIN `docsgo-team-master` AS team2 ON rev.`assigned-to` = team2.`id` 
        LEFT JOIN `docsgo-team-master` AS team3 ON rev.`approved-by` = team3.`id`
        ".$whereCondition."
         ORDER BY rev.`updated-at` desc;";

        $query = $db->query($sql);
        $data = $query->getResult('array');
        $teamModel = new TeamModel();

        for($i=0; $i<count($data );$i++){
            $reviewerIDs = explode(",",$data[$i]['reviewer']);
            $reviewersArr = $teamModel->getMemberNames($reviewerIDs);
            $reviewers = implode(",",$reviewersArr);
            $data[$i]['reviewer'] = $reviewers;

            $approverIDs = explode(",",$data[$i]['approved-by']);
            $approverArr = $teamModel->getMemberNames($approverIDs);
            $approvers = implode(",",$approverArr);
            $data[$i]['approver'] = $approvers;

            $description = $data[$i]['description'];
            
            if($description != null && $description != ""){
                $description = json_decode($description, true);
                $comments = "";
                foreach($description as $comment){
                    $comments .= "[".$comment["by"].", " . $comment["timestamp"] . "]";
                    $comments .= $comment["message"];
                    $comments .= "<br/>";
                }
                $data[$i]['description'] = $comments;
            }
            
        }
        return $data;
    }

    public function getReviewsCount($project_id, $user_id, $category){
        $db      = \Config\Database::connect();
        $userCondition = "";
        if($user_id != "ALL"){
            $userCondition = " AND ( FIND_IN_SET($user_id,`review-by`) > 0 OR `assigned-to` = ".$user_id." ) ";
        }

        $categoryCondition = "";
        if($category != "ALL"){
            $categoryCondition = ' AND `category` like "'. $category.'%"';
        }
        
        //$sql = "select count(*) as count ,status from `docsgo-reviews` where `project-id` = ".$project_id.$userCondition." group by status";
        $sql = "select count(*) as count ,status from `docsgo-reviews` where `project-id` = ".$project_id.$userCondition.$categoryCondition." group by status";

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

    public function getPrevReviewId($updateDate, $project_id, $status, $user_id){
        $userCondition = "";
        if($user_id != ""){
            $userCondition = " AND ( FIND_IN_SET($user_id,`review-by`) > 0 OR `assigned-to` = ".$user_id." ) ";
        }

        $db      = \Config\Database::connect();
        $sql = "SELECT id from `docsgo-reviews` where `updated-at` < '".$updateDate."' and `project-id` = ".$project_id." and status = '".$status."' ".$userCondition."  ORDER BY `updated-at` desc LIMIT 1;";
       
        $query = $db->query($sql);

        $data = $query->getResult('array');
        if(count($data)){
            $data = $data[0]['id'];
        }else{
            $data = null;
        }
        return $data;
    }
     
    public function getNextReviewId($updateDate, $project_id, $status, $user_id){
        $userCondition = "";
        if($user_id != ""){
            $userCondition = " AND (`review-by` = ".$user_id." OR `assigned-to` = ".$user_id." ) ";
        }
        
        $db      = \Config\Database::connect();
        $sql = "SELECT id from `docsgo-reviews` where `updated-at` > '".$updateDate."' and `project-id` = ".$project_id." and status = '".$status."' ".$userCondition." ORDER BY `updated-at`,id desc LIMIT 1;";
       
        $query = $db->query($sql);

        $data = $query->getResult('array');
        if(count($data)){
            $data = $data[0]['id'];
        }else{
            $data = null;
        }
        return $data;
    }

    public function getProjectReviewRecords($project_id){
        $db      = \Config\Database::connect();

        $sql = "select count(*) as count ,category from `docsgo-reviews` where `project-id` = ".$project_id." group by category";

        $query = $db->query($sql);
        $result = $query->getResult('array');
        if(count($result)){
            for($i=0; $i<count($result);$i++){
                $data[$result[$i]['category']] = $result[$i]['count'];
            }
            return $data;
        }else{
            return null;
        }   
    }

    public function getMappedRecordsForDocs($whereCondition = ""){
        $db      = \Config\Database::connect();
        $sql = "SELECT CONCAT('R','-',rev.`id`) as reviewId, rev.`id`, rev.`review-by`, team.`name` as `reviewer`, rev.`assigned-to`, team2.name as `author`,
        rev.`context`,rev.`description`,proj.`name` as `project-name`,rev.`review-name`,rev.`review-ref`,rev.`status`, rev.`updated-at`
        FROM `docsgo-reviews` AS rev
        INNER JOIN `docsgo-projects` AS proj ON proj.`project-id` = rev.`project-id`
        INNER JOIN `docsgo-team-master` AS team ON rev.`review-by` = team.`id` 
        INNER JOIN `docsgo-team-master` AS team2 ON rev.`assigned-to` = team2.`id` 
        ".$whereCondition."
         ORDER BY rev.`updated-at` desc;";

        $query = $db->query($sql);
        $data = $query->getResult('array');
        $projectModel = new ProjectModel();

        for($i=0; $i<count($data );$i++){
            $description = $data[$i]['description'];
            
            if($description != null && $description != ""){
                $description = json_decode($description, true);
                $comments = "";
                foreach($description as $comment){
                    $comments .= "[".$comment["by"].", " . $comment["timestamp"] . "]";
                    $comments .= $comment["message"];
                    $comments .= "<br/>";
                }
                $data[$i]['description'] = $comments;
            }
            $reviewReferee = "";
            $reviewReferee .= "[".$projectModel->getName($data[$i]["assigned-to"]).":".$data[$i]["updated-at"]."]";
            $reviewReferee .= $data[$i]["review-ref"];
            //$data[$i]['description'] = $data[$i]['description'] . $reviewReferee;
            $data[$i]['description'] =  $reviewReferee . $data[$i]['description'];
        }
        return $data;
    }
}
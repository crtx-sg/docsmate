<?php  namespace App\Models;

use CodeIgniter\Model;

class ProjectModel extends Model{
    protected $table = 'docsgo-projects';
    protected $primaryKey = 'project-id'; 
    protected $allowedFields = ['project-id','name', 'version', 'description', 'start-date', 'end-date', 'status', 'manager-id'];
    
    public function getAll($condition = array())
    {
        //print_r($condition);exit;
        $db = \Config\Database::connect();
        $productMapTable = 'docsgo-products-projects-mapping';
        $productTable = 'docsgo-products';
        $builder = $db->table($this->table);
        $builder->select("
                         `$this->table`.project-id,
                         `$this->table`.name,
                         `$this->table`.version,
                         `$this->table`.description,
                         `$this->table`.start-date,
                         `$this->table`.end-date,
                         `$productTable`.name as product");
        $builder->where($condition);
        $builder->join('docsgo-products-projects-mapping', 'docsgo-products-projects-mapping.project-id = docsgo-projects.project-id');
        $builder->join('docsgo-products', 'docsgo-products.product-id = docsgo-products-projects-mapping.product-id');
        $builder->orderBy("`$this->table`.start-date DESC");
        $query = $builder->get();

        $result = $query->getResult('array');
        return $result;
    }

    public function getProjects(){
        $db = \Config\Database::connect();
        $sql = "Select `project-id`, `name` from `docsgo-projects` where status = 'Active' ORDER BY `start-date` DESC ";
        $query = $db->query($sql);

        $result = $query->getResult('array');
        $data = [];
        foreach($result as $row){
            $data[$row['project-id']] = $row['name'];
        }
        
        return $data;
    }

    public function getCompletedProjects(){
        $db = \Config\Database::connect();
        $sql = "Select `project-id`, `name` from `docsgo-projects` where status = 'Completed' ORDER BY `start-date` DESC ";
        $query = $db->query($sql);

        $result = $query->getResult('array');
        $data = [];
        foreach($result as $row){
            $data[$row['project-id']] = $row['name'];
        }
        
        return $data;
    }
    
    public function getDownloadedProjectStatus($projectId, $updateDate) {
        $db = \Config\Database::connect();
        $sql = "SELECT count(*) as count from `docsgo-documents` where `project-id` = ".$projectId." AND `update-date` > '".$updateDate."'";
        $query = $db->query($sql);

        $result = $query->getResult('array');
        return $result;
    }

    public function updateGenerateDocumentPath($projectId, $link) {
        $db = \Config\Database::connect();
        $whereCondition = " WHERE `project-id` = ".$projectId." "; 
        $sql = "UPDATE `docsgo-projects` SET `download-path` = '".$link."' WHERE `project-id` = '".$projectId."'";

        $query = $db->query($sql);
        $data = $query->getResult('array');
        return $data;
    }    

    public function getProjectReviewsCount($projectId) {
        $db = \Config\Database::connect();
        $sql = "SELECT count(*) as count from `docsgo-reviews` where `project-id` = ".$projectId."";
        $query = $db->query($sql);

        $result = $query->getResult('array');
        return $result[0]['count'];
    }

    public function getProjectDocumentsCount($projectId) {
        $db = \Config\Database::connect();
        $sql = "SELECT count(*) as count from `docsgo-documents` where `project-id` = ".$projectId."";
        $query = $db->query($sql);

        $result = $query->getResult('array');
        return $result[0]['count'];
    }

    public function getProjectResourcesCount($projectId) {
        $db = \Config\Database::connect();
        $sql = "SELECT count(distinct assignee) as count from `docsgo-taskboard` where `project_id` = ".$projectId."";
        $query = $db->query($sql);

        $result = $query->getResult('array');
        return $result[0]['count'];
    }

    public function getProjectOpenReviews($projectId)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('docsgo-reviews');
        $builder->select('id, context, category');
        $builder->where('status', "Request Review");
        $builder->where('project-id', $projectId);
        $query = $builder->get();
        $data = $query->getResult('array');
        $reviews = [];
        foreach ($data as $review) {
            $reviews[] = ['context' => $review['context'], 'category' => $review['category']];
        }
        return $reviews;
    }

    //This function gets the full name
    public function getName($id){
        $db = \Config\Database::connect();
        $builder = $db->table('docsgo-team-master');
        $builder->select('name');
        $builder->where('id', $id);
        $query = $builder->get();
        $data = $query->getResult('array');
        if(count($data)){
            $user = $data[0]['name'];
        }else{
            $user = "";
        }
        return $user;
    }

    public function getProjectOpenRisks($projectId)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('docsgo-risks');
        $builder->select('risk, risk_type');
        $builder->where('status', "Open");
        $builder->where('project_id', $projectId);
        $query = $builder->get();
        $data = $query->getResult('array');
        $risks = [];
        foreach ($data as $risk) {
            $risks[] = ['risk_type'=> $risk['risk_type'], 'risk' => $risk['risk']];
        }
        return $risks;
    }

    //This function gets project name
    public function getProjectName($id){
        $db = \Config\Database::connect();
        $builder = $db->table('docsgo-projects');
        $builder->select('name');
        $builder->where('project-id', $id);
        $query = $builder->get();
        $data = $query->getResult('array');
        $project = $data[0]; 
        return $project['name'];
    }

    public function getProjectTotalHrsSpent($projectId) {
        $db = \Config\Database::connect();
        //$sql = "SELECT SEC_TO_TIME( SUM( TIME_TO_SEC( `duration` ) ) ) AS timeSum from `docsgo-timesheet` where `project-id` = ".$projectId."";
        $sql = "SELECT SUM(`total-logged-hours`) AS timeSum from `docsgo-timesheet` where `project-id` = ".$projectId."";
        $query = $db->query($sql);

        $result = $query->getResult('array');
        return $result[0]['timeSum'];
    }

    public function getProjectDeveloperWiseHrsSpent($projectId)
    {
        $db = \Config\Database::connect();
        $sql = "SELECT SUM(`total-logged-hours`) AS timeSum, team.name as author FROM `docsgo-timesheet` AS timesheet LEFT JOIN `docsgo-team-master` AS team ON timesheet.`user-id` = team.`id` 
        where `project-id` = ".$projectId." GROUP BY `user-id`";
        $query = $db->query($sql);

        $result = $query->getResult('array');


        $developerHrs = [];
        foreach ($result as $res) {
            $developerHrs[] = ['name'=> $res['author'], 'timeSpent' => $res['timeSum']];
        }
        return $developerHrs;
    }

    public function getProjectGaps($projectId)
    {
        $db      = \Config\Database::connect();

        $sql = "(SELECT GROUP_CONCAT(CONCAT_WS(',', req.requirement)  ORDER BY req.requirement SEPARATOR '<br/>' ) as requirement, type
        FROM `docsgo-requirements` req
        WHERE req.id NOT IN (
            SELECT options.requirement_id
             FROM `docsgo-traceability-options` as options
        ) AND req.`project-id`=".$projectId."
        GROUP BY req.type)";
        
        $query = $db->query($sql);
        $unmappedData = $query->getResult('array');
        $data = array();
        foreach($unmappedData as $row){
            $data[] = ['type'=> $row['type'], 'requirement' => $row['requirement']];
        }
        
        return $data;
    }

    public function getProjectData($id) {
        $db = \config\Database::connect();

        $sql = "SELECT * FROM `docsgo-projects` WHERE `project-id` = '".$id."' ";
       
        $query = $db->query($sql);
        $data = $query->getResult('array');
        return $data;
    }

    //This function gets id
    public function getProjectId($name){
        $db = \Config\Database::connect();
        $builder = $db->table('docsgo-projects');
        $builder->select('project-id');
        $builder->where('name', $name);
        $query = $builder->get();
        $data = $query->getResult('array');
        if(count($data)){
            $id = $data[0]['project-id'];
        }else{
            $id = "";
        }
        return $id;
    }
}
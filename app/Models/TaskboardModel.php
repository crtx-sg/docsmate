<?php  namespace App\Models;

use CodeIgniter\Model;

class TaskboardModel extends Model{
    protected $table = 'docsgo-taskboard';
    protected $allowedFields = ['assignee','comments','description','project_id','creator','verifier',
    'task_category','title','task_column', 'attachments'];

    public function getTasks($whereCondition = ""){
        $db      = \Config\Database::connect();
        $sql = "SELECT tasks.id, NULLIF(tasks.assignee, 0) AS assignee, tasks.comments, tasks.description, tasks.project_id, tasks.creator
                        , NULLIF(tasks.verifier, 0) AS verifier, tasks.task_category, tasks.title, tasks.task_column,tasks.attachments
                FROM `docsgo-taskboard` AS tasks
                LEFT JOIN `docsgo-team-master` AS team ON tasks.`assignee` = team.`id` 
                LEFT JOIN `docsgo-team-master` AS team2 ON tasks.`verifier` = team2.`id` 
                ".$whereCondition."
                ORDER BY IF(tasks.task_column='Under Verification', team2.name, team.name) ASC";

        $query = $db->query($sql);

        $data = $query->getResult('array');
        
        return $data;
    }

    public function getTasksCount($project_id, $user_id="ALL"){
        $userCondition = "";
        $developerCondition = "";
        $verifierCondition = "";
        if($user_id != "ALL"){
            $userCondition = " AND (tasks.assignee = ".$user_id." OR tasks.verifier = ".$user_id.") ";
            $developerCondition = " AND tasks.assignee = ".$user_id;
            $verifierCondition = " AND tasks.verifier = ".$user_id;
        }
        $db      = \Config\Database::connect();
        $data = [];

        $sql = "SELECT count(*) as series, IFNULL(team.name, 'Unassigned') as labels, team.`id` as userId
                FROM `docsgo-taskboard` tasks 
                LEFT JOIN `docsgo-team-master` AS team ON tasks.`assignee` = team.`id` 
                WHERE  project_id = ".$project_id.$developerCondition." 
                GROUP BY assignee
                ORDER BY team.name";
        $query = $db->query($sql);
        $result1 = $query->getResult('array');
        $data["developerData"] = $this->organizeDataForCharts($result1);

        $sql = "SELECT count(*) as series, IFNULL(team.name, 'Unassigned') as labels, team.`id` as userId
                FROM `docsgo-taskboard` tasks 
                LEFT JOIN `docsgo-team-master` AS team ON tasks.`verifier` = team.`id` 
                WHERE  project_id = ".$project_id.$verifierCondition." 
                GROUP BY verifier
                ORDER BY team.name";
        $query = $db->query($sql);
        $result2 = $query->getResult('array');
        $data["verificationData"] = $this->organizeDataForCharts($result2);

        $sql =  "SELECT count(*) as series, task_column as labels FROM `docsgo-taskboard`  tasks  where project_id = ".$project_id.$userCondition." group by task_column";
        $query = $db->query($sql);
        $result3 = $query->getResult('array');
        $data["columnData"] = $this->organizeDataForCharts($result3);

        $sql = "SELECT count(*) as series,task_category as labels FROM `docsgo-taskboard`  tasks  where project_id = ".$project_id.$userCondition." group by task_category";
        $query = $db->query($sql);
        $result4 = $query->getResult('array');
        $data["categoryData"] = $this->organizeDataForCharts($result4);
        
        return $data;
    }

    private function organizeDataForCharts($result){
        $data['series'] = [];
        $data['labels'] = [];
        foreach($result as $row){
            array_push($data['series'],(int)$row['series']);
            array_push($data['labels'],$row['labels']);
        }
        return $data;
    }

}
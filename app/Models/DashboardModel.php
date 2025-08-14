<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\ActionListModel;

class DashboardModel extends Model
{
    protected $db;

    public function __construct()
    {
        $this->db = db_connect();
    }

    private function getActiveProjectIds()
    {
        $query = "SELECT GROUP_CONCAT(`project-id`) AS id FROM `docsgo-projects` where status='Active'";
        $result = $this->db->query($query);
        return $result->getRow();
    }

    public function getTasks()
    {
        $activeProjectIds = $this->getActiveProjectIds();

        if ($activeProjectIds == null) {
            return null;
        }

        $activeProjectIds = isset($activeProjectIds->id)? $activeProjectIds->id: 0;
        $userId = session()->get('id');
        $query = "SELECT 
                    project_id, task_column, title 
                FROM `docsgo-taskboard` 
                WHERE project_id IN ($activeProjectIds) 
                AND task_column <> 'Complete' 
                AND (assignee = $userId OR verifier = $userId) 
                ORDER BY project_id desc";

        $result = $this->db->query($query);

        return $result->getResult();
    }

    public function getReviews()
    {
        $activeProjectIds = $this->getActiveProjectIds();

        if ($activeProjectIds == null) {
            return null;
        }

        $activeProjectIds = isset($activeProjectIds->id)? $activeProjectIds->id: 0;
        $userId = session()->get('id');
        $query = "SELECT 
                        id, `context`, `status` 
                    FROM `docsgo-reviews` 
                    WHERE `project-id` IN ($activeProjectIds) 
                    AND status <> 'Approved' 
                    AND (`assigned-to` = $userId OR FIND_IN_SET($userId,`review-by`) > 0) 
                    ORDER BY `project-id`, `updated-at` desc";

        $result = $this->db->query($query);

        return $result->getResult();
    }

    public function getDocuments()
    {
        $activeProjectIds = $this->getActiveProjectIds();

        if ($activeProjectIds == null) {
            return null;
        }

        $activeProjectIds = isset($activeProjectIds->id)? $activeProjectIds->id: 0;
        $userId = session()->get('id');
        $query = "SELECT 
                        id, `json-object`, `status`, `type`
                    FROM `docsgo-documents` 
                    WHERE `project-id` IN ($activeProjectIds) 
                    AND status <> 'Approved' 
                    AND (`author-id` = $userId OR FIND_IN_SET($userId,`reviewer-id`) > 0)
                    ORDER BY `project-id`, `update-date` desc";

        $result = $this->db->query($query);
        $data = [];
        foreach ($result->getResult('array') as $row) {
            $type = $row['type'];
            $json = json_decode($row['json-object'], true);
            $temp['title'] = $json[$type]['cp-line3'];
            $temp['id'] = $row['id'];
            $temp['status'] = $row['status'];
            array_push($data, $temp);
        }
        return $data;
    }

    public function getUnitTests()
    {
        $activeProjectIds = $this->getActiveProjectIds();

        if ($activeProjectIds == null) {
            return null;
        }

        $activeProjectIds = isset($activeProjectIds->id)? $activeProjectIds->id: 0;
        $userId = session()->get('id');
        $query = "SELECT 
                        UT.id, UT.name, projects.name As project
                    FROM `docsgo-unit-tests` as UT
                    INNER JOIN `docsgo-projects` AS projects ON UT.project_id = projects.`project-id`
                    WHERE UT.`project_id` IN ($activeProjectIds) 
                    AND UT.`author_id` = $userId 
                    ORDER BY UT.`updated_at` desc";

        $result = $this->db->query($query);

        return $result->getResult();
    }

    public function getActionItems()
    {
        $actionListModel = new ActionListModel();
        $userId = session()->get('id');
        $actionList = $actionListModel->getActions($userId);
        $data = array();
        foreach ($actionList as $item) {
            $action = json_decode($item["action"]);
            if ($action->state == "todo") {
                $temp = array();
                $temp["id"] = $item["id"];
                $temp["title"] = $action->title;
                $temp["priority"] = $action->priority;
                $temp["completion"] = $action->completion;
                array_push($data, $temp);
            }
        }
        return $data;
    }
}

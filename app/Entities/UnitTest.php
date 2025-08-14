<?php 

namespace App\Entities;

use CodeIgniter\Entity;
use App\Models\ProjectModel;
use App\Models\TeamModel;

class UnitTest extends Entity
{

    public function getProjectName(){
        $projectModel = new ProjectModel();
        $project = $projectModel->find($this->attributes["project_id"]);
        return $project["name"];
    }

    public function getAuthorName(){
        $teamModel = new TeamModel();
        $author = $teamModel->find($this->attributes["author_id"]);
        return $author["name"];
    }

}
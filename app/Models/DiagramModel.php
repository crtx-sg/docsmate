<?php  namespace App\Models;

use CodeIgniter\Model;

class DiagramModel extends Model{
    protected $table = 'docsgo-diagrams';
    protected $allowedFields = ['author_id', 'diagram_name', 'link', 'markdown', 'updated_at'];

    public function getDiagrams($whereCondition = ""){
        $db      = \Config\Database::connect();
        $sql = "SELECT diagrams.`id`, diagrams.`author_id`, team.name as author, diagrams.`diagram_name`, diagrams.`link`, diagrams.`markdown`, diagrams.`updated_at`
                FROM `docsgo-diagrams` AS diagrams
                LEFT JOIN `docsgo-team-master` AS team ON diagrams.`author_id` = team.`id` 
                ".$whereCondition."
                ORDER BY diagrams.`updated_at` DESC";

        $query = $db->query($sql);

        $data = $query->getResult('array');
        
        return $data;
    }
    
}
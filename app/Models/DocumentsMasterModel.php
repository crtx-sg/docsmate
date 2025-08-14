<?php  namespace App\Models;

use CodeIgniter\Model;

class DocumentsMasterModel extends Model{
    protected $table = 'docsgo-document-master';
    protected $allowedFields = ['name', 'category', 'version', 'description', 'ref', 'location', 'status'];

    public function getRefrences(){
        $db      = \Config\Database::connect();
        $sql = "SELECT 
                id,name,category,version,description,ref as reference, location, status 
                FROM `docsgo-document-master`";

        $query = $db->query($sql);

        $data = $query->getResult('array');
        
        return $data;
    }
    
}
<?php  namespace App\Models;

use CodeIgniter\Model;

class DocumentTemplateModel extends Model{
    protected $table = 'docsgo-document-template';
    protected $allowedFields = ['id','name', 'type', 'template-json-object'];

    public function getTypes(){
        $db      = \Config\Database::connect();
        $builder = $db->table('docsgo-document-template');
        $builder->select('name, type')->orderBy('name');
        $query = $builder->get();
        $data = $query->getResult('array');
        $types = [];
		foreach($data as $type){
			$types[$type['type']] = $type['name'];
		}
		return $types;
    }
    
}
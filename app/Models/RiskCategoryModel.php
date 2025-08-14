<?php  
namespace App\Models;

use CodeIgniter\Model;

class RiskCategoryModel extends Model{
    protected $table = 'docsgo-risk-categories';
    protected $primaryKey = 'id'; 
    protected $allowedFields = ['id','name', 'risk-methodology', 'status','created_at'];

    public function getRiskCategoriesData(){
        $db = \Config\Database::connect();
        $builder = $db->table('docsgo-risk-categories');
        $query = $builder->get();
        $data = $query->getResult('array');
        return $data;
    }

    public function getRiskCategories()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('docsgo-risk-categories');
        $builder->select('id, name,risk-methodology');
        $builder->where('status', "Active");
        //$builder->orderBy('created_at', 'desc');
        $query = $builder->get();
        $data = $query->getResult('array');
        $riskCategory = [];
        foreach ($data as $member) {
            $riskCategory[$member['id']] = $member['name'];
        }
        return $riskCategory;
    }
}
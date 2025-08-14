<?php  
namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model{
    protected $table = 'docsgo-products';
    protected $primaryKey = 'product-id'; 
    protected $allowedFields = ['product-id','name', 'display-name', 'description', 'status','created_at'];

    public function getProductsData($status){
        $db = \Config\Database::connect();
        $builder = $db->table('docsgo-products');
        $builder->where('status', $status);
        $query = $builder->get();
        $data = $query->getResult('array');
        return $data;
    }

    public function getProducts()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('docsgo-products');
        $builder->select('product-id, name');
        $builder->where('status', "Active");
        //$builder->orderBy('created_at', 'desc');
        $query = $builder->get();
        $data = $query->getResult('array');
        $product = [];
        foreach ($data as $member) {
            $product[$member['product-id']] = $member['name'];
        }
        return $product;
    }

    public function getProductId($projectId){
        $db = \Config\Database::connect();
        $builder = $db->table('docsgo-products-projects-mapping');
        $builder->select('product-id');
        $builder->where('project-id', $projectId);
        $query = $builder->get();
        $data = $query->getResult('array');
        if(count($data)){
            $user = $data[0]['product-id'];
        }else{
            $user = "";
        }
        return $user;
    }

    public function getProductProjects($productId)
    {
        $db = \Config\Database::connect();
        $sql = "SELECT a.`name`,a.`project-id`  FROM `docsgo-projects` AS a JOIN `docsgo-products-projects-mapping` AS b 
        ON a.`project-id` = b.`project-id` WHERE b.`product-id` =".$productId."";
        $query = $db->query($sql);
        $data = $query->getResult('array');
        $project = [];
        foreach ($data as $member) {
            $project[$member['project-id']] = $member['name'];
        }
        return $project;
    }

    public function getProductProjectIds($productId)
    {
        $db = \Config\Database::connect();
        $sql = "SELECT `project-id`  FROM  `docsgo-products-projects-mapping` WHERE `product-id` =".$productId."";
        $query = $db->query($sql);
        $data = $query->getResult('array');
        $project = [];
        foreach ($data as $member) {
            $project[] = $member['project-id'];
        }
        return $project;
    }

    public function getProductByProjectId($projectId)
    {
		$db = \Config\Database::connect();
        $sql = "SELECT a.`product-id`,a.`name`  FROM `docsgo-products` AS a JOIN `docsgo-products-projects-mapping` AS b 
        ON a.`product-id` = b.`product-id` WHERE b.`project-id` =".$projectId."";
        $query = $db->query($sql);
        $data = $query->getResult('array');
        //$product = [];
        foreach ($data as $member) {
            $product[$member['product-id']] = $member['name'];
        }
        return $product;
    }

    public function getProductIdByName($name){
        $db = \Config\Database::connect();
        $builder = $db->table('docsgo-products');
        $builder->select('product-id');
        $builder->where('name', $name);
        $query = $builder->get();
        $data = $query->getResult('array');
        if(count($data)){
            $id = $data[0]['product-id'];
        }else{
            $id = "";
        }
        return $id;
    }
}
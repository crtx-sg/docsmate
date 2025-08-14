<?php  
namespace App\Models;

use CodeIgniter\Model;

class ProductsProjectsMappingModel extends Model{
    protected $table = 'docsgo-products-projects-mapping';
    protected $primaryKey = 'id'; 
    protected $allowedFields = ['id','product-id','project-id'];
}
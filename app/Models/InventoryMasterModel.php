<?php  namespace App\Models;

use CodeIgniter\Model;

class InventoryMasterModel extends Model{
    protected $table = 'docsgo-inventory-master';
    protected $allowedFields = ['item','type','description','make','model','serial','entry_date','retired_date','cal_date','cal_due','location','invoice','invoice_date','vendor','status','used_by','updated_by'];

    public function bulkInsert($data){
        $db      = \Config\Database::connect();
        $builder = $db->table('docsgo-inventory-master');
        $builder->insertBatch($data);		
    }

}
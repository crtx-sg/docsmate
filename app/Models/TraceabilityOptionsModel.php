<?php  namespace App\Models;

use CodeIgniter\Model;

class TraceabilityOptionsModel extends Model{
    protected $table = 'docsgo-traceability-options';
    protected $allowedFields = ['traceability_id', 'type', 'requirement_id', 'update_date'];

}


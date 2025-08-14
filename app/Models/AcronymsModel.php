<?php  namespace App\Models;

use CodeIgniter\Model;

class AcronymsModel extends Model{
    protected $table = 'docsgo-acronyms';
    protected $allowedFields = ['acronym', 'description', 'update_date'];
}
<?php  namespace App\Models;

use CodeIgniter\Model;

class QueueModel extends Model{
    protected $table = 'docsgo-queue';
    protected $allowedFields = ['type', 'status', 'json'];
}
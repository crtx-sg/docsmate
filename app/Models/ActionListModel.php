<?php

namespace App\Models;

use CodeIgniter\Model;

class ActionListModel extends Model
{
    protected $table = 'docsgo-action-list';
    protected $allowedFields = ['id', 'owner_id', 'responsible_id', 'sharing', 'update_date', 'action', 'revision_history'];

    public function getActions($id)
    {
        $db      = \Config\Database::connect();
        $sql = "SELECT * FROM `docsgo-action-list`
		WHERE owner_id = $id OR ( FIND_IN_SET( $id ,responsible_id) > 0 AND sharing = 1)
		ORDER BY update_date desc";

        $query = $db->query($sql);
        $data = $query->getResult('array');
        return $data;
    }
}

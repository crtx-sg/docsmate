<?php

namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\I18n\Time;

class UnitTestModel extends Model
{
    protected $table = 'docsgo-unit-tests';
    protected $returnType = '\App\Entities\UnitTest';
    protected $allowedFields = ['name', 'project_id', 'author_id', 'json'];

    public function getAll($condition = array())
    {
        $db = \Config\Database::connect();
        $teamsTable = 'docsgo-team-master';
        $builder = $db->table($this->table);
        $builder->select("CONCAT('UT','-',`$this->table`.id) as utId,
                         `$this->table`.id,
                         `$this->table`.author_id,
                         `$this->table`.name,
                         `$this->table`.created_at,
                         `$this->table`.updated_at,
                         `$teamsTable`.name as author");
        $builder->where($condition);
        $builder->join('docsgo-team-master', 'docsgo-team-master.id = docsgo-unit-tests.author_id');
        $builder->orderBy("`$this->table`.updated_at DESC");
        $query = $builder->get();

        $result = $query->getResult();

        foreach ($result as $row) {
            $time = Time::parse($row->updated_at);
            $timezone = $time->getTimezoneName();

            if ($timezone != "Asia/Kolkata") {
                $created_at = $this->getIndianDateTime($row->created_at);
                $updated_at = $this->getIndianDateTime($row->updated_at);
            } else {
                $created_at = Time::parse($row->created_at);
                $updated_at = Time::parse($row->updated_at);
            }

            $row->created_at = $created_at->toDateString();
            $row->updated_at = $updated_at->humanize();
        }
        return $result;
    }

    private function getIndianDateTime($dateString)
    {
        $time = Time::parse($dateString);
        $epoch = $time->getTimestamp() + 19800;
        $indianTime = Time::createFromTimestamp($epoch, 'Asia/Kolkata');
        return $indianTime;
    }


    public function getAllForDocs($condition)
    {
        $db = \Config\Database::connect();
        $builder = $db->table($this->table);
        $builder->select("id,name,json");
        $builder->where($condition);
        $builder->orderBy("`$this->table`.updated_at DESC");
        $query = $builder->get();
        return $query->getResult('array');
    }
}

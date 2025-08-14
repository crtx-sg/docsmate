<?php  namespace App\Models;

use CodeIgniter\Model;

class MeetingModel extends Model{
    protected $table = 'docsgo-meeting-notes';
    protected $primaryKey = 'meeting-id'; 
    protected $allowedFields = ['meeting-id','title', 'notes', 'entry-date', 'created_at'];

    public function getMeetings(){
        $db      = \Config\Database::connect();
        $sql = "SELECT * FROM `docsgo-meeting-notes` ORDER BY `entry-date` desc;";

        $query = $db->query($sql);
        $data = $query->getResult('array');
        
        for($i=0; $i<count($data );$i++){
            $notes = $data[$i]['notes'];
            
            $data[$i]['notes'] =  strlen($notes) > 100 ? substr($notes,0,100)."..." : $notes;
        }
        return $data;
    }
}
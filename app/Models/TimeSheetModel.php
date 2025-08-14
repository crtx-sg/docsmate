<?php  namespace App\Models;

use CodeIgniter\Model;

class TimeSheetModel extends Model{
    protected $table = 'docsgo-timesheet';
    protected $primaryKey = 'timesheet-id'; 
    protected $allowedFields = ['timesheet-id','project-id','user-id', 'type', 'log', 'dependencies', 'entry-date', 'status', 'total-logged-hours','day-log-hours', 'log-date'];

    public function getCurrentDateTimesheets(){
        $db      = \Config\Database::connect();
        $sql = "SELECT   tms.`user-id`, team.`name` as `person`, tms.`log`, 
        tms.`status`,tms.`total-logged-hours`,pro.`name`
        FROM `docsgo-timesheet` AS tms
        LEFT JOIN `docsgo-projects` AS pro ON pro.`project-id` = tms.`project-id` 
        LEFT JOIN `docsgo-team-master` AS team ON tms.`user-id` = team.`id` WHERE STR_TO_DATE(tms.`updated_at`, '%Y-%m-%d') like STR_TO_DATE(now(), '%Y-%m-%d')
         ORDER BY tms.`updated_at` desc;";

        $query = $db->query($sql);
        $data = $query->getResult('array');

        return $data;
    }


    public function getTimesheets($whereCondition){
        $db      = \Config\Database::connect();
        $sql = "SELECT  team.`name` as `person`, tms.`log`, tms.`type`, tms.`entry-date`, tms.`dependencies`, tms.`updated_at`,
        tms.`status`,tms.`day-log-hours`, tms.`total-logged-hours`, tms.`timesheet-id`, pro.name 
        FROM `docsgo-timesheet` AS tms
        LEFT JOIN `docsgo-team-master` AS team ON tms.`user-id` = team.`id` 
        LEFT JOIN `docsgo-projects` AS pro ON pro.`project-id` = tms.`project-id`
        ".$whereCondition."
         ORDER BY tms.`entry-date` desc;";

        $query = $db->query($sql);
        $data = $query->getResult('array');

        return $data;
    }

    public function getTimesheetReportRecords($from_date, $to_date){
        $db      = \Config\Database::connect();
        $sql = "SELECT  tms.`timesheet-id`, tms.`total-logged-hours` as `tHours`, team.`name`, tms.`log`,
        tms.`status` as `status`, tms.`type` as `task`
        FROM `docsgo-timesheet` AS tms
        LEFT JOIN `docsgo-team-master` AS team ON tms.`user-id` = team.`id`
        WHERE DATE(tms.`entry-date`) BETWEEN '".$from_date."' AND '".$to_date."'
        ORDER BY team.name";

        $query = $db->query($sql);
        $data = $query->getResult('array');

        for($i=0; $i<count($data);$i++){

            $log = $data[$i]['log'];
            if($log != null && $log != ""){
                $log = json_decode($log, true);
                $msgs = "";
                if($log != null && $log != ""){
                    foreach($log as $msg){
                        if(isset($msg["logHrs"]) && $msg["logHrs"] != ""){
                            $msgs .= "[ For " .$msg["logHrs"]." Hours on ".substr($msg["timestamp"],0,10) . "]&nbsp;&nbsp;";
                        }else {
                            $msgs .= "[".substr($msg["timestamp"],0, 10) . "]&nbsp;&nbsp;";
                        }
                        $msgs .= $msg["message"];
                        $msgs .= "<br/>";
                    }
                }
                $data[$i]['log'] = $msgs;
            }
        }
    return $data;
}


public function getTimesheetExportRecords($from_date, $to_date){
    $db      = \Config\Database::connect();
    $sql = "SELECT  tms.`timesheet-id`, tms.`total-logged-hours` as `tHours`, team.`name`, tms.`log`,
    tms.`status` as `status`, tms.`type` as `task`
    FROM `docsgo-timesheet` AS tms
    LEFT JOIN `docsgo-team-master` AS team ON tms.`user-id` = team.`id`
    WHERE DATE(tms.`entry-date`) BETWEEN '".$from_date."' AND '".$to_date."'
    ORDER BY team.name";

    $query = $db->query($sql);
    $data = $query->getResult('array');

    for($i=0; $i<count($data);$i++){

        $log = $data[$i]['log'];
        if($log != null && $log != ""){
            $log = json_decode($log, true);
            $j = 0;
            $msgs = "";
            if($log != null && $log != ""){
            $len = count($log);
            foreach($log as $msg){
                //if(substr($msg["timestamp"],0,10) == $data[$i]['log-date']){
                    if ($j == $len - 1) {
                        if(isset($msg["logHrs"]) && $msg["logHrs"] != ""){
                            $msgs .= "[ For " .$msg["logHrs"]." Hours on " . substr($msg["timestamp"],0,10) . "]\t";
                        }else{
                            $msgs .= "[" . substr($msg["timestamp"],0,10) . "]\t";
                        }
                        $msgs .= $msg["message"];
                }else{
                    if(isset($msg["logHrs"]) && $msg["logHrs"] != ""){
                        $msgs .= "[ For " .$msg["logHrs"]." Hours on " . substr($msg["timestamp"],0,10) . "]\t";
                    }else{
                        $msgs .= "[" . substr($msg["timestamp"],0,10) . "]\t";
                    }
                    $msgs .= $msg["message"];
                    $msgs .= "\n";
                }
                $j++;
               // }
            }
        }
            $data[$i]['log'] = $msgs;
        }
    }
    return $data;
}

}
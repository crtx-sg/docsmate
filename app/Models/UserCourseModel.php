<?php  namespace App\Models;

use CodeIgniter\Model;

class UserCourseModel extends Model{
    protected $table = 'docsgo-user-courses';
    protected $primaryKey = 'user_course_id'; 
    protected $allowedFields = ['user_course_id','user_id', 'course_id', 'planned_date', 'completed_date', 'status'];

    public function getMappedRecords($whereCondition = ""){
        $db      = \Config\Database::connect();
        $sql = "SELECT  ucour.`user_course_id`, ucour.`planned_date`, ucour.`user_id`, team.`name` as `register`, ucour.`completed_date`,ucour.`status`, ucour.`updated_at`, cour.`k-points`, cour.`title` as `course_title` 
        FROM `docsgo-user-courses` AS ucour
        LEFT JOIN `docsgo-courses` AS cour ON cour.`course_id` = ucour.`course_id`
        INNER JOIN `docsgo-team-master` AS team ON ucour.`user_id` = team.`id` 
        ".$whereCondition."
         ORDER BY ucour.`updated_at` desc;";

        $query = $db->query($sql);
        $data = $query->getResult('array');
        return $data;
    }

    public function getUserCourseStatsCount($user_id){
        $db      = \Config\Database::connect();
        $userCondition = "";
        if($user_id != "ALL"){
            $userCondition = "(`user_id` = ".$user_id.")";
        }

        // $courseCondition = "";
        // if($course_id != "ALL"){
        //     $courseCondition = "(`course_id` = ".$course_id.")";
        // }
        
       // if($userCondition == "" && $courseCondition == ""){
        if($userCondition == ""){
            $sql = "select count(*) as count ,status from `docsgo-user-courses` group by status";
        }
        else if($userCondition != ""){
            $sql = "select count(*) as count ,status from `docsgo-user-courses` where ".$userCondition." group by status";

        }
        // else if($courseCondition != ""){
        //     $sql = "select count(*) as count ,status from `docsgo-user-courses` where ".$courseCondition." group by status";
        // }
        // else{
        //     $sql = "select count(*) as count ,status from `docsgo-user-courses` where ".$courseCondition." AND ".$userCondition." group by status";
        // }

        $query = $db->query($sql);
        $result = $query->getResult('array');

        if(count($result)){
            for($i=0; $i<count($result);$i++){
                $data[$result[$i]['status']] = $result[$i]['count'];
            }
            return $data;
        }else{
            return null;
        }
    }

    public function getUserTotalKPoints($user_id){
        if (!(session()->get('is-admin'))){
            $user_id = session()->get('id');
        }
        $db      = \Config\Database::connect();
        $whereCondition = "";
        if($user_id != "ALL"){
            $whereCondition = " AND ucour.`user_id` = ".$user_id;
        }


        $sql = "SELECT  sum(cour.`k-points`) as `points` 
        FROM `docsgo-courses` AS cour
        INNER JOIN `docsgo-user-courses` AS ucour ON ucour.`course_id` = cour.`course_id` 
        ".$whereCondition;
       
        $query = $db->query($sql);
        $result = $query->getResult('array');

        return (($result[0]['points']) == "")? 0: $result[0]['points'];
    }


    public function getUserAchievedKPoints($user_id){
        $db      = \Config\Database::connect();
        $whereCondition = "";
        if($user_id != "ALL"){
            $whereCondition = " AND ucour.`user_id` = ".$user_id;
        }

        $sql = "SELECT  sum(cour.`k-points`) as `points` 
        FROM `docsgo-courses` AS cour
        INNER JOIN `docsgo-user-courses` AS ucour ON ucour.`course_id` = cour.`course_id` WHERE ucour.`status` ='Completed'
        ".$whereCondition;
       
        $query = $db->query($sql);
        $result = $query->getResult('array');

        return (($result[0]['points']) == "")? 0: $result[0]['points'];
    }


    public function getUserCourseReportRecords($from_date, $to_date){
        $db      = \Config\Database::connect();
        $sql = "SELECT  ucour.`user_course_id`, ucour.`planned_date` as `pDate`, team.`name`, ucour.`completed_date` as `cDate`,
        ucour.`status` as `status`, cour.`k-points` as `points`, cour.`title` as `title`
        FROM `docsgo-user-courses` AS ucour
        LEFT JOIN `docsgo-courses` AS cour ON cour.`course_id` = ucour.`course_id`
        LEFT JOIN `docsgo-team-master` AS team ON ucour.`user_id` = team.`id`;";
        /*GROUP BY `name`;";*/

        $query = $db->query($sql);
        $data = $query->getResult('array');

        for($i=0; $i<count($data);$i++){

            $pDate = $data[$i]['pDate'];
            $cDate = $data[$i]['cDate'];
            $status = $data[$i]['status'];
            $courseTitle = $data[$i]['title'];
            $kpoints = $data[$i]['points'];
            $name = $data[$i]['name'];
            if($pDate != null && $pDate != ""){
                $plannedCourses = "";
                $completedCourses = "";
                $totalKpoints = 0;
                if((($pDate >= $from_date) || ($pDate <= $to_date)) && $status != "Completed"){
                    $plannedCourses .= $courseTitle." ";
                }else if((($cDate >= $from_date) || ($cDate <= $to_date)) && $status == "Completed"){
                    $completedCourses .= $courseTitle." ";
                    $totalKpoints += $kpoints;
                }
                $data[$i]['pDate'] = $plannedCourses;
                $data[$i]['cDate'] = $completedCourses;
                $data[$i]['points'] = $totalKpoints;
            }
        }

        $changeLog =[];
        foreach($data as $key =>$val){
                $changeLog[$val["name"]][] = array(
                    "name" => $val["name"],
                    "pDate" => $val["pDate"],
                    "cDate" => $val["cDate"],
                    "points" => $val["points"]
                );
        }
         

    $outputArr = array();
    foreach($changeLog as $key=>$val){
        $name=$key;
        $pdate='';
        $cdate='';
        $points=0;
        foreach($val as $key1=>$val1){
            if($val1['pDate']!=''){
                if($pdate==''){
                    $pdate = $val1['pDate'];
                }else{
                    $pdate .= ' | '.$val1['pDate'];
                }
            }
            
            if($val1['cDate']!=''){
                if($cdate==''){
                    $cdate = $val1['cDate'];
                }else{
                    $cdate .= ' | '.$val1['cDate'];
                }    
            }
            
            $points+=(int)$val1['points'];
        }
        if($pDate == ""){
            $pDate ="-";
        }
        if($cDate == ""){
            $cDate ="-";
        }
        array_push($outputArr,array(
            'name'=>$name,
            'pDate'=>$pdate,
            'cDate'=>$cdate,
            'points'=>$points)
            );
        
    }

    //echo "==================Final Arr========================="."\n";
    //print_r($outputArr);
    return $outputArr;
}

}
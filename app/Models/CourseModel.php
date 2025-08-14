<?php  namespace App\Models;

use CodeIgniter\Model;

class CourseModel extends Model{
    protected $table = 'docsgo-courses';
    protected $primaryKey = 'course_id'; 
    protected $allowedFields = ['course_id','title', 'description', 'url', 'k-points', 'is_certified', 'status', 'assessment'];
    
    public function getCourses(){
        $db = \Config\Database::connect();
        $sql = "Select `course_id`, `title` from `docsgo-courses` where status = 'Active' ORDER BY `created_at` DESC ";
        $query = $db->query($sql);

        $result = $query->getResult('array');
        $data = [];
        foreach($result as $row){
            $data[$row['course_id']] = $row['title'];
        }
        
        return $data;
    }
}
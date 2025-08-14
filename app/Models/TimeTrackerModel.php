<?php  namespace App\Models;

use CodeIgniter\Model;

class TimeTrackerModel extends Model{
    protected $table = 'docsgo-time-tracker';
    protected $allowedFields = ['user_id', 'tracker_date', 'action_list'];

    public function getTrackerList($tracker_date)
    {
        $user_id = session()->get('id');
        $trackerModel = new TimeTrackerModel();
        $trackerList = $trackerModel->where('user_id', $user_id)
            ->where('tracker_date', $tracker_date)
            ->first();
        return $trackerList;
    }
}
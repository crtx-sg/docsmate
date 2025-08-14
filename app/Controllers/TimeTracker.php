<?php

namespace App\Controllers;

use App\Models\TimeTrackerModel;
use App\Models\SettingsModel;

class TimeTracker extends BaseController
{
    private $settingsModel;
    private $timeTrackerModel;

    public function __construct()
    {
        $this->settingsModel = new SettingsModel();
        $this->timeTrackerModel = new TimeTrackerModel();
    }

    public function index()
    {
        $data = [];
        $activityCategory = $this->settingsModel->getConfig("timeTrackerCategory");
        $data['activityCategory'] = $activityCategory;
        echo view('templates/header');
        echo view('TimeTracker/index', $data);
    }

    public function create()
    {
        $props["item_id"] = $this->request->getVar('item_id');
        $props["slot_id"] = $this->request->getVar('slot_id');
        $props["user_id"] = session()->get('id');
        $props["tracker_date"] =  $this->request->getVar('tracker_date');
        $props["category"] =  $this->request->getVar('category');
        $props["reoccuring"] =  $this->request->getVar('reoccuring');
        $props["description"] =  $this->request->getVar('description');

        $item = $this->createActionItem($props);

        $response = array("success" => "true", 'slot_id' => $props["slot_id"], 'item' => $item, 'tracker_date' => $props["tracker_date"]);

        if ($props["reoccuring"] != "") {
            $reoccuring_dates = $this->getRecorringDates($props["tracker_date"], $props["reoccuring"]);
            foreach ($reoccuring_dates as $tracker_date) {
                $props["tracker_date"] = $tracker_date;
                $item = $this->createActionItem($props);
            }
        }
        echo json_encode($response);
    }

    private function createActionItem($props){
        $trackerList = $this->timeTrackerModel->getTrackerList($props["tracker_date"]);
        $action_list = $trackerList == null ? [] : json_decode($trackerList["action_list"], true);
        if ($props["item_id"] == "") {
            helper('text');
            $props["item_id"] = random_string('alnum', 3);
        }else{
            $props["reoccuring"] = $action_list[$props["slot_id"]]["reoccuring"];
        }
        

        $action_list[$props["slot_id"]] = array(
            'id' => $props["item_id"],
            'category' => $props["category"],
            'reoccuring' => $props["reoccuring"],
            'description' => $props["description"]
        );

        $data = [
            'user_id' => $props["user_id"],
            'tracker_date' => $props["tracker_date"],
            'action_list' => json_encode($action_list)
        ];

        if ($trackerList == null) {
            $this->timeTrackerModel->insert($data);
        } else {
            $this->timeTrackerModel->update($trackerList["id"], $data);
        }

        return $action_list[$props["slot_id"]];
    }

    public function delete()
    {
        $slot_id = $this->request->getVar('slot_id');
        $tracker_date = $this->request->getVar('tracker_date');
        $trackerList = $this->timeTrackerModel->getTrackerList($tracker_date);
        $action_list = json_decode($trackerList["action_list"], true);

        unset($action_list[$slot_id]);

        $trackerList["action_list"] = json_encode($action_list);
        $this->timeTrackerModel->update($trackerList["id"], $trackerList);

        $response = array("success" => "true");
        $response["slot_id"] = $slot_id;

        echo json_encode($response);
    }

    public function show()
    {
        $tracker_date = $this->request->getVar('tracker_date');
        $response = array("success" => "true");
        $trackerList = $this->timeTrackerModel->getTrackerList($tracker_date);
        if($trackerList != null){
            $trackerList = $trackerList["action_list"];
        }
        $response['trackerList'] = $trackerList;
        $response["tracker_date"] = $tracker_date;
        echo json_encode($response);
    }

    private function getRecorringDates($tracker_date, $reoccuring, $forward = true)
    {
        $reoccuring_dates = [];
        if($forward){
            for ($i = $reoccuring; $i > 0; $i--) {
                $date = new \DateTime($tracker_date);
                $date->modify("+$i day");
                array_push($reoccuring_dates, $date->format('Y-m-d'));
            }
        }else{
            for ($i = 1; $i <= $reoccuring; $i++) {
                $date = new \DateTime($tracker_date);
                $date->modify("-$i day");
                array_push($reoccuring_dates, $date->format('Y-m-d'));
            }

        }
        return $reoccuring_dates;
    }

    public function getWeeklyStats(){
        $tracker_date = $this->request->getVar('tracker_date');
        $graph_dates = $this->getRecorringDates($tracker_date, 7, false);
        $series = array();
        $categories = array();
        foreach ($graph_dates as $graph_date) {
            $trackerList = $this->timeTrackerModel->getTrackerList($graph_date);
            if($trackerList != null){
                array_push($categories, $graph_date);
                $data["categories"] = $graph_date;
                $action_list = json_decode($trackerList["action_list"], true);
                array_push($series, $this->getActivityBreakUp($action_list));
            }
        }
        $response = array('success' => true);
        $response['series'] = $this->formatDataForGraph($series);
        $response['categories'] = $categories;
        echo json_encode($response);
    }

    private function getActivityBreakUp($action_list){

        $activity_category = $this->settingsModel->getConfig("timeTrackerCategory");
        $stats = [];
        foreach($activity_category as $ac){
            $stats[$ac] = 0;
        }

        foreach($action_list as $item){
            $category = $activity_category[$item["category"]];
            $stats[$category] = $stats[$category]+1;
        }

        return $stats;
    }

    private function formatDataForGraph($series){
        $chart_stats = [];
        foreach($series as $day_stat){
            foreach($day_stat as $key=>$value){
                if(!isset($chart_stats[$key])){
                    $chart_stats[$key] = array();
                }
                array_push($chart_stats[$key], $value);
            }
            
        }
        $data = [];
        foreach($chart_stats as $key=>$value){
            $temp = array("name" => $key, "data" => $value);
            array_push($data, $temp);
        }
        return $data;
    }

}
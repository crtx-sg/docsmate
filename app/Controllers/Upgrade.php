<?php namespace App\Controllers;
use App\Models\ActionListModel;
class Upgrade extends BaseController
{
    public function upgradeActionItems(){
        $actionListModel = new ActionListModel();
		$actionItems = $actionListModel->findAll();

        foreach($actionItems as $item){
            $id = $item["id"];
            $action = json_decode($item["action"],true);
            $title = substr($action["description"],0, 60);
            $action["title"] = $title;
            $data = [
                "action" => json_encode($action),
            ];

            $actionListModel->update($id, $data);
        }                                    
        dd($actionItems);
    }
}
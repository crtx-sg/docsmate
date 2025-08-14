<?php  namespace App\Models;

use CodeIgniter\Model;

class SettingsModel extends Model{
    protected $table = 'docsgo-settings';
    protected $allowedFields = ['type', 'identifier', 'options'];

    public function getSettings($identifier = ""){
        $db      = \Config\Database::connect();
        $whereCondition = "";
        if($identifier != ""){
            $whereCondition = " WHERE settings.identifier = '".$identifier."' ";
        }

        $sql = "SELECT settings.id, settings.type, settings.identifier, settings.options
        FROM `docsgo-settings` settings
        ".$whereCondition.";";

        $query = $db->query($sql);
        $data = $query->getResult('array');
        
        return $data;
    }

    public function getConfig($identifier){
        $db      = \Config\Database::connect();
        $builder = $db->table('docsgo-settings');
        $builder->select('options');
        $builder->where('identifier', $identifier);
        $query = $builder->get();
        $result = $query->getResult('array');
        $options = $result[0]["options"];

        $data = [];
        if( $options != null){
            $options = json_decode( $options, true );
            foreach($options as $option){
                $data[$option['key']] = $option['value'];
            }
		}else{
			$data = [];
        }
        
        return $data;
    }

    public function getThirdPartyConfig($key = ""){
        $settingsModel = new SettingsModel();
		$thirdParty = $settingsModel->where("identifier","third-party")->first();
        $thirdParty = json_decode($thirdParty["options"], true);
        $thirdPartyObj = array();
        foreach($thirdParty as $details){
              $temp = array(
                "url" => $details["url"],
                "key" => $details["apiKey"],
             );
             if($details["key"] == "jenkins"){
                $temp["user"] = $details["user"];
                $temp["job"] = $details["job"];
                $temp["fileManagerUrl"] = $details["fileManagerUrl"];
            }
            if($details["key"] == "generic"){
                $temp["dailyBuildLiveUrl"] = $details["dailyBuildLiveUrl"];
            }
            $thirdPartyObj[$details["key"]] = $temp;
            // $thirdPartyObj[$details["key"]]=array($details["url"], $details["apiKey"]);
        }
        if($key != ""){
            return $thirdPartyObj[$key];
        }else{
            return $thirdPartyObj;
        }
    }

}
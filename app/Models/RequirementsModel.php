<?php  namespace App\Models;

use CodeIgniter\Model;
use PHPUnit\Runner\Exception;
use TP\Tools\TestLink;

class RequirementsModel extends Model{
    protected $table = 'docsgo-requirements';
    protected $allowedFields = ['product-id', 'type', 'requirement', 'description', 'update_date'];

    public function bulkInsert($data){
        $db      = \Config\Database::connect();
        $builder = $db->table('docsgo-requirements');
        $builder->insertBatch($data);		
    }

    function getRequirements($status = '', $productId = '') {
        $db      = \Config\Database::connect();
        if($status != ""){
            //$whereCondition = " WHERE type = '".$status."' AND `product-id` IN ('".$projects."')";
            $whereCondition = " WHERE type = '".$status."' AND `product-id` = ".$productId."";
        }
        $sql = "SELECT * from `docsgo-requirements` ". $whereCondition . " ORDER BY update_date;";
        $query = $db->query($sql);
        $data = $query->getResult('array');
        return $data;
    }

    function fetchTestLinkRequirements(){
        $projectId = 0;
        $apiKey = ""; 
        $TestLinkURL = ""; 
        
        $testLinkObj = new TestLink();
        $settingsModel = new SettingsModel();

        $serverConfig = $settingsModel->getSettings("third-party");
        $serverDetails = json_decode($serverConfig[0]['options']);
        foreach( $serverDetails as $server ){
            if($server->key == "testLink"){
                $TestLinkURL = $server->url;
                $apiKey = $server->apiKey;
            }
        }
        $TestLinkBaseURL="${TestLinkURL}/lib/api/rest/v2";

        #fetch project details
        try {
            $projectDetails = $testLinkObj->getProjects("$TestLinkBaseURL/testprojects", $apiKey );
            
            if( $projectDetails->status == "ok"){
                $projectId = $projectDetails->item[0]->id;
            } else {
                error_log("[DOCSGO] [RequirementsModel.fetchTestLinkRequirements] Unable to fetch project details");
                return false;
            }
        } catch(Exception $e){
            error_log($e);
            return false;
        }

        #fetch requirements
        try {
            $requirementsAPIURL = "$TestLinkBaseURL/testprojects/$projectId/requirements"; 
            $requirements = $testLinkObj->getRequirements($requirementsAPIURL, $apiKey);

            if( $requirements->status = "ok"){
                $data = simplexml_load_string($requirements->data);
                return $data;
            } else {
                error_log("[DOCSGO] [RequirementsModel.fetchTestLinkRequirements] Error on fetching requirements from test link server");
                return false;
            }
        } catch(Exception $e){
            error_log($e);
            return false;
        }
    }

    function getRequirementRecord($whereCondition = ''){
        $db      = \Config\Database::connect();
        if( $whereCondition != ""){
            $sql = "SELECT * from `docsgo-requirements` ". $whereCondition . "ORDER BY update_date;";
        } else {
            $sql = "SELECT * from `docsgo-requirements` ORDER BY update_date;";
        }
        $query = $db->query($sql);
        $data = $query->getResult('array');
        return $data;
    }

    public function bulkInsertion($data){
        try{
            $db      = \Config\Database::connect();
            $builder = $db->table('docsgo-requirements');
            $builder->insertBatch($data);
            return true;
        }catch(Exception $e){
            error_log($e);
            return false;
        }
    }
}
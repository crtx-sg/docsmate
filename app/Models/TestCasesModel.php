<?php  namespace App\Models;

use CodeIgniter\Model;
use stdClass;
use TP\Tools\TestLink;

class TestCasesModel extends Model{
    protected $table = 'docsgo-test-cases';
    protected $allowedFields = ['testcase', 'description', 'product-id', 'update_date'];

    public function bulkInsert($data){
        $db      = \Config\Database::connect();
        $builder = $db->table('docsgo-test-cases');
        $builder->insertBatch($data);		
    }

    public function fetchTestLinkTestCases(){
        $projectId = 0;
        $testCasePrefix = "";
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
                $testCasePrefix = $projectDetails->item[0]->prefix;
            } else {
                error_log("[DOCSGO] [TestCasesModel.fetchTestLinkTestCases] Unable to fetch project details");
                return false;
            }
        } catch(Exception $e){
            error_log($e);
            return false;
        }
        
        #fetch test cases
        try {
            $testCasesAPIURL = "$TestLinkBaseURL/testprojects/$projectId/testcases";
            $testCases = $testLinkObj->getTestCases($testCasesAPIURL, $apiKey);
            if( $testCases->status = "ok" ){
                $testCasesData = new stdClass();
                $testCasesData->testCasePrefix = $testCasePrefix;
                $testCasesData->testCasesList = $testCases->items;
                return $testCasesData;
            } else {
                error_log("[DOCSGO][TestCasesModel.fetchTestLinkTestCases][ERROR] Unable to fetch test cases list");
                return false;
            }
        } catch(Exception $e){
            error_log($e);
            return false;
        }
    }

    function getTestCaseRecord($whereCondition = ''){
        $db      = \Config\Database::connect();
        if( $whereCondition != ""){
            $sql = "SELECT * from `docsgo-test-cases` " . $whereCondition . " ORDER BY update_date;";
        } else {
            $sql = "SELECT * from `docsgo-test-cases` ORDER BY update_date;";
        }
        $query = $db->query($sql);
        $data = $query->getResult('array');
        return $data;
    }

    public function bulkInsertion($data){
        try{
            $db      = \Config\Database::connect();
            $builder = $db->table('docsgo-test-cases');
            $builder->insertBatch($data);
            return true;
        }catch(Exception $e){
            error_log($e);
            return false;
        }
    }

}
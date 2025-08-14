<?php namespace App\Controllers;

use App\Models\RequirementsModel;
use App\Models\TestCasesModel;
use App\Models\InventoryMasterModel;

class BulkInsert extends BaseController
{
	public function index()
    {   // Expected Url Format
        // /bulk-insert?fileName=CNCR.csv&tableName=requirements
        // http://localhost/bulk-insert?fileName=SubsystemRequirements.csv&tableName=requirements
        // http://localhost/bulk-insert?fileName=TestcasesCSV.csv&tableName=testcases
        // http://localhost/bulk-insert?fileName=Company_asset.csv&tableName=inventory
        $fileName = $this->request->getVar('fileName');
        $tableName = $this->request->getVar('tableName'); 
        
        $res = $this->bulkInsert($fileName, $tableName);
        $response = array();
     
        if($res){
            $response['success'] = 'true';
        }else{
            $response['success'] = 'false';
        }
        return json_encode($response);
       
    }
   
    public function bulkInsert($fileName, $tableName){		
        if($fileName != '' && $tableName != ''){
            helper('Helpers\csv_to_array');
            $data =  csvToArray($fileName);
            // var_dump($data);
            // return true;
            if($tableName == 'requirements'){
                $model = new RequirementsModel();
                $model->bulkInsert($data);
                return true;
            }else if($tableName == 'testcases'){
                $model = new TestCasesModel();
                $model->bulkInsert($data);
                return true;
            }else if($tableName == 'inventory'){
                $model = new InventoryMasterModel();
                $model->bulkInsert($data);
                return true;
            }else{
                return false;
            }
        }
		
	}
}

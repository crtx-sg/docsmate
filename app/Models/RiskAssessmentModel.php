<?php  namespace App\Models;

use CodeIgniter\Model;
use TP\Tools\SonarQube;

class RiskAssessmentModel extends Model{
    protected $table = 'docsgo-risks';
    protected $allowedFields = ['project_id', 'risk_type', 'risk', 'software_name', 'type', 'version','latest_version','initial_risk_priority_number','residual_risk_priority_number',
    'risk_description', 'component', 'initial_risk_evaluation', 'risk_analysis', 'risk_control_measures', 'vulnerability',
    'residual_risk_evaluation', 'benefit_risk_analysis','CVSS_3_1_base_risk_assessment','assessment','status', 'update_date'];


    function getRisks($status = '', $type = '') {
        $db      = \Config\Database::connect();
        $whereCondition = ""; $riskType = 'Vulnerability';
        $riskType = $type;
        if($status == "All"){
            $whereCondition = ($riskType == 'Automation') ? " WHERE component = 'vms_automation' AND risk_type = 'Vulnerability' " : " WHERE risk_type = '".$riskType."' AND component != 'vms_automation' ";
        }else{
            $whereCondition = ($riskType == 'Automation') ? " WHERE component = 'vms_automation' AND risk_type = 'Vulnerability' AND status = '".$status."' " : " WHERE risk_type = '".$riskType."' AND status = '".$status."' AND component != 'vms_automation' ";
        }
        $sql = "SELECT * from `docsgo-risks` ". $whereCondition . "ORDER BY update_date desc;";
        $query = $db->query($sql);
        $data = $query->getResult('array');
        return $data;
    }

    function getVulnerabilitiesList(){
        $db = \Config\Database::connect();

        $whereCondition = " WHERE risk_type = 'Vulnerability' "; 
        $sql = "SELECT * FROM `docsgo-risks` ". $whereCondition . "ORDER BY update_date desc;";
        $query = $db->query($sql);
        $data = $query->getResult('array');
        return $data;
    }

    function updateVulnerabilityDescription( $riskId, $description){
        $db = \Config\Database::connect();

        $whereCondition = " WHERE id = ".$riskId." "; 
        $sql = "UPDATE `docsgo-risks` SET `risk_description` = '".$description."'
        ".$whereCondition ."";

        $query = $db->query($sql);
        $data = $query->getResult('array');

        return $data; 
    }

    function getRisksForDocuments($condition = ""){
        $db      = \Config\Database::connect();
        $sql = "SELECT * from `docsgo-risks` ".$condition." ORDER BY update_date desc;";
        $query = $db->query($sql);
        $result = $query->getResult('array');
        $data = array();
        
    // $data['assessment'] = $row['assessment'];
       


        foreach($result as $row){
            $temp = array();
            $temp['CVSS_3_1_base_risk_assessment'] = $row['CVSS_3_1_base_risk_assessment'];
            $temp['component'] = $row['component'];
            $temp['risk_description'] = $row['risk_description'];
            $temp['id'] = $row['id'];
            $temp['risk_analysis'] = $row['risk_analysis'];
            $temp['project_id'] = $row['project_id'];
            $temp['risk_type'] = $row['risk_type'];
            $temp['benefit_risk_analysis'] = $row['benefit_risk_analysis'];
            $temp['initial_risk_evaluation'] = $row['initial_risk_evaluation'];
            $temp['residual_risk_evaluation'] = $row['residual_risk_evaluation'];
            switch(strtolower($temp['risk_type'])){
                case 'vulnerability':
                    if($temp['component'] != 'vms_automation')
                        $temp['risk'] = 'V- '.$row['risk'];
                    else
                        $temp['risk'] = 'AT- '.$row['risk'];
                    break;
                case 'software of unknown provenance' || 'SOUP':
                    $temp['risk'] = 'S- '.$row['risk'];
                    break;
                case 'open anomaly' || 'open-issue':
                    $temp['risk'] = 'OI- '.$row['risk'];
                    break;
                case 'scope-items':
                    $temp['risk'] = 'SI- '.$row['risk'];
                    break;
            }
            $temp['status'] = $row['status'];
            
            if($row['assessment'] == "" ){
                $temp['assessment'] = "";
            }else{
                $assessment = json_decode( $row['assessment'], true );
                if($assessment["risk-assessment"]["cvss"][0]["Score"][0]["value"] == ""){
                    //FMEA
                    $fmea = $assessment["risk-assessment"]["fmea"];
                    $content = "<ol>";
                    foreach($fmea as $section){
                        $content .= "<li>".($section["category"])." => ";
                        if($section["value"] == ""){
                            $section["value"] = "--";
                        }
                        $content .= " ".$section["value"]."</li>";
                    }
                    $content .= "</ol>";
                    $temp['assessment'] = $content;
                }else{
                    //CVSS
                    $cvss = $assessment["risk-assessment"]["cvss"][0];
                    $temp['assessment'] = "";
                    foreach($cvss as $key=>$metric){
                        if($key == "Score"){
                            $key = "CVSS 3.1 ". $key;
                        }
                        $content = "**".strtoupper($key)."** <br/>";
                        $content .= "<ol>";
                        foreach($metric as $section){
                            $content .= "<li>".$section["category"] . " => ";
                            if($section["value"] == ""){
                                $section["value"] = "--";
                            }
                            $content .= " ".$section["value"]."</li>";
                        }
                        $content .= "</ol>";
                        $temp['assessment'] .= $content .  "<br/>" ;
                    }
                    
                }
            }
           
            array_push($data, $temp);
        }

        return $data;
    }

    function formatAssesment($data){

    }


    function getSonarRecords(){
        $settingsModel = new SettingsModel();
        $sonarQubeObj = new SonarQube();

        $thirdParty = $settingsModel->getThirdPartyConfig();
        $BaseURL = $thirdParty["sonar"]["url"];
        $authentication_token = $thirdParty["sonar"]["key"];
        
        $vulnerabilities = [];
        if( $BaseURL){
            try {
                $vulnerabilitiesAPIURL = "$BaseURL/api/issues/search?types=VULNERABILITY&statuses=OPEN";
                
                $vulnerabilities = $sonarQubeObj->getVulnerabilities($vulnerabilitiesAPIURL, "$authentication_token:");
                return $vulnerabilities;
            } catch(Exception $e){
                error_log($e);
                return false;
            }
        } else {
            return false;
        }        
    }

    public function bulkInsert($data){
        try{
            $db      = \Config\Database::connect();
            $builder = $db->table('docsgo-risks');
            $builder->insertBatch($data);
            return true;
        }catch(Exception $e){
            error_log($e);
            return false;
        }
    }
}
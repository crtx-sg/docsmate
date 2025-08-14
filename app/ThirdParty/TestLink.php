<?php
namespace TP\Tools;

use stdClass;

#
# @Class TestLink
# 
# This Class can be used for the data extraction from the test link server
#

class TestLink
{
    /**
     * This method is used to fetch project details
     * @param {String} URL - contains the TestLink API URL
     * @param {String} APIKey - contains the API key
     * @return {Object} project details
     */
    function getProjects($URL, $APIKey){
        $options = new stdClass();
        $options->apiKey = $APIKey; 
        $response = $this->callAPI($URL, $options);
        
        return $response;
    }


    /**
     * This method is used to make a http/https call
     * @param {String} URL - contains the URL
     * @param {Object} options - contains the http/https options
     * @return {Object} API response 
     */
    private function callAPI($URL, $options){
        try {
            $ch = curl_init();  
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, $URL); 
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'APIKEY: ' . $options->apiKey
            ));
            $result = curl_exec($ch); 
            curl_close($ch);
            
            return json_decode($result);
        } catch(Exception $e){
            $res = new stdClass();
            $res->status = "failed";
            error_log($e);
            return $res;
        }
    }

    /**
     * This method is used to fetch test case details
     * @param {String} URL - contains the TestLink API URL
     * @param {String} APIKey - contains the API key
     * @return {Object} test cases
     */
    function getTestCases($URL, $APIKey){
        $options = new stdClass();
        $options->apiKey = $APIKey; 
        $response = $this->callAPI($URL, $options);
        
        return $response;
    }

    /**
     * This method is used to fetch test case details
     * @param {String} URL - contains the TestLink API URL
     * @param {String} APIKey - contains the API key
     * @return {Object} requirements
     */
    function getRequirements($URL, $APIKey){
        $options = new stdClass();
        $options->apiKey = $APIKey; 
        $response = $this->callAPI($URL, $options);
        
        return $response;
    }

}
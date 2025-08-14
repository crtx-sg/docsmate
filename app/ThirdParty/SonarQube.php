<?php
namespace TP\Tools;

use stdClass;

#
# @Class SonarQube
# 
# This Class can be used for the data extraction from the sonar qube server
#

class SonarQube
{
    /**
     * This method is used to make a http/https call
     * @param {String} URL - contains the URL
     * @param {Object} options - contains the request options
     * @return {Object} API response 
     */
    function getVulnerabilities($URL, $authentication_token){
        try {
            $vulnerabilities = [];
            
            helper('Helpers\utils');
            $data = curlGETRequest($URL,  $authentication_token, false);
            
            if( $data->total ){
                $count = $data->total;  
                $pageCount = floor($count/100);
                if( $pageCount == 0 ){
                    $pageCount = 1;
                }        
                if( $count != 0 && $pageCount > 0 ){
                    for( $i = 1; $i <= $pageCount; $i++ ){
                        $pageIndex = $i;
                        $tmp = curlGETRequest("$URL&pageIndex=$pageIndex",  $authentication_token, false);
                        $vulnerabilities = array_merge( $vulnerabilities, $tmp->issues);
                    }
                }
            }
            return $vulnerabilities;
        } catch(Exception $e){
            error_log($e);
            return false;
        }
    }
}
<?php

function csvToArray($filename){
    if(!file_exists($filename) || !is_readable($filename)) return false;
    $header = null;
    $data = array();

    $handle = fopen($filename, "r");
    while (($row = fgetcsv($handle)) !== FALSE) {
        // echo $row[2];
        // echo strlen($row[1]);
        if(!strlen($row[0])){
            echo "Empty Record Found ".$row[0];
        }else{
            if(!$header) $header = $row;
            else $data[] = array_combine($header, $row);
        }
       
        
    }
   
    return $data;
}

?>
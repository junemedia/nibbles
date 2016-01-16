<?php

// Set the upload size limitation to 64M
ini_set('post_max_size', '2048M');
ini_set('upload_max_filesize', '2048M');
ini_set('memory_limit', '2048M');
ini_set('max_input_time', '300');
ini_set('max_execution_time', '300');

define('MD5_EXPORT_FILE', false);
define('MD5_EXPORT_DISPLAY', true);
define('MD5_EXPORT_MAIL', true);
define('PERPAGE', 10000);

define('FILE_EXPORT', dirname(__FILE__) . '/download/export.csv');

//date_default_timezone_set("Asia/Shanghai");
date_default_timezone_set("America/Chicago");

function formatBytes($size, $precision = 2) { 

    $base = log($size) / log(1024);
    $suffixes = array('', 'k', 'M', 'G', 'T');   

    return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];

}

function tryMail($to, $subject, $message, $headers, $times = 3){
    for($i=0; $i<$times; $i++){
        if(mail($to, $subject, $message, $headers)){
            // Sending mail success, exit
           break; 
        }else{
            $body = $to."\n------------------\n".$subject."\n------------------\n".$message."\n------------------\n".$headers;
            mail('leonz@junemedia.com', 'Mail Error', $body);
        }
    }
}
?>
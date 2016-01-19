<?php


// Set the upload size limitation to 64M
ini_set('post_max_size', '64M');
ini_set('upload_max_filesize', '64M');
ini_set('memory_limit', '1024M');
ini_set('max_input_time', '300');
ini_set('max_execution_time', '300');

define('MD5_EXPORT_FILE', true);
define('MD5_EXPORT_COMMAND_DISPLAY', true);
define('MD5_EXPORT_MAIL', false);

define("PERPAGE_BYTES", 33 * 10000 * 5); // 33 * $i.  We won't find the split issue
//define("PERPAGE_BYTES", 80); // 33 * $i.  We won't find the split issue 
define('PERPAGE_ROWS', 10000); // How many rows to look up in one query
//define('LINE_ENDING', "\r\n");  //End of every line. Sometimes they have \n only
define('LINE_ENDING', "\n");

$_SERVER["SERVER_ADDR"] = "127.0.0.1"; 


//print_r($_FILES);exit;

// We will import the DB connections now
require_once(dirname(__FILE__) . '/../../../config.php');
mysql_select_db('arcamax');



$filesource =  dirname(__FILE__) . "/source/source.txt";
$totalLength = filesize($filesource);

function readSource($filename, $start, $length){
    $handlePlain = fopen($filename, "r");
    
    // Put the pointer at the right position
    fseek($handlePlain,$start);
    $extendLenghth = 0;
    if($start != 0 && (fgetc($handlePlain) != "\r") && (fgetc($handlePlain) != "\n")){
        //We skip the first row
        for($i=1; $i<=32; $i++){
              fseek($handlePlain, ($start - $i));
              if(fgetc($handlePlain) == "\n"){
                  // We found the last row
                  $backstart = fseek($handlePlain, ($start - $i));
                  //echo '==>Read Back [' . $i . "] charactors ==> Start charator is [" . fgetc($handlePlain) . "]\r\n";
                  $extendLenghth = $i;
                  break;
              }
        }
    }

    
    $contentsPlain = fread($handlePlain, $length + $extendLenghth);
    $contentsPlain = str_replace('"','',$contentsPlain);
	$contentsPlain = str_replace("'",'',$contentsPlain);
    $contentsPlain = str_replace("\r",'',$contentsPlain);
    $email = explode(LINE_ENDING, $contentsPlain);
    foreach($email as $key=>$value){
        $value = trim(strtolower($value));
        if($value != "")$plainTextFile[$value] = $value;
        //echo $plainTextFile[$value] . "\n\r";
    }
    
    
    $return['content'] = $plainTextFile;
    $return['pos'] = ftell($handlePlain);
    
    $char = fgetc($handlePlain);
    //echo "The Last pointer Charactor [" . $char . "]\n\r";
    
    if ($char == "\r") echo "==>It is [\\r]\n";
    if ($char == "\n") echo "==>It is [\\n]\n";
    if ($char == "\n\r") echo "==>It is [\\n\\r]\n";
    
    fseek($handlePlain,($length+1));
    $next = fgetc($handlePlain);
    //echo "==>Next is [" . $next . "]\n\r";
    

    
    fclose($handlePlain);
    //print_r($return['content']);
    return $return;     
}

$total_open_file_times = ceil($totalLength/PERPAGE_BYTES);

for($i = 0; $i<$total_open_file_times; $i++){
    //if($i == 10)break;  // For test only 
    $readLength = PERPAGE_BYTES;
    if($i == ($total_open_file_times - 1)){
        // It is the last page
        $readLength = $totalLength - ($i * PERPAGE_BYTES);
    }
    $emailContent = readSource($filesource,(PERPAGE_BYTES*$i), $readLength);
    $plainTextFile = $emailContent['content'];

    echo '====>We read from bytes [' . PERPAGE_BYTES*$i . "] to [" . (PERPAGE_BYTES * $i + $readLength) .  "], Total is [$totalLength] \r\n"; 
    SaveToFile($plainTextFile);   
}

echo "Done! It is [" . date("Y-m-d H:m:s") . "]";

function SaveToFile($plainTextFile){
    $emailSame = array();
    $mailContent = "";
    $perPage = PERPAGE_ROWS;
    //echo "<div id='output' style='width: 1024px; height:800px; overflow:scroll;'>";
    //echo "<pre>";

    if(MD5_EXPORT_FILE){
        // We will dump into a file
        $filename = dirname(__FILE__) . '/download/export.csv';
        // Let's make sure the file exists and is writable first.
        if (is_writable($filename)) {
            if (!$handle = fopen($filename, 'a')) {
                 echo "Cannot open file ($filename)";
                 exit;
            }
        // We will do 100 per query
        
        $total = count($plainTextFile);
        $pages = ceil($total/$perPage);
        //echo "<pre>";
        for($i = 0; $i < $pages; $i++){
            //if($i == 3)exit;
            $msc=microtime(true);
            $start = $i * $perPage;
            if($start == 1) $start = 0;
            $temp_array = array_slice($plainTextFile, $start, $perPage, true);
            //echo "$temp_array = array_slice($this->_data, $start, 100)";
            //print_r($temp_array);
            $query = implode($temp_array, "','");
            $sql = "SELECT email FROM `campaignerContacts` where email in ('$query')";
            //echo $sql . "<br>";
            $result = mysql_query($sql);
            if(mysql_num_rows($result) > 0){
                while($row = mysql_fetch_object($result)){
                    $emailSame[] = $row->email;
                    echo $row->email . "\n";
                    
                    $content = $row->email . "\n";
                    $mailContent .= $content;
                    
                    // Write $somecontent to our opened file.
                    if (fwrite($handle, $content) === FALSE) {
                        echo "Cannot write to file ($filename)";
                        exit;
                    } 
                }          
            }
            $msc=microtime(true)-$msc;
            echo "==>[$start] - " .  round($msc, 2). " seconds --------------------------------------------------------------------\n"; // in seconds
            //ob_flush();
            //flush();
        }   
        //$content = implode($emailSame, "\n\r");
            echo "Success, wrote [" . mysql_num_rows($result) . "] rows of email address to file ($filename)\n\r";
            fclose($handle);
        } else {
            echo "The file $filename is not writable";
        }    
    }
}

    

if(MD5_EXPORT_MAIL){
    $random_hash = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 25);
    $attachment = chunk_split(base64_encode($mailContent));
    $headers = "From: admin@myfree.com\r\nReply-To: leonz@junemedia.com";
    $headers .= "\r\nContent-Type: multipart/mixed; boundary=\"PHP-mixed-".$random_hash."\"";

    $email_output = "
    --PHP-mixed-$random_hash;
    Content-Type: multipart/alternative; boundary='PHP-alt-$random_hash'
    --PHP-alt-$random_hash
    Content-Type: text/plain; charset='iso-8859-1'
    Content-Transfer-Encoding: 7bit

    --PHP-mixed-$random_hash
    Content-Type: text/csv; name=email_dump.csv
    Content-Transfer-Encoding: base64
    Content-Disposition: attachment

    $attachment
    --PHP-mixed-$random_hash--";


    mail("leonz@junemedia.com", "Mail Dump", $email_output, $headers);
}


if(!MD5_EXPORT_COMMAND_DISPLAY){
    ?>
    <script language="javascript">
    /*
    window.setInterval(function() {
      var elem = document.getElementById('output');
      elem.scrollTop = elem.scrollHeight;
    }, 5000);
    */
    </script>    
    <?php
}

?>


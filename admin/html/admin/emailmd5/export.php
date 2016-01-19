<?php


ini_set('post_max_size', '64M');
ini_set('upload_max_filesize', '64M');
ini_set('memory_limit', '1024M');
ini_set('max_input_time', '300');
ini_set('max_execution_time', '300');

define('MD5_EXPORT_FILE', false);
define('MD5_EXPORT_DISPLAY', true);
define('MD5_EXPORT_MAIL', true);
define('PERPAGE', 10000);

//print_r($_FILES);exit;

// We will import the DB connections now
require_once(dirname(__FILE__) . '/../../../config.php');
mysql_select_db('arcamax');


// Let's do a switch first:
if($_FILES['email']['tmp_name'] != ""){
    // This is a plain text email file
    $datatype = 'email';
    
}else if($_FILES['emailHash']['tmp_name'] != ""){
   // This is a md5 hash email file
   $datatype = 'emailHash';
     
}else if($_FILES['emailDomain']['tmp_name'] != ""){
    // This is a domain file
    $datatype = 'emailDomain';
}else{
    exit("No Upload file found");
}


interface Export_Operator{
    function prepare(); // Prepare data array
    function search();  // Search data in the DB
}

class dataOprator{
    protected $_data;
    protected $_result;
    protected $_datatype;
    protected $_filetype;
    public function export(){
        foreach($this->_result as $key=>$value){
            echo "$value\n";
        }        
    }
    
    public function display(){

        echo "</pre><p>Done! There are [" . count($this->_result) . "] rows</p>";
        echo "</div>";
    }
    
    public function header(){
        if(MD5_EXPORT_DISPLAY){
            ?>
            <script language="javascript">
            window.setInterval(function() {
              var elem = document.getElementById('output');
              elem.scrollTop = elem.scrollHeight;
            }, 500);
            </script>
            <?php
            echo "<div id='output' style='width: 1024px; height:800px; overflow:scroll;'>";
            echo "<pre>";            
        }
        
        if(MD5_EXPORT_FILE){
            echo "<pre>";
            echo "Email Same:\r\n"; 
            //print_r($this->_result);
            //exit;


            //Header download the cvs file
            header("Content-Type: text/csv");   
            header("Content-Disposition: attachment; filename=test.csv");   
            header('Cache-Control:must-revalidate,post-check=0,pre-check=0');   
            header('Expires:0');   
            header('Pragma:public');   
            echo "Email\n";            
        }
        
    }
    
    public function __construct($filetype){
        $this->_filetype = $filetype;
    }
}




class email extends dataOprator implements Export_Operator{
    public function prepare(){
        $file = new fileOperator();
        $this->_data = $file->getDateFromFile('email',$this->_filetype);
    }
    
    public function search(){
        //$query = $this->_data;
        // We will do 100 per query
        $perPage = PERPAGE;
        $total = count($this->_data);
        $pages = ceil($total/$perPage);
        if(MD5_EXPORT_DISPLAY){echo "<pre>";}
        for($i = 0; $i < $pages; $i++){
            //if($i == 10)break;
            $msc=microtime(true);
            $start = $i * $perPage;
            if($start == 1) $start = 0;
            $temp_array = array_slice($this->_data, $start, $perPage, true);
            //echo "$temp_array = array_slice($this->_data, $start, 100)";
            //print_r($temp_array);
            $query = implode($temp_array, "','");
            $sql = "SELECT email FROM `campaignerContacts` where email in ('$query')";
            //echo $sql . "<br>";
            //echo mysql_error();
            $result = mysql_query($sql);
            if(mysql_num_rows($result) > 0){
                while($row = mysql_fetch_object($result)){
                    $emailSame[] = $row->email;
                    
                    if(MD5_EXPORT_DISPLAY){
                        echo $row->email . "\n";
                        ob_flush();
                        flush();
                    }
                     
                }          
            }
            $msc=microtime(true)-$msc;
            //echo "[] - [$start+$perPage]" .  round($msc, 2).' seconds'; // in seconds
        }

        $this->_result = $emailSame;
        return $emailSame;
    }   
}

class emailHash extends dataOprator implements Export_Operator{
    public function prepare(){
        $file = new fileOperator();
        $this->_data = $file->getDateFromFile('emailHash',$this->_filetype);        
    }
    public function search(){
        //$query = $this->_data;
        $emailSame = array();
        
        // We will do 100 per query
        $perPage = PERPAGE;
        $total = count($this->_data);
        $pages = ceil($total/$perPage);
        if(MD5_EXPORT_DISPLAY){echo "<pre>";}
        for($i = 0; $i < $pages; $i++){
            //if($i == 10)break;
            $msc=microtime(true);
            $start = $i * $perPage;
            if($start == 1) $start = 0;
            $temp_array = array_slice($this->_data, $start, $perPage, true);
            //echo "$temp_array = array_slice($this->_data, $start, 100)";
            //print_r($temp_array);
            $query = implode($temp_array, "','");
            $sql = "SELECT email FROM `campaignerContacts` where emailHash in ('$query')";
            //echo $sql . "<br>";
            $result = mysql_query($sql);
            if(mysql_num_rows($result) > 0){
                while($row = mysql_fetch_object($result)){
                    $emailSame[] = $row->email;
                    
                    if(MD5_EXPORT_DISPLAY){
                        echo $row->email . "\n";
                        ob_flush();
                        flush();
                    }
                     
                }          
            }
            $msc=microtime(true)-$msc;
            //echo "[] - [$start+$perPage]" .  round($msc, 2).' seconds'; // in seconds
        }

        $this->_result = $emailSame;
        
        //print_r($this->_result);
        return $emailSame;
    }

}

//class emailDomain implements Export_Operator{}





class fileOperator{
    
    
    public function getDateFromFile($datatype, $fileType){
        if($fileType == 'txt'){
             $data = $this->_getFromTXT($datatype);
        }
        
        return $data;
    }
    
    private function _getFromTXT($datatype){
        // Get the email upload file
        $emailPlain = $_FILES[$datatype]['tmp_name'];
        $handlePlain = fopen($emailPlain, "r");
        $contentsPlain = fread($handlePlain, filesize($emailPlain));
        
        fclose($handlePlain);
        
        // change to lower case
        
        $contentsPlain = strtolower($contentsPlain);
        $replaceArray = array("'", "\"");
        $contentsPlain = str_replace($replaceArray, "", $contentsPlain);

        $email = explode("\r\n", $contentsPlain);
        foreach($email as $key=>$value){
            $value = trim(strtolower($value));
            if($value != "")$plainTextFile[] = $value;
        }
        return $plainTextFile;        
    }
}










$fo = new $datatype('txt');
$fo->prepare();
$fo->header();
$fo->search();
$fo->display();


?>
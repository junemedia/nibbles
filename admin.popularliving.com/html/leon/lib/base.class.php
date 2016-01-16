<?php
// include the functions.php
require_once("functions.php");

/**
* The client model shared by all the campaign client classes
* @author Leon Zhao
*/
class CampaignClientModel{
    protected $_client = false;
    protected $_authorization = array("Username"=>'api@junemedia.dom',"Password"=>'zhijiage209H@0');
    protected $_responseStacks = array();
    public function throwErrorResponse(){
        $response = $this->_client->__getLastResponse();
        if(SOAP_RESPONSE_TRACK)$this->_responseStacks[] = $response;        
        $errorFlag = "<ErrorFlag>true</ErrorFlag>";
        if(strpos($response, $errorFlag) !== false){
            // We found the error

            $headers = 'From: leonz@junemedia.com' . "\r\n" . 'Reply-To: leonz@junemedia.com';            
            // Sent the error notification to
            $mailList = 'leonz@junemedia.com';
            @mail($mailList, 'Development Error Response', $response, $headers);
            
            // we will print it as well
            echo "\n\r-------------------------------------------         Error Found         ----------------------------------------------------\n\r";
            echo $response;
            echo "\n\r===========================================         Error Found End     ====================================================\n\r";
            return true;
        }else{
            return false;
        }
    }
    
    /**
    * @uses the response array
    * @return array $response[]
    * 
    */
    public function getResponseStacks(){
        return $this->_responseStacks;
    }

    /**
    * @uses get Functions List
    * @return array $functionList[]
    */
    public function getFunctionList(){
        return $this->_client->__getFunctions();
    }
    
    public function destroy(){
        unset($this->_client);
        //$this->__destruct();
    }
}


echo "-->Initialize the Base class and function lists ... Yes\n";
?>

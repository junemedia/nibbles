<?php
// include the functions.php
require_once("functions.php");

/**
* The client model shared by all the campaign client classes
* @author Leon Zhao
*/
class CampaignClientModel{
    protected $_client = false;
    protected $_authorization = array("Username"=>'api@junemedia.dom',"Password"=>'v$k}^4]zJ8!!');
    protected $_responseStacks = array();
    public function throwErrorResponse($SoapFault = false){
        $response = $this->_client->__getLastResponse();
        $erro = false;
        if (is_soap_fault($SoapFault)) {
            $erro = "SOAP Fault: (faultcode: {$SoapFault->faultcode}, faultstring: {$SoapFault->faultstring})";
        }
        if(SOAP_RESPONSE_TRACK)$this->_responseStacks[] = $response;        
        $errorFlag = "<ErrorFlag>true</ErrorFlag>";
        $faultFlag = "<faultcode>";
        if(strpos($response, $errorFlag) !== false || strpos($response, $faultFlag) !== false || $erro){
            // We found the error

            $headers = 'From: leonz@junemedia.com' . "\r\n" . 'Reply-To: leonz@junemedia.com';            
            // Sent the error notification to
            $mailList = 'leonz@junemedia.com';
            @mail($mailList, 'Development Error Response', $response, $headers);
            
            // we will print it as well
            echo "\n\r-------------------------------------------         Error Found         ----------------------------------------------------\n\r";
            echo $response;
            if($erro){echo "$erro";}
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
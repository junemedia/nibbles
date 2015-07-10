<?php

require_once('base.class.php');

class Content extends CampaignClientModel{
    public function __construct(){
        echo "-->Connecting to Campaigner ... ";
        $this->_client = new SoapClient('https://ws.campaigner.com/2013/01/contentmanagement.asmx?WSDL',  array('exceptions' => false,
                           'compression'=> SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,'soap_version'=> 'SOAP_1_1','trace' => true,'connection_timeout' => 300));
        echo "Success\n";
    }
    
    /**
    * Get Campaign Summary Result
    * 
    * @param array $campaignFilter
    * @param string $fromDate
    * @param string $toDate
    */
    public function deleteImageById($imageId){
        echo '-->Deleting Image - ['.$imageId.'] ... ';
        $response = $this->_client->DeleteMediaFiles(
                                Array(
                                    'authentication' => $this->_authorization,
                                    'mediaFileIds'=>array($imageId)
                                ));
        
        $errorFlag = $this->throwErrorResponse();
        if($errorFlag){
            echo "Failed\n";
            return false;
        }else{
            echo "Done\n";
            return $response; 
        }
    }
}
?>
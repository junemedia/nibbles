<?php

require_once('base.class.php');

class Campaign extends CampaignClientModel{
    public function __construct(){
        $this->_client = new SoapClient('https://ws.campaigner.com/2013/01/campaignmanagement.asmx?WSDL',  array('exceptions' => false,
                           'compression'=> SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,'soap_version'=> 'SOAP_1_1','trace' => true,'connection_timeout' => 300));
    }
    
    /**
    * Get Campaign Summary Result
    * 
    * @param array $campaignFilter
    * @param string $fromDate
    * @param string $toDate
    */
    private function _CampaignGetCampaignRunsSummaryReport($campaignFilter, $fromDate, $toDate){
        $response = $this->_client->GetCampaignRunsSummaryReport(
                                Array(
                                    'authentication' => $this->_authorization,
                                    'campaignFilter' => $campaignFilter,
                                    'groupByDomain'=>false,
                                    'dateTimeFilter'=>array('FromDate'=>$fromDate,'ToDate'=>$toDate)
                                ));
        $errorFlag = $this->throwErrorResponse();
        if($errorFlag){
            return false;
        }else{
            return $response; 
        }
    }
    
    public function getCampaignResult($campaignFilter, $fromDate, $toDate){
        $response = $this->_CampaignGetCampaignRunsSummaryReport($campaignFilter, $fromDate, $toDate);
        return $response;
    }
}
?>
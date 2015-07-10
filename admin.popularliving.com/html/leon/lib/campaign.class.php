<?php

require_once('base.class.php');

class Campaign extends CampaignClientModel{
    public function __construct(){
        echo "-->Connecting to Campaigner ... ";
        $this->_client = new SoapClient('https://ws.campaigner.com/2013/01/campaignmanagement.asmx?WSDL',  array('exceptions' => false,
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
    private function _CampaignGetCampaignRunsSummaryReport($campaignFilter, $fromDate, $toDate){
        
        echo "-->Preparing the Report ... ";
        $response = $this->_client->GetCampaignRunsSummaryReport(
                                Array(
                                    'authentication' => $this->_authorization,
                                    'campaignFilter' => $campaignFilter,
                                    'groupByDomain'=>false,
                                    'dateTimeFilter'=>array('FromDate'=>$fromDate,'ToDate'=>$toDate)
                                ));
        echo "Done\n";
        //echo "\tRunReportTicketId: " . $response->RunReportResult->ReportTicketId . "\n";
        //echo "\tRunReportRows: " . $response->RunReportResult->RowCount . "\n";
        $errorFlag = $this->throwErrorResponse($response);
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
    
    public function getCampaignEmailAddressInfo(){
        $response = $this->_client->ListFromEmails(
                                Array(
                                    'authentication' => $this->_authorization,
                                    'xmlContactQuery' => $xmlQuery
                                ));
        echo "Done\n";
        $errorFlag = $this->throwErrorResponse();
        if($errorFlag){
            return false;
        }else{
            return $response; 
        }
    }
}
?>
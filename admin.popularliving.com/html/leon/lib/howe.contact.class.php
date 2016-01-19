<?php
require_once('contact.class.php');

class HoweContact extends Contact{
    public function __construct(){
        $this->_connectSoap();           
    }
    
    protected function _connectSoap(){
        echo "-->Connecting to Campaigner ...";
        $this->_client = new SoapClient(
                                        'https://ws.campaigner.com/2013/01/contactmanagement.asmx?WSDL', 
                                        array(
                                            'exceptions' => false,
                                            'compression'=> SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,
                                            'soap_version'=> 'SOAP_1_1',
                                            'trace' => true,
                                            'connection_timeout' => 600
                                        )
                                    );
        echo " Success\n";        
    }

    // We will prepare the data first using RunReport
    /**
    * @example     $xmlQuery = '<contactssearchcriteria>
    *                                               <version major="2" minor="0" build="0" revision="0" />
                                                        <accountid>439960</accountid>
                                                        <set>Partial</set>
                                                        <evaluatedefault>True</evaluatedefault>
                                                        <group>
                                                            <filter>
                                                                <filtertype>SearchAttributeValue</filtertype>
                                                                <contactattributeid>3844843</contactattributeid>
                                                                <action>
                                                                    <type>Boolean</type>
                                                                    <operator>EqualTo</operator>
                                                                    <value>1</value>
                                                                </action>
                                                            </filter>
                                                        </group>
                                                    </contactssearchcriteria>';
    * 
    * @param mixed $xmlQuery
    */
    protected function _CampaignRunReport($xmlQuery){
        echo "-->Preparing the Report ... ";
        $response = $this->_client->RunReport(
                                Array(
                                    'authentication' => $this->_authorization,
                                    'xmlContactQuery' => $xmlQuery
                                ));
        echo "Done\n";
        echo "\tRunReportTicketId: " . $response->RunReportResult->ReportTicketId . "\n";
        echo "\tRunReportRows: " . $response->RunReportResult->RowCount . "\n";
        $errorFlag = $this->throwErrorResponse();
        if($errorFlag){
            return false;
        }else{
            //echo $response->RunReportResult->RowCount;exit;
            return $response; 
        }
           
    }    

    /**
    * @uses we will download the report from the preparation of RunReport
    * @param string $reportTicketId
    * @param int $from
    * @param int $to
    * @param string $reportTypes
    * @return string
    * Report Types
    * rpt_Detailed_Contact_Results_by_Campaign (50,000) 
    * rpt_Summary_Contact_Results_by_Campaign (25,000) 
    * rpt_Summary_Campaign_Results (10,000) 
    * rpt_Summary_Campaign_Results_by_Domain (10,000) 
    * rpt_Contact_Attributes (100,000) 
    * rpt_Contact_Details (25,000) 
    * rpt_Contact_Group_Membership (150,000) 
    * rpt_Groups (1,000) 
    * rpt_Tracked_Links (25,000)
    */
    protected function _CampaignDownloadReport($reportTicketId, $from, $to, $reportTypes){
        if(!$this->_client){$this->_connectSoap();}   
		
		echo 'TicketId: '.$reportTicketId."\r\n";
		echo 'From: '.$from."\r\n";
		echo 'To: '.$to."\r\n";
		echo 'reportTypes: '.$reportTypes."\r\n";
        $response = $this->_client->DownloadReport(
                                Array(
                                    'authentication' => $this->_authorization,
                                    'reportTicketId'=> $reportTicketId,
                                    'fromRow' =>$from,
                                    'toRow' => $to,
                                    'reportType' => $reportTypes
                                ));
		echo $response;
        $errorFlag = $this->throwErrorResponse();
        unset($response);
        if($errorFlag){
            return false;
        }else{
            // We will refine the return result with the SimpleXMLObjectArray
            $xml = new SimpleXMLElement($this->_client->__getLastResponse());
			
            $xml->registerXPathNamespace('c', 'https://ws.campaigner.com/2013/01');
            $result = $xml->xpath('//c:DownloadReportResult');
            unset($xml);
            return $result[0]; 
        }     
    }
    
    public function getGeneralReport($xmlQuery,$printInfo = false){
        if($printInfo)echo "-->Preparing the Report ... ";
        $response = $this->_client->RunReport(
                                Array(
                                    'authentication' => $this->_authorization,
                                    'xmlContactQuery' => $xmlQuery
                                ));
        if($printInfo){
            echo "Done\n";
            echo "\tRunReportTicketId: " . $response->RunReportResult->ReportTicketId . "\n";
            echo "\tRunReportRows: " . $response->RunReportResult->RowCount . "\n";
        }
        $errorFlag = $this->throwErrorResponse();
        if($errorFlag){
            return false;
        }else{
            //echo $response->RunReportResult->RowCount;exit;
            return $response; 
        }
    }
    

 /**
 * get Report
 * 
 * @param string $xml
 * @param int $rowFrom
 * @param int $rowTo
 * @param string $reportType
 * @uses Report Types
    * rpt_Detailed_Contact_Results_by_Campaign (50,000) 
    * rpt_Summary_Contact_Results_by_Campaign (25,000) 
    * rpt_Summary_Campaign_Results (10,000) 
    * rpt_Summary_Campaign_Results_by_Domain (10,000) 
    * rpt_Contact_Attributes (100,000) 
    * rpt_Contact_Details (25,000) 
    * rpt_Contact_Group_Membership (150,000) 
    * rpt_Groups (1,000) 
    * rpt_Tracked_Links (25,000)
    * @return SimpleXMLObject $reportXML
 */
    public function getReport($xml, $reportType, $perPage = 10000, $customFrom = false, $customTo = false){
        //echo "==>Preparing the Report ... \n\r";
        $runReport = $this->_CampaignRunReport($xml);
        
        echo "-->Download Report Now\n";
        echo "-->ReportTicketID: " . $runReport->RunReportResult->ReportTicketId . "\n\r";
        echo "-->ReportRowCount: " . $runReport->RunReportResult->RowCount . "\n\r";
        
        $downloadReportArray = array();
        
        if($customFrom && $customTo){
            // We will get custom rows
                $downloadReport = $this->_CampaignDownloadReport($runReport->RunReportResult->ReportTicketId, $customFrom, $customTo, $reportType);
                echo "-->Download Rows [$customFrom] - [$customTo] - Total [" . count($downloadReport) . "] ResponseRows ";
                if(phpversion() >= '5.3.1') {echo " - Memory usage [" . formatBytes(memory_get_peak_usage(),2).' bytes' . "]\n\r";}else{echo " \n\r";}
                $downloadReportArray[] = $downloadReport; 
        }else{
            // We will get the total result with several trial
            $rowCount = $runReport->RunReportResult->RowCount;
            if($rowCount > $perPage){
                // We need to use pagination to get the result
                $pages = ceil($rowCount/$perPage);
                
                // Ok, let's get the result step by step
                for($i = 1; $i<= $pages; $i++){
                    if($i == 1){
                        // This is the first page
                        $fromRow = 1;
                        $toRow = $i * $perPage;
                    }elseif($i == $pages){
                        // This is the last page
                        $fromRow = (($i - 1) * $perPage) + 1;
                        $toRow = $rowCount;    
                    }else{
                        $fromRow = (($i - 1) * $perPage) + 1; 
                        $toRow = $i * $perPage; 
                    }
                    $downloadReport = $this->_CampaignDownloadReport($runReport->RunReportResult->ReportTicketId, $fromRow, $toRow, $reportType);
                    echo "-->Download Rows [$fromRow] - [$toRow] - Total [" . count($downloadReport) . "] ResponseRows ";
                    if(phpversion() >= '5.3.1') {echo " - Memory usage [" . formatBytes(memory_get_peak_usage(),2).' bytes' . "]\n\r";}else{echo " \n\r";}
                    $downloadReportArray[] = $downloadReport;    
                }
            }
        }
        
        return $downloadReportArray;
    }
    
    public function saveReport($xml, $reportType, $perPage = 10000, $tableName, $saveFunction){
        //echo "==>Preparing the Report ... \n\r";
        $runReport = $this->_CampaignRunReport($xml);
        
        //echo "-->ReportTicketID: " . $runReport->RunReportResult->ReportTicketId . "\n\r";
        //echo "-->ReportRowCount: " . $runReport->RunReportResult->RowCount . "\n\r";
        
        if(!$runReport){
            echo "Failed to get Run Report ... Exit!\n";
            exit();
        }
        
        // Truncate table first
        //echo "-->TRUNCATE Table $tableName ...";
        //if(mysql_query("TRUNCATE Table $tableName")){echo " - [Done!]\n\r";}else{echo " - [Failed!]\n\r";}
        
        // We will get the total result with several trial
        $rowCount = $runReport->RunReportResult->RowCount;
        //$rowCount = "25000"; // For test purpose
        if($rowCount > $perPage){
            // We need to use pagination to get the result
            $pages = ceil($rowCount/$perPage);
            
            // Ok, let's get the result step by step
            for($i = 1; $i<= $pages; $i++){
                if($i == 1){
                    // This is the first page
                    $fromRow = 1;
                    $toRow = $i * $perPage;
                }elseif($i == $pages){
                    // This is the last page
                    $fromRow = (($i - 1) * $perPage) + 1;
                    $toRow = $rowCount;    
                }else{
                    $fromRow = (($i - 1) * $perPage) + 1; 
                    $toRow = $i * $perPage; 
                }
                $process = round((($toRow/$rowCount) * 100) , 2);
                echo "-->Download Rows [$fromRow] - [$toRow] ...";
                $downloadReport = $this->_CampaignDownloadReport($runReport->RunReportResult->ReportTicketId, $fromRow, $toRow, $reportType);
                echo " Done! Total [" . count($downloadReport) . "] ResponseRows in this session. Total Count is [$rowCount]";
                if(phpversion() >= '5.3.1') {echo " - Memory usage [" . formatBytes(memory_get_peak_usage(),2).' bytes' . "] - [$process%]\n\r";}else{echo " \n\r";} 
                $saveFunction($tableName, $downloadReport);
                if(!SOAP_RESPONSE_TRACK){
                     echo "\tFree memory response stacks ... [Done!] "; unset($this->_responseStacks);
                     echo " Free memory report Object ... [Done!]\n\r"; unset($downloadReport);
                }
            }
        }else{
            // We have less than a page record! Let's save it simply in one trial
            $fromRow = 1;
            $toRow = $runReport->RunReportResult->RowCount;
            echo "-->Download Rows [$fromRow] - [$toRow] ...";
            $downloadReport = $this->_CampaignDownloadReport($runReport->RunReportResult->ReportTicketId, $fromRow, $toRow, $reportType);
            echo " Done! Total [" . count($downloadReport) . "] ResponseRows ";
            if(phpversion() >= '5.3.1') {echo " - Memory usage [" . formatBytes(memory_get_peak_usage(),2).' bytes' . "]\n\r";}else{echo " \n\r";}
            $saveFunction($tableName, $downloadReport); 
            
        }
        echo "Done on [" . date("Y-m-d H:m:s") . "] \n\r";
        //return $downloadReportArray;
        return $rowCount;
    }
    

    
    public function saveReportContinue($xml, $reportType, $perPage = 10000, $tableName, $saveFunction, $ReportTicketId, $rowCount , $startRow){
        //echo "==>Preparing the Report ... \n\r";
        //$runReport = $this->_CampaignRunReport($xml);
        
        //echo "-->ReportTicketID: " . $runReport->RunReportResult->ReportTicketId . "\n\r";
        //echo "-->ReportRowCount: " . $runReport->RunReportResult->RowCount . "\n\r";
        
        echo "Continue the Report\n";
        echo "TickentId: $ReportTicketId\n";
        echo "RowCount: $rowCount\n";
        echo "StartRow: $startRow\n";
        
        //$rowCount = "25000"; // For test purpose
        if($rowCount > $perPage){
            // We need to use pagination to get the result
            $pages = ceil($rowCount/$perPage);
            
            $continue = false;
            // Ok, let's get the result step by step
            for($i = 1; $i<= $pages; $i++){
                if($i == 1){
                    // This is the first page
                    $fromRow = 1;
                    $toRow = $i * $perPage;
                }elseif($i == $pages){
                    // This is the last page
                    $fromRow = (($i - 1) * $perPage) + 1;
                    $toRow = $rowCount;    
                }else{
                    $fromRow = (($i - 1) * $perPage) + 1; 
                    $toRow = $i * $perPage;
                    if($fromRow < $startRow){
                        echo "Ignore [$fromRow] - [$toRow]\n";
                        //$continue = true;
                    }else{
                        $continue = true;
                    } 
                }
                
                if($continue){
                    echo "-->Download Rows [$fromRow] - [$toRow] ...";
                    $downloadReport = $this->_CampaignDownloadReport($ReportTicketId, $fromRow, $toRow, $reportType);
                    echo " Done! Total [" . count($downloadReport) . "] ResponseRows in this session. Total Count is [$rowCount]";
                    if(phpversion() >= '5.3.1') {echo " - Memory usage [" . formatBytes(memory_get_peak_usage(),2).' bytes' . "]\n\r";}else{echo " \n\r";} 
                    $saveFunction($tableName, $downloadReport);
                    if(!SOAP_RESPONSE_TRACK){
                        echo "\tFree memory response stacks ... [Done!] "; unset($this->_responseStacks);
                        echo " Free memory report Object ... [Done!]\n\r"; unset($downloadReport);
                    }
                }
            }
        }else{
            // We have less than a page record! Let's save it simply in one trial
            echo "<<It's less than 1 page>>\n";
            exit();
            $fromRow = 1;
            $toRow = $runReport->RunReportResult->RowCount;
            echo "-->Download Rows [$fromRow] - [$toRow] ...";
            $downloadReport = $this->_CampaignDownloadReport($runReport->RunReportResult->ReportTicketId, $fromRow, $toRow, $reportType);
            echo " Done! Total [" . count($downloadReport) . "] ResponseRows ";
            if(phpversion() >= '5.3.1') {echo " - Memory usage [" . formatBytes(memory_get_peak_usage(),2).' bytes' . "]\n\r";}else{echo " \n\r";}
            //$saveFunction($tableName, $downloadReport); 
            
        }
        echo "Done on [" . date("Y-m-d H:m:s") . "] \n\r";
        //return $downloadReportArray;
    }   
}


?>
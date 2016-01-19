<?php

require_once('base.class.php');


class Contact extends CampaignClientModel{

	private $_CampaignerAttributeArray;

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
        $response = $this->_client->DownloadReport(
                                Array(
                                    'authentication' => $this->_authorization,
                                    'reportTicketId'=> $reportTicketId,
                                    'fromRow' =>$from,
                                    'toRow' => $to,
                                    'reportType' => $reportTypes
                                ));
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

    //  ====================  Push contacts to Campaigner customized functions  START  ====================  //
    /**
     * @author Leon Zhao <leonz@junemedia.com>
     * Update several users in a single query
     * @example 
        $users = array(
            'leonz@junemedia.com' => array(
                                        "IsRecipe4LivingSweeps"=>"True"
                                    )
        );

        echo CampaignerUpdateContactInfoByEmail($users);
        echo "\n";
     */

    /**
     * 
     * @param array $users
     * @return response
     */
    function pushCampaigner($users) {
        echo "-->Try to push to campaigner ...";
        $data_array = array();
        foreach($users as $key=>$row){
            $userRow = $this->CampaignerPrepareDataArray($key, $row);
            if($userRow){$data_array[] = $userRow;}
        }
        //print_r($data_array);
        //exit();
        if(count($data_array)>0){
            $response = $this->_client->ImmediateUpload(Array(
                'authentication' => $this->_authorization,
                'UpdateExistingContacts' => true,
                'TriggerWorkflow' => false,
                'contacts' => Array(
                    'ContactData' =>
                            $data_array

                    )));

            $this->_saveUploadCampaignerResponseLogs($users, $this->_client->__getLastResponse());
            //$errorFlag = $this->throwErrorResponse($response);
            //if($errorFlag){
            //    echo "Failed\n";
            //    return false;
            //}else{
                //echo $response->RunReportResult->RowCount;exit;
                echo "Done\n";
                return $response; 
            //}
        }else{
            echo "No users need to be processed\n";
            return false;
        }
    }

    /**
     * 
     * @param array $attr_array
     * @param string $res
     */
    private function _saveUploadCampaignerResponseLogs($attr_array, $res){
        $insResSql = "INSERT INTO `LeonCampaignerApiResponse` (`id`, `attrs`, `response`, `datetime`) VALUES "
                . "(NULL, '".json_encode($attr_array)."', '$res', NOW())";
        mysql_query($insResSql);
    }

    /**
     * 
     * @param type $email
     * @param type $updateAttrubuteArray
     * @return array $user
     * @example CampaignerPrepareDataArray('leonz@junemedia.com', array('IsRecipe4LivingSweeps' => 'False'))
     */
    public function CampaignerPrepareDataArray($email, $updateAttrubuteArray){

        $ContactId = $this->getContactIdByEmail($email);
        if(!$ContactId){ return false;}

        $UserCustomizedAttributes = array();
        foreach($updateAttrubuteArray as $key=>$value){
            $attr_id = $this->getAttributeIdByName($key);
            $tmpRow = array("Id" => $attr_id, "_" => $value);
            $UserCustomizedAttributes[] = $tmpRow;
        }

        $user = array(
                        'IsTestContact' => false,	// if set to 'true', then specified email will receive test email
                        'ContactKey' => Array(
                            'ContactId'=>$ContactId,
                            'ContactUniqueIdentifier' => $email,
                            ),                    
                        'EmailAddress'=>$email,
                        'Status' => 'Subscribed',
                        'MailFormat' => 'Both',
                        'CustomAttributes' => $UserCustomizedAttributes
                );
        return $user;
    }


    /**
     * 
     * @param string $email
     * @return ContactId
     */
    public function getContactIdByEmail($email){
        $emailSql = "SELECT ContactId,ContactUniqueIdentifier FROM `LeonCampaignContact` WHERE `ContactUniqueIdentifier` LIKE '$email' ";
        $r = mysql_query($emailSql);    
        $row = mysql_fetch_object($r);
        if(mysql_num_rows($r)>0){
            return $row->ContactId;
        }else{
            return false;
        }
    }

    /**
     * 
     * @global array $CampaignerAttributeArray
     * @param string $attrName
     * @return string AttrubuteId
     */
    public function getAttributeIdByName($attrName){
        if($this->_CampaignerAttributeArray){
        }else{
            $attrSql = "SELECT Id,Name FROM `LeonCampaignContactAttribute`";
            $r= mysql_query($attrSql);
            while($row = mysql_fetch_object($r)){
                $attributeArray[$row->Name] = $row->Id;
            }
            $this->_CampaignerAttributeArray = $attributeArray;
        }
        return $this->_CampaignerAttributeArray[$attrName];
    }

    //  ====================  Push contacts to Campaigner customized functions END   ====================  //
 
}


?>

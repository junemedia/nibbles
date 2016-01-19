<?php
require_once(dirname(__FILE__) . '/../config.inc.php');
require_once('base.class.php');

class ListContact extends CampaignClientModel{
    public function __construct(){
        $this->_connectSoap();           
    }
    
    protected function _connectSoap(){
        echo "-->Connecting to Campaigner ...";
        $this->_client = new SoapClient(
                                        'https://ws.campaigner.com/2013/01/listmanagement.asmx?WSDL', 
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
    
    public function listContactGroups(){
        $xmlQuery = '<contactssearchcriteria>
                        <version major="2" minor="5" build="0" revision="0"/><accountid>439960</accountid>
                        <set>Partial</set>
                        <evaluatedefault>True</evaluatedefault>
                        <group>
                            <filter>
                                <filtertype>Grouping</filtertype>
                                <action>
                                    <type>Mailing</type>
                                    <operator>BelongTo</operator>
                                    <groupingid></groupingid>
                                </action>
                            </filter>
                        </group>
                        </contactssearchcriteria>';
        echo "-->Preparing the Report ... ";
        $response = $this->_client->ListContactGroups(
                                Array(
                                    'authentication' => $this->_authorization,
                                    'xmlContactQuery' => $xmlQuery
                                ));
        echo "Done\n";
        //echo "-------------------------------\n";
        //echo $this->_client->__getLastResponse();
        //echo "-------------------------------\n";
        
        //var_dump($response);
        $errorFlag = $this->throwErrorResponse();
        if($errorFlag){
            return false;
        }else{
            return $response->ListContactGroupsResult->ContactGroupDescription; 
        }
    }    
}




//var_dump(CampaignListContactGroups());
?>
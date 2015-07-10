<?php
require_once(dirname(__FILE__) . '/../config.inc.php');
require_once(CAMPAIGNER_LEON_ROOT . 'controllers/contactController.php');

$downloadReportDays = 1;




$creport = new Contact();
$lastDayContacts = '<contactssearchcriteria><version major="2" minor="0" build="0" revision="0" /><accountid>439960</accountid><set>Partial</set><evaluatedefault>True</evaluatedefault>
                    <group>
                        <filter>
                            <filtertype>SearchAttributeValue</filtertype>
                            <systemattributeid>2</systemattributeid>
                                <action>
                                    <type>DDMMYY</type>
                                    <operator>WithinLastNDays</operator>
                                    <value>'.$downloadReportDays.'</value>
                                </action>
                        </filter>
                        <filter>
                            <relation>Or</relation>
                            <filtertype>SearchAttributeValue</filtertype>
                            <systemattributeid>3</systemattributeid>
                            <action>
                                <type>DDMMYY</type>
                                <operator>WithinLastNDays</operator>
                                <value>'.$downloadReportDays.'</value>
                            </action>
                        </filter>
                    </group>
                </contactssearchcriteria>';             

                
// save details attributes
$r = $creport->saveReport($lastDayContacts, "rpt_Contact_Attributes",2500, "LeonCampaignContactDetails", "saveContactDetailsDaily");
//print_r($r);         


// save general information
$result =  $creport->saveReport($lastDayContacts, 'rpt_Contact_Details',24000,'LeonCampaignContact','saveContactGeneralDaily');    


// Send results mail to Leon
date_default_timezone_set('America/Chicago');
$email = "";
// Send the mail notification
$to      = $email . ',leonz@junemedia.com';
$subject = 'Daily Report - Download Campaign Contact Result';
$message = "Done! Save/Update [$result] emails";
$headers = 'From: leonz@junemedia.com' . "\r\n" .
    'Reply-To: leonz@junemedia.com' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

tryMail($to, $subject, $message, $headers);

?>
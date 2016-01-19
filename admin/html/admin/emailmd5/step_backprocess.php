<?php require_once("settings.php");?>

<?php

$email = (isset($_REQUEST['notify_email']) && trim($_REQUEST['notify_email']) != "")? trim($_REQUEST['notify_email']):"leonz@junemedia.com";
$type = isset($_REQUEST['type'])? trim($_REQUEST['type']):"emailhash";
$time = date("YmdHms");

// Test
//$type = "emailhash";


$directory = dirname(__FILE__) . "/";
$sourceFile = FILE_EXPORT;
$targetFile = "export.csv.$type.$time.zip";
$zipFile = $directory . "download/" . $targetFile;
$logFile = dirname(__FILE__) . "/log/export.csv.$type.$time.log";

echo "Preparing the export file... " . FILE_EXPORT . " <br>\n";
echo "Truncate the export file ..." . exec("cat /dev/null > " . FILE_EXPORT) . " Done!<br>\n";
echo "Converting ... <br>\n";
echo "php textconvertscript.php > $logFile <br>\n";


// Send the log nitification first
$to1      = $email . ',leonz@junemedia.com, williamg@junemedia.com, laural@junemedia.com';
$subject1 = 'Suppression file process notification starts';
$message1 = "\nIt is [" . date('Y-m-d H:m:s') . "].\n, To check the process status, please go to http://admin.popularliving.com/admin/emailmd5/log/export.csv.$type.$time.log\n";
$headers1 = 'From: leonz@junemedia.com' . "\r\n" .
    'Reply-To: leonz@junemedia.com' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

tryMail($to1, $subject1, $message1, $headers1);


if($type == "email"){  echo exec("php textconvertscript.php > $logFile") . "<br>\n"; } 
if($type == "emailhash"){  echo exec("php md5convertscript.php > $logFile") . "<br>\n"; }
if($type == "emaildomain"){  echo exec("php domainconvertscript.php > $logFile") . "<br>\n"; } 


echo "Zipping the file ... --> $zipFile<br>\n";
echo exec("zip $zipFile download/export.csv") . "<br>\n";

//echo $email;

echo "Back Process done\r\n";

date_default_timezone_set('America/Chicago');
// Send the mail notification
$to      = $email . ',leonz@junemedia.com, williamg@junemedia.com, laural@junemedia.com';
$subject = 'Suppression file process notification finished';
$message = "Done!\nIt is [" . date('Y-m-d H:m:s') . "].\nPlease go to http://admin.popularliving.com/admin/emailmd5/download/" . $targetFile . " to download the file. \nTo view the logs, Please go to http://admin.popularliving.com/admin/emailmd5/log/export.csv.$type.$time.log\n";
$filesize = round(filesize($zipFile)/(1024*1024), 2);
$message .= "File size is " . $filesize . "M bytes ";
$headers = 'From: leonz@junemedia.com' . "\r\n" .
    'Reply-To: leonz@junemedia.com' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

tryMail($to, $subject, $message, $headers);

//echo "mail($to, $subject, $message, $headers)";



?>
<?php
require_once(dirname(__FILE__) . '/../config.inc.php');
require_once(CAMPAIGNER_LEON_ROOT . 'controllers/campaignController.php');
define("DEBUG_PRINT", true);

//date_default_timezone_set('America/Chicago');

$today = date("Y-m-d", time()-86400);
$fromDate   = $today . 'T00:00:00-06:00';
$toDate     = $today . 'T23:59:59-06:00';
//$fromDate   = '2014-12-04T00:00:00-06:00';
//$toDate     = '2014-12-06T23:59:59-06:00';
$total = updateCampaignResult($fromDate, $toDate);


echo "Updated $total rows\n";


if(SERVER_RUN){
    // This is run on the server, send the email result to Leon
    $email = "";

    // Send the mail notification
    $to      = $email . ',leonz@junemedia.com';
    $subject = 'Daily Report - Download Campaign Run Result';
    $message = "Done! Save/Update [$total] Campaign Runs [$fromDate] to [$toDate]";
    $headers = 'From: leonz@junemedia.com' . "\r\n" .
        'Reply-To: leonz@junemedia.com' . "\r\n" .
        'X-Mailer: PHP/' . phpversion();

    tryMail($to, $subject, $message, $headers);
}

?>

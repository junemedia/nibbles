<?php

$dbase = "jr";

$user = "root" ;

$pass = "092363jr" ;


// DO NOT CHANGE THESE TWO LINES!

mysql_pconnect ('localhost', $user, $pass);

// mysql_connect ('localhost', $user, $pass);

mysql_select_db ($dbase);

$sGblTableOffers = "offers";
$sGblTableProspects = "prospects";
$sGblTableOffersTaken = "offersTaken";

$sGblEmailFrom = "support@popularliving.com";

?>
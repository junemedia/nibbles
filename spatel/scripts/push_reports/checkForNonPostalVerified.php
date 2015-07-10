<?PHP
#################################################################################
#       Sample crontab entry                                                    #
#################################################################################
#
# this crontab entry will cause this script to run
#  hourly on the hour.
#
# 0 * * * * /usr/bin/php /home/scripts/checkMysqlCorruptions.php
#
#################################################################################
#       User Variables                                                          #
#################################################################################
#
# modify these variables as required

//include( "/home/scripts/includes/cssLogFunctions.php" );
//$iScriptId = cssLogStart( "checkForNonPostalVerified.php" );
include("/home/sites/admin.popularliving.com/html/includes/paths.php");

$aIniConfig = parse_ini_file( "/home/scripts/includes/mysqlServer.conf" );
$MYSQLSERVER = $aIniConfig['mysqlHostLocal'];
$MYSQLUSER = $aIniConfig['mysqlROOTUSER'];
$MYSQLPASSWD = $aIniConfig['mysqlROOTPASS'];
$mysqlDatabaseNibbles = $aIniConfig['mysqlDatabaseNibbles'];
$MAILTO = $aIniConfig['recipNotify'];


$SERVERNAME = "CORY";
$REPORTNAME = "$SERVERNAME 'checkForNonPostalVerified.php'";

$outputlog .= "Checking for any OtData or UserData entries that have not been marked with ";
$outputlog .= "postalVerified='V' or 'N'.\n\n";

//mysql_connect( $MYSQLSERVER , $MYSQLUSER , $MYSQLPASSWD );

//mysql_select_db( $mysqlDatabaseNibbles );

$sQueryUserData = "SELECT * FROM userData WHERE postalVerified='' OR postalVerified IS NULL";
$rUserData = mysql_query( $sQueryUserData );
echo mysql_error();

$outputlog .= "Number of userData rows: ".mysql_num_rows( $rUserData )."\n\n";

while ($row = mysql_fetch_object ($rUserData)) {
	$outputlog .= var_export( $row, true)."\n\n";
}

$outputlog .= "\n\n";
$sQueryOtData = "SELECT * FROM otData WHERE postalVerified='' OR postalVerified IS NULL";
$rOtData = mysql_query( $sQueryOtData );
echo mysql_error();

$outputlog .= "Number of otData rows: ".mysql_num_rows( $rOtData )."\n\n";

$iTempTotal = mysql_num_rows($rUserData) + mysql_num_rows($rOtData);

while ($row = mysql_fetch_object ($rOtData)) {
	$outputlog .= var_export( $row, true)."\n\n";
}

$outputlog .= "\n\n";

################################################################################
#      Send report                                                             #
################################################################################
if ($iTempTotal > 0) {
	mail($MAILTO, $REPORTNAME, $outputlog, "From: ".$MAILTO."\nReply-To: ".$MAILTO."\nX-Mailer: PHP/" . phpversion());
}

//cssLogFinish( $iScriptId );

?>

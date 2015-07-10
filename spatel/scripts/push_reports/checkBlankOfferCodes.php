<?PHP
#################################################################################
#       Sample crontab entry                                                    #
#################################################################################
#
#
#################################################################################
#       User Variables                                                          #
#################################################################################
#
# modify these variables as required

include( "/home/scripts/includes/cssLogFunctions.php" );
$iScriptId = cssLogStart( "checkBlankOfferCodes.php" );

$aIniConfig = parse_ini_file( "/home/scripts/includes/mysqlServer.conf" );

$MAILTO = $aIniConfig['recipNotify'];

$PAGERECIPIENTS = $aIniConfig['recipPagers'];

$SERVERNAME = "MySQL";
$REPORTNAME = "$SERVERNAME 'checkBlankOfferCodes.php'";

	
$MYSQLSERVER = $aIniConfig['mysqlMASTERIP'];
$MYSQLUSER = $aIniConfig['mysqlNibblesUSER'];
$MYSQLPASSWD = $aIniConfig['mysqlNibblesPASS'];

$sDateYesterday = date( "Y-m-d", date("U")-60*60*24 );
$sDateTimeYesterday = "$sDateYesterday 00:00:00";

$outputlog = "";

mysql_connect( $MYSQLSERVER , $MYSQLUSER , $MYSQLPASSWD );

mysql_select_db("nibbles");

$sBlankOfferCodes = "SELECT id, email, sourceCode, dateTimeAdded, pageId
					FROM otDataHistory WHERE dateTimeAdded > '$sDateTimeYesterday'
					AND (offerCode IS NULL OR offerCode = '')";

$rBlankOfferCodes = mysql_query($sBlankOfferCodes);

echo mysql_errno()."   ".mysql_error();

$iNumBlankOfferCodes = mysql_num_rows($rBlankOfferCodes);

if( $iNumBlankOfferCodes > 0 ) {

	$outputlog .= "Report of otDataHistory entries with blank offer codes:\n";
	$outputlog .= "    For Date: $sDateYesterday\n";
	$outputlog .= "    Count:    $iNumBlankOfferCodes\n\n";
	
	$iCount = 0;

	while( $oRowBlankOfferCode = mysql_fetch_object( $rBlankOfferCodes ) ) {
		$iCount++;
		$outputlog .= "Entry # $iCount\n";
		$outputlog .= "otDataHistory ID: $oRowBlankOfferCode->id\n";
		$outputlog .= "Email Address:    $oRowBlankOfferCode->email\n";
		$outputlog .= "Page / Source:    $oRowBlankOfferCode->pageId\t / $oRowBlankOfferCode->sourceCode\n";
		$outputlog .= "DateTimeAdded:    $oRowBlankOfferCode->dateTimeAdded\n\n";
	}
	

	################################################################################
	#      Send report                                                             #
	################################################################################
	
	/* if ( $outputErrors != "" ) {
		$REPORTNAME .= " - ERRORS!";
		mail( $PAGERECIPIENTS, $REPORTNAME, "CHECK EMAIL FOR DETAILS", "From: cory@amperemedia.com\n" );
	}
	*/

	// mail only if $iNumBlankOfferCodes is greater than 0
	mail($MAILTO, $REPORTNAME, $outputlog, "From: ".$MAILTO."\nReply-To: ".$MAILTO."\nX-Mailer: PHP/" . phpversion());
}

echo $outputlog;


cssLogFinish( $iScriptId );

?>

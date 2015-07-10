<?php

/*********

Script to Display Ampere Mailing Statistics from the ezmlm/qmail system.

**********/


include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");
include("$sGblLibsPath/stringFunctions.php");
include("$sGblLibsPath/validationFunctions.php");
include("$sGblIncludePath/reportInclude.php");

$iScriptStartTime = getMicroTime();

$sPageTitle = "Ampere Mailing Delayed Send";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {

	dbSelect( "ezmlm" );

	if ($sDelete) {
		$sDeleteScheduled = "DELETE FROM sendDelayed WHERE id='$iId' limit 1";
		$rDeleteScheduled = dbQuery( $sDeleteScheduled );
		echo dbError();

		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
		dbSelect( "nibbles" );	
		$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Delete: $sDeleteScheduled\")"; 
		$rResult = dbQuery($sAddQuery); 
		echo  dbError(); 
		dbSelect( "ezmlm" );
		// end of track users' activity in nibbles		
	
	}

	if ($sSave) {
		// Process Info into Database!!
		if( strlen($iHour) == 1 ) {
			$iHour = '0'.$iHour;
		}
		
		if( strlen($iMinute) == 1 ) {
			$iMinute = '0'.$iMinute;
		}
		
		$sendDateTime = "$sDateToSend $iHour:$iMinute:00";
		$sUpdateScheduled = "UPDATE sendDelayed
					SET dateTimeDelay='$sendDateTime', moderateText='$sModerateAddress'
					WHERE id='$iId'";

		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
		dbSelect( "nibbles" );	
		$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Update: $sUpdateScheduled\")"; 
		$rResult = dbQuery($sAddQuery); 
		echo  dbError(); 
		dbSelect( "ezmlm" );
		// end of track users' activity in nibbles		
		
		$rUpdateScheduled = dbQuery( $sUpdateScheduled );
		echo dbError();
		unset( $iId );
		unset( $sSave );
		unset( $sEdit );
		unset( $sViewReport );

	}

	if ($iId && ($sEdit == 1) ) {
		$sGetScheduled = "SELECT * FROM sendDelayed where id='$iId'";

		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
		dbSelect( "nibbles" );	
		$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Open for editing: $sGetScheduled\")"; 
		$rResult = dbQuery($sAddQuery); 
		echo  dbError(); 
		dbSelect( "ezmlm" );
		// end of track users' activity in nibbles		

		
		$rGetScheduled = dbQuery( $sGetScheduled );
		echo dbError();
		if( $rGetScheduled ) {
			$oRowScheduled = dbFetchObject( $rGetScheduled );
			$sEditTempDate = $oRowScheduled->dateTimeDelay;
			$sEditModerateText = $oRowScheduled->moderateText;
			$sEditTimeHour = substr($sEditTempDate, 11, 2);
			$sEditTimeMinute = substr($sEditTempDate, 14, 2);
		}
	}

	if ($sViewReport && !$iId) {
		if( $sModerateAddress != "" && eregi(  "^[A-Za-z0-9\._-]+[@]{1,1}[A-Za-z0-9-]+[\.]{1}[A-Za-z0-9\.-]+[A-Za-z]$", $sModerateAddress) ) {

			// Process Info into Database!!
			if( strlen($iHour) == 1 ) {
				$iHour = '0'.$iHour;
			}
			if( strlen($iMinute) == 1 ) {
				$iMinute = '0'.$iMinute;
			}
			$sendDateTime = "$sDateToSend $iHour:$iMinute:00";

			$sInsertQuery = 'insert into sendDelayed (dateTimeDelay, moderateText)
						values ( "'.$sendDateTime.'", "'.$sModerateAddress.'" )';

			// start of track users' activity in nibbles 
			$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
			dbSelect( "nibbles" );	
			$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Insert: " . addslashes($sInsertQuery) . "\")"; 
			$rResult = dbQuery($sAddQuery); 
			echo  dbError(); 
			dbSelect( "ezmlm" );
			// end of track users' activity in nibbles		
			
			$rInsert = dbQuery( $sInsertQuery );
			echo dbError();
			$sMessage = "Entry Added.  Message will send on $sendDateTime.";

		} else {
			$sMessage .= "Invalid Email Address.  Please Re-Enter.";
		}
	}


	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
				<input type=hidden name=iId value='$iId'>";	

	if ( $sEdit == 1 ) {
		$sHidden .= "<input type=hidden name=sSave value='1'>";
	}


	$iCurrYear = date('Y');
	$iCurrMonth = date('m');
	$iCurrDay = date('d');

	$iCurrHH = date('H');
	$iCurrMM = date('i');
	$iCurrSS = date('s');

	$iMaxDaysToDelay = 5;

	$sRunDateAndTime = "$iCurrMonth-$iCurrDay-$iCurrYear $iCurrHH:$iCurrMM:$iCurrSS";

	for ($i = 0; $i < $iMaxDaysToDelay; $i++) {
		$sTempDate = DateAdd("d", $i, date('Y')."-".date('m')."-".date('d'));
		if( $sTempDate == substr( $sEditTempDate, 0, 10 ) ) {
			$sSelected = " selected ";
		} else {
			$sSelected = "";
		}
		$sDateOptions .= "<option value='$sTempDate' $sSelected>$sTempDate";
	}

	for ($i=0;$i<24;$i++) {
		if( $i == (1*$sEditTimeHour) ) {
			$sHourSelected = " selected ";
		} else {
			$sHourSelected = "";
		}
		$sHourOptions .= "<option value='$i' $sHourSelected>$i";
	}

	for ($i=0;$i<60;$i++) {
		if( $i == (1*$sEditTimeMinute) ) {
			$sMinuteSelected = " selected ";
		} else {
			$sMinuteSelected = "";
		}
		$sMinuteOptions .= "<option value='$i' $sMinuteSelected>$i";
	}

	include("../../includes/adminHeader.php");

	$iScriptEndTime = getMicroTime();
	$iScriptExecutionTime = round($iScriptEndTime - $iScriptStartTime);

	// display javascript from reportInclude.php which defined funcReportClicked() function
	echo $sReportJavaScript;

	$sQueryCurrentlyScheduled = "SELECT * FROM sendDelayed order by dateTimeDelay";

	// start of track users' activity in nibbles 
	$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
	dbSelect( "nibbles" );	
	$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
	  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"View report: $sQueryCurrentlyScheduled\")"; 
	$rResult = dbQuery($sAddQuery); 
	echo  dbError(); 
	dbSelect( "ezmlm" );
	// end of track users' activity in nibbles		

	
	$rCurrentlyScheduled = dbQuery( $sQueryCurrentlyScheduled );
	echo dbError();

	while ($oRowScheduled = dbFetchObject( $rCurrentlyScheduled )) {
		$sCurrentlyScheduled .= "<tr>
			<td>$oRowScheduled->id</td>
			<td>$oRowScheduled->dateTimeDelay</td>
			<td>$oRowScheduled->moderateText</td>
			<td>
				<a href='$PHP_SELF?iId=$oRowScheduled->id&sEdit=1&iMenuId=$iMenuId'>edit</a>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<a href='JavaScript:confirmDelete(this,".$oRowScheduled->id.");'>delete</a>
			</td>";
	}
	
	if( $sEdit ) {
		$sEditCancel = "<a href='$PHP_SELF?iMenuId=$iMenuId'>Cancel Edit</a>";
	}
	
	if ( $sCurrentlyScheduled == "" ) {
		$sCurrentlyScheduled = '<tr><td colspan="4" align="center" style="color: ff0000;">There are no scheduled emails at this time.</td></tr>';
	}
?>

<script language=JavaScript>
	function confirmDelete(form1,id)
	{
		if(confirm('Are you sure to delete this record ?'))
		{	
			document.form1.elements['sDelete'].value='Delete';
			document.form1.elements['iId'].value=id;
			document.form1.submit();												
		}
	}
</script>

<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $sHidden;?>

<input type=hidden name=sDelete>

<input type=hidden name=reportClicked>
<input type=hidden name=sViewReport value="Submit">

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>

<tr><td>Date To Send</td><td><select name=sDateToSend><?php echo $sDateOptions;?>
	</select></td>
	</tr><tr>
	<td>Time To Send</td>
	<td><select name=iHour><?php echo $sHourOptions;?>
	</select> &nbsp;<select name=iMinute><?php echo $sMinuteOptions;?>
	</select></td></tr>	
<tr><td>Moderation Address</td><td><input type=text name=sModerateAddress  value="<?php echo $sEditModerateText; ?>" size="50">
	</td>
	</tr><tr>
	<tr><td colspan="4" align="center">
		<input type="submit" value="Submit">
		<br><br>
		<?php echo $sEditCancel; ?>
	</td></tr>
</table>
<br />
<br />
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td><b><u>ID</u></b></td><td><b><u>Date/Time To Be Sent</u></b></td><td><b><u>Address</u></b></td></tr>
<?php echo $sCurrentlyScheduled; ?>
</table>

</form>

<?php

include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}

?>
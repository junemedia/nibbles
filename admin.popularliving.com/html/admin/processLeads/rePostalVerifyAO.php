<?php


include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");
include_once("$sGblRoot/validateAddress/validateAddressAo.php");

session_start();

$sPageTitle = "Nibbles - Re-PostalVerify Using AddressObject";

if (hasAccessRight($iMenuId) || isAdmin()) {

	$iCurrYear = date(Y);
	$iCurrMonth = date(m); //01 to 12
	$iCurrDay = date(d); // 01 to 31

	$sYesterday = DateAdd("d", -1, date('Y')."-".date('m')."-".date('d'));
	$sToday = DateAdd("d", 0, date('Y')."-".date('m')."-".date('d'));

	if (!($sRePostalVerify)) {
		$iStartYear = date('Y');
		$iStartMonth = date('m');
		$iStartDay = date('d');

		$iEndMonth = $iStartMonth;
		$iEndDay = $iStartDay;
		$iEndYear = $iStartYear;
	}

	$sTodaysLeads = stripslashes($sTodaysLeads);


	if (DateDiff("d",mktime(0,0,0,date('m'),date('d'),date('Y')),mktime(0,0,0,$iEndMonth,$iEndDay,$iEndYear)) >= 0 || $iEndYear=='') {
		$iEndYear = substr( $sToday, 0, 4);
		$iEndMonth = substr( $sToday, 5, 2);
		$iEndDay = substr( $sToday, 8, 2);
	}

	if (DateDiff("d",mktime(0,0,0,date('m'),date('d'),date('Y')),mktime(0,0,0,$iStartMonth,$iStartDay,$iStartYear)) >= 0 || $iStartYear=='') {
		$iStartYear = substr( $sToday, 0, 4);
		$iStartMonth = substr( $sToday, 5, 2);
		$iStartDay = substr( $sToday, 8, 2);
	}

	$sDateStart = "$iStartYear-$iStartMonth-$iStartDay 00:00:00";
	$sDateEnd = "$iEndYear-$iEndMonth-$iEndDay 23:59:59";

	$sOtDataTable = "otDataHistory";
	$sUserDataTable = "userDataHistory";

	$iCountFailures = 0;
	$iCountSuccesses = 0;
	$iCountUpdated = 0;
	
	if( $sRePostalVerify ) {

		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Re-Postal verify: $sDateStart to $sDateEnd\")"; 
		$rLogResult = dbQuery($sLogAddQuery); 
		echo  dbError(); 
		// end of track users' activity in nibbles		

		
		$sAllLeads = "SELECT * FROM userDataHistory WHERE dateTimeAdded BETWEEN '$sDateStart' AND '$sDateEnd'";
		//echo "$sAllLeads";
		$rAllLeads = dbQuery( $sAllLeads );
		echo dbError();

		$iCountTotal = dbNumRows( $rAllLeads );

		while( $oRowUserData = mysql_fetch_object( $rAllLeads ) ) {
			$sEmail = $oRowUserData->email;
			$sAddress = $oRowUserData->address;
			$sAddress2 = $oRowUserData->address2;
			$sCity = $oRowUserData->city;
			$sState = $oRowUserData->state;
			$sZip = $oRowUserData->zip;
			$sAoValidation = validateAddressAo( $sAddress, $sAddress2, $sCity, $sState, $sZip, $sGblRoot );

			if( substr( $sAoValidation, 0, 7 ) == "Failure" ) {

				$sUpdateFailure = "UPDATE userDataHistory SET postalVerified='N' WHERE email='$sEmail'";
				$rUpdateFailure = dbQuery( $sUpdateFailure );
				echo dbError();
				$sUpdateFailure = "UPDATE otDataHistory SET postalVerified='N' WHERE email='$sEmail'";
				$rUpdateFailure = dbQuery( $sUpdateFailure );
				echo dbError();
				$iCountFailures++;
				
			} elseif ( substr( $sAoValidation, 0, 6 ) == "update" ) {
				
				// EX: update|address=3198  Darby St |address2= |city=Simi Valley|state=CA|zip=93063|oldaddress=3198 Darby St|oldaddress2=115|oldcity=Simi Valley|oldstate=CA|oldzip=93065|
				$aAoErrorLine = explode( "|", $sAoValidation );

				$aNewAddressText = explode( "=", $aAoErrorLine[1] );
				$aNewAddress2Text = explode( "=", $aAoErrorLine[2] );
				$aNewCityText = explode( "=", $aAoErrorLine[3] );
				$aNewStateText = explode( "=", $aAoErrorLine[4] );
				$aNewZipText = explode( "=", $aAoErrorLine[5] );

				$sAddress = $aNewAddressText[1];
				$sAddress2 = $aNewAddress2Text[1];
				$sCity = $aNewCityText[1];
				$sState = $aNewStateText[1];
				$sZip = $aNewZipText[1];
				
				$sUpdateUpdated = "UPDATE userDataHistory SET postalVerified='V',
									address=\"$sAddress\",
									address2=\"$sAddress2\",
									city=\"$sCity\",
									state=\"$sState\",
									zip=\"$sZip\" 
								WHERE email=\"$sEmail\"
								AND dateTimeAdded BETWEEN '$sDateStart' AND '$sDateEnd'";
				$rUpdateUpdated = dbQuery( $sUpdateUpdated );
				echo dbError();
				$sUpdateUpdated = "UPDATE otDataHistory SET postalVerified='V'
								WHERE email=\"$sEmail\"
								AND dateTimeAdded BETWEEN '$sDateStart' AND '$sDateEnd'";
				$rUpdateUpdated = dbQuery( $sUpdateUpdated );
				echo dbError();
				$iCountUpdated++;

			} else {
				
				$sUpdateSuccess = "UPDATE userDataHistory SET postalVerified='V' WHERE email='$sEmail'";
				$rUpdateSuccess = dbQuery( $sUpdateSuccess );
				echo dbError();
				$sUpdateSuccess = "UPDATE otDataHistory SET postalVerified='V' WHERE email='$sEmail'";
				$rUpdateSuccess = dbQuery( $sUpdateSuccess );
				echo dbError();
				$iCountSuccesses++;

			}
		}

		$sAllLeads = "SELECT * FROM userData WHERE dateTimeAdded BETWEEN '$sDateStart' AND '$sDateEnd'";
		//echo "$sAllLeads";
		$rAllLeads = dbQuery( $sAllLeads );
		echo dbError();

		$iCountTotal = dbNumRows( $rAllLeads );

		while( $oRowUserData = mysql_fetch_object( $rAllLeads ) ) {
			$sEmail = $oRowUserData->email;
			$sAddress = $oRowUserData->address;
			$sAddress2 = $oRowUserData->address2;
			$sCity = $oRowUserData->city;
			$sState = $oRowUserData->state;
			$sZip = $oRowUserData->zip;
			$sAoValidation = validateAddressAo( $sAddress, $sAddress2, $sCity, $sState, $sZip, $sGblRoot );

			if( substr( $sAoValidation, 0, 7 ) == "Failure" ) {

				$sUpdateFailure = "UPDATE userData SET postalVerified='N' WHERE email='$sEmail'";
				$rUpdateFailure = dbQuery( $sUpdateFailure );
				echo dbError();
				$sUpdateFailure = "UPDATE otData SET postalVerified='N' WHERE email='$sEmail'";
				$rUpdateFailure = dbQuery( $sUpdateFailure );
				echo dbError();
				$iCountFailures++;
				
			} elseif ( substr( $sAoValidation, 0, 6 ) == "update" ) {
				
				// EX: update|address=3198  Darby St |address2= |city=Simi Valley|state=CA|zip=93063|oldaddress=3198 Darby St|oldaddress2=115|oldcity=Simi Valley|oldstate=CA|oldzip=93065|
				$aAoErrorLine = explode( "|", $sAoValidation );

				$aNewAddressText = explode( "=", $aAoErrorLine[1] );
				$aNewAddress2Text = explode( "=", $aAoErrorLine[2] );
				$aNewCityText = explode( "=", $aAoErrorLine[3] );
				$aNewStateText = explode( "=", $aAoErrorLine[4] );
				$aNewZipText = explode( "=", $aAoErrorLine[5] );

				$sAddress = $aNewAddressText[1];
				$sAddress2 = $aNewAddress2Text[1];
				$sCity = $aNewCityText[1];
				$sState = $aNewStateText[1];
				$sZip = $aNewZipText[1];
				
				$sUpdateUpdated = "UPDATE userData SET postalVerified='V',
									address=\"$sAddress\",
									address2=\"$sAddress2\",
									city=\"$sCity\",
									state=\"$sState\",
									zip=\"$sZip\" 
								WHERE email=\"$sEmail\"
								AND dateTimeAdded BETWEEN '$sDateStart' AND '$sDateEnd'";
				$rUpdateUpdated = dbQuery( $sUpdateUpdated );
				echo dbError();
				$sUpdateUpdated = "UPDATE otData SET postalVerified='V'
								WHERE email=\"$sEmail\"
								AND dateTimeAdded BETWEEN '$sDateStart' AND '$sDateEnd'";
				$rUpdateUpdated = dbQuery( $sUpdateUpdated );
				echo dbError();
				$iCountUpdated++;

			} else {
				
				$sUpdateSuccess = "UPDATE userData SET postalVerified='V' WHERE email='$sEmail'";
				$rUpdateSuccess = dbQuery( $sUpdateSuccess );
				echo dbError();
				$sUpdateSuccess = "UPDATE otData SET postalVerified='V' WHERE email='$sEmail'";
				$rUpdateSuccess = dbQuery( $sUpdateSuccess );
				echo dbError();
				$iCountSuccesses++;

			}
		}
		$sMessage .= "<br>Total: ($iCountTotal), Failures: ($iCountFailures), Successes: ($iCountSuccesses), Updates: ($iCountUpdated)";
	}



	// prepare month options for From and To date

	$sStartMonthOptions = "";
	$sEndMonthOptions = "";

	for ($i = 0; $i < count($aGblMonthsArray); $i++) {
		$iValue = $i+1;

		if ($iValue < 10) {
			$iValue = "0".$iValue;
		}

		if ($iValue == $iStartMonth) {
			$sStartMonthSel = "selected";
		} else {
			$sStartMonthSel = "";
		}
		if ($iValue == $iEndMonth) {
			$sEndMonthSel = "selected";
		} else {
			$sEndMonthSel = "";
		}


		$sStartMonthOptions .= "<option value='$iValue' $sStartMonthSel>$aGblMonthsArray[$i]";
		$sEndMonthOptions .= "<option value='$iValue' $sEndMonthSel>$aGblMonthsArray[$i]";
	}


	// prepare day options for From and To date
	$sStartDayOptions = "";
	$sEndDayOptions = "";

	for ($i = 1; $i <= 31; $i++) {

		if ($i < 10) {
			$iValue = "0".$i;
		} else {
			$iValue = $i;
		}

		if ($iValue == $iStartDay) {
			$sStartDaySel = "selected";
		} else {
			$sStartDaySel = "";
		}

		if ($iValue == $iEndDay) {
			$sEndDaySel = "selected";
		} else {
			$sEndDaySel = "";
		}

		$sStartDayOptions .= "<option value='$iValue' $sStartDaySel>$i";
		$sEndDayOptions .= "<option value='$iValue' $sEndDaySel>$i";
	}

	// prepare year options for From and To date
	$sStartYearOptions = "";
	$sEndYearOptions = "";

	for ($i = $iCurrYear-1; $i <= $iCurrYear+5; $i++) {

		if ($i == $iStartYear) {
			$sStartYearSel = "selected";
		} else {
			$sStartYearSel ="";
		}

		if ($i == $iEndYear) {
			$sEndYearSel = "selected";
		} else {
			$sEndYearSel = "";
		}

		$sStartYearOptions .= "<option value='$i' $sStartYearSel>$i";
		$sEndYearOptions .= "<option value='$i' $sEndYearSel>$i";
	}

	if ($iRerun) {
		$sRerunChecked = "checked";
	} else {
		$sRerunChecked = "";
	}

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"> 
<html>

<head>
<title><?php echo $sPageTitle;?></title>
<LINK rel="stylesheet" href="<?php echo $sGblAdminSiteRoot;?>/styles.css" type="text/css" >
</head>

<body>
<form name=form1 action='<?php echo $PHP_SELF;?>' method=post enctype=multipart/form-data >
<input type=hidden name=iMenuId value='<?php echo $iMenuId;?>'>
<table cellpadding=3 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
	<tr><Td class=message align=center colspan=2><?php echo $sMessage;?></td></tr>
</table>

<table cellpadding=3 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td colspan=2><BR>All Leads and User Data will be marked as "N" (Not postal verified) if they
fail the AddressObject postal verification.</td></tr>
<tr><td class=header>Date Range</td><td>From: <select name=iStartMonth>
			<?php echo $sStartMonthOptions;?>
			</select> &nbsp;<select name=iStartDay>
			<?php echo $sStartDayOptions;?>
			</select> &nbsp;<select name=iStartYear>
			<?php echo $sStartYearOptions;?>
			</select>
			&nbsp; To: 
			<select name=iEndMonth>
			<?php echo $sEndMonthOptions;?>
			</select> &nbsp;<select name=iEndDay>
			<?php echo $sEndDayOptions;?>
			</select> &nbsp;<select name=iEndYear>
			<?php echo $sEndYearOptions;?>
			</select>
			</td></tr>
<tr><td colspan=2 align=center><BR><BR>
<input type=submit name=sRePostalVerify value='RePostalVerify'>  &nbsp; &nbsp; 
	</td></tr>
</table>	
<?php

} else {
	echo "You are not authorized to access this page...";
}
?>
<?php


include("../../includes/paths.php");

session_start();

$sPageTitle = "Nibbles - Repost PMD Entries";
$sPmdPostUrlPrefix = "http://web0.popularliving.com/cgi-bin/FormMail4.pl";
//$sPmdPostUrlPrefix = "http://web0.popularliving.com/testFormPost.leeOnly.php";

if (hasAccessRight($iMenuId) || isAdmin()) {

	include("../../includes/adminHeader.php");
	if ($sSetBack) {

	
		
		$sStartDate = 	$iStartYear."-".$iStartMonth."-".$iStartDay;
		$sEndDate = 	$iEndYear."-".$iEndMonth."-".$iEndDay;


		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Report PMD leads: $sStartDate to $sEndDate\")"; 
		$rLogResult = dbQuery($sLogAddQuery); 
		echo  dbError(); 
		// end of track users' activity in nibbles		
		
		
		$sPmdEntries = "select * from pmdUsers
			where dateTimeAdded between '$sStartDate 00:00:00' and '$sEndDate 23:59:59'";

		$rPmdEntries = dbQuery( $sPmdEntries );
		
		
		if( $rPmdEntries ) {

			while( $oRowPmdEntry = dbFetchObject( $rPmdEntries ) ) {

				if ($sMessage == '') {

					$sEmail = $oRowPmdEntry->email;
					
					$sAdditionalInfo = "select * from userDataHistory where email='$sEmail'";
					$rAdditionalInfo = dbQuery( $sAdditionalInfo );
					if( dbNumRows( $rAdditionalInfo ) > 0 ) {
						$oRowAdditionalInfo = dbFetchObject( $rAdditionalInfo );
						$sName = $oRowAdditionalInfo->first." ".$oRowAdditionalInfo->last;
						$sAddress = $oRowAdditionalInfo->address;
						$sCity = $oRowAdditionalInfo->city;
						$sPhone = $oRowAdditionalInfo->phoneNo;
					} else {
						$sName = "";
						$sAddress = "";
						$sCity = "";
						$sPhone = "";
					}
					
					$sState = $oRowPmdEntry->state;
					$sZip = $oRowPmdEntry->zip;
					$sPostCat = '';

					$sSex = $oRowPmdEntry->sex;
					$iBirthYear = $oRowPmdEntry->birthYear;
					$icanReceiveHtml = $oRowPmdEntry->canReceiveHtml;
					$sJobTitle = $oRowPmdEntry->jobTitle;
					$sJobFunction = $oRowPmdEntry->jobFunction;
					$sSourceCode = $oRowPmdEntry->sourceCode;
					$sDateTimeAdded = $oRowEntry->dateTimeAdded;

					$sPmdCategories = "select * from pmdUserCategories where email='$sEmail'";

					$rPmdCategories = dbQuery( $sPmdCategories );

					$i=0;
					
					if( $rPmdCategories ) {
						while( $oRowPmdCategory = dbFetchObject( $rPmdCategories ) ) {
							$iCategoryId = $oRowPmdCategory->categoryId;

							// prepare form post data
							$sCatQuery = "SELECT categorySQL
										  FROM	 pmdCategories
										  WHERE  id = '$iCategoryId'";
							$rCatResult = dbQuery($sCatQuery);
							while ($oCatRow = dbFetchObject($rCatResult)) {
								$sPostCat .= "&listname=".urlencode("MyFree.com/".$oCatRow->categorySQL.".list");
							}
						}
					}

					// post pmd data
					// take all values from session and assign it to variable...
					$sUrlEmail = urlencode($sEmail);
					//$sUrlSalutation = urlencode($_SESSION["sSesSalutation"]);
					$iUrlYearOfBirth = urlencode($iBirthYear);

					$sUrlName = urlencode($sName);
					$sUrlAddress = urlencode($sAddress);
					//$sUrlAddress2 = urlencode($_SESSION["sSesAddress2"]);
					$sUrlCity = urlencode($sCity);
					$sUrlPhone = urlencode($sPhone);
						
					$sUrlState = urlencode($sState);
					$sUrlZip = urlencode($sZip);
					$sUrlSourceCode = urlencode($sSourceCode);
					$sUrlJobTitle = urlencode($sJobTitle);
					$sUrlJobFunction = urlencode($sJobFunction);

					//$sUrlThankYou = urlencode("$sGblSiteRoot/taf/index.php?e=$sEmail");
					$sUrlOwner = urlencode("MyFree.com");
					$sUrlGatherer = urlencode("signup");
					$sUrlDisableConfirmWarning = '';
					//$sUrlNextRedirect = urlencode("$sGblSiteRoot/j/index.php");

					// concat email...to...DisableConfirmWarning
					$sPmdPostString = "email=$sEmail&name=$sUrlName&address=$sUrlAddress&city=$sUrlCity&state=$sUrlState&yearofbirth=$iUrlYearOfBirth";
					$sPmdPostString .= "&zip=$sUrlZip&phone=$sUrlPhone&gender=$sSex&src=$sUrlSourceCode&title=$sUrlJobTitle&jobfunction=$sUrlJobFunction";
					$sPmdPostString .= "&owner=$sUrlOwner&gatherer=$sUrlGatherer&disableconfirmationwarning=$sUrlDisableConfirmWarning";

					//concat above line + sPostCat
					//&thankyouurl=$sUrlThankYou&redirect=$sUrlThankYou
					$sPmdPostString .= $sPostCat;


					$aPmdUrlArray = explode("//", $sPmdPostUrl);
					$sPmdUrlPart = $aPmdUrlArray[1];


					// concat post url followed by "?" + followed by above string
					// eg. http//www.test.com/test.php("?")(string)
					$sPmdPostUrl = $sPmdPostUrlPrefix."?".$sPmdPostString;


					//	echo $sPmdPostUrl;
					//	exit;

//					echo $sPmdPostUrl."<br>";

					$rFp = fopen($sPmdPostUrl, "r");
					if ($rFp) {
						while ($line = fread($rFp,8192)) {
							$result .= $line;
						}
						//echo $result;

						fclose($rFp);
					}


					//$sPmdHostPart = substr($sPmdUrlPart,0,strlen($sPmdUrlPart)-strrpos(strrev($sPmdUrlPart),"/"));
					//$sPmdScriptPath = substr($sPmdUrlPart,strlen($sPmdHostPart));

					//$sPmdScriptPath = "FormMail4.pl";

					/*
					$rSocketConnection = fsockopen($sPmdHostPart, 80, $errno, $errstr, 30);

					if ($rSocketConnection) {
					//echo "Dfdfd";
					$sPmdScriptPath  .= "?".$sPmdPostString;

					fputs($rSocketConnection, "GET $sPmdScriptPath HTTP/1.1\r\n");
					fputs($rSocketConnection, "Host: $sPmdHostPart\r\n");
					fputs($rSocketConnection, "User-Agent: MSIE\r\n");
					fputs($rSocketConnection, "Connection: close\r\n\r\n");
					}
					*/



				}
			}
			$sMessage .= "Completed Re-Posting";
		} else {
			$sMessage .= "Mysql returned no rows.";
		}
	}


	$iCurrYear = date(Y);
	$iCurrMonth = date(m); //01 to 12
	$iCurrDay = date(d); // 01 to 31

	if (!($iStartMonth && $iStartDay && $iStartYear)) {
		$iStartMonth = $iCurrMonth;
		$iStartDay = $iCurrDay;
		$iStartYear = $iCurrYear;
	}

	if (!($iEndMonth && $iEndDay && $iEndYear)) {
		$iEndMonth = $iCurrMonth;
		$iEndDay = $iCurrDay;
		$iEndYear = $iCurrYear;
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
<tr><td colspan=2><BR>This form will resubmit any PMD lead that was successfully entered into the system.
		<BR>Select date range on which leads were entered which you want to set back.</td></tr>

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
<tr><td colspan=2 align=center><BR><BR><input type=submit name=sSetBack value='Repost PMD Leads'></td></tr>
</table>	
<?php

} else {
	echo "You are not authorized to access this page...";
}
?>

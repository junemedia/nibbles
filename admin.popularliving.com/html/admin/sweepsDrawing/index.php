<?php

/*********

Script to Display

**********/


include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");
include("$sGblLibsPath/stringFunctions.php");
include("$sGblIncludePath/reportInclude.php");

$iScriptStartTime = getMicroTime();

$sPageTitle = "SweepStakes Drawing";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
	
	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
				<input type=hidden name=iId value='$iId'>";	
	
	$iCurrYear = date('Y');
	$iCurrMonth = date('m');
	$iCurrDay = date('d');
	
	$iCurrHH = date('H');
	$iCurrMM = date('i');
	$iCurrSS = date('s');
	
	$sRunDateAndTime = "$iCurrMonth-$iCurrDay-$iCurrYear $iCurrHH:$iCurrMM:$iCurrSS";
	
	$sYesterday = DateAdd("d", -1, date('Y')."-".date('m')."-".date('d'));
	
	
	if (!$sSubmit) {
		
		$iYearFrom = date('Y');
		$iMonthFrom = date('m');
		$iDayFrom = date('d');
		
		$iMonthTo = $iMonthFrom;
		$iDayTo = $iDayFrom;
		$iYearTo = $iYearFrom;
		
	}
		
	// prepare month options for From and To date
	for ($i = 0; $i < count($aGblMonthsArray); $i++) {
		
		$iValue = $i+1;
		
		if ($iValue < 10) {
			$iValue = "0".$iValue;
		}
		
		if ($iValue == $iMonthFrom) {
			$sFromSel = "selected";
		} else {
			$sFromSel = "";
		}
		if ($iValue == $iMonthTo) {
			$sToSel = "selected";
		} else {
			$sToSel = "";
		}
		
		$sMonthFromOptions .= "<option value='$iValue' $sFromSel>$aGblMonthsArray[$i]";
		$sMonthToOptions .= "<option value='$iValue' $sToSel>$aGblMonthsArray[$i]";
	}
	
	// prepare day options for From and To date
	for ($i = 1; $i <= 31; $i++) {
		
		if ($i < 10) {
			$iValue = "0".$i;
		} else {
			$iValue = $i;
		}
		
		if ($iValue == $iDayFrom) {
			$sFromSel = "selected";
		} else {
			$sFromSel = "";
		}
		if ($iValue == $iDayTo) {
			$sToSel = "selected";
		} else {
			$sToSel = "";
		}
		$sDayFromOptions .= "<option value='$iValue' $sFromSel>$i";
		$sDayToOptions .= "<option value='$iValue' $sToSel>$i";
	}
	
	// prepare year options
	for ($i = $iCurrYear; $i >= $iCurrYear-5; $i--) {
		
		if ($i == $iYearFrom) {
			$sFromSel = "selected";
		} else {
			$sFromSel ="";
		}
		if ($i == $iYearTo) {
			$sToSel = "selected";
		} else {
			$sToSel ="";
		}
		
		$sYearFromOptions .= "<option value='$i' $sFromSel>$i";
		$sYearToOptions .= "<option value='$i' $sToSel>$i";
	}
	
		
	$sDateFrom = "$iYearFrom-$iMonthFrom-$iDayFrom";
	$sDateTo = "$iYearTo-$iMonthTo-$iDayTo";
	
	if (checkdate($iMonthFrom, $iDayFrom, $iYearFrom) && checkdate($iMonthTo, $iDayTo, $iYearTo)) {
		
		$sDateTimeFrom = $sDateFrom. " 00:00:00";
		$sDateTimeTo = $sDateTo." 23:59:59";
		
		// Prepare comma-separated pages 
			
			$sPagesQuery = "SELECT id, pageName
							FROM   otPages
							ORDER BY pageName";
			$rPagesResult = dbQuery($sPagesQuery);

			// start of track users' activity in nibbles 
			$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
			mysql_connect ($host, $user, $pass); 
			mysql_select_db ($dbase); 
	
			$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"View report: between $sDateTimeFrom and  $sDateTimeTo\")"; 
			$rResult = dbQuery($sAddQuery); 
			echo  dbError(); 
			mysql_connect ($reportingHost, $reportingUser, $reportingPass); 
			mysql_select_db ($reportingDbase); 
			// end of track users' activity in nibbles		
			
			$i = 0;
			while ($oPagesRow = dbFetchObject($rPagesResult)) {
				
				// prepare Categories of this offer
				$sCheckboxName = "page_".$oPagesRow->id;
				
				$iCheckboxValue = $$sCheckboxName;
				
				if ($iCheckboxValue != '') {
					$aPagesArray[$i] = $iCheckboxValue;
					$sPagesString .= $iCheckboxValue.",";
					$i++;
				}
			}
			
			$sPagesString = substr($sPagesString, 0, strlen($sPagesString)-1);

			if ($sPagesString != '') {
			
			$u = 0;	
			for ($i = 0; $i < 24; $i++) {
			
				$iAddMonths = $i + 1;	
			// loop for monthwise
			$sSelectQuery1 = "SELECT DISTINCT userDataHistory.email
							 FROM  userDataHistory, otDataHistory
							 WHERE userDataHistory.email = otDataHistory.email
							 AND   otDataHistory.dateTimeAdded BETWEEN DATE_ADD('$sDateTimeFrom', INTERVAL $i MONTH) AND DATE_ADD('$sDateTimeFrom', INTERVAL $iAddMonths MONTH)
							 AND   otDataHistory.dateTimeAdded < '$sDateTimeTo'
							 AND   otDataHistory.pageId IN ($sPagesString)";
			$rSelectResult1 = dbQuery($sSelectQuery1);
			
			if (dbNumRows($rSelectResult1) >0 ) {
				
			echo dbError();
			
			while ($oSelectRow1 = dbFetchObject($rSelectResult1)) {
				$sEmail = $oSelectRow1->email;
				$aUserArray[$u][0] = $sEmail;
				$aUserArray[$u][1] = 'userData';
				$u++;
			}
			
			} else {
				break;
			} 
			}
			
			$sSelectQuery2 = "SELECT email
							  FROM	 sweepStakesEntries
							  WHERE dateTimeAdded BETWEEN '$sDateTimeFrom' AND '$sDateTimeTo'";
			$rSelectResult2 = dbQuery($sSelectQuery2);
			//echo "<BR>".$sSelectQuery2;
			
			echo dbError();
			while ($oSelectRow2 = dbFEtchObject($rSelectResult2)) {
				$sEmail = $oSelectRow2->email;
				$aUserArray[$i][0] = $sEmail;
				$aUserArray[$i][1] = 'sweeps';
				$i++;
			}
			
			
			}
			
			if ( count($aUserArray) >0) {
				
				$iRandomUser = array_rand($aUserArray);
				
				$sWinnerEmail = $aUserArray[$iRandomUser][0];
				$sWinnerFrom = $aUserArray[$iRandomUser][1];
				
				if ($sWinnerFrom == 'sweeps') {
					$sWinnerQuery = "SELECT * 
									 FROM sweepStakesEntries
									 WHERE email = '$sWinnerEmail'";
					$rWinnerResult = dbQuery($sWinnerQuery);
										
				} else {
					$sWinnerQuery = "SELECT * 
									 FROM userDataHistory
									 WHERE email = '$sWinnerEmail'";
					$rWinnerResult = dbQuery($sWinnerQuery);
					
				}
				
				echo dbError();
				while ($oWinnerRow = dbFetchObject($rWinnerResult)) {
					$sFirst = $oWinnerRow->first;
					$sLast = $oWinnerRow->last;
					$sAddress = $oWinnerRow->address;
					$sAddress2 = $oWinnerRow->address2;
					$sCity = $oWinnerRow->city;
					$sState = $oWinnerRow->state;
					$sZip = $oWinnerRow->zip;
					$sPhone = $oWinnerRow->phoneNo;
					$sDateTimeAdded = $oWinnerRow->dateTimeAdded;
					
					$sDrawResult = "Email: $sWinnerEmail
									<BR>First Name: $sFirst  &nbsp; &nbsp; Last Name: $sLast
								<BR>Address: $sAddress &nbsp; &nbsp; Address2: $sAddress2
								<BR>City: $sCity &nbsp; &nbsp; State: $sState &nbsp; &nbsp; Zip: $sZip
								<BR>Phone: $sPhone
								<BR>Date Added: $sDateTimeAdded";
					
								
					
				}
			}
						
	}

	
	
// Prepare checkboxes for Pages
$sPagesQuery = "SELECT *
			    FROM   otPages
				ORDER BY pageName";
$rPagesResult = dbQuery($sPagesQuery);

$j = 0;
$sPageCheckboxes = "<tr>";
while ($oPagesRow = dbFetchObject($rPagesResult)) {
	$iPageId = $oPagesRow->id;
	$sPageName = $oPagesRow->pageName;
	
	$sTempPageVar = "page_".$iPageId;
	$sTempPageValue = ${$sTempPageVar};
	
	if ($sTempPageValue == $iPageId) {
		$sPageChecked  = "checked";
	} else {
		$sPageChecked = "";
	}
	
	if ($j%3 == 0) {
		if ($j != 0) {
			$sPageCheckboxes .= "</tr>";
		}
		$sPageCheckboxes .= "<tr>";
	}
		
	$sPageCheckboxes .= "<td width=5% valign=top><input type=checkbox name='page_".$oPagesRow->id."' value='".$oPagesRow->id."' $sPageChecked></td><td  width=28%>$sPageName</td>";
	$j++;
}
$sPageCheckboxes .= "</tr>";




	include("../../includes/adminHeader.php");
		
?>

<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $sHidden;?>
<table cellpadding=5 cellspacing=0 width=30% align=center>
<TR><TD class=message><?php echo $sDrawResult;?></td></tr>
</tr>
</table>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>

<tr><td>Date From</td><td><select name=iMonthFrom><?php echo $sMonthFromOptions;?>
	</select> &nbsp;<select name=iDayFrom><?php echo $sDayFromOptions;?>
	</select> &nbsp;<select name=iYearFrom><?php echo $sYearFromOptions;?>
	</select></td><td>Date To</td>
	<td><select name=iMonthTo><?php echo $sMonthToOptions;?>
	</select> &nbsp;<select name=iDayTo><?php echo $sDayToOptions;?>
	</select> &nbsp;<select name=iYearTo><?php echo $sYearToOptions;?>
	</select></td></tr>	
</table>
<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>	
	<tr>
	<td colspan=2 class=header>Assign Offer To The Following OT Pages</td>
	</tr>
	</table>
<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>	
	
	<?php echo $sPageCheckboxes;?>
		
</table>

<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>	
<tr><td><input type=submit name=sSubmit value='Draw Winner' > 
</tr>

</table>

</form>

<?php

	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}

?>
<?php
mysql_connect('64.132.70.20','nibbles','#a!!yu5');
mysql_select_db('nibbles');

while (list($key,$val) = each($HTTP_GET_VARS)) {
	$$key = $val;
}
while (list($key,$val) = each($HTTP_POST_VARS)) {
	$$key = $val;
}

$iCurrYear = date('Y');
$iCurrMonth = date('m');
$iCurrDay = date('d');
$iCurrHH = date('H');
$iCurrMM = date('i');
$iCurrSS = date('s');

$sReportContent = '';
if (!$sViewReport) {
	$iMonthTo = substr( DateAdd( "d", -1, date('Y')."-".date('m')."-".date('d') ), 5, 2);
	$iDayTo = substr( DateAdd( "d", -0, date('Y')."-".date('m')."-".date('d') ), 8, 2);
	$iYearTo = substr( DateAdd( "d", -1, date('Y')."-".date('m')."-".date('d') ), 0, 4);
	$iYearFrom = substr( DateAdd( "d", -1, date('Y')."-".date('m')."-".date('d') ), 0, 4);
	$iMonthFrom = substr( DateAdd( "d", -1, date('Y')."-".date('m')."-".date('d') ), 5, 2);
	$iDayFrom = 1;
}

$aGblMonthsArray = array('Jan','Feb','Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
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




$sSortLink = $_SERVER['PHP_SELF']."?iYearFrom=$iYearFrom&iMonthFrom=$iMonthFrom&iDayFrom=$iDayFrom&iYearTo=$iYearTo&iMonthTo=$iMonthTo&iDayTo=$iDayTo&sViewReport=$sViewReport";
if ($sViewReport != '') {
	if (checkdate($iMonthFrom, $iDayFrom, $iYearFrom) && checkdate($iMonthTo, $iDayTo, $iYearTo)) {
		$aUrl = array();
		$i = 0;
		$iStartSec = strtotime("$iYearFrom-$iMonthFrom-$iDayFrom 00:00:00");
		$iEndSec = strtotime("$iYearTo-$iMonthTo-$iDayTo 23:59:59");
		
		$sReportQuery = "SELECT DISTINCT url FROM siteMonitor WHERE startTime >= $iStartSec AND endTime <= $iEndSec";
		$rReportResult = mysql_query($sReportQuery);
		echo mysql_error();
		while ($oReportRow = mysql_fetch_object($rReportResult)) {
			$aUrl[$i] = $oReportRow->url;
			$i++;
		}
		
		$sHourlyReport = '';
		if ($sHour == 'Y' && $sHourlyUrl !='') {
			for ($x=0; $x<24; $x++) {
				if ($x<10) { $x = '0'.$x; }
				$iHourlyStart = strtotime("$iYearFrom-$iMonthFrom-$iDayFrom $x:00:00");
				$iHourlyEnd = strtotime("$iYearTo-$iMonthTo-$iDayTo $x:59:59");
				
				$sReportQuery = "SELECT * FROM siteMonitor 
						WHERE startTime >= $iHourlyStart AND endTime <= $iHourlyEnd
						AND url IN (\"$sHourlyUrl\")";
				$rReportResult = mysql_query($sReportQuery);
				echo mysql_error();
				
				
				$fLoadTimeMean = 0.0;
				$fLoadTimeMedian = 0.0;
				$fLoadTimeMode = '';
				$fMedianLoadTime = 0;
				$fSizeMean = 0.0;
				$fSizeMedian = 0.0;
				$iCount = 0;
				$aSize = array();
				$aLoadTime = array();
				
				if ($sBgcolorClass == "WHITE") {
					$sBgcolorClass = "C9C9C9";
				} else {
					$sBgcolorClass = "WHITE";
				}
				
				
				if (mysql_num_rows($rReportResult) > 0) {
					while ($oReportRow = mysql_fetch_object($rReportResult)) {
						$aSize[$iCount] = $oReportRow->sizeOfPage;
						$aLoadTime[$iCount] = $oReportRow->diff;
						
						$iCount++;
						$fLoadTimeMean += $oReportRow->diff;
						$fSizeMean += $oReportRow->sizeOfPage;
						$urlToDisplay = $oReportRow->url;
						$iUrlId = $oReportRow->id;
					}
					
					// calculate mode
					$temp2 = max(array_values((array_count_values($aLoadTime))));
					$fLoadTimeMode = $aLoadTime[$temp2];
					if ($fLoadTimeMode =='') { $fLoadTimeMode = max($aLoadTime); }
					
		
					// calculate mean (average)
					$fSizeMean = $fSizeMean / $iCount;
					$fLoadTimeMean = $fLoadTimeMean / $iCount;
					
					// calculate median (middle number after sorting)
					sort($aLoadTime);
					$n = count($aLoadTime);
					$h = intval($n / 2);
					if($n % 2 == 0) {
						$fMedianLoadTime = ($aLoadTime[$h] + $aLoadTime[$h-1]) / 2;
					} else {
						$fMedianLoadTime = $aLoadTime[$h];
					}
					
					$s56K = ($fSizeMean * 8000) / 56000;
					$sT1 = ($fSizeMean * 8000) / 1560000;
					$s56K = number_format($s56K, 2, '.', '');
					$sT1 = number_format($sT1, 2, '.', '');

					$sHourlyReport .= "<tr bgcolor=$sBgcolorClass>
										<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$x:00:00 - $x:59:59</td>
										<td>".substr($fLoadTimeMean,0,5)."</td>
										<td>$fMedianLoadTime</td>
										<td>$fLoadTimeMode</td>
										<td>".min($aLoadTime)."</td>
										<td>".max($aLoadTime)."</td>
										
										<td>".substr($fSizeMean,0,5)." kb</td>
										<td>".min($aSize)." kb</td>
										<td>".max($aSize)." kb</td>
										
										<td>$s56K</td>
										<td>$sT1</td>
									</tr>";
				}
			}
		}
		
		if ($sHour == 'Y') {
			$sHour = 'N';
		} else {
			$sHour = 'Y';
		}
		
		foreach ($aUrl as $url) {
			$sReportQuery = "SELECT * FROM siteMonitor 
					WHERE startTime >= $iStartSec AND endTime <= $iEndSec
					AND url IN (\"$url\")";
			$rReportResult = mysql_query($sReportQuery);
			echo mysql_error();
			
			
			$fLoadTimeMean = 0.0;
			$fLoadTimeMedian = 0.0;
			$fLoadTimeMode = '';
			$fMedianLoadTime = 0;
			$fSizeMean = 0.0;
			$fSizeMedian = 0.0;
			$iCount = 0;
			$aSize = array();
			$aLoadTime = array();
			
			if ($sBgcolorClass == "WHITE") {
				$sBgcolorClass = "C9C9C9";
			} else {
				$sBgcolorClass = "WHITE";
			}
	
			while ($oReportRow = mysql_fetch_object($rReportResult)) {
				$aSize[$iCount] = $oReportRow->sizeOfPage;
				$aLoadTime[$iCount] = $oReportRow->diff;
				
				$iCount++;
				$fLoadTimeMean += $oReportRow->diff;
				$fSizeMean += $oReportRow->sizeOfPage;
				$urlToDisplay = $oReportRow->url;
				$iUrlId = $oReportRow->id;
			}
			
			// calculate mode
			$temp2 = max(array_values((array_count_values($aLoadTime))));
			$fLoadTimeMode = $aLoadTime[$temp2];
			if ($fLoadTimeMode =='') { $fLoadTimeMode = max($aLoadTime); }
			

			// calculate mean (average)
			$fSizeMean = $fSizeMean / $iCount;
			$fLoadTimeMean = $fLoadTimeMean / $iCount;
			
			// calculate median (middle number after sorting)
			sort($aLoadTime);
			$n = count($aLoadTime);
			$h = intval($n / 2);
			if($n % 2 == 0) {
				$fMedianLoadTime = ($aLoadTime[$h] + $aLoadTime[$h-1]) / 2;
			} else {
				$fMedianLoadTime = $aLoadTime[$h];
			}
			
			$s56K = ($fSizeMean * 8000) / 56000;
			$sT1 = ($fSizeMean * 8000) / 1560000;
			$s56K = number_format($s56K, 2, '.', '');
			$sT1 = number_format($sT1, 2, '.', '');
			
			$sReportContent .= "<tr bgcolor=$sBgcolorClass>
								<td><a href='$sSortLink&sHour=$sHour&sHourlyUrl=$urlToDisplay'>$urlToDisplay</a></td>
								<td>".substr($fLoadTimeMean,0,5)."</td>
								<td>$fMedianLoadTime</td>
								<td>$fLoadTimeMode</td>
								<td>".min($aLoadTime)."</td>
								<td>".max($aLoadTime)."</td>
								
								<td>".substr($fSizeMean,0,5)." kb</td>
								<td>".min($aSize)." kb</td>
								<td>".max($aSize)." kb</td>
								
								<td>$s56K</td>
								<td>$sT1</td>
							</tr>";
			if ($sHourlyUrl == $urlToDisplay) {
				$sReportContent .= "$sHourlyReport";
			}
		}
	}
}



echo "
<script language=JavaScript>
function funcReportClicked(btnClicked) {
	if (btnClicked == 'report') {
		document.form1.sViewReport.value = \"View Report\";
	}
	var repClicked = document.form1.reportClicked.value;
	if (repClicked == '') {
		document.form1.reportClicked.value = 'Y';
		document.form1.submit();
	} else {
		alert('Report is running... Please Wait');
	}
}
</script>";


// Pass Date in format yyyy-mm-dd returns yyyy-mm-dd
function DateAdd($intervalType,$interval,$date) {
	$year = substr($date,0,4);
	$month = substr($date,5,2);
	$day = substr($date,8,2);
	switch($intervalType) {
		case "y":
		$time = mktime(0,0,0, $month, $day, $year+$interval);
		break;
		case "m":
		$time = mktime(0,0,0,$month+$interval, $day, $year);
		break;
		case "d":
		$time = mktime(0,0,0,$month, $day+$interval, $year);
	}

	$date = getdate($time);
	if ($date["mon"] <10)
	$month = "0".$date["mon"];
	else
	$month = $date["mon"];
	if ($date["mday"] <10)
	$day = "0".$date["mday"];
	else
	$day = $date["mday"];
	return $date["year"]."-".$month."-".$day;

}

function getMicroTime() {
	list($usec, $sec) = explode(" ",microtime());
	return ((float)$usec + (float)$sec);
}

?>
<html>
<head>
<title>Load Times Report</title>
</head>
<body>
<form name=form1 action='<?php echo $_SERVER['PHP_SELF'];?>'>
<input type=hidden name=reportClicked>
<input type=hidden name=sViewReport>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>

<tr><td>Date From</td><td><select name=iMonthFrom><?php echo $sMonthFromOptions;?>
	</select> &nbsp;<select name=iDayFrom><?php echo $sDayFromOptions;?>
	</select> &nbsp;<select name=iYearFrom><?php echo $sYearFromOptions;?>
	</select></td><td>Date To</td>
	<td><select name=iMonthTo><?php echo $sMonthToOptions;?>
	</select> &nbsp;<select name=iDayTo><?php echo $sDayToOptions;?>
	</select> &nbsp;<select name=iYearTo><?php echo $sYearToOptions;?>
	</select></td></tr>

<tr><td colspan=2><input type=button name=sSubmit value='View Report' onClick="funcReportClicked('report');">
	<td colspan=2></td>
</tr>
</table>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td>
<table cellpadding=0 cellspacing=0 bgcolor=c9c9c9 width=95% align=center border=1 bordercolor=#000000>
		<tr><td>
		<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=100% align=center>
	<tr><td>
	<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=100% align=center>
	<tr><td colspan=11 align=center><h4>Site Monitor Report<BR>From <?php echo "$sDateFrom to $sDateTo";?></h4></td></tr>
	<tr><td colspan=11><b>Run Date / Time: <?php echo "$iCurrMonth-$iCurrDay-$iCurrYear $iCurrHH:$iCurrMM:$iCurrSS";?></b></td></tr>
	<tr><?php echo $sDateSentHeader;?>
		<td valign="top"><h5>URL</h5></td>
		<td valign="top"><h5>Mean Load Time (Sec)</h5></td>
		<td valign="top"><h5>Median Load Time (Sec)</h5></td>
		<td valign="top"><h5>Mode Load Time (Sec)</h5></td>
		<td valign="top"><h5>Min Load Time (Sec)</h5></td>
		<td valign="top"><h5>Max Load Time (Sec)</h5></td>
		
		<td valign="top"><h5>Mean Page Size</h5></td>
		<td valign="top"><h5>Min Page Size</h5></td>
		<td valign="top"><h5>Max Page Size</h5></td>
		
		<td valign="top"><h5>56K</td>
		<td valign="top"><h5>T1</td>
	</tr>

<?php echo $sReportContent;?>

<tr><td colspan=11 align=left><hr color=#000000></td></tr>	
	<tr><td colspan=11 class=header><br><b>Notes -</b>
	</td></tr>
	<tr><td colspan=11>
		<b>Mean:</b> The mean is the arithmetic average, the average you are probably used 
		to finding for a set of numbers - add up the numbers and divide by how many there are.
		<br><b>Median:</b> The median is the number in the middle. In order to find the median, 
		you have to put the values in order from lowest to highest, then find the number that is exactly in the middle.
		<br><b>Mode:</b> The mode is the value that occurs most often.
		<br><b>Min: </b>The smallest number in a set.
		<br><b>Max: </b>The highest number in a set.
		</td></tr>
		</table></td></tr></table></td></tr>
	</table>
</td></tr>
</table>
</form>
</body>
</html>

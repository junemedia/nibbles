<?php

/*********

Script to Display

**********/

include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");
include("$sGblLibsPath/stringFunctions.php");

$sPageTitle = "Export Fahime";

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
	
	// assume today is friday
	$sLastFriday = DateAdd("d", -7, date('Y')."-".date('m')."-".date('d'));
	$sYesterday = DateAdd("d", -1, date('Y')."-".date('m')."-".date('d'));
	
	
	if (!($sGetList || $sExport)) {
		
		$iYearFrom = substr( $sLastFriday, 0, 4);
		$iMonthFrom = substr( $sLastFriday, 5, 2);
		$iDayFrom = substr( $sLastFriday, 8, 2);
		
		$iYearTo = substr( $sYesterday, 0, 4);
		$iMonthTo = substr( $sYesterday, 5, 2);
		$iDayTo = substr( $sYesterday, 8, 2);
		
	}
	
	// prepare month options for From and To date
	for ($i = 0; $i < count($aGblMonthsArray); $i++) {
		
		$value = $i+1;
		
		if ($value < 10) {
			$value = "0".$value;
		}
		
		if ($value == $iMonthFrom) {
			$fromSel = "selected";
		} else {
			$fromSel = "";
		}
		if ($value == $iMonthTo) {
			$toSel = "selected";
		} else {
			$toSel = "";
		}
		
		$sMonthFromOptions .= "<option value='$value' $fromSel>$aGblMonthsArray[$i]";
		$sMonthToOptions .= "<option value='$value' $toSel>$aGblMonthsArray[$i]";
	}
	
	// prepare day options for From and To date
	for ($i = 1; $i <= 31; $i++) {
		
		if ($i < 10) {
			$value = "0".$i;
		} else {
			$value = $i;
		}
		
		if ($value == $iDayFrom) {
			$fromSel = "selected";
		} else {
			$fromSel = "";
		}
		if ($value == $iDayTo) {
			$toSel = "selected";
		} else {
			$toSel = "";
		}
		$sDayFromOptions .= "<option value='$value' $fromSel>$i";
		$sDayToOptions .= "<option value='$value' $toSel>$i";
	}
	
	// prepare year options
	for ($i = $iCurrYear; $i >= $iCurrYear-5; $i--) {
		
		if ($i == $iYearFrom) {
			$fromSel = "selected";
		} else {
			$fromSel ="";
		}
		if ($i == $iYearTo) {
			$toSel = "selected";
		} else {
			$toSel ="";
		}
		
		$sYearFromOptions .= "<option value='$i' $fromSel>$i";
		$sYearToOptions .= "<option value='$i' $toSel>$i";
	}
	if (checkdate($iMonthFrom, $iDayFrom, $iYearFrom) && checkdate($iMonthTo, $iDayTo, $iYearTo) &&  $sGetList) {
		
		$sDateFrom = "$iYearFrom-$iMonthFrom-$iDayFrom";
		$sDateTo = "$iYearTo-$iMonthTo-$iDayTo";
		
		if ($sGetList) {
		$sDropTableQuery = "DROP TABLE IF EXISTS listOrders.$sExportTableName";
		$rDropTableResult = dbQuery($sDropTableQuery);
		
		$sQuery = " CREATE TABLE listOrders.$sExportTableName AS SELECT n.first, n.last, n.address, n.address2, n.city, n.state, n.zip
							FROM nibbles.userDataHistory AS n				
							WHERE date_format(n.dateTimeAdded,'%Y-%m-%d') BETWEEN '$sDateFrom' AND '$sDateTo'";
		if ($iLimit) {
			$sQuery .= " LIMIT 0, $iLimit ";
		}
	
		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

		$sAddLogQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Drop & Create Imports: $sGetList $sExport Export Filename: $sExportFileName sql: $sDropTableQuery $sQuery\")"; 
		$rLogResult = dbQuery($sAddLogQuery); 
		echo  dbError(); 
		// end of track users' activity in nibbles		
	
	
		$rResult = dbQuery($sQuery);
		
		$iNumRecords = 0;
		// count query
		$sCountQuery = "SELECT count(*) AS counts
						FROM   listOrders.$sExportTableName";
		$rCountResult = dbQuery($sCountQuery);
		while ($oCountRow = dbFetchObject($rCountResult)) {
			$iNumRecords = $oCountRow->counts;
		}
		
		$sMessage = "$iNumRecords records are inserted in the table";
		
		} 
		// if any rows inserted in the table, export data from the table
		
	} 
		
	if ($sExport) {
		$sExportQuery = "SELECT *
						FROM   listOrders.$sExportTableName";
		
		$rExportResult =  dbQuery($sExportQuery);
		if ( dbNumRows($rExportResult)) {
			while ($oExportRow = dbFetchObject($rExportResult)) {
				$sExportData .= "\"$oExportRow->first\",\"$oExportRow->last\",\"$oExportRow->address\",";
				$sExportData .= "\"$oExportRow->address2\",\"$oExportRow->city\",\"$oExportRow->state\",\"$oExportRow->zip\"\r\n";
			}
			
			header("Content-type: text/plain");
			header("Content-Disposition: attachment; filename=$sExportFileName.csv");
			header("Content-Description: Excel output");
			echo $sExportData;
			// if didn't exit, all the html page content will be saved as excel file.
			exit();
		}
	}
		
	if (!(isset($sExportTableName))) {
		$sExportTableName = "list_orders_fahime_".date('m').date('d').date('Y');
	}
	
	if (!(isset($sExportFileName))) {
		$sExportFileName = "list_orders_fahime_".date('m').date('d').date('Y');
		
	}
	
	if (!(isset($iLimit))) {
		$iLimit = 15000;
	}
	
	include("../../includes/adminHeader.php");
	
?>

<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $sHidden;?>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>

<tr><td>Date From</td><td><select name=iMonthFrom><?php echo $sMonthFromOptions;?>
	</select> &nbsp;<select name=iDayFrom><?php echo $sDayFromOptions;?>
	</select> &nbsp;<select name=iYearFrom><?php echo $sYearFromOptions;?>
	</select></td><td>Date To</td>
	<td><select name=iMonthTo><?php echo $sMonthToOptions;?>
	</select> &nbsp;<select name=iDayTo><?php echo $sDayToOptions;?>
	</select> &nbsp;<select name=iYearTo><?php echo $sYearToOptions;?>
	</select></td></tr>		
	<tr><td>Export Table Name</td>
		<td colspan=3><input type=text name='sExportTableName'  value='<?php echo $sExportTableName;?>' size=40></td>
	</tr>
	
	<tr><td>Export File Name</td>
		<td colspan=3><input type=text name='sExportFileName'  value='<?php echo $sExportFileName;?>' size=40></td>
	</tr>
	<tr><td>Max No. Of Records</td>
		<td colspan=3><input type=text name='iLimit'  value='<?php echo $iLimit;?>'></td>
	</tr>
		
<tr><td colspan=3><input type=submit name=sGetList value='Get List'> &nbsp; &nbsp; <input type=submit name=sExport value='Export'>
	</td>
	</tr>
</table>


</form>

<?php

} else {
	echo "You are not authorized to access this page...";
}
?>
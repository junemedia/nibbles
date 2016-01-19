<?php

/*********

Script to Display error stats
$Author: smita $
$Id: index.php,v 1.3 2005/03/18 15:49:22 smita Exp $

**********/


include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");
include("$sGblLibsPath/stringFunctions.php");
include("$sGblIncludePath/reportInclude.php");

$iScriptStartTime = getMicroTime();

$sPageTitle = "Error Stats Report";

session_start();

mysql_connect ($reportingHost, $reportingUser, $reportingPass);
mysql_select_db ($reportingDbase);


if (hasAccessRight($iMenuId) || isAdmin()) {

	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>";


	$iCurrYear = date('Y');
	$iCurrMonth = date('m');
	$iCurrDay = date('d');

	$iCurrHH = date('H');
	$iCurrMM = date('i');
	$iCurrSS = date('s');

	$sRunDateAndTime = "$iCurrMonth-$iCurrDay-$iCurrYear $iCurrHH:$iCurrMM:$iCurrSS";

	$sYesterday = DateAdd("d", -1, date('Y')."-".date('m')."-".date('d'));

	if (!($iYearFrom)) {

		$iYearFrom = substr( $sYesterday, 0, 4);
		$iMonthFrom = substr( $sYesterday, 5, 2);
		$iDayFrom = substr( $sYesterday, 8, 2);
		$iYearTo = substr( $sYesterday, 0, 4);
		$iMonthTo = substr( $sYesterday, 5, 2);
		$iDayTo = substr( $sYesterday, 8, 2);
	}

	if (DateDiff("d",mktime(0,0,0,date('m'),date('d'),date('Y')),mktime(0,0,0,$iMonthTo,$iDayTo,$iYearTo)) >= 0 || $iYearTo=='') {
		$iYearTo = substr( $sYesterday, 0, 4);
		$iMonthTo = substr( $sYesterday, 5, 2);
		$iDayTo = substr( $sYesterday, 8, 2);
	}

	if (DateDiff("d",mktime(0,0,0,date('m'),date('d'),date('Y')),mktime(0,0,0,$iMonthFrom,$iDayFrom,$iYearFrom)) >= 0 || $iYearFrom=='') {
		$iYearFrom = substr( $sYesterday, 0, 4);
		$iMonthFrom = substr( $sYesterday, 5, 2);
		$iDayFrom = substr( $sYesterday, 8, 2);
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



	if (checkdate($iMonthFrom, $iDayFrom, $iYearFrom) && checkdate($iMonthTo, $iDayTo, $iYearTo)) {
		if ($sAllowReport == 'N') {
			$sMessage = "Server Load Is High. Please check back soon...";
		} else {

			$sDateFrom = "$iYearFrom-$iMonthFrom-$iDayFrom";
			$sDateTo = "$iYearTo-$iMonthTo-$iDayTo";
			//$sDateTimeFrom = "$iYearFrom-$iMonthFrom-$iDayFrom"." 00:00:00";
			//$sDateTimeTo = "$iYearTo-$iMonthTo-$iDayTo"." 23:59:59";

			// Set Default order column
			if (!($sOrderColumn)) {				
				$sOrderColumn = "noOfRejects";
				$sNoOfRejectsOrder = "DESC";				
			}

			// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
			if (!($sCurrOrder)) {
				switch ($sOrderColumn) {
					case "valueInvalidated" :
					$sCurrOrder = $sValueInvalidatedOrder;
					$sValueInvalidatedOrder = ($sValueInvalidatedOrder != "DESC" ? "DESC" : "ASC");
					break;
					case "function" :
					$sCurrOrder = $sFunctionOrder;
					$sFunctionOrder = ($sFunctionOrder != "DESC" ? "DESC" : "ASC");
					break;	
					case "pageName" :
					$sCurrOrder = $sPageNameOrder;
					$sPageNameOrder = ($sPageNameOrder != "DESC" ? "DESC" : "ASC");
					break;				
					case "sourceCode" :
					$sCurrOrder = $sSourceCodeOrder;
					$sSourceCodeOrder = ($sSourceCodeOrder != "DESC" ? "DESC" : "ASC");
					break;
					case "errorDate" :
					$sCurrOrder = $sErrorDateOrder;
					$sErrorDateOrder = ($sErrorDateOrder != "DESC" ? "DESC" : "ASC");
					break;
					case "noOfRejects" :
					case "percentRejects" :					
					default:
					$sCurrOrder = $sNoOfRejectsOrder;
					$sNoOfRejectsOrder = ($sNoOfRejectsOrder != "DESC" ? "DESC" : "ASC");
				}
			}

			// Prepare filter part of the query if filter/exclude specified...
			/*
			if ($sFilter != '') {

			$sFilterPart .= " AND ( ";

			switch ($sSearchIn) {
			case "valueInvalidated" :
			$sFilterPart .= ($iExactMatch) ? "valueInvalidated = '$sFilter'" : "valueInvalidated like '%$sFilter%'";
			break;
			case "function" :
			$sFilterPart .= ($iExactMatch) ? "function = '$sFilter'" : "function like '%$sFilter%'";
			break;
			case "ipAddress" :
			$sFilterPart .= ($iExactMatch) ? "ipAddress = '$sFilter'" : "ipAddress like '%$sFilter%'";
			break;
			case "sourceCode" :
			$sFilterPart .= ($iExactMatch) ? "sourceCode = '$sFilter'" : "sourceCode like '%$sFilter%'";
			break;
			case "errorDateTime" :
			$sFilterPart .= ($iExactMatch) ? "errorDateTime = '$sFilter'" : "errorDateTime like '%$sFilter%'";
			break;

			default:
			$sFilterPart .= ($iExactMatch) ? "valueInvalidated = '$sFilter' || function = '$sFilter' || ipAddress = '$sFilter' || sourceCode = '$sFilter' || errorDateTime = '$sFilter'" : " valueInvalidated like '%$sFilter%' || function LIKE '%$sFilter%' || ipAddress like '%$sFilter%' || sourceCode like '%$sFilter%' || errorDateTime like '%$sFilter%'";
			}

			$sFilterPart .= ") ";
			}

			if ($sExclude != '') {
			$sFilterPart .= " AND ( ";
			switch ($sExclude) {
			case "valueInvalidated" :
			$sFilterPart .= "valueInvalidated NOT LIKE '%$sExclude%'";
			break;
			case "function" :
			$sFilterPart .= "function NOT LIKE '%$sExclude%'";
			break;
			case "ipAddress" :
			$sFilterPart .= "ipAddress NOT LIKE '%$sExclude%'";
			break;
			case "sourceCode" :
			$sFilterPart .= "sourceCode NOT LIKE '%$sExclude%'";
			break;
			case "errorDateTime" :
			$sFilterPart .= "errorDateTime NOT LIKE '%$sExclude%'";
			break;
			//	case "dateLastUpdated" :
			//	$sFilterPart .= "dateLastUpdated NOT LIKE '%$sExclude%'";
			//	break;
			default:
			$sFilterPart .= "valueInvalidated NOT LIKE '%$sExclude%' && function NOT LIKE '%$sExclude%'  && ipAddress NOT LIKE '%$sExclude%' && sourceCode NOT LIKE '%$sExclude%' && errorDateTime NOT LIKE '%$sExclude%'" ;
			}
			$sFilterPart .= " ) ";

			}

			*/

			// Specify Page no. settings
			if (!($iRecPerPage)) {
				$iRecPerPage = 50;
			}
			if (!($iPage)) {
				$iPage = 1;
			}

			$sSortLink = $PHP_SELF."?iMenuId=$iMenuId&iYearFrom=$iYearFrom&iMonthFrom=$iMonthFrom&iDayFrom=$iDayFrom
					&iYearTo=$iYearTo&iMonthTo=$iMonthTo&iDayTo=$iDayTo&iRecPerPage=$iRecPerPage
					&iReportByFunction=$iReportByFunction&iReportByPage=$iReportByPage&iReportBySrc=$iReportBySrc
					&iReportByValue=$iReportByValue";


			$sErrorLogQuery = "SELECT errorDate, sum(counts) as noOfRejects
					  			 FROM   errorStats
					   WHERE  errorDate BETWEEN '$sDateFrom' AND '$sDateTo'";

			if ($iReportByFunction || $iReportByPage || $iReportBySrc || $iReportByValue ) {
				$sGroupBy = " GROUP BY ";

				if ($iReportByFunction) {
					$sErrorLogQuery = eregi_replace("SELECT", "SELECT function, ", $sErrorLogQuery);
					$sGroupBy .= " function, ";
					$sFunctionHeader = "<td width=180 class=header><a href=\"$sSortLink&sOrderColumn=function&sFunctionOrder=$sFunctionOrder\" class=header>Validation Function</a></td>";
				}

				if ($iReportByPage) {
					$sErrorLogQuery = eregi_replace("SELECT", "SELECT pageName, ", $sErrorLogQuery);
					$sErrorLogQuery = eregi_replace("errorStats", "errorStats LEFT JOIN otPages ON errorStats.pageId = otPages.id ", $sErrorLogQuery);
					//$sErrorLogQuery = eregi_replace("WHERE", "WHERE otPages.id = errorStats.pageId AND ", $sErrorLogQuery);
					$sGroupBy .= "pageId, ";
					$sPageHeader = "<td width=180 class=header><a href=\"$sSortLink&sOrderColumn=pageName&sPageNameOrder=$sPageNameOrder\" class=header>Page Name</a></td>";
				}

				if ($iReportBySrc) {
					$sErrorLogQuery = eregi_replace("SELECT", "SELECT errorStats.sourceCode, ", $sErrorLogQuery);
					$sGroupBy .= "sourceCode, ";
					$sSrcHeader = "<td width=180 class=header><a href=\"$sSortLink&sOrderColumn=sourceCode&sSourceCodeOrder=$sSourceCodeOrder\" class=header>Source Code</a></td>";
				}

				if ($iReportByValue) {
					$sErrorLogQuery = eregi_replace("SELECT", "SELECT valueInvalidated, ", $sErrorLogQuery);
					$sGroupBy .= "valueInvalidated, ";
					$sValueHeader = "<td width=180 class=header><a href=\"$sSortLink&sOrderColumn=valueInvalidated&sValueInvalidatedOrder=$sValueInvalidatedOrder\" class=header>Value Invalidated</a></td>";
				}

				$sGroupBy = substr($sGroupBy, 0, strlen($sGroupBy)-2);

				$sErrorLogQuery .= $sGroupBy;
			} else {
				$sErrorLogQuery .= " GROUP BY errorDate";
			}
			$sErrorLogQuery .= " ORDER BY $sOrderColumn $sCurrOrder ";

			// start of track users' activity in nibbles 
			$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
			mysql_connect ($host, $user, $pass); 
			mysql_select_db ($dbase); 
	
			$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"View report: $sErrorLogQuery\")"; 
			$rResult = dbQuery($sAddQuery); 
			echo  dbError(); 
			mysql_connect ($reportingHost, $reportingUser, $reportingPass); 
			mysql_select_db ($reportingDbase); 
			// end of track users' activity in nibbles		
			
			
			
			$rErrorLogResult = dbQuery($sErrorLogQuery);
			//echo $sErrorLogQuery. mysql_error();

			$iNumRecords = dbNumRows($rErrorLogResult);

			$iTotalPages = ceil($iNumRecords/$iRecPerPage);

			// If current page no. is greater than total pages move to the last available page no.
			if ($iPage > $iTotalPages) {
				$iPage = $iTotalPages;
			}

			$iStartRec = ($iPage-1) * $iRecPerPage;
			$iEndRec = $iStartRec + $iRecPerPage -1;

			if ($iNumRecords > 0) {
				$sCurrentPage = " Page $iPage "."/ $iTotalPages";
			}

		
			// use query to fetch only the rows of the page to be displayed
			$sErrorLogQuery .= " LIMIT $iStartRec, $iRecPerPage";

			$rErrorLogResult = dbQuery($sErrorLogQuery);
			$iTotalNoOfRejects = 0;
			echo dbError();
			if ( dbNumRows($rErrorLogResult) >0) {
				if ($iTotalPages > $iPage ) {
					$iNextPage = $iPage+1;
					$sNextPageLink = "<a href='".$sSortLink."&sOrderColumn=$sOrderColumn&iPage=$iNextPage&sCurrOrder=$sCurrOrder' class=header>Next</a>";
					$sLastPageLink = "<a href='".$sSortLink."&sOrderColumn=$sOrderColumn&iPage=$iTotalPages&sCurrOrder=$sCurrOrder' class=header>Last</a>";
				}
				if ($iPage != 1) {
					$iPrevPage = $iPage-1;
					$sPrevPageLink = "<a href='".$sSortLink."&sOrderColumn=$sOrderColumn&iPage=$iPrevPage&sCurrOrder=$sCurrOrder&iRecPerPage=$iRecPerPage' class=header>Previous</a>";
					$sFirstPageLink = "<a href='".$sSortLink."&sOrderColumn=$sOrderColumn&iPage=1&sCurrOrder=$sCurrOrder&iRecPerPage=$iRecPerPage' class=header>First</a>";
				}

				$i=0;
				while ($oErrorLogRow = dbFetchObject($rErrorLogResult)) {

					if ($sBgcolorClass == "ODD") {
						$sBgcolorClass = "EVEN_WHITE";
					} else {
						$sBgcolorClass = "ODD";
					}

					$aReportArray['errorDate'][$i] = $oErrorLogRow->errorDate;
					$aReportArray['function'][$i] = $oErrorLogRow->function;
					$aReportArray['pageName'][$i] = $oErrorLogRow->pageName;
					$aReportArray['sourceCode'][$i] = $oErrorLogRow->sourceCode;
					$aReportArray['valueInvalidated'][$i] = $oErrorLogRow->valueInvalidated;
					$aReportArray['noOfRejects'][$i] = $oErrorLogRow->noOfRejects;


					$iTotalNoOfRejects += $oErrorLogRow->noOfRejects;
					//}
					$i++;
				}
			}

			for ($i=0; $i < count($aReportArray['errorDate']); $i++) {


				$fPercentRejects = $aReportArray['noOfRejects'][$i] * 100 /$iTotalNoOfRejects;
				$fPercentRejects = sprintf("%6.2f",round($fPercentRejects, 2));

				if ($sBgcolorClass == "ODD") {
					$sBgcolorClass = "EVEN_WHITE";
				} else {
					$sBgcolorClass = "ODD";
				}
				$sReportContent .= "<tr class=$sBgcolorClass><td>".$aReportArray['errorDate'][$i]."</td>";

				$iColspan = '1';
				if ($iReportByFunction) {
					$sReportContent .= "<td>".$aReportArray['function'][$i]."</td>";
					$iColspan++;
				}
				if ($iReportByPage) {
					$sReportContent .= "<td>".$aReportArray['pageName'][$i]."</td>";
					$iColspan++;
				}
				if ($iReportBySrc) {
					$sReportContent .= "<td>".$aReportArray['sourceCode'][$i]."</td>";
					$iColspan++;
				}
				if ($iReportByValue) {
					$sReportContent .= "<td>".$aReportArray['valueInvalidated'][$i]."</td>";
					$iColspan++;
				}

				$sReportContent .= "<td align=right>".$aReportArray['noOfRejects'][$i]."</td>
							<td align=right>$fPercentRejects</td></tr>";	

			}

			$sReportContent .= "<tr><td colspan=7><hr color=#000000></td></tr>
						<tr><td colspan=$iColspan class=header>Total No. Of Rejects</td>
							<td class=header align=right>$iTotalNoOfRejects</td><td></td></tr>";

		}
	}


	if ($iReportByFunction) {
		$sReportByFunctionChecked = "Checked";
	}
	if ($iReportByPage) {
		$sReportByPageChecked = "checked";
	}
	if ($iReportBySrc) {
		$sReportBySrcChecked = "checked";
	}
	if ($iReportByValue) {
		$sReportByValueChecked = "checked";
	}



	$iScriptEndTime = getMicroTime();
	$iScriptExecutionTime = round($iScriptEndTime - $iScriptStartTime);

	include("../../includes/adminHeader.php");

	// display javascript from reportInclude.php which defined funcReportClicked() function
	echo $sReportJavaScript;

?>
<script language=JavaScript>
function funcRecPerPage(form1) {
					//document.form1.elements['sAdd'].value='';
					document.form1.submit();
				}					
</script>
<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $sHidden;?>

<input type=hidden name=reportClicked>
<input type=hidden name=sViewReport>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center >

<tr><td>Date From</td><td><select name=iMonthFrom><?php echo $sMonthFromOptions;?>
	</select> &nbsp;<select name=iDayFrom><?php echo $sDayFromOptions;?>
	</select> &nbsp;<select name=iYearFrom><?php echo $sYearFromOptions;?>
	</select></td><td>Date To</td>
	<td><select name=iMonthTo><?php echo $sMonthToOptions;?>
	</select> &nbsp;<select name=iDayTo><?php echo $sDayToOptions;?>
	</select> &nbsp;<select name=iYearTo><?php echo $sYearToOptions;?>
	</select></td></tr>		
	
	<tr><td></td><td colspan=4><input type=checkbox name=iReportByFunction value='1' <?php echo $sReportByFunctionChecked;?>> Report By Function
	<BR><input type=checkbox name=iReportByPage value='1' <?php echo $sReportByPageChecked;?>> Report By Page
	<BR><input type=checkbox name=iReportBySrc value='1' <?php echo $sReportBySrcChecked;?>> Report By Source Code
	<BR><input type=checkbox name=iReportByValue value='1' <?php echo $sReportByValueChecked;?>> Report By Value Invalidated
	</td></tr>
	<tr><td></td><td colspan=4><input type=button name=sSubmit value='View Report' onClick="funcReportClicked('report');"></td></tr>
	
<tr><td colspan=4 align=right class=header><input type=text name=iRecPerPage value='<?php echo $iRecPerPage;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp;Records Per Page &nbsp; &nbsp; 
&nbsp; Go To Page <input type=text name=iPage value='<?php echo $iPage;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp; &nbsp; <?php echo $sFirstPageLink;?> &nbsp; <?php echo $sPrevPageLink;?> &nbsp; <?php echo $sNextPageLink;?> &nbsp; <?php echo $sLastPageLink;?> &nbsp; <?php echo $sCurrentPage;?></td></tr>
</table>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
	<tr><td>
	<table cellpadding=0 cellspacing=0 bgcolor=c9c9c9 width=80% align=center border=1 bordercolor=#000000>
		<tr><td>
		<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=100% align=center>
	<tr><td>
	<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=80% align=center>
	<tr><td colspan=7 class=bigHeader align=center><BR><?php echo $sPageTitle;?><BR><?php echo "From $iMonthFrom-$iDayFrom-$iYearFrom to $iMonthTo-$iDayTo-$iYearTo";?><BR><BR><BR></td></tr>
	<tr><td colspan=7 class=header>Run Date / Time: <?php echo $sRunDateAndTime;?></td></tr>
	
	<tr><td width=200><a href="<?php echo $sSortLink;?>&sOrderColumn=errorDate&sErrorDateOrder=<?php echo $sErrorDateOrder;?>" class=header>Error Date</a></td>		
		<?php echo $sFunctionHeader;?>
		<?php echo $sPageHeader;?>
		<?php echo $sSrcHeader;?>
		<?php echo $sValueHeader;?>
		<td class=header align=right><a href="<?php echo $sSortLink;?>&sOrderColumn=noOfRejects&sNoOfRejectsOrder=<?php echo $sNoOfRejectsOrder;?>" class=header>No. Of Rejects</a></td>
		<td class=header align=right><a href="<?php echo $sSortLink;?>&sOrderColumn=noOfRejects&sNoOfRejectsOrder=<?php echo $sNoOfRejectsOrder;?>" class=header>% Rejects</a></td>
		</tr>
		
			<?php echo $sReportContent;?>
			
			</table></td></tr></table></td></tr>
	</table>
</td></tr>
</table>
</form>

<?php

include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
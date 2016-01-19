<?php


include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

session_start();

$sPageTitle = "Nibbles Mutually Exclusive Offers Leads Report";

if (hasAccessRight($iMenuId) || isAdmin()) {
	
	// Set Default order column
	if (!($sOrderColumn)) {
		$sOrderColumn = "processDate";
		$sProcessDateOrder = "DESC";
	}	
	
	
	if (!($sCurrOrder)) {
		switch ($sOrderColumn) {
			case "processDate" :
			$sCurrOrder = $sProcessDateOrder;
			$sProcessDateOrder = ($sProcessDateOrder != "DESC" ? "DESC" : "ASC");
			break;
		}
	}
	
	
	if ($sMutExclOfferCode) {
	$sTotalLeadsQuery = "SELECT count(*) as totalLeads, date_format(dateTimeProcessed, '%Y-%m-%d') as processDate
					FROM   otDataHistory
					WHERE  (offerCode = '$sOfferCode' OR offerCode = '$sMutExclOfferCode')
					AND    processStatus IS NOT NULL
					GROUP BY processDate ";
	} else {
		$sTotalLeadsQuery = "SELECT count(*) as totalLeads, date_format(dateTimeProcessed, '%Y-%m-%d') as processDate
					FROM   otDataHistory, offersMutExclusive
					WHERE  (offerCode1 = '$sOfferCode' OR offerCode2 = '$sOfferCode')
					AND    ( otDataHistory.offerCode = offersMutExclusive.offerCode1 OR 
						   otDataHistory.offerCode = offersMutExclusive.offerCode2 )
					AND	   processStatus IS NOT NULL
					GROUP BY processDate ";
	}
	$sTotalLeadsQuery .= " ORDER BY $sOrderColumn $sCurrOrder ";
	
	$rTotalLeadsResult = dbQuery($sTotalLeadsQuery);
	//echo $sTotalLeadsQuery.mysql_error();
	while ($oTotalLeadsRow = dbFetchObject($rTotalLeadsResult)) {
		$iGrandTotalLeads += $oTotalLeadsRow->totalLeads;
	}
	
	if ($sMutExclOfferCode) {
	$sDupLeadsQuery = "SELECT count(*) as dupLeads, date_format(dateTimeProcessed, '%Y-%m-%d') as processDate
					FROM   otDataHistory
					WHERE  (offerCode = '$sOfferCode' OR offerCode = '$sMutExclOfferCode')
					AND    processStatus = 'R'
					AND    reasonCode = 'meo'
					GROUP BY processDate ";
	} else {
		$sDupLeadsQuery = "SELECT count(*) as dupLeads, date_format(dateTimeProcessed, '%Y-%m-%d') as processDate
					FROM   otDataHistory, offersMutExclusive
					WHERE  (offerCode1 = '$sOfferCode' OR offerCode2 = '$sOfferCode')
					AND    ( otDataHistory.offerCode = offersMutExclusive.offerCode1 OR 
						   otDataHistory.offerCode = offersMutExclusive.offerCode2 )
					AND	   processStatus = 'R'
					AND	   reasonCode = 'meo'
					GROUP BY processDate ";
	}
	$rDupLeadsResult = dbQuery($sDupLeadsQuery);
	echo dbError();
	//echo $sDupLeadsQuery;
	while ($oDupLeadsRow = dbFetchObject($rDupLeadsResult)) {
		//echo "<BR>11 ".$oDupLeadsRow->dupLeads;
		$iGrandTotalDupLeads += $oDupLeadsRow->dupLeads;
	}
		
	$iGrandTotalLeadsDelivered = $iGrandTotalLeads - $iGrandTotalDupLeads;
	
	
	
	
	
	
	// Specify Page no. settings
	if (!($iRecPerPage)) {
		$iRecPerPage = 10;
	}
	if (!($iPage)) {
		$iPage = 1;
	}
	
	$sSortLink = $PHP_SELF."?iMenuId=$iMenuId&iRecPerPage=$iRecPerPage&sOfferCode=$sOfferCode&sMutExclOfferCode=$sMutExclOfferCode";
	
	
	
	$iNumRecords = dbNumRows($rTotalLeadsResult);
	
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
	$sTotalLeadsQuery .= " LIMIT $iStartRec, $iRecPerPage";
	
	//echo $sTotalLeadsQuery;
	$iPageTotalDupLeads = 0;
	
	$rTotalLeadsResult = dbQuery($sTotalLeadsQuery);
	if (dbNumRows($rTotalLeadsResult) > 0) {
		
		
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

		while ($oTotalLeadsRow = dbFetchObject($rTotalLeadsResult)) {
			
			if ($sBgcolorClass == "ODD") {
				$sBgcolorClass = "EVEN";
			} else {
				$sBgcolorClass = "ODD";
			}
			
			$sProcessDate = $oTotalLeadsRow->processDate;
			$iTotalLeads = $oTotalLeadsRow->totalLeads;
			$iPageTotalLeads += $iTotalLeads;
			
			$sLeadsReport .= "<tr class=$sBgcolorClass>
								<td>$oTotalLeadsRow->processDate</td>
								<td>$iTotalLeads</td>";
			$iDupLeads = 0;
			if ($sMutExclOfferCode) {
				$sTempDupLeadsQuery = "SELECT count(*) as dupLeads, date_format(dateTimeProcessed, '%Y-%m-%d') as processDate
					FROM   otDataHistory
					WHERE  (offerCode = '$sOfferCode' OR offerCode = '$sMutExclOfferCode')
					AND    processStatus = 'R'
					AND    reasonCode = 'meo'
					AND   date_format(dateTimeProcessed, '%Y-%m-%d') = '$sProcessDate'
					GROUP BY processDate";
			} else {
				$sTempDupLeadsQuery = "SELECT count(*) as dupLeads, date_format(dateTimeProcessed, '%Y-%m-%d') as processDate
					FROM   otDataHistory, offersMutExclusive
					WHERE  (offerCode1 = '$sOfferCode' OR offerCode2 = '$sOfferCode')
					AND    ( otDataHistory.offerCode = offersMutExclusive.offerCode1 OR 
						   otDataHistory.offerCode = offersMutExclusive.offerCode2 )
					AND	   processStatus = 'R'
					AND	   reasonCode = 'meo'
					AND   date_format(dateTimeProcessed, '%Y-%m-%d') = '$sProcessDate'
					GROUP BY processDate";
			}
			$rTempDupLeadsResult = dbQuery($sTempDupLeadsQuery);
			//echo "<BR>".$sTempDupLeadsQuery. mysql_error();
			while ($oTempDupLeadsRow = dbFetchObject($rTempDupLeadsResult)) {
				$iDupLeads = $oTempDupLeadsRow->dupLeads;
				$iPageTotalDupLeads += $oTempDupLeadsRow->dupLeads;
				
			}
			$sLeadsReport .= "<td>$iDupLeads</td>";
			$iLeadsDelivered = $iTotalLeads - $iDupLeads;
			
			$sLeadsReport .="<td>$iLeadsDelivered</td></tr>";
			
		}
		$iPageTotalLeadsDelivered = $iPageTotalLeads - $iPageTotalDupLeads;
		if ($sBgcolorClass == "ODD") {
				$sBgcolorClass = "EVEN";
			} else {
				$sBgcolorClass = "ODD";
			}
		$sLeadsReport .= "<tr class=$sBgcolorClass><td>Page Total</td><td>$iPageTotalLeads</td><td>$iPageTotalDupLeads</td><td>$iPageTotalLeadsDelivered</td></tr>";
		if ($sBgcolorClass == "ODD") {
				$sBgcolorClass = "EVEN";
			} else {
				$sBgcolorClass = "ODD";
			}
		$sLeadsReport .= "<tr class=$sBgcolorClass><td>Grand Total</td><td>$iGrandTotalLeads</td><td>$iGrandTotalDupLeads</td><td>$iGrandTotalLeadsDelivered</td></tr>";
	}
}

$sHiddenFields = "<input type=hidden name=iMenuId value='$iMenuId'>
				  <input type=hidden name=sOfferCode value='$sOfferCode'>
				  <input type=hidden name=sOfferCode value='$sOfferCode'>";

?>

<html>
<script language=JavaScript>

function funcRecPerPage(form1) {					
					document.form1.submit();
				}					
</script>
<head>
<title><?php echo $sPageTitle;?></title>
<LINK rel="stylesheet" href="<?php echo $sGblAdminSiteRoot;?>/styles.css" type="text/css" >
</head>

<body>

<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $sHiddenFields;?>
<?php echo $sHidden;?>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td colspan=4 align=right class=header><input type=text name=iRecPerPage value='<?php echo $iRecPerPage;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp;Records Per Page &nbsp; &nbsp; 
&nbsp; Go To Page <input type=text name=iPage value='<?php echo $iPage;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp; &nbsp; <?php echo $sFirstPageLink;?> &nbsp; <?php echo $sPrevPageLink;?> &nbsp; <?php echo $sNextPageLink;?> &nbsp; <?php echo $sLastPageLink;?> &nbsp; <?php echo $sCurrentPage;?></td></tr>
<tr>	
	<TD class=header><a href = '<?php echo $sSortLink;?>&sOrderColumn=processDate&sProcessDateOrder=<?php echo $sProcessDateOrder;?>' class=header>Process Date</a></td>	
	<TD class=header>Total Leads</td>	
	<td class=header>Duplicate Leads</td>
	<td class=header>Leads Delivered</td>
</tr>
<?php echo $sLeadsReport;?>
<tr><td colspan=4 align=right class=header><?php echo $sFirstPageLink;?> &nbsp; <?php echo $sPrevPageLink;?> &nbsp; <?php echo $sNextPageLink;?> &nbsp; <?php echo $sLastPageLink;?> &nbsp; <?php echo $sCurrentPage;?></td></tr>
<tr><td colspan=4 align=center><input type=button name=sClose value='Close' onClick='JavaScript:self.close();'></td></tr>
</table>
</form>
</body>
</html>

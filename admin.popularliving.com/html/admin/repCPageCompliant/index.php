<?php
/*********
Script to Display C Page compliant offers. 
**********/
session_start();

include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");
include("$sGblLibsPath/stringFunctions.php");
include("$sGblIncludePath/reportInclude.php");

$iScriptStartTime = getMicroTime();

$sPageTitle = "C Page Compliant Offers";;
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
mysql_connect ($reportingHost, $reportingUser, $reportingPass);
mysql_select_db ($reportingDbase);


if (hasAccessRight($iMenuId) || isAdmin()) {

	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
				<input type=hidden name=iId value='$iId'>";	
	if(!$sViewReport){
		$sOrderColumn = "offerCode";
		$sOfferCodeOrder = "DESC";
	}

	// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
	if (!($sCurrOrder)) {
		switch ($sOrderColumn) {
			case "offerCode" :
			default:
			$sCurrOrder = $sOfferCodeOrder;
			$sOfferCodeOrder = ($sOfferCodeOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "name" :
			$sCurrOrder = $sNameOrder;
			$sNameOrder = ($sNameOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "type" :
			$sCurrOrder = $sTypeOrder;
			$sTypeOrder = ($sTypeOrder != "DESC" ? "DESC" : "ASC");
			break;
		}
	}

	// Specify Page no. settings
	if (!($iRecPerPage)) {
		$iRecPerPage = 30;
	}
	if (!($iPage)) {
		$iPage = 1;
	}


	$sSortLink = $PHP_SELF."?iMenuId=$iMenuI&sViewReport=$sViewReport&iRecPerPage=$iRecPerPage&sType=$sType&sValidationChecks=$sValidationChecks";

	if ($sViewReport != "") {
			if ($sAllowReport == 'N') {
				$sMessage .= "<br>Server Load Is High. Please check back soon...";
			} else {
				$sReportQuery = "SELECT offerCode, name , offerType
					FROM offers 
					WHERE (page2Template LIKE '%USER_FORM_%' or offerType = 'CR' or offerType = 'CRP')
					AND ( mode = 'A' OR mode = 'P' )";
								
				if ($sType != '') { $sReportQuery .= " AND offerType = '$sType' "; }

                                $sReportQuery .= " ORDER BY $sOrderColumn $sCurrOrder";

				$rReportResult = dbQuery($sReportQuery);

				$iNumRecords = dbNumRows($rReportResult);

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
				$sReportQuery .= " LIMIT $iStartRec, $iRecPerPage";

				// start of track users' activity in nibbles 
				mysql_connect ($host, $user, $pass); 
				mysql_select_db ($dbase); 
				$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
					VALUES('$sTrackingUser', '$PHP_SELF', now(), \"View report: $sReportQuery\")"; 
				$rResult = dbQuery($sAddQuery); 
				echo  dbError(); 
				mysql_connect ($reportingHost, $reportingUser, $reportingPass); 
				mysql_select_db ($reportingDbase); 
				// end of track users' activity in nibbles		
				
				
				//$rReportResult = dbQuery($sReportQuery);

				
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
					
                                $rOffersResult = dbQuery($sReportQuery);

                                echo (dbError() ? __line__.dbError() : '');

                                $aOffers = array();
                                $aTypes = array();
                                while($oOffers = dbFetchObject($rOffersResult)){
                                        $aOffers[$oOffers->offerCode] = $oOffers->name;
                                        switch($oOffers->offerType){
                                        case 'CR':
                                        case 'CRP':
                                        case 'CWH':
                                                $aTypes[$oOffers->offerCode] = $oOffers->offerType;
                                                break;
                                        case '':
                                                $aTypes[$oOffers->offerCode] = '&nbsp;';
                                                break;
					default:
                                                $aTypes[$oOffers->offerCode] = $oOffers->offerType;//'&nbsp;';
                                                break;
                                        }
                                }

				
				$moreSQL = "SELECT distinct offerCode FROM offerPage2Validation UNION SELECT distinct offerCode FROM offerPage2Options";
				$rMoreResults = dbQuery($moreSQL);

				$aValidations = array();
				while($oMoreOfferCodes = dbFetchObject($rMoreResults)){
				        array_push($aValidations, $oMoreOfferCodes->offerCode);

				}

					foreach($aOffers as $key => $value) {
						if ($sBgcolorClass == "ODD") {
							$sBgcolorClass = "EVEN_WHITE";
						} else {
							$sBgcolorClass = "ODD";
						}

						$sReportContent .= "<tr class=$sBgcolorClass>
								<td>$key</td>
								<td>$value</td>
								<td>".$aTypes[$key]."</td>";
						if(!in_array($key, $aValidations)){
                					$sReportContent .= "<td>yes</td>";
        					} else {
                					$sReportContent .= "<td>no</td>";
       		 				}
        					$sReportContent .= "</tr>\n";
					}
			}
	}

	
	// Get all source code
	$sTypeSelectOptions = '';
	$sTypeSelectQuery = "SELECT distinct offerType FROM offers";
	$rTypeSelectResult = dbQuery($sTypeSelectQuery);
	while ($oTypeSelectRow = dbFetchObject($rTypeSelectResult)) {
		if ($sType == $oTypeSelectRow->offerType) {
			$sSourceCodeSelected = "selected";
		} else {
			$sSourceCodeSelected = "";
		}
		$sTypeSelectOptions.= "<option value='".$oTypeSelectRow->offerType."' $sSourceCodeSelected>$oTypeSelectRow->offerType";
	}
	
	$sHidden = "<input type='hidden' value='$sCurrOrder' name='sCurrOrder'>
		<input type='hidden' value='$sOrderColumn' name='sOrderColumn'>"; 
	include("../../includes/adminHeader.php");

	$iScriptEndTime = getMicroTime();
	$iScriptExecutionTime = round($iScriptEndTime - $iScriptStartTime);

	// display javascript from reportInclude.php which defined funcReportClicked() function
	echo $sReportJavaScript;

?>

<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $sHidden;?>

<input type=hidden name=reportClicked>
<input type=hidden name=sViewReport>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>

	
<tr><td>Type:</td><td><select name=sType>
<option value=''>All</option>
<?php echo $sTypeSelectOptions;?>
</select></td></tr>
	
	<td colspan=2></td>
<tr><td colspan=2><input type=button name=sSubmit value='View Report' onClick="funcReportClicked('report');"></td>
	<td colspan=2></td>
</tr>
<tr><td colspan=4 align=right class=header><input type=text name=iRecPerPage value='<?php echo $iRecPerPage;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp;Records Per Page &nbsp; &nbsp; 
&nbsp; Go To Page <input type=text name=iPage value='<?php echo $iPage;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp; &nbsp; <?php echo $sFirstPageLink;?> &nbsp; <?php echo $sPrevPageLink;?> &nbsp; <?php echo $sNextPageLink;?> &nbsp; <?php echo $sLastPageLink;?> &nbsp; <?php echo $sCurrentPage;?></td></tr>
</table>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td>
<table cellpadding=0 cellspacing=0 bgcolor=c9c9c9 width=80% align=center border=1 bordercolor=#000000>
		<tr><td>
		<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=100% align=center>
	<tr><td>
	<table cellpadding=6 cellspacing=0 bgcolor=#FFFFFF width=100% align=center>
	<tr><td colspan=6 class=bigHeader align=center><BR><?php echo $sPageTitle;?><BR><BR><BR><BR></td></tr>
	<tr><td colspan=6 class=header>Run Date / Time: <?php echo $sRunDateAndTime;?></td></tr>
	<tr><?php echo $sDateSentHeader;?>

		<td class=header width=30%><a href="<?php echo $sSortLink;?>&sOrderColumn=submitDate&sOfferCodeOrder=<?php echo $sOfferCodeOrder;?>" class=header>Offer Code</a></td>
		<td class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=sourceCode&sOfferNameOrder=<?php echo $sOfferNameOrder;?>" class=header>Offer Name</a></td>
		<td class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=pageId&sTypeOrder=<?php echo $sTypeOrder;?>" class=header>Offer Type<a></td>
		<td class=header>Validation Checks</td>
	</tr>

<?php echo $sReportContent;?>

<tr><td colspan=6 align=left><hr color=#000000></td></tr>	
	<tr><td colspan=6 class=header><BR>Notes -
	</td></tr>
	<tr><td>
		<br>- Approximate time to run this report - <?php echo $iScriptExecutionTime;?> second(s).
		<br>- Report Query: <?php echo $sReportQuery;?>
		</td></tr>
	<tr><td colspan=6><BR><BR></td></tr>
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

<?php

include("../../includes/paths.php");
session_start();
$sPageTitle = "Nibbles - Rules Management";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

// Check user permission to access this page
if (hasAccessRight($iMenuId) || isAdmin()) {
	if ($sDelete) {
		$sDeleteQuery = "DELETE FROM rules WHERE  id = $iId";
		$rResult = dbQuery($sDeleteQuery);

		// start of track users' activity in nibbles
		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($sDeleteQuery) . "\")";
		$rLogResult = dbQuery($sLogAddQuery);
		// end of track users' activity in nibbles
		
		$iId = '';
	}
	
	
	if ($sFilter != '') {
		$sFilterPart = '';
		switch ($sSearchIn) {
			case 'offerCode':
			$sSelectQuery = "SELECT * FROM rules WHERE offerCode LIKE '%$sFilter%' ORDER BY flowId ASC";
			break;
			case 'sourceCode':
			$sSelectQuery = "SELECT R.* FROM rules R, links F WHERE F.sourceCode LIKE '%$sFilter%' AND R.linkId = F.id ORDER BY R.linkId ASC";
			break;
			case 'category':
			$sSelectQuery = "SELECT * FROM rules WHERE catOffers LIKE '%$sFilter%' ORDER BY flowId ASC";
			break;
			case 'flowName':
			$sSelectQuery = "SELECT R.* FROM rules R, flows F WHERE F.flowName LIKE '%$sFilter%' AND R.flowId = F.id ORDER BY R.linkId ASC";
		}
	} else {
		if ($src != '') {
			$sSelectQuery = "SELECT R.* FROM rules R, links F WHERE F.sourceCode = '$src' and R.linkId = F.id ORDER BY R.linkId ASC";
		} else if ($flowid != '') {
			$sSelectQuery = "SELECT * FROM rules WHERE flowId = '$flowid' ORDER BY flowId ASC";
		} else {
			$sSelectQuery = "SELECT * FROM rules ORDER BY flowId ASC";
		}
	}
	$rSelectResult = dbQuery($sSelectQuery);
	$sList = '';
	while ($oRow = dbFetchObject($rSelectResult)) {
		if ($sBgcolorClass=="ODD") {
			$sBgcolorClass="EVEN";
		} else {
			$sBgcolorClass="ODD";
		}
		
		$sGetFlowName = "SELECT flowName FROM flows WHERE id = '$oRow->flowId' LIMIT 1";
		$rGetFlowNameResult = dbQuery($sGetFlowName);
		$sFlowName = '';
		while ($oFlowRow = dbFetchObject($rGetFlowNameResult)) {
			$sFlowName = $oFlowRow->flowName;
		}
		
		$sCategory = '';
		if ($oRow->catOffers !='') {
			$sCategory = str_replace(',',', ',$oRow->catOffers);
		}
		
		if ($oRow->global != 'Y') {
			$iPgNum = $oRow->pageNo + 1;
		} else {
			$iPgNum = $oRow->pageNo;
		}
		if ($oRow->mutExcCat =='Y' && $oRow->global=='Y') {
			$iPgNum++;
		}
		
		if ($oRow->offerPosition == 0) {
			$iOfferPosition = 1;
		} else {
			if ($oRow->global != 'Y') {
				$iOfferPosition = $oRow->offerPosition + 1;
			} else {
				$iOfferPosition = $oRow->offerPosition;
			}
		}
		
		$sGetSource = "SELECT sourceCode FROM links WHERE id = '$oRow->linkId'";
		$rGetSource = dbQuery($sGetSource);
		$sSourceCode = '';
		while ($oSourceRow = dbFetchObject($rGetSource)) {
			$sSourceCode = $oSourceRow->sourceCode;
		}
		
		if ($iPgNum == 1000 || $iPgNum == 999) {
			$iPgNum = '';
		}

		$sList .= "<tr class=$sBgcolorClass><td>$sFlowName</td>
					<td>$sSourceCode</td>
					<td>".($oRow->global == 'Y' ? 'Yes' : 'No')."</td>
					<td>$oRow->offerCode</td>
					<td>$iPgNum</td>
					<td>$iOfferPosition</td>
					<td>$sCategory</td>
					<td>$oRow->offerIncExc</td>
					<td><a href='JavaScript:void(window.open(\"addRules.php?iMenuId=$iMenuId&id=".$oRow->id."\", \"AddContent\", \"height=500, width=650, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>
					&nbsp;&nbsp;&nbsp;<a href='JavaScript:confirmDelete(this,$oRow->id);' >Delete</a></td></tr>";
	}

	if (dbNumRows($rSelectResult) == 0) {
		$sMessage = "No Records Exist...";
	}
	

	
	$sFlowNameSelected = '';
	$sSourceCodeSelected = '';
	$sOfferCodeSelected = '';
	$sCategorySelected = '';
	switch ($sSearchIn) {
		case 'offerCode':
		$sOfferCodeSelected = "selected";
		break;		
		case 'sourceCode':
		$sSourceCodeSelected = "selected";
		break;
		case 'category':
		$sCategorySelected = "selected";
		default:
		$sFlowNameSelected = "selected";
	}
	
	$sSearchInOptions = "<option value='flowName' $sFlowNameSelected>Flow Name
						<option value='sourceCode' $sSourceCodeSelected>Source Code
						<option value='category' $sCategorySelected>Category
						<option value='offerCode' $sOfferCodeSelected>Offer Code";
	
	
	
	
	

	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>";

	$sAddButton ="<input type=button name=sAdd value=Add onClick='JavaScript:void(window.open(\"addRules.php?iMenuId=$iMenuId\", \"\", \"height=500, width=650, scrollbars=yes, resizable=yes, status=yes\"));'>";
	include("../../includes/adminHeader.php");

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
<?php echo $sHidden; ?>
<input type=hidden name=sDelete>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td colspan=5 align=left><?php echo $sAddButton;?></td></tr>

<tr><td>Filter By</td>
	<td><input type=text name=sFilter value='<?php echo $sFilter;?>'>
	<input type=submit name=sViewOffers value='Search'>
	</td>
</tr>

	
<tr><td>Search In:</td>
<td><select name="sSearchIn">
<?php echo $sSearchInOptions; ?>
</select>
</td>
</tr>


<tr><td class=header>Flow Name</td>
<td class=header>Source Code</td>
<td class=header>Global</td>
<td class=header>Offer Code</td>
<td class=header>Page No</td><td class=header>Offer Position</td>
<td class=header>Category</td><td class=header>Include/Exclude</td>
</tr>
<?php echo $sList;?>
<tr><td colspan=5 align=left><?php echo $sAddButton;?></td></tr>
</table>
</form>
<?php
	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
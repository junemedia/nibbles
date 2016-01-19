<?php 

/***********

Script to Manage Site Contents of MyHealthyLiving site

*************/

include("../../../includes/paths.php");

$sPageTitle = "MyHealthyLiving Sources - Order Source Report";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {

	// SELECT HCV DATABASE
	dbSelect($sGblMhlDBName);
	
	$currYear = date('Y');
	if (!($yearFrom)) {
		$yearFrom = date('Y');
		$monthFrom = date('m');
		$dayFrom = "01";
		
		$monthTo = $monthFrom;
		$dayTo = date('d');
		$yearTo = $yearFrom;
	}
	// prepare month options for From and To date
	for ($i = 0; $i < count($aGblMonthsArray); $i++) {
		
		$value = ($i+1);
		
		if ($value == $monthFrom) {
			$fromSel = "selected";
		} else {
			$fromSel = "";
		}
		if ($value == $monthTo) {
			$toSel = "selected";
		} else {
			$toSel = "";
		}
		
		$monthFromOptions .= "<option value='$value' $fromSel>$aGblMonthsArray[$i]";
		$monthToOptions .= "<option value='$value' $toSel>$aGblMonthsArray[$i]";
	}
	
	// prepare day options for From and To date
	for ($i = 1; $i <= 31; $i++) {
		
		if ($i < 10) {
			$value = "0".$i;
		} else {
			$value = $i;
		}
		
		if ($value == $dayFrom) {
			$fromSel = "selected";
		} else {
			$fromSel = "";
		}
		if ($value == $dayTo) {
			$toSel = "selected";
		} else {
			$toSel = "";
		}
		$dayFromOptions .= "<option value='$value' $fromSel>$i";
		$dayToOptions .= "<option value='$value' $toSel>$i";
	}
	
	// prepare year options
	for ($i = $currYear; $i >= $currYear-5; $i--) {
		
		if ($i == $yearFrom) {
			$fromSel = "selected";
		} else {
			$fromSel ="";
		}
		if ($i == $yearTo) {
			$toSel = "selected";
		} else {
			$toSel ="";
		}
		
		$yearFromOptions .= "<option value='$i' $fromSel>$i";
		$yearToOptions .= "<option value='$i' $toSel>$i";
	}
	
	// Set Default order column
	if (!($orderColumn)) {
		$orderColumn = "orDate";
		$orDateOrder = "ASC";
	}
	
	// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
	switch ($orderColumn) {
		case "sscName":
		$currOrder = $subSrcNameOrder;
		$subSrcNameOrder = ($subSrcNameOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "counts":
		$currOrder = $countsOrder;
		$countsOrder = ($countsOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "total":
		$currOrder = $totalOrder;
		$totalOrder = ($totalOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "srcName":
		$currOrder = $srcNameOrder;
		$srcNameOrder = ($srcNameOrder != "DESC" ? "DESC" : "ASC");
		break;
		default:
		$currOrder = $orDateOrder;
		$orDateOrder = ($orDateOrder != "DESC" ? "DESC" : "ASC");
		
	}
	
	// Query to get the list of BDPartners
	$dateFrom = "$yearFrom$monthFrom$dayFrom"."000000";
	$dateTo = "$yearTo$monthTo$dayTo"."235959";
	
	// check if selected dates are valid dates
	if (checkDate($monthFrom, $dayFrom, $yearFrom) && checkdate($monthTo, $dayTo,$yearTo)) {
		
$sql = "select orSource,orSubSource,count(*),sum(orAmount) from orders
	where orDate >= '$StartDate'
	and orDate <= '$EndDate'
	and orSource!=''
	$WhereClause
	group by orSource,orSubSource
	order by orSource,orSubSource";

		$selectQuery = "SELECT date_format(orDate,'%m-%d-%Y') orDate, srcName, sscName, count(*) counts, sum(orAmount) total
					FROM   orders	
					INNER JOIN source_codes ON source_codes.srcCode = orders.orSource
					LEFT JOIN  sub_source_codes ON orders.orSubSource = sub_source_codes.sscCode				
					WHERE  orDate >= '$dateFrom'
					AND    orDate <= '$dateTo'	";
		if ($srcCode)
		{
			$selectQuery .= " AND orSource = '$srcCode'";
		}
		if ($subSrcCode)
		{
			$selectQuery .= " AND orSubSource = '$subSrcCode'";
		}
		
		$selectQuery .= " GROUP BY orSource, orSubSource
						  ORDER BY $orderColumn $currOrder";
		
		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"View Order Source Report: $selectQuery\")"; 
		$rLogResult = dbQuery($sLogAddQuery); 
		echo  dbError(); 
		// end of track users' activity in nibbles		

		
		$result = dbQuery($selectQuery);
		
		if ($result) {
			if (dbNumRows($result) > 0) {
				
				while ($row = dbFetchObject($result)) {
					
					//$srcID = $row->srcID;
					
					if ($bgcolorClass == "ODD") {
						$bgcolorClass = "EVEN";
					} else {
						$bgcolorClass = "ODD";
					}
					
					$reportData .= "<tr class=$bgcolorClass><td>$row->orDate</td>
									<td>$row->srcName</td>
						<td>$row->sscName</td><td>$row->counts</td>
						<td>$row->total</td>
						<td></td></tr>";
					
				}
			} else {
				$sMessage = "No Records Exist...";
			}
			dbFreeResult($result);
			
		} else {
			echo dbError();
		}
		
	}
	// source code options
	$srcCodeOptions = "<option value=''>View All";
	$srcQuery = "SELECT *
				 FROM   source_codes
				 ORDER BY srcCode";
	$srcResult = dbQuery($srcQuery);
	while ($srcRow = dbFetchObject($srcResult)) {
		if ($srcRow->srcCode == $srcCode) {
			$selected = "selected";
		} else {
			$selected = "";
		}
		$srcCodeOptions .= "<option value=$srcRow->srcCode $selected>$srcRow->srcCode";
	}
	
	// source code options
	$subSrcCodeOptions = "<option value=''>View All";
	$subSrcQuery = "SELECT *
				 FROM   sub_source_codes
				 ORDER BY sscCode";
	$subSrcResult = dbQuery($subSrcQuery);
	while ($subSrcRow = dbFetchObject($subSrcResult)) {
		if ($subSrcRow->sscCode == $subSrcCode) {
			$selected = "selected";
		} else {
			$selected = "";
		}

		$subSrcCodeOptions .= "<option value=$subSrcRow->sscCode $selected>$subSrcRow->sscCode";
	}
	
	$backToSourceLink = "<a href='index.php?iMenuId=$iMenuId&iParentMenuId=$iParentMenuId&sParentMenuFolder=$sParentMenuFolder'>Back To Source Management</a>";
	
	// Hidden variable to be passed with form submit
	$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>
				<input type=hidden name=iParentMenuId value='$iParentMenuId'>
				<input type=hidden name=sParentMenuFolder value='$sParentMenuFolder'>";
	
	$sortLink = $PHP_SELF."?iMenuId=$iMenuId&iParentMenuId=$iParentMenuId&sParentMenuFolder=$sParentMenuFolder&monthFrom=$monthFrom&dayFrom=$dayFrom";
	$sortLink .= "&yearFrom=$yearFrom&monthTo=$monthTo&dayTo=$dayTo&yearTo=$yearTo&srcCode=$srcCode&subSrcCode=$subSrcCode";
	
	include("$sGblIncludePath/adminHeader.php");	
	
	?>
	
	
<form name=form1 action='<?php echo $PHP_SELF;?>'>

<?php echo $hidden;?>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>

<tr><td colspan=2 align=left><?php echo $backToSourceLink;?></td></tr>
<tr><td>Date from</td><td><select name=monthFrom><?php echo $monthFromOptions;?>
	</select> &nbsp;<select name=dayFrom><?php echo $dayFromOptions;?>
	</select> &nbsp;<select name=yearFrom><?php echo $yearFromOptions;?>
	</select></td><td>Date to</td>
	<td><select name=monthTo><?php echo $monthToOptions;?>
	</select> &nbsp;<select name=dayTo><?php echo $dayToOptions;?>
	</select> &nbsp;<select name=yearTo><?php echo $yearToOptions;?>
	</select></td></tr>	
	
<tr><td width=20%>Source</td>
	<td><select name=srcCode><?php echo $srcCodeOptions;?></select></td>
</tr>
<tr><td width=20%>SubSource</td>
	<td><select name=subSrcCode><?php echo $subSrcCodeOptions;?></select></td>
</tr>
<tr>
<td colspan=2 align=center><br><input type=submit name=save value='View Report'></td></tr>
</table>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr>	
<td align=left><a href='<?php echo $sortLink;?>&orderColumn=orDate&orDateOrder=<?php echo $orDateOrder;?>' class=header>Order Date</a></td>
	<td align=left><a href='<?php echo $sortLink;?>&orderColumn=srcName&srcNameOrder=<?php echo $srcNameOrder;?>' class=header>Source Name</a></td>
		<td align=left><a href='<?php echo $sortLink;?>&orderColumn=sscName&subSrcNameOrder=<?php echo $subSrcNameOrder;?>' class=header>SubSource Name</a></td>
	<td align=left><a href='<?php echo $sortLink;?>&orderColumn=counts&countsOrder=<?php echo $countsOrder;?>' class=header>Counts</a></td>	
	<td align=left><a href='<?php echo $sortLink;?>&orderColumn=total&totalOrder=<?php echo $totalOrder;?>' class=header>Order Sum</a></td>		
</tr>
<?php echo $reportData;?>

</table>

</form>

<?php

} else {
	echo "You are not authorized to access this page...";
}
?>	


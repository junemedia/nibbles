<?php

/*********

Script to Display Add/Edit HandCraftersVillage Add/Edit Project

*********/

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

$sPageTitle = "Flow Details";


session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {

if ($sSaveClose || $sSaveNew || $sSaveContinue) {
	
	$skipQuery = "UPDATE flowDetails
				SET showSkip = 'N'
				WHERE  flowId = '$id'";
	$skipResult = mysql_query($skipQuery);
	
	// Change the sort orders
	if(is_array($flowOrder)) {
		while (list($key, $val) = each($flowOrder)) {
			$editQuery = "UPDATE flowDetails
							  SET    flowOrder = '$val'
							  WHERE  flowId = '$id'
							  AND url = '$key'";
			$editResult = mysql_query($editQuery);
		}
	}
	
	
	if (is_array($aSkip)) {
		while (list($key, $val) = each($aSkip)) {
			$skipQuery = "UPDATE flowDetails
							SET showSkip = '$val'
							WHERE  id = '$key'";
			$skipResult = mysql_query($skipQuery);
		}
		$sMessage = '';
	}
	

	// check if page already exists in this category...
	if ($addPage1 !='') {
		$checkQuery = "SELECT * FROM flowDetails
						   WHERE  flowId = '$id'
						   AND    url = \"$addPage1\"";
		$checkResult = mysql_query($checkQuery);
		if (mysql_num_rows($checkResult) == 0) {
			$sGetMaxSortOrder = "SELECT max(flowOrder) as maxOrderId FROM flowDetails
						   WHERE  flowId = '$id' LIMIT 1";
			$rGetMaxSortOrderResult = mysql_query($sGetMaxSortOrder);
			$iMaxOrderId = mysql_fetch_object($rGetMaxSortOrderResult);
			$iMaxOrderId = $iMaxOrderId->maxOrderId + 1;
			if (substr($addPage1, -7) == '_e1.php') {
				$iMaxOrderId = 0;
			}
			$addQuery = "INSERT INTO flowDetails(flowId,url,flowOrder)
							 VALUES('$id', '$addPage1', '$iMaxOrderId')";
			$addResult = mysql_query($addQuery);
			echo mysql_error();
		} else {
			$sMessage = "Page Already Exists In This Category....";
		}
	}
	
	// check if page already exists in this category...
	if ($addPage2 !='') {
		$checkQuery = "SELECT * FROM flowDetails
						   WHERE  flowId = '$id'
						   AND    url = \"$addPage2\"";
		$checkResult = mysql_query($checkQuery);
		if (mysql_num_rows($checkResult) == 0) {
			$sGetMaxSortOrder = "SELECT max(flowOrder) as maxOrderId FROM flowDetails
						   WHERE  flowId = '$id' LIMIT 1";
			$rGetMaxSortOrderResult = mysql_query($sGetMaxSortOrder);
			$iMaxOrderId = mysql_fetch_object($rGetMaxSortOrderResult);
			$iMaxOrderId = $iMaxOrderId->maxOrderId + 1;
			if (substr($addPage2, -7) == '_e1.php') {
				$iMaxOrderId = 0;
			}
			$addQuery = "INSERT INTO flowDetails(flowId,url,flowOrder)
							 VALUES('$id', '$addPage2', '$iMaxOrderId')";
			$addResult = mysql_query($addQuery);
			echo mysql_error();
		} else {
			$sMessage = "Page Already Exists In This Category....";
		}
	}
	
		// check if page already exists in this category...
	if ($addPage3 !='') {
		$checkQuery = "SELECT * FROM flowDetails
						   WHERE  flowId = '$id'
						   AND    url = \"$addPage3\"";
		$checkResult = mysql_query($checkQuery);
		if (mysql_num_rows($checkResult) == 0) {
			$sGetMaxSortOrder = "SELECT max(flowOrder) as maxOrderId FROM flowDetails
						   WHERE  flowId = '$id' LIMIT 1";
			$rGetMaxSortOrderResult = mysql_query($sGetMaxSortOrder);
			$iMaxOrderId = mysql_fetch_object($rGetMaxSortOrderResult);
			$iMaxOrderId = $iMaxOrderId->maxOrderId + 1;
			if (substr($addPage3, -7) == '_e1.php') {
				$iMaxOrderId = 0;
			}
			$addQuery = "INSERT INTO flowDetails(flowId,url,flowOrder)
							 VALUES('$id', '$addPage3', '$iMaxOrderId')";
			$addResult = mysql_query($addQuery);
			echo mysql_error();
		} else {
			$sMessage = "Page Already Exists In This Category....";
		}
	}
	
	
	// check if page already exists in this category...
	// E1 - NO SKIP
	if ($addPage4 !='') {
		$checkQuery = "SELECT * FROM flowDetails
						   WHERE  flowId = '$id'
						   AND    url = \"$addPage4\"";
		$checkResult = mysql_query($checkQuery);
		if (mysql_num_rows($checkResult) == 0) {
			$sGetMaxSortOrder = "SELECT max(flowOrder) as maxOrderId FROM flowDetails
						   WHERE  flowId = '$id' LIMIT 1";
			$rGetMaxSortOrderResult = mysql_query($sGetMaxSortOrder);
			$iMaxOrderId = mysql_fetch_object($rGetMaxSortOrderResult);
			$iMaxOrderId = $iMaxOrderId->maxOrderId + 1;
			if (substr($addPage4, -7) == '_e1.php') {
				$iMaxOrderId = 0;
			}
			$addQuery = "INSERT INTO flowDetails(flowId,url,flowOrder,showSkip)
							 VALUES('$id', '$addPage4', '$iMaxOrderId','N')";
			$addResult = mysql_query($addQuery);
			echo mysql_error();
		} else {
			$sMessage = "Page Already Exists In This Category....";
		}
	}
	
	
	
	// check if page already exists in this category...
	// SOP - NO SKIP
	if ($addPage5 !='') {
		$checkQuery = "SELECT * FROM flowDetails
						   WHERE  flowId = '$id'
						   AND    url = \"$addPage5\"";
		$checkResult = mysql_query($checkQuery);
		if (mysql_num_rows($checkResult) == 0) {
			$sGetMaxSortOrder = "SELECT max(flowOrder) as maxOrderId FROM flowDetails
						   WHERE  flowId = '$id' LIMIT 1";
			$rGetMaxSortOrderResult = mysql_query($sGetMaxSortOrder);
			$iMaxOrderId = mysql_fetch_object($rGetMaxSortOrderResult);
			$iMaxOrderId = $iMaxOrderId->maxOrderId + 1;
			if (substr($addPage5, -7) == '_e1.php') {
				$iMaxOrderId = 0;
			}
			$addQuery = "INSERT INTO flowDetails(flowId,url,flowOrder,showSkip)
							 VALUES('$id', '$addPage5', '$iMaxOrderId','N')";
			$addResult = mysql_query($addQuery);
			echo mysql_error();
		} else {
			$sMessage = "Page Already Exists In This Category....";
		}
	}
	
	
	
	if (is_array($remove)) {
		while (list($key, $val) = each($remove)) {
			$deleteQuery = "DELETE FROM flowDetails
								WHERE  flowId = '$id'
								AND    url = \"$key\"";
			$deleteResult = mysql_query($deleteQuery);
		}
		$sMessage = '';
	}
}

if ($sSaveClose && $sMessage == '') {
	echo "<script language=JavaScript>
			window.opener.location.reload();
			self.close();
			</script>";			
	// exit from this script
	exit();		
}

if ($sSaveContinue && $sMessage == '') {
	echo "<script language=JavaScript>
			window.opener.location.reload();
			</script>";
}

// Set Default order column
if (!($orderColumn)) {
	$orderColumn = "flowOrder";
	$sortOrderOrder = "ASC";
}
// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
switch ($orderColumn) {
	case "url" :
	$currOrder = $sUrlOrder;
	$sUrlOrder = ($sUrlOrder != "DESC" ? "DESC" : "ASC");
	break;
	case "flowOrder" :
	$currOrder = $sFlowOrderOrder;
	$sFlowOrderOrder = ($sFlowOrderOrder != "DESC" ? "DESC" : "ASC");
}

// Select Query to display list of data
$selectQuery = "SELECT * FROM flowDetails WHERE flowId='$id'";
$selectQuery .= " ORDER BY $orderColumn $currOrder";
$selectResult = mysql_query($selectQuery);
echo mysql_error();
while ($row = mysql_fetch_object($selectResult)) {
	if ($bgcolorClass == "ODD") {
		$bgcolorClass = "EVEN";
	} else {
		$bgcolorClass = "ODD";
	}
	if ($row->showSkip == 'Y') {
		$sChecked = 'checked';
	} else {
		$sChecked = '';
	}
	
	$sDisableSkipButton = '';
	if (substr($row->url, -7) == '_e1.php') {
		$sDisableE1PageOptions = ' disabled ';
		$sDisableSkipButton = ' disabled ';
	}
	
	if (strstr($row->url,"/p/scFlowSopC.php")) {
		$sDisableSkipButton = ' disabled ';
	}
	
	$pageList .= "<tr class=$bgcolorClass><td>$row->url</td>
				<td><input type=text name=flowOrder[".$row->url."] value='$row->flowOrder' size=3 maxlength=3></td>
				<td><input type=checkbox name=aSkip[".$row->id."] value='Y' $sChecked $sDisableSkipButton></td>
				<td><input type=checkbox name=remove[".$row->url."]></td>
				</tr>";
}


if (mysql_num_rows($selectResult) == 0) {
	$message = "No Pages In This Category...";
}


$pagesQuery = "SELECT * FROM otPages WHERE pageName LIKE 'scf_%' order by pageName";
$pagesResult = mysql_query($pagesQuery);
$addPageOptions1 = "<option value=''>Select Page To Add (A Version)";
$addPageOptions2 = "<option value=''>Select Page To Add (B Version)";
$addPageOptions3 = "<option value=''>Select Page To Add (C Version)";
$addPageOptions4 = "<option value=''>Select E1 Page";
while ($pagesRow = mysql_fetch_object($pagesResult)) {
	$sTempPageName1 = $pagesRow->pageName;
	$sTempPageName2 = $pagesRow->pageName."b";
	$sTempPageName3 = $pagesRow->pageName."_c";
	$sTempPageName4 = $pagesRow->pageName."_e1";
	$addPageOptions1 .= "<option value='http://www.popularliving.com/p/$sTempPageName1.php'>$sTempPageName1";
	$addPageOptions2 .= "<option value='http://www.popularliving.com/p/$sTempPageName2.php'>$sTempPageName2";
	$addPageOptions3 .= "<option value='http://www.popularliving.com/p/$sTempPageName3.php'>$sTempPageName3";
	$addPageOptions4 .= "<option value='http://www.popularliving.com/p/$sTempPageName4.php'>$sTempPageName4";
}


$sSopOffersQuery = "SELECT * FROM offers ORDER BY offerCode ASC";
$rSopOffersResult = mysql_query($sSopOffersQuery);
$addPageOptions5 = "<option value=''>SOP - C Version</option>";
while ($sOfferRow = mysql_fetch_object($rSopOffersResult)) {
	$addPageOptions5 .= "<option value='http://www.popularliving.com/p/scFlowSopC.php?oc=$sOfferRow->offerCode&tm=12'>$sOfferRow->offerCode";
}



$sortLink = $PHP_SELF."?iMenuId=$iMenuId&id=$id&sTempSrc=$sTempSrc";

// Hidden variable to be passed with form submit
$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=id value='$id'>";

$sSrcTemp = trim($_GET['sTempSrc']);
if ($sSrcTemp != '') {
	$sTempSrc = "<tr><td>Source Code: $sSrcTemp</td>
				<td>&nbsp;</td><td>&nbsp;</td></tr>";
}

include("$sGblIncludePath/adminAddHeader.php");	
?>

<form action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $hidden;?>
<?php echo $reloadWindowOpener;?>
<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
<?php echo $sTempSrc;?>
<tr>
	<TD class=header><a href="<?php echo $sortLink;?>&orderColumn=url&sUrlOrder=<?php echo $sUrlOrder;?>" class=header>Page Name</td>
	<TD class=header><a href="<?php echo $sortLink;?>&orderColumn=flowOrder&sFlowOrderOrder=<?php echo $sFlowOrderOrder;?>" class=header>Flow Order</td>
	<td class=header>Show Skip Button</td>
	<td class=header>Remove This Page</td>
</tr>
<?php echo $pageList;?>
<!--<input type=submit name=saveClose value="Save & Close">-->

<tr><td colspan=4><br>Note: No skip button for e1 and sop pages.</td></tr>

<tr><td><BR></td></tr>
<tr><td colspan=4 class=header>Select Page To Add To This Category:</td></tr>

<tr><td colspan=4><select name=addPage5>
<?php echo $addPageOptions5;?>
</select>
</td></tr>


<tr><Td  colspan=4><select name=addPage4 <?php echo $sDisableE1PageOptions?>>
<?php echo $addPageOptions4;?>
</select>
</td></tr>


<tr><Td  colspan=4><select name=addPage1>
<?php echo $addPageOptions1;?>
</select>
</td></tr>

<tr><Td  colspan=4><select name=addPage2>
<?php echo $addPageOptions2;?>
</select>
</td></tr>

<tr><Td  colspan=4><select name=addPage3>
<?php echo $addPageOptions3;?>
</select>
</td></tr>
</table>
	
<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
	<tr><TD colspan=2 align=center >
		<input type=submit name=sSaveContinue value='Save & Continue'>
		</td><td></td>
	</tr>	
</table>

<?php				

include("$sGblIncludePath/adminAddFooter.php");

} else {
	echo "You are not authorized to access this page...";
}	

?>
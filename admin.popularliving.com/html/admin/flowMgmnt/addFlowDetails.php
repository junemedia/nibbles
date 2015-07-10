<?php

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

$sPageTitle = "Flow Management";


session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {

if ($sSaveClose || $sSaveNew || $sSaveContinue) {
	
	if (is_array($remove)) {
		$iTemp = 0;
		while (list($key, $val) = each($remove)) {
			$deleteQuery = "DELETE FROM flowDetails
							WHERE  id = '$key'";
			$deleteResult = mysql_query($deleteQuery);
			
			// start of track users' activity in nibbles
			$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($deleteQuery) . "\")";
			$rLogResult = dbQuery($sLogAddQuery);
			// end of track users' activity in nibbles
			
			$iTemp++;
		}
		$sMessage = '';
	}
	
	
	
	if (is_array($maxOffers)) {
		while (list($key, $val) = each($maxOffers)) {
			$sUpdate = "UPDATE flowDetails
						SET maxOffers = '$val'
						WHERE  id = '$key'";
			$rUpdate = mysql_query($sUpdate);
			
			// start of track users' activity in nibbles
			$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($sUpdate) . "\")";
			$rLogResult = dbQuery($sLogAddQuery);
			// end of track users' activity in nibbles
		}
		$sMessage = '';
	}
	
	
	if (is_array($iOfferLayOutId)) {
		while (list($key, $val) = each($iOfferLayOutId)) {
			$sUpdate = "UPDATE flowDetails
						SET offersLayoutId = '$val'
						WHERE  id = '$key'";
			$rUpdate = mysql_query($sUpdate);
			
			// start of track users' activity in nibbles
			$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($sUpdate) . "\")";
			$rLogResult = dbQuery($sLogAddQuery);
			// end of track users' activity in nibbles
		}
		$sMessage = '';
	}
	
	
	
	
	if (is_array($iFrameHeight)) {
		while (list($key, $val) = each($iFrameHeight)) {
			if ($val == '') { $sMessage .= 'Frame Height Is Required And Must Be Numeric'; }
			if (!(ctype_digit($val))) { $sMessage .= 'Frame Height Is Required And Must Be Numeric'; }

			$sUpdate = "UPDATE flowDetails
						SET frameHeight = '$val'
						WHERE  id = '$key'";
			$rUpdate = mysql_query($sUpdate);
			
			// start of track users' activity in nibbles
			$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($sUpdate) . "\")";
			$rLogResult = dbQuery($sLogAddQuery);
			// end of track users' activity in nibbles
		}
	}
	
	if (is_array($sFrame3rdPartyUrl)) {
		while (list($key, $val) = each($sFrame3rdPartyUrl)) {
			if ($val == '') { $sMessage .= 'Frame URL Is Required'; }
			$sUpdate = "UPDATE flowDetails
						SET frame3rdPartyUrl = \"$val\"
						WHERE  id = '$key'";
			$rUpdate = mysql_query($sUpdate);
			
			// start of track users' activity in nibbles
			$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($sUpdate) . "\")";
			$rLogResult = dbQuery($sLogAddQuery);
			// end of track users' activity in nibbles
		}
	}
	
	if (is_array($sRedirect3rdPartyUrl)) {
		while (list($key, $val) = each($sRedirect3rdPartyUrl)) {
			if ($val == '') { $sMessage .= '3rd Party Redirect URL Is Required'; }
			$sUpdate = "UPDATE flowDetails
						SET frame3rdPartyUrl = \"$val\"
						WHERE  id = '$key'";
			$rUpdate = mysql_query($sUpdate);
			
			// start of track users' activity in nibbles
			$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($sUpdate) . "\")";
			$rLogResult = dbQuery($sLogAddQuery);
			// end of track users' activity in nibbles
		}
	}
	
	
	

	
	
	/*
		Change the sort orders:
		
		A little explaination of what's going on here.
		The elements in the flowDetails table each have a "flow order" input that the user can update,
		as well as a hidden "old order" input, so that we can see which flow details have had their orders
		changed. 
		
		I first run through the flowOrder array, looking for the elements that have had their 
		order changed ("if($flowOrder[$keys[$i]] != $oldOrder[$keys[$i]])"), and push each of those onto 
		an array representing the location that they've been changed to. It's important that these elements
		with a changed order are moved onto the output array first. 
		
		Next, I run through the same array, looking for the elements that havn't had their order changed.
		These are pushed onto the unchanged array, in order. 
		
		Next, I take the elements of the new order array, put them into their places in the output array. 
		
		Lastly, I take the total number of entires for this flow, and iterate over the output array, taking 
		elements from the head of the unchangedOrder array, and putting them into the empty places in the 
		output array, in order.
	*/
	if(is_array($flowOrder)) {
		$newOrder = array();
		$unchangedOrder = array();
		//$asdf = '';
		$keys = array_keys($flowOrder);
		for($i=0;$i<count($keys);$i++){
			if($flowOrder[$keys[$i]] != $oldOrder[$keys[$i]]){
				if(!is_array($newOrder[$flowOrder[$keys[$i]]])){
					$newOrder[$flowOrder[$keys[$i]]] = array();
				}
				//$asdf .= "pushed ".$keys[$i]."(new) to ".$flowOrder[$keys[$i]]."<br>\n";
				array_push($newOrder[$flowOrder[$keys[$i]]], $keys[$i]);
				//$asdf .= "  newOrder[".$flowOrder[$keys[$i]]."] <= ".var_export($newOrder[$flowOrder[$keys[$i]]], true)."<br>\n";
			}
		}
		
		for($i=0;$i<count($keys);$i++){
			if($flowOrder[$keys[$i]] == $oldOrder[$keys[$i]]){
				//$asdf .= "pushed ".$keys[$i]."(old) to ".$flowOrder[$keys[$i]]."<br>\n";
				array_push($unchangedOrder, $keys[$i]);
				//$asdf .= "  unchangedOrder push <= ".$keys[$i]."<br>\n";
			}
		}
		
		$out = array();
		$newKeys = array_keys($newOrder);
		sort($newKeys, SORT_ASC);
		for($i=0;$i<count($newKeys);$i++){
			if(is_array($newOrder[$newKeys[$i]])){
				for($j=0;$j<count($newOrder[$newKeys[$i]]);$j++){
					//array_push($out, $newOrder[$newKeys[$i]][$j]);
					$out[($newKeys[$i]+$j-1)] = $newOrder[$newKeys[$i]][$j];
					//$asdf .= "out[".($newKeys[$i]+$j-1)."] <= newOrder[".$newKeys[$i]."][".$j."](".$out[($newKeys[$i]+$j-1)].")<br>\n";
				}
			}
		}
		
		
		$unchangedReverse = array_reverse($unchangedOrder);
		$finalOut = array();
		for($i=0;$i<count($keys);$i++){
			if($out[$i] == ''){
				$temp = array_pop($unchangedReverse);
				//$asdf .= "out[".$i."] <= ".$temp."<br>\n";
				$out[$i] = $temp;
				if(is_array($remove)){
					if(!in_array($temp, array_keys($remove))){
						array_push($finalOut,$temp);
					}
				}
			}
		}
		
		$out = (is_array($remove)? $finalOut : $out);

		for($i=0;$i<count($out);$i++){
			$editQuery = "UPDATE flowDetails
							  SET    flowOrder = '".($i+1)."'
							  WHERE  id = '".$out[$i]."'";
			$editResult = mysql_query($editQuery);
			
			// start of track users' activity in nibbles
			$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($editQuery) . "\")";
			$rLogResult = dbQuery($sLogAddQuery);
			// end of track users' activity in nibbles
		}
	}


	if ($iPageTemplateId !='') {
		$sGetMaxSortOrder = "SELECT max(flowOrder) as maxOrderId FROM flowDetails
					   WHERE  flowId = '$id' LIMIT 1";
		$rGetMaxSortOrderResult = mysql_query($sGetMaxSortOrder);
		$iMaxOrderId = mysql_fetch_object($rGetMaxSortOrderResult);
		$iMaxOrderId = $iMaxOrderId->maxOrderId + 1;

		$addQuery = "INSERT INTO flowDetails(flowId,templateId,flowOrder)
						 VALUES('$id', '$iPageTemplateId', '$iMaxOrderId')";
		$addResult = mysql_query($addQuery);
		echo mysql_error();
		
		// start of track users' activity in nibbles
		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($addQuery) . "\")";
		$rLogResult = dbQuery($sLogAddQuery);
		// end of track users' activity in nibbles

		$sGetFlowName = "SELECT flowName FROM flows WHERE id='$id'";
		$rGetFlowName = mysql_query($sGetFlowName);
		$oFlowNameRow = mysql_fetch_object($rGetFlowName);
		$sTempFlowName = $oFlowNameRow->flowName;
		
		// Create a phantom page id for each page in a flow
		$addQuery1 = "INSERT IGNORE INTO otPages(pageName,flowId,pageNo)
						 VALUES(\"$sTempFlowName-$iMaxOrderId\",'$id', '$iMaxOrderId')";
		$addResult1 = mysql_query($addQuery1);
		echo mysql_error();
		
		// start of track users' activity in nibbles
		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($addQuery1) . "\")";
		$rLogResult = dbQuery($sLogAddQuery);
		// end of track users' activity in nibbles
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

$sBtnDisable = '';
// Select Query to display list of data
$selectQuery = "SELECT * FROM flowDetails WHERE flowId='$id'
				ORDER BY flowOrder ASC";
$selectResult = mysql_query($selectQuery);
$iPd=0;
$aPd=array();
while ($row = mysql_fetch_object($selectResult)) {
	if ($bgcolorClass == "ODD") {
		$bgcolorClass = "EVEN";
	} else {
		$bgcolorClass = "ODD";
	}
	
	$sGetTmpName = "SELECT templateType, templateName FROM pageTemplates WHERE id='$row->templateId'";
	$rGetTmpName = mysql_query($sGetTmpName);
	while ($oTmpRow = mysql_fetch_object($rGetTmpName)) {
		$sTemplateName = $oTmpRow->templateName;
		$sTemplateType = $oTmpRow->templateType;
	}
	
	
	$sTempList = '';
	if ($sTemplateType == '3rdPP') {
		$sTempList = "<tr class=$bgcolorClass><td></td><td></td>
		<td>Frame Height: <br><br>Frame URL: </td>
		<td><input type=text name=iFrameHeight[".$row->id."] value='$row->frameHeight' size=5 maxlength=11> Required
		<br>
		<input type=text name=sFrame3rdPartyUrl[".$row->id."] value=\"$row->frame3rdPartyUrl\" size=100> Required
		</td>
		<td></td><td></td></tr>";
	}
	
	if ($sTemplateType == 'PP') {
		$sTempList = "<tr class=$bgcolorClass><td></td><td></td>
		<td>Redirect URL (3rd Party): </td>
		<td><input type=text name=sRedirect3rdPartyUrl[".$row->id."] value=\"$row->frame3rdPartyUrl\" size=100> Required
		</td>
		<td></td><td></td></tr>";
	}

	$sDisable = '';
	if ($sTemplateType == 'EP' || $sTemplateType == 'PP' || $sTemplateType == '3rdPP') {
		$sDisable = ' disabled ';
	}
	
	
	// if 3rd party page and no url, then disable abandon button
	if ($row->frame3rdPartyUrl == '' && $sTemplateType == '3rdPP') {
		$sBtnDisable = ' disabled ';
	}
	
	
	
	$sOfferListLayout = "SELECT id,layout 
						FROM nibbles2OfferLayouts 
						ORDER by layout";
	$rGetLayout = mysql_query($sOfferListLayout);
	$sLayoutOptions = '';
	while ($oRow1 = mysql_fetch_object($rGetLayout)) {
		if ($row->offersLayoutId == $oRow1->id) {
			$sSelected = 'selected';
			$iSelectedId=$oRow1->id;
		} else {
			$sSelected = '';
		}
		$sLayoutOptions .= "<option value='$oRow1->id' $sSelected>$oRow1->layout";
	}
	
	$sList .= "<tr class=$bgcolorClass><td>$sTemplateName</td>
		<td><input type=text name=maxOffers[".$row->id."] value='$row->maxOffers' size=3 maxlength=3 $sDisable></td>
		<td><input type=text name=flowOrder[".$row->id."] value='$row->flowOrder' size=3 maxlength=3></td>

		<td><select name=iOfferLayOutId[".$row->id."] id=".$iPd." onchange='upd8(this.id, this.value);' $sDisable>$sLayoutOptions</select></td>
		<td><input type=\"button\" value=\"desc.\" id=B".$iPd." onclick='popper(this.id);' $sDisable></td>
		<td><input type=checkbox name=remove[".$row->id."]></td>
		<input type=hidden name=oldOrder[".$row->id."] value='$row->flowOrder'></tr>$sTempList";
	array_push($aPd, $iSelectedId) ;
	$iPd++;
}


$sGetTemplate = "SELECT id,templateName FROM pageTemplates 
			ORDER BY templateName";
$rGetTemplate = mysql_query($sGetTemplate);
$sTemplateOptions = "<option value=''>";
while ($oRow = mysql_fetch_object($rGetTemplate)) {
	$sTemplateOptions .= "<option value='$oRow->id'>$oRow->templateName";
}


// Hidden variable to be passed with form submit
$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=id value='$id'>";
$sortLink = $PHP_SELF."?iMenuId=$iMenuId&id=$id&sTempFlowName=$sTempFlowName";
$sTempFlowName = trim($_GET['sTempFlowName']);
if ($sTempFlowName != '') {
	$sTempFlowName = "<tr><td>Flow Name: $sTempFlowName</td>
				<td>&nbsp;</td><td>&nbsp;</td></tr>";
}
$sPd='<script type="text/javascript">var hiding = new Array(';
$ic=0;
while($ic < sizeof($aPd)){
	if($ic!=sizeof($aPd)-1)
		$sPd.='"'.$aPd[$ic].'",' ;
	else 	
		$sPd.='"'.$aPd[$ic].'");</script>';	
	$ic++; 
}

include("$sGblIncludePath/adminAddHeader.php");	
echo $sPd ;
?>
<script type="text/javascript">
function upd8(idee, key){
	hiding[idee]=key ;	
}
function popper(sid){
	
	window.open("pop_desc.php?id="+hiding[sid.substring(1,sid.length)],"winName","left=20,top=20,width=700,height=500,toolbar=0,resizable=0");
}
</script>
<form action='<?php echo $PHP_SELF;?>' method=post name='form'>
<?php echo $hidden;?>
<?php echo $reloadWindowOpener;?>
<?php //echo "$asdf";?>
<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
<?php echo $sTempFlowName;?>
<tr>
	<td class=header>Template Name</td>
	<td class=header>Max Offers</td>
	<td class=header>Flow Order</td>
	<td class=header>Offer Layout</td>
	<td class=header>Layout Description</td>
	<td class=header>Remove This Template</td>
</tr>
<?php echo $sList;?>
<!--<input type=submit name=saveClose value="Save & Close">-->

<tr><td><BR></td></tr>
<tr><td colspan=4 class=header>Select Template To Add To This Flow:</td></tr>

<tr><td colspan=4><select name=iPageTemplateId>
<?php echo $sTemplateOptions;?>
</select>
</td></tr>


<tr><td colspan=4 >
<p>Tags: 
[salutation] [email] [first] [last] [address] [address2] [city] [state] [zip] [zip5only]
[phone] [ipAddress] [phone_areaCode] [phone_exchange] [phone_number]
[mm] [dd] [yyyy] [yy] [hh] [ii] [ss] [SESSION_ID]
[birthYear] [birthMonth] [birthDay] [gender] [binary_gender] [sourcecode] [gVariable]
</p>
</td></tr>



</table>
	
<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
	<tr><TD colspan=2 align=center >
		<input type=submit name=sSaveContinue value='Save & Continue'>
		</td><td></td>
	</tr>	
</table>

<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
	<tr><td colspan=2 align=center >
		<input type=submit name=sSaveClose value='Save & Close'> &nbsp; &nbsp; 
		<input type=button name=sAbandonClose value='Abandon & Close' onclick="self.close();" <?php echo $sBtnDisable; ?>>
		</td><td></td>
	</tr>
</table>

<?php				

//include("$sGblIncludePath/adminAddFooter.php");

} else {
	echo "You are not authorized to access this page...";
}	

?>
<?php


include("../../includes/paths.php");

session_start();

$sPageTitle = "Nibbles - Custom Error Message Management";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

function apostropheStrip($str){
	$str = str_replace("'",'&#39;',$str);
	return $str;
}

function apostropheUnStrip($str){
	$str = str_replace('&#39;',"'",$str);
	return $str;	
}

// Check user permission to access this page
if (hasAccessRight($iMenuId) || isAdmin()) {
	
	
	if($sourceCode != '' && $submit == 'Submit' && $sMessage == '')  {
		
		if($iId == ''){
			$sInsertLvPSQL = "INSERT INTO linksErrorMessages (
			sourceCode,
			 checkAllOffers,checkAtLeastOneOffer,ynAtLeastOneOffer,
			 sFirst_error, sFirst_empty,
			 sLast_error, sLast_empty, 
			 sEmail_error, sEmail_empty, 
			 sAddress_error, sAddress_empty, 
			 sCity_error, sCity_empty, 
			 sState_error, sState_empty, 
			 sZip_error, sZip_empty, 
			 sPhone_areaCode_error, sPhone_areaCode_empty, 
			 sPhone_exchange_error, sPhone_exchange_empty,
			 sPhone_number_error, sPhone_number_empty, 
			 iBirthYear_error, iBirthYear_empty, 
			 iBirthMonth_error, iBirthMonth_empty, 
			 iBirthDay_error, iBirthDay_empty, 
			 sGender_error, sGender_empty) values (
			 '$sourceCode',
			 '".addslashes(apostropheStrip($sCheckAllOffers))."','".apostropheStrip(addslashes($sCheckAtLeastOneOffer))."','".apostropheStrip(addslashes($sYNAtLeastOneOffer))."',
			 '".addslashes(apostropheStrip($ssFirst_error))."','".apostropheStrip(addslashes($ssFirst_empty))."',
			 '".addslashes(apostropheStrip($ssLast_error))."','".apostropheStrip(addslashes($ssLast_empty))."',
			 '".addslashes(apostropheStrip($ssEmail_error))."','".apostropheStrip(addslashes($ssEmail_empty))."',
			 '".addslashes(apostropheStrip($ssAddress_error))."','$".apostropheStrip(addslashes(ssAddress_empty))."',
			 '".addslashes(apostropheStrip($ssCity_error))."','".apostropheStrip(addslashes($ssCity_empty))."',
			 '".addslashes(apostropheStrip($ssState_error))."','".apostropheStrip(addslashes($ssState_empty))."',
			 '".addslashes(apostropheStrip($ssZip_error))."','".apostropheStrip(addslashes($ssZip_empty))."',
			 '".addslashes(apostropheStrip($ssPhone_areaCode_error))."','".apostropheStrip(addslashes($ssPhone_areaCode_empty))."',
			 '".addslashes(apostropheStrip($ssPhone_exchange_error))."','".apostropheStrip(addslashes($ssPhone_exchange_empty))."',
			 '".addslashes(apostropheStrip($ssPhone_number_error))."','".apostropheStrip(addslashes($ssPhone_number_empty))."',
			 '".addslashes(apostropheStrip($siBirthYear_error))."','".apostropheStrip(addslashes($siBirthYear_empty))."',
			 '".addslashes(apostropheStrip($siBirthMonth_error))."','".apostropheStrip(addslashes($siBirthMonth_empty))."',
			 '".addslashes(apostropheStrip($siBirthDay_error))."','".apostropheStrip(addslashes($siBirthDay_empty))."',
			 '".addslashes(apostropheStrip($ssGender_error))."','".apostropheStrip(addslashes($ssGender_empty))."')";
		
			
			$rInsertLvP = dbQuery($sInsertLvPSQL);
				
			// start of track users' activity in nibbles
			$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
							VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($rInsertLvP) . "\")";
			$rLogResult = dbQuery($sLogAddQuery);
			// end of track users' activity in nibbles
		} else {
			//updates
			$sUpdateSQL = "UPDATE linksErrorMessages SET 
			sourceCode = '".apostropheStrip(addslashes($sourceCode))."',
			 checkAllOffers = '".apostropheStrip(addslashes($sCheckAllOffers))."',
			 checkAtLeastOneOffer = '".apostropheStrip(addslashes($sCheckAtLeastOneOffer))."',
			 ynAtLeastOneOffer = '".apostropheStrip(addslashes($sYNAtLeastOneOffer))."',
			 sFirst_error = '".apostropheStrip(addslashes($ssFirst_error))."', 
			 sFirst_empty = '".apostropheStrip(addslashes($ssFirst_empty))."',
			 sLast_error = '".apostropheStrip(addslashes($ssLast_error))."', 
			 sLast_empty = '".apostropheStrip(addslashes($ssLast_empty))."', 
			 sEmail_error = '".apostropheStrip(addslashes($ssEmail_error))."', 
			 sEmail_empty = '".apostropheStrip(addslashes($ssEmail_empty))."', 
			 sAddress_error = '".apostropheStrip(addslashes($ssAddress_error))."', 
			 sAddress_empty = '".apostropheStrip(addslashes($ssAddress_empty))."', 
			 sCity_error = '".apostropheStrip(addslashes($ssCity_error))."', 
			 sCity_empty = '".apostropheStrip(addslashes($ssCity_empty))."', 
			 sState_error = '".apostropheStrip(addslashes($ssState_error))."', 
			 sState_empty = '".apostropheStrip(addslashes($ssState_empty))."', 
			 sZip_error = '".apostropheStrip(addslashes($ssZip_error))."', 
			 sZip_empty = '".apostropheStrip(addslashes($ssZip_empty))."', 
			 sPhone_areaCode_error = '".apostropheStrip(addslashes($ssPhone_areaCode_error))."', 
			 sPhone_areaCode_empty = '".apostropheStrip(addslashes($ssPhone_areaCode_empty))."', 
			 sPhone_exchange_error = '".apostropheStrip(addslashes($ssPhone_exchange_error))."', 
			 sPhone_exchange_empty = '".apostropheStrip(addslashes($ssPhone_exchange_empty))."',
			 sPhone_number_error = '".apostropheStrip(addslashes($ssPhone_number_error))."', 
			 sPhone_number_empty = '".apostropheStrip(addslashes($ssPhone_number_empty))."', 
			 iBirthYear_error = '".apostropheStrip(addslashes($siBirthYear_error))."', 
			 iBirthYear_empty = '".apostropheStrip(addslashes($siBirthYear_empty))."', 
			 iBirthMonth_error = '".apostropheStrip(addslashes($siBirthMonth_error))."', 
			 iBirthMonth_empty = '".apostropheStrip(addslashes($siBirthMonth_empty))."', 
			 iBirthDay_error = '".apostropheStrip(addslashes($siBirthDay_error))."', 
			 iBirthDay_empty = '".apostropheStrip(addslashes($siBirthDay_empty))."', 
			 sGender_error = '".apostropheStrip(addslashes($ssGender_error))."', 
			 sGender_empty = '".apostropheStrip(addslashes($ssGender_empty))."'
			 WHERE 
			 sourceCode = '$sourceCode'";
			
			$res = dbQuery($sUpdateSQL);
		}
		
	} else if($submit == 'Submit' && $sourceCode == ''){
		$sMessage = 'You must select a Source Code.';
	}
	
				//	WHERE popups.popType !='' 
	$sSelectQuery = "SELECT * FROM linksErrorMessages WHERE sourceCode = '$sourceCode'";
	$rSelectResult = dbQuery($sSelectQuery);
	$sList = '';
	$oRow = dbFetchArray($rSelectResult);
	$iId = $oRow['id'];
	$aFields = array('Offer Validation','sFirst', 'sLast', 'sEmail', 'sAddress', 'sCity','sState', 'sZip', 'sPhone_areaCode','sPhone_exchange','sPhone_number','iBirthYear','iBirthMonth','iBirthDay','sGender');
	$aFieldNames = array('sFirst'=>'First Name',
						'sLast'=>'Last Name',
						'sEmail'=>'Email',
						'sAddress'=>'Address',
						'sCity'=>'City',
						'sState'=>'State',
						'sZip'=>'Zip Code',
						'sPhone_areaCode'=>'Phone Area Code',
						'sPhone_exchange'=>'Phone Exchange',
						'sPhone_number'=>'Phone, last 4 digits',
						'iBirthYear'=>'Birth Year',
						'iBirthMonth'=>'Birth Month',
						'sGender'=>'Gender',
						'iBirthDay'=>'Birth Day');
	foreach($aFields as $field){
		if ($sBgcolorClass=="ODD") {
			$sBgcolorClass="EVEN";
		} else {
			$sBgcolorClass="ODD";
		}
		
		if($field == 'Offer Validation'){
						
			$sList .= "<tr class=$sBgcolorClass><td>
			<table><tr><td><b>$field</b></td></tr>
					<tr><td>Y/N All Offers: </td><td><input name='sCheckAllOffers' value='".$oRow['checkAllOffers']."'></td></tr>
					<tr><td>Y At Least One: </td><td><input name='sCheckAtLeastOneOffer' value='".$oRow['checkAtLeastOneOffer']."'></td></tr>
					<tr><td>Y/N At Least One: </td><td><input name='sYNAtLeastOneOffer' value='".$oRow['ynAtLeastOneOffer']."'></td></tr>
			</table></td></tr>";
		} else if($field == 'sPhoneDistance'){				
			$sList .= "<tr class=$sBgcolorClass><td>
			<table><tr><td colspan=2><b>Phone Distance Error</b></td></tr>
					<tr><td><input name='ssPhoneDistance' value='".$oRow['sPhoneDistance']."'></td></tr>
			</table></td></tr>";
		}else{
			$EmptyMember = $field.'_empty';
			$ErrorMember = $field.'_error';
				
			$sList .= "<tr class=$sBgcolorClass><td>
			<table><tr><td colspan=2><b>".$aFieldNames[$field]."</b></td></tr>
					<tr><td>Empty: </td><td><input name='s$EmptyMember' value='".$oRow[$EmptyMember]."'></td></tr>
					<tr><td>Error: </td><td><input name='s$ErrorMember' value='".$oRow[$ErrorMember]."'></td></tr>
			</table></td></tr>";
		}
	}
		
	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>";

	$sourceCodeSelect = "<select name='sourceCode' onChange='reloadWithSource(this.value);'>";
	$sGetSourceCodeSQL = "SELECT sourceCode FROM links";
	$rGetSourceCode = dbQuery($sGetSourceCodeSQL);
	while($oGetSourceCode = dbFetchObject($rGetSourceCode)){
		if($sourceCode != '' && $sourceCode == $oGetSourceCode->sourceCode) $sourceSelected = 'selected';
		else  $sourceSelected = '';
		$sourceCodeSelect .= "<option value='$oGetSourceCode->sourceCode' $sourceSelected>$oGetSourceCode->sourceCode";
	}
	$sourceCodeSelect .= "</select>";
	
	
	include("../../includes/adminHeader.php");

	
	
	echo "<script type='text/javascript'>
function reloadWithSource(src){
	//src = document.form1.sourceCode.value;
	document.location = '/admin/linksMgmnt/customErrorMessages.php?PHPSESSID=".session_id()."&iMenuId=$iMenuId&sourceCode='+src;
}
	
	
	</script>"
	?>
<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $sHidden;?>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=65% align=center>
<tr><td colspan=5 align=left><?php echo $sourceCodeSelect;?></td></tr>
<tr><td colspan=5 align=center>Fields left blank will show the default error message.</td></tr>
<?php echo $sList;?>
<tr><td colspan=5 align=left><input type='submit' name='submit' value='Submit'></td></tr>
</table>
</form>
<?php
	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
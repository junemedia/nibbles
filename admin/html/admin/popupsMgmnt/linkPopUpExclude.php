<?php


include("../../includes/paths.php");

session_start();

$sPageTitle = "Nibbles - Exclude Specific Popups";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

// Check user permission to access this page
if (hasAccessRight($iMenuId) || isAdmin()) {
	
	
	if($sourceCode != '' && $submit == 'Submit' && $sMessage == ''){
		//run the updates
		
		//popupsMgmnt/linkPopUpExclude.php
		
		$sDeleteLvPSQL = "DELETE FROM linksPopupsExclusion WHERE sourceCode = '$sourceCode'";
		$rDeleteLvP = dbQuery($sDeleteLvPSQL);
			
		// start of track users' activity in nibbles
		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
						VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($sDeleteLvPSQL) . "\")";
		$rLogResult = dbQuery($sLogAddQuery);
		// end of track users' activity in nibbles
		//print_r($aExcludePopup);

		if($aExcludePopup){
			$aExcludeMyPopups = array();
			$sInsertLvPSQL = "INSERT INTO linksPopupsExclusion (popupId, sourceCode) values ";
		
			foreach($aExcludePopup as  $popId => $source){
				array_push($aExcludeMyPopups,"($popId, '$sourceCode')");
			}
		
		
			$sInsertLvPSQL .= join(',',$aExcludeMyPopups);
			//echo "$sInsertLvPSQL<br>";
			//mail('bbevis@amperemedia.com','the linksPopupsExclusion query',"$sInsertLvPSQL");
			
			$rInsertLvP = dbQuery($sInsertLvPSQL);
				
			// start of track users' activity in nibbles
			$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
							VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($rInsertLvP) . "\")";
			$rLogResult = dbQuery($sLogAddQuery);
			// end of track users' activity in nibbles
		}
		
	} else if($submit == 'Submit' && $sourceCode == ''){
		$sMessage = 'You must select a Source Code.';
	}
	
				//	WHERE popups.popType !='' 
	$sSelectQuery = "SELECT popups.id as popId, popups.*, linksPopupsExclusion.sourceCode as sourceCode FROM popups LEFT JOIN linksPopupsExclusion ON linksPopupsExclusion.popupId = popups.id
					ORDER BY popups.popType,id ASC";
	$rSelectResult = dbQuery($sSelectQuery);
	$sList = '';
	while ($oRow = dbFetchObject($rSelectResult)) {
		if ($sBgcolorClass=="ODD") {
			$sBgcolorClass="EVEN";
		} else {
			$sBgcolorClass="ODD";
		}
		
		if ($oRow->popType=='S') {
			$sPopupType = 'Standard';
		} elseif ($oRow->popType=='E') {
			$sPopupType = 'Exit';
		} elseif ($oRow->popType=='A') {
			$sPopupType = 'Abandoned';
		} elseif ($oRow->popType=='W') {
			$sPopupType = 'Window Manager';
		}
		
		$sStartDate = $oRow->startDate;
		$sEndDate = $oRow->endDate;

		if ($oRow->startDate == '0000-00-00') {
			$sStartDate = '';
		} elseif ($oRow->endDate == '0000-00-00') {
			$sEndDate = '';
		}

		if($oRow->sourceCode == $sourceCode && $sourceCode != ''){
			$Exclude = 'checked';
		} else {
			$Exclude = '';
		}
		
		$sList .= "<tr class=$sBgcolorClass><td>$sPopupType</td>
						<td>$oRow->popUpUnder</td>
						<td>$sStartDate</td>
						<td>$sEndDate</td>
						<td><input type=checkbox name='aExcludePopup[$oRow->popId]' value='exclude' $Exclude></td></tr>";
	}
	
	if (dbNumRows($rSelectResult) == 0) {
		$sMessage = "No Records Exist...";
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
	document.location = '/admin/popupsMgmnt/linkPopUpExclude.php?PHPSESSID=".session_id()."&iMenuId=$iMenuId&sourceCode='+src;
}
	
	
	</script>"
	?>
<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $sHidden;?>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td colspan=5 align=left><?php echo $sourceCodeSelect;?><input type='submit' name='submit' value='Submit'></td></tr>
<tr><td class=header>Popup Type</td><td class=header>Pop Up/Under</td>
<td class=header>Start Date</td><td class=header>End Date</td>
<td class=header>Exclude From This Link</td>
</tr>
<?php echo $sList;?>
</table>
</form>
<?php
	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
<?php

include("../../includes/paths.php");
session_start();

$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

function charEncode($str) {
	$str = str_replace('<','\<',$str);
	$str = str_replace('>','\>',$str);
	return $str;
}

if (hasAccessRight($iMenuId) || isAdmin()) {
	if ($sSubmit) {
		$sMessage = '';
		if ($iOrder == '') {
			$sMessage = "Order Required...";
			$bKeepValues = true;
		} elseif ($campaignId == '') {
			$sMessage = "Campaign Id Required...";
			$bKeepValues = true;
		} elseif(!(ctype_digit($iOrder))) {
			$sMessage  = "Order must be an integer...";
			$bKeepValues = true;
		}
		
		
		
		if ($campaignId != '' && $sSubmit == 'Submit' && $sMessage == '') {
			if ($iId == '') {
				$sInsertSQL = "INSERT INTO campaignText (campaignId, pageOrder, text1, text2) 
						values ('$campaignId','$iOrder','".addslashes($sText1)."','".addslashes($sText2)."')";
				$res = dbQuery($sInsertSQL);
				echo dbError();
			} else {
				$sUpdateSQL = "UPDATE campaignText SET
							campaignId = '$campaignId',
							pageOrder = '$iOrder',
							text1 = '".addslashes($sText1)."',
							text2 = '".addslashes($sText2)."'
							WHERE id = '$iId'";
				$res = dbQuery($sUpdateSQL);
				echo dbError();
			}
		} else if($sSubmit == 'Submit' && $campaignId == '') {
			$sMessage = 'You must select a Campaign.';
		}
	}
	
	if($iId != '') {
		$sql = "SELECT * FROM campaignText WHERE id = '$iId'";
		$res = dbQuery($sql);
		$oRow = dbFetchObject($res);
		$sText1 =$oRow->text1;
		$sText2 =$oRow->text2;
		$iOrder = $oRow->pageOrder;
	}
	
	
	$sPageOrderOptions = '';
	for ($x=0;$x<100;$x++) {
		if ($iOrder == $x) {
			$sSelect = " selected ";
		} else {
			$sSelect = '';
		}
		$sPageOrderOptions .= "<option value='$x' $sSelect>$x</option>";
	}
	
	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
				<input type=hidden name=iId value='$iId'>
				<input type=hidden name=campaignId value='$campaignId'>";
	
	include("../../includes/adminAddHeader.php");
	if($sMessage == '' and $sSubmit == 'Submit'){
		echo "<script language='javascript'>window.opener.document.location.reload(); window.close();</script>";
	}
	?>
	
	<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
	<?php echo $sHidden;?>
	<?php echo $sReloadWindowOpener;?>
	<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
		
		<tr><td width=15%>Text 1: </td>
		<td><textarea name=sText1 rows=10 cols=75><?php echo $sText1;?></textarea></td>
		</tr>
		
		<tr><td width=15%>Text 2: </td>
		<td><textarea name=sText2 rows=10 cols=75><?php echo $sText2;?></textarea></td>
		</tr>
		
		<tr><td width=15%>Order In Flow: </td>
		<td><select name="iOrder">
		<?php echo $sPageOrderOptions; ?>
		</select>
		</td>
		</tr>
	

	</table>
	
	
	<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
	<tr><TD colspan=2 align=center >
		<input type=submit name=sSubmit value='Submit'> &nbsp; &nbsp; 
		</td><td></td>
	</tr>	
	</table>
	
	
	<?php
	//include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
<?php

include("../../includes/paths.php");
session_start();

$sPageTitle = "Nibbles - Recipe For Living Management";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

if (hasAccessRight($iMenuId) || isAdmin()) {
	if ($sSaveClose || $sSaveNew || $sSaveContinue) {
		$sMessage = '';
		if ($sMaterial == ''){
			$sMessage = "Material Required...";
			$bKeepValues = true;
		} elseif ($iRecipeId == ''){
			$sMessage = "Recipe ID Required...";
			$bKeepValues = true;
		} elseif ($sStyle == ''){
			$sMessage = "Style Required...";
			$sStyle = "color: #336600;font-family: Arial;font-weight: bold;font-size: 14px;position:absolute;padding-left:185px;padding-top:100px;font-family:Arial,sans-serif;";
			$bKeepValues = true;
		}
		
		if ($sMessage == '') {
			$sAmount = addslashes($sAmount);
			$sMaterial = addslashes($sMaterial);
			$sStyle = addslashes($sStyle);
		}
		
		if (!($id) && $sMessage == '') {
				$sAddQuery = "INSERT INTO recipeIngredients (recipeId, material, amount, style) 
								VALUES(\"$iRecipeId\",\"$sMaterial\", \"$sAmount\", \"$sStyle\")";
				$rAddResult = dbQuery($sAddQuery);
				
				// start of track users' activity in nibbles
				$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($sAddQuery) . "\")";
				$rLogResult = dbQuery($sLogAddQuery);
				// end of track users' activity in nibbles
				
				
				$sGetTheRecipeBackSQL = "SELECT id FROM recipeIngredients WHERE recipeId = \"$iRecipeId\" AND material = \"$sMaterial\" and amount = \"$sAmount\"";
				$rGetTheRecipeBack = dbQuery($sGetTheRecipeBackSQL);
				$oGet = dbFetchObject($rGetTheRecipeBack);
				$id = $oGet->id;
			
		} elseif (($id) && $sMessage == '') {
			
				$editQuery = "UPDATE recipeIngredients 
								SET material = \"$sMaterial\",
								amount = \"$sAmount\",
								style = \"$sStyle\"
							WHERE  id = '$id'";
				$result = mysql_query($editQuery);
				
				// start of track users' activity in nibbles
				$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($editQuery) . "\")";
				$rLogResult = dbQuery($sLogAddQuery);
				// end of track users' activity in nibbles
			
		}
	}
	
	if ($sSaveClose) {
		if ($bKeepValues != true) {
			$id = '';
			$sMaterial = '';
			$sAmount = '';
			$sStyle = '';
			echo "<script language=JavaScript>
				window.opener.location.reload();
				self.close();
				</script>";			
			exit();
		}
	} else if ($sSaveNew) {
		if ($bKeepValues != true) {
			$sReloadWindowOpener = "<script language=JavaScript>
						window.opener.location.reload();
						</script>";			
			$id = '';
			$sMaterial = '';
			$sAmount = '';
			$sStyle = '';
		}
	}
	
	if ($id != '') {
		$selectQuery = "SELECT * FROM recipeIngredients WHERE  id = '$id'";
		$result = mysql_query($selectQuery);
		while ($row = mysql_fetch_object($result)) {
			$sMaterial = $row->material;
			$sAmount = $row->amount;
			$sStyle = $row->style;
		}
	}


	if($sStyle == ''){
		$sStyle = "color:#336600;font-family:Arial;font-weight:bold;font-size:14px;position:absolute;padding-left:185px;padding-top:100px;font-family:Arial,sans-serif;";
	}
	
	// If add button is clicked, display another two buttons
	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=sAbandonNew value=' Abandon & New  '>";	
	
	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
				<input type=hidden name=id value='$id'>";
	
	include("../../includes/adminAddHeader.php");
	?>
	
	<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
	<?php echo $sHidden;?>
	<?php echo $sReloadWindowOpener;?>
	<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
		<tr><td width=35%>Material: </td>
			<td><input type="text" name=sMaterial maxlength="255" size='50' value="<?php echo $sMaterial; ?>">
			</td>
		</tr>
		
	
		<tr><td width=35%>Amount: </td>
		<td><input type="text" name=sAmount maxlength="255" size='50' value="<?php echo $sAmount; ?>">
			</td>
		</tr>
		
		<tr><td width=35%>Style: </td>
		<td><textarea name=sStyle cols=38 rows=8><?php echo $sStyle;?></textarea>
			</td>
		</tr>
		
	</table>
	<input type=hidden name=iRecipeId value='<?php echo $iRecipeId; ?>'>
	
	
	<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
	<tr><TD colspan=2 align=center >
		<input type=submit name=sSaveContinue value='Save & Continue'> &nbsp; &nbsp; 
		</td><td></td>
	</tr>	
	</table>
	
	
	<?php
	include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
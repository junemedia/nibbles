<?php

include("../../includes/paths.php");
session_start();

$sPageTitle = "Nibbles - Email Capture Management";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

if (hasAccessRight($iMenuId) || isAdmin()) {
	
	if ($sDelete) {
		$sDeleteQuery = "DELETE FROM recipeIngredients WHERE id = '$iId'";
		$rResult = dbQuery($sDeleteQuery);
		$iId = '';
		//echo "$rResult is result";
		// start of track users' activity in nibbles
		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($sDeleteQuery) . "\")";
		$rLogResult = dbQuery($sLogAddQuery);
		// end of track users' activity in nibbles
	}
	
	if ($sSaveClose || $sSaveNew || $sSaveContinue) {
		$sMessage = '';
		if ($sTitle == '') {
			$sMessage = "Title Required...";
			$bKeepValues = true;
		}
		
		if ($sMessage == '') {
			$sTitle = addslashes($sTitle);
			$sDirections = addslashes($sDirections);
		}
		
		if (!($id) && $sMessage == '') {
				$sAddQuery = "INSERT INTO recipes (title,directions, header) 
								VALUES(\"$sTitle\",\"$sDirections\",\"$sHeader\")";
				$rAddResult = dbQuery($sAddQuery);
				
				// start of track users' activity in nibbles
				$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($sAddQuery) . "\")";
				$rLogResult = dbQuery($sLogAddQuery);
				// end of track users' activity in nibbles
				
				$sGetTheRecipeBackSQL = "SELECT id FROM recipes WHERE title = \"$sTitle\" AND header = \"$sHeader\" and directions = \"$sDirections\"";
				$rGetTheRecipeBack = dbQuery($sGetTheRecipeBackSQL);
				$oGet = dbFetchObject($rGetTheRecipeBack);
				$id = $oGet->id;
			
		} elseif (($id) && $sMessage == '') {
				$editQuery = "UPDATE recipes 
								SET title = \"$sTitle\",
								directions = \"$sDirections\",
								header = \"$sHeader\"
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
			$sName = '';
			$sEmailCaptureContent = '';
			$sDescription = '';
			$sNotes = '';
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
			$sName = '';
			$sEmailCaptureContent = '';
			$sDescription = '';
			$sTitle = '';
			$sHeader = '';
			$sNotes = '';
		}
	}
	
	$sList = '';
	if ($id != '') {
		$selectQuery = "SELECT * FROM recipes WHERE  id = '$id'";
		$result = mysql_query($selectQuery);
		while ($row = mysql_fetch_object($result)) {
			$sTitle = $row->title;
			$sDirections = $row->directions;
			$sHeader = $row->header;
		}
		
		$sSelectQuery = "SELECT * FROM recipeIngredients WHERE recipeId = '$id'";
		$rSelectResult = dbQuery($sSelectQuery);
		$sList = '';
		while ($oRow = dbFetchObject($rSelectResult)) {
			if ($sBgcolorClass=="ODD") {
				$sBgcolorClass="EVEN";
			} else {
				$sBgcolorClass="ODD";
			}
			
			$sList .= "<tr class=$sBgcolorClass>
							<td>$oRow->amount</td>
							<td>$oRow->material</td>
							<td><a href='JavaScript:void(window.open(\"addIngredient.php?iMenuId=$iMenuId&id=$oRow->id&iRecipeId=$oRow->recipeId\", \"\", \"height=300, width=650, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>
							<a href='JavaScript:void(confirmDelete(\"\",$oRow->id));'>Delete</a>
							</td></tr>";
		}
	}


	// If add button is clicked, display another two buttons
	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=sAbandonNew value=' Abandon & New  '>";	
	
	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
				<input type=hidden name=sDelete value=''>
				<input type=hidden name=iId value='$iId'>
				<input type=hidden name=id value='$id'>";
	
	$sAddButton ="<input type=button name=sAdd value=Add onClick='JavaScript:void(window.open(\"addIngredient.php?iMenuId=$iMenuId&iRecipeId=$id\", \"\", \"height=300, width=650, scrollbars=yes, resizable=yes, status=yes\"));'>";
	include("../../includes/adminAddHeader.php");
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
	<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
	<?php echo $sHidden;?>
	<?php echo $sReloadWindowOpener;?>
	<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
		<tr><td width=35%>Title: </td>
			<td><input type="text" name=sTitle maxlength="255" size='50' value="<?php echo $sTitle; ?>">
			</td>
		</tr>
		<tr><td width=35%>Header: </td>
			<td><input type="text" name=sHeader maxlength="255" size='50' value="<?php echo $sHeader; ?>">
			</td>
		</tr>
	
		<tr><td width=35%>Directions: </td>
		<td><textarea name=sDirections rows=5 cols=50><?php echo $sDirections;?></textarea></td>
		</tr>
		
	</table>
	
	<?php if($id != ''){ ?>
	<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
	<tr><td colspan=5 align=left><?php echo $sAddButton;?></td></tr>
	<tr><td class=header>Amount</td><td class=header>Material</td>
	</tr>
	<?php echo $sList;?>
	<tr><td colspan=5 align=left><?php echo $sAddButton;?></td></tr>
	</table>
	<?php } ?>
	
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
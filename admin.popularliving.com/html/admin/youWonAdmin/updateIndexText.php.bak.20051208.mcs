<?php

/*********

Script to Display List/Add/Edit/Delete You Won eMail Contents

**********/

include("../../includes/paths.php");

$sPageTitle = "Update You Won Index Page Text";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
	
	if ($sSave) {
		// if record edited
		$sSelectQuery="SELECT * FROM vars
					   WHERE  varName = 'youWonIndexText'
					   AND  system = 'youWon'";
		$rSelectResult = dbQuery($sSelectQuery);
		if (dbNumRows($rSelectResult) == 0) {
			$sInsertQuery = "INSERT INTO vars(system, varName, varValue)
							VALUES('youWon', 'youWonIndexText', '$sYouWonIndexText')";
			$rInsertResult = dbQuery($sInsertQuery);
		} else {
		
			$sEditQuery = "UPDATE vars SET
							varValue = '$sYouWonIndexText'
							WHERE varName = 'youWonIndexText'
							AND   system = 'youWon'";
			$rResult = dbQuery($sEditQuery);
		//	echo mysql_error();
			if (!($result)) {
				$sMessage=dbError();
			}
		}
			header("Location:index.php?iMenuId=$iMenuId&".SID);				
	} 
			
	$sSelectQuery="SELECT * FROM vars
				  WHERE  varName = 'youWonIndexText'";
	$rSelectResult = dbQuery($sSelectQuery);
	while ($oSelectRow = dbFetchObject($rSelectResult)) {			
		$sYouWonIndexText = $oSelectRow->varValue;
	}		
	
	
		
	if (dbNumRows($rSelectResult)==0) {
		$sMessage = "No records exist...";
	}
	
	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>";
	
	$sYouWonLink = "<a href='index.php?iMenuId=$iMenuId'>Back To You Won Admin Menu</a>";
	
	
	// Parse common variables and common steps to display the template						
	
include("../../includes/adminHeader.php");	

?>
<form name=form1 action='<?php echo $PHP_SELF;?>'>

<?php echo $sHidden;?>
		

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>

<tr><td colspan=7 align=left>&nbsp; &nbsp; <?php echo $sYouWonLink;?></td></tr>

<tr><Td align=left valign=top>You Won index.php Text</a></th>

<Td align=left valign=top><textarea  name=sYouWonIndexText rows=6 cols=40><?php echo $sYouWonIndexText;?></textarea></td>

</tr>
<tr><td></tD><td><input type=submit name=sSave value='Save'></td></tr>
</table>
</form>

<?php 
	include("../../includes/adminFooter.php");
	
} else {
	echo "You are not authoresed to access this page...";
}				

?>
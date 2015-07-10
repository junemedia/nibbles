<?php

/*********

Script to Edit You Won - Pop Up Text

**********/

include("../../includes/paths.php");

$sPageTitle = "You Won - Edit Pop Up Text";

session_start();
if (hasAccessRight($iMenuId) || isAdmin()) {
	
	if ($sSave) {
		// if record edited
		$sSelectQuery="SELECT * FROM vars
					  WHERE  varName = 'youWonPopUpText'
					  AND  system = 'youWon'";
		$rSelectResult = dbQuery($sSelectQuery);
		if (dbNumRows($rSelectResult) == 0) {
			$sInsertQuery = "INSERT INTO vars(system, varName, varValue)
							VALUES('youwon', 'youWonPopUpText', \"$sYouWonPopUpText\")";

			// start of track users' activity in nibbles 
			$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
	
			$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Add: " . addslashes($sInsertQuery) . "\")"; 
			$rLogResult = dbQuery($sLogAddQuery); 
			echo  dbError(); 
			// end of track users' activity in nibbles		
			
			
			$rInsertResult = dbQuery($sInsertQuery);
		} else {
		
			$sEditQuery = "UPDATE vars SET
							varValue = \"$sYouWonPopUpText\"
							WHERE varName = 'youWonPopUpText'
							AND system ='youWon'";

			// start of track users' activity in nibbles 
			$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
	
			$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Edit: " . addslashes($sEditQuery) . "\")"; 
			$rLogResult = dbQuery($sLogAddQuery); 
			echo  dbError(); 
			// end of track users' activity in nibbles		
			
			
			$rResult = dbQuery($sEditQuery);
		
			if (!($rResult)) {
				$sMessage = dbError();
			}
		}
			header("Location:index.php?iMenuId=$iMenuId&".SID);				
	} 
			
	$sSelectQuery="SELECT * FROM vars
				  WHERE  varName = 'youWonPopUpText'
				  AND    system = 'youWon'";
	$rSelectResult = dbQuery($sSelectQuery);
	while ($oSelectRow = dbFetchObject($rSelectResult)) {			
		$sYouWonPopUpText = $oSelectRow->varValue;
	}				
		
	if (dbNumRows($rSelectResult)==0) {
		$sMessage = "No records exist...";
	}
	
	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>";
	
		
	$sYouWonLink = "<a href='index.php?iMenuId=$iMenuId'>Back To You Won Admin Menu</a>";
	

	include("../../includes/adminHeader.php");	

?>

<form name=form1 action='<?php echo $PHP_SELF;?>'>

<?php echo $sHidden;?>


<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>

<tr><td colspan=7 align=left>&nbsp; &nbsp; <?php echo $sYouWonLink;?></td></tr>

<tr><Td align=left valign=top>Pop Up Text</a></th>

<Td align=left valign=top><textarea name=sYouWonPopUpText rows=3 cols=40><?php echo $sYouWonPopUpText;?></textarea></td>

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
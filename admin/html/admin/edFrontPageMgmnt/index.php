<?php

/*********

Script to Set You Won PopUp Delay

**********/

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");


$sPageTitle = "MyFree Front Page Management";

session_start();

// Check user permission to access this page

if (hasAccessRight($iMenuId) || isAdmin()) {
	
	if ($sSave) {
		// if record edited
		$selectQuery="SELECT * FROM vars
					  WHERE  varName = 'noOfTrivia'";
		$selectResult = mysql_query($selectQuery);
		if (mysql_num_rows($selectResult) == 0) {
			$insertQuery = "INSERT INTO vars(varName, varValue)
							VALUES('noOfTrivia', '$noOfTrivia')";
			$sTempData = "\n$insertQuery";
			$insertResult = mysql_query($insertQuery);
		} else {
			
			$editQuery = "UPDATE vars SET
							varValue = '$noOfTrivia'
							WHERE varName = 'noOfTrivia'";
			$sTempData .= "\n$editQuery";
			$result = mysql_query($editQuery);
			//	echo mysql_error();
			if (!($result)) {
				$sMessage=mysql_error();
			}
		}
		
		// if record edited
		$selectQuery="SELECT * FROM vars
					  WHERE  varName = 'noOfJokes'";
		$selectResult = mysql_query($selectQuery);
		if (mysql_num_rows($selectResult) == 0) {
			$insertQuery = "INSERT INTO vars(varName, varValue)
							VALUES('noOfJokes', '$noOfJokes')";
			$sTempData .= "\n$insertQuery";
			$insertResult = mysql_query($insertQuery);
		} else {
			
			$editQuery = "UPDATE vars SET
							varValue = '$noOfJokes'
							WHERE varName = 'noOfJokes'";
			$sTempData .= "\n$editQuery";
			$result = mysql_query($editQuery);
			
			if (!($result)) {
				$sMessage=mysql_error();
			}
		}
		
		// if record edited
		$selectQuery="SELECT * FROM vars
					  WHERE  varName = 'noOfStickers'";
		$selectResult = mysql_query($selectQuery);
		if (mysql_num_rows($selectResult) == 0) {
			$insertQuery = "INSERT INTO vars(varName, varValue)
							VALUES('noOfStickers', '$noOfStickers')";
			$sTempData .= "\n$insertQuery";
			$insertResult = mysql_query($insertQuery);
		} else {
			
			$editQuery = "UPDATE vars SET
							varValue = '$noOfStickers'
							WHERE varName = 'noOfStickers'";
			$sTempData .= "\n$editQuery";
			$result = mysql_query($editQuery);
			//	echo mysql_error();
			if (!($result)) {
				$sMessage=mysql_error();
			}
		}
		$sMessage = "Changes are saved...";
		//header("Location:index.php?menuId=$menuId&message=$sMessage");
		
		// start of track users' activity in nibbles
		$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
		$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Edit Queries: $sTempData\")";
		$rResult = dbQuery($sAddQuery);
		echo  dbError();
		// end of track users' activity in nibbles
		
	}
	
	
	// get no. of jokes/trivia/stickers to be displayed in front page
	$varQuery = "SELECT *
			 FROM   vars
			 WHERE  varName IN ('noOfTrivia','noOfJokes','noOfStickers')";
	$varResult = mysql_query($varQuery);
	while ($varRow = mysql_fetch_object($varResult)) {
		$varName = $varRow->varName;
		$$varName = $varRow->varValue;
	}
	
	// Hidden fields to be passed with form submission
	$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=id value='$id'>";
	
	$sortLink = $PHP_SELF."?iMenuId=$iMenuId";
	$youWonLink = "<a href='index.php?iMenuId=$iMenuId'>Back To You Won Admin Menu</a>";
	
		
	include("../../includes/adminHeader.php");	
	
?>


<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $hidden;?>		

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=55% align=center>
<tr><Td align=left valign=top>No. Of Trivia</a></td>

<Td align=left valign=top><input type=text name = noOfTrivia value="<?php echo $noOfTrivia;?>"></td>

</tr>
<tr><Td align=left valign=top>No. Of Jokes</a></td>

<Td align=left valign=top><input type=text name = noOfJokes value="<?php echo $noOfJokes;?>"></td>

</tr>
<tr><Td align=left valign=top>No. Of Stickers/Slogans</a></td>

<Td align=left valign=top><input type=text name = noOfStickers value="<?php echo $noOfStickers;?>"></td>

</tr>
<tr><td></tD><td><input type=submit name=sSave value='Save'></td></tr>
</table>
</td></tr>
</table>
</form>

<?php
	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
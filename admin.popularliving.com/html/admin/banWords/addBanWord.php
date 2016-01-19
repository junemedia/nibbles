<?php

/*********

Script to Display Add/Edit Banned Words
**********/

include("../../includes/paths.php");

session_start();

$sPageTitle = "Nibbles Banned Words - Add/Edit Banned Word";

if (hasAccessRight($iMenuId) || isAdmin()) {
	
if (($sSaveClose || $sSaveNew) && !($iId)) {
	// if new banned word added
	// first check to see that this word isn't already in the list

	$sDupeQuery = "SELECT * FROM bannedWords WHERE lower(word) = lower('$sWord');";
	//$sMessage += $sDupeQuery;
	$rResult = dbQuery($sDupeQuery);
	if (!($rResult))
		$sMessage = dbError();
	if(dbNumRows($rResult)){
		$sMessage = "This word was found to be a duplicate of an existing word. NOT INSERTED.";
	} else {
		$sAddQuery = "INSERT INTO bannedWords(word) 
				 VALUES(lower('$sWord'))";

		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Add: $sAddQuery\")"; 
		$rLogResult = dbQuery($sLogAddQuery); 
		echo  dbError(); 
		// end of track users' activity in nibbles		
		
		
		$rResult = dbQuery($sAddQuery);
		if (!($rResult))
		$sMessage = dbError();
	}
	
} else if (($sSaveClose || $sSaveNew) && ($iId)) {
	//check for duplicates 
	
	$sDupeQuery = "SELECT * FROM bannedWords WHERE lower(word) = lower('$sWord');";
	
	//$sMessage += $sDupeQuery;
	$rResult = dbQuery($sDupeQuery);
	if (!($rResult))
		$sMessage = dbError();
	if(dbNumRows($rResult)){
		$sMessage = "This word was found to be a duplicate of an existing word. NOT INSERTED.";
	} else {
		
		$sEditQuery = "UPDATE bannedWords
					  SET word = lower('$sWord')
					  WHERE id = '$iId'";
	
		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
	
		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Edit: $sEditQuery\")"; 
		$rLogResult = dbQuery($sLogAddQuery); 
		echo  dbError(); 
		// end of track users' activity in nibbles		
		
		
		$rResult = dbQuery($sEditQuery);
		
		if (!($rResult)) {
			$sMessage = dbError();
		}
	}
}

if ($sSaveClose) {
	echo "<script language=JavaScript>
			var href = window.opener.location.href.replace('&sDelete=Delete','');\n";
			if($sMessage) echo "href += \"&sMessage=$sMessage\";\n";
			echo "window.opener.location.replace(href);
				self.close();
			</script>";			
	// exit from this script
	exit();		
} else if ($sSaveNew) {
	$sReloadWindowOpener = "<script language=JavaScript>
			var href = window.opener.location.href.replace('&sDelete=Delete','');\n";
			if($sMessage) $sReloadWindowOpener += "href += \"&sMessage=$sMessage\";\n";
			$sReloadWindowOpener += "window.opener.location.replace(href);
							</script>";	
}

if ($iId) {
	
	// If Clicked to edit, get the data to display in fields 
	
	$sSelectQuery = "SELECT * FROM bannedWords
				    WHERE  id = '$iId'";
	$rSelectResult = dbQuery($sSelectQuery);
	while ($oSelectRow = dbFetchObject($rSelectResult)) {
		$sWord = $oSelectRow->word;
	}	
	/*
	if($sMessage){
		echo "<script language=JavaScript>
			alert('$sMessage');
			</script>";
	}*/			
	// exit from this script
	exit();		
} else {	
	
	// If add button is clicked, display another two buttons
	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=sAbandonNew value=' Abandon & New  '>";	
}
	
// Hidden fields to be passed with form submission
$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>			
			<input type=hidden name=iId value='$iId'>";
	
include("../../includes/adminAddHeader.php");
?>
<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $sHidden;?>
<?php if($sReloadWindowOpener) echo $sReloadWindowOpener; ?>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
		<tr><TD>Ban Word</td><td><input type=text name=sWord value='<?php echo $sWord;?>'></td></tr>		
	</table>
		
<?php
	include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>

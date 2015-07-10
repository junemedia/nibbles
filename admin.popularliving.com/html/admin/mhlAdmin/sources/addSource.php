<?php

/*********

Script to Display List/Add/Edit/Delete Affiliate Management Company information

*********/

include("../../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

session_start();

$sPageTitle = "MyHealthyLiving Source Management - Add/Edit Source";

if (hasAccessRight($iMenuId) || isAdmin()) {
		
	// SELECT HCV DATABASE
	dbSelect($sGblMhlDBName);	
	
if (($sSaveClose || $sSaveNew) && !($id || $ssId)) {
	
	// if new data submitted
	//Check For Dupe
	$checkQuery = "SELECT *
				   FROM   source_codes
				   WHERE  srcCode = '$srcCode'";
	$checkResult = dbQuery($checkQuery);
	if (dbNumRows($checkResult) > 0 ) {
		$sMessage = "Source Code Exists...";
		$keepValues = true;
	} else {
		if ($sscSrcID) {
			//$srcURL = "$hlSiteRoot/index.php?s=$srcCode&ss=$sscCode";
			// get parent source code
			$tempQuery = "SELECT srcCode
						  FROM   source_codes
						  WHERE  srcID = '$sscSrcID'";
			$tempResult = dbQuery($tempQuery);
			while ($tempRow = dbFetchObject($tempResult)) {
				$parentSrcCode = $tempRow->srcCode;
			}
			$srcURL = "$sGblMhlSiteRoot/index.php?s=".urlencode($parentSrcCode)."&ss=".urlencode($srcCode);
			
			$addQuery = "INSERT INTO sub_source_codes(sscSrcID, sscCode, sscName, sscURL, sscNotes)
				 VALUES('$sscSrcID', '$srcCode', '$srcName', '$srcURL', '$srcNotes')";
		} else {
			$srcURL = "$sGblMhlSiteRoot/index.php?s=".urlencode($srcCode);
			$addQuery = "INSERT INTO source_codes(srcCode, srcName, srcURL, srcNotes)
				 VALUES('$srcCode', '$srcName', '$srcURL', '$srcNotes')";
		}

		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Add: $addQuery\")"; 
		$rLogResult = dbQuery($sLogAddQuery); 
		echo  dbError(); 
		// end of track users' activity in nibbles		
		
		
		$result = dbQuery($addQuery);
		if (! $result) {
			echo dbError();
		}
	}
	
} elseif (($sSaveClose || $sSaveNew) && ($id || $ssId)) {
	//if record edited
	if ($ssId) {
		$tempQuery = "SELECT srcCode
						  FROM   source_codes
						  WHERE  srcID = '$sscSrcID'";
			$tempResult = dbQuery($tempQuery);
			while ($tempRow = dbFetchObject($tempResult)) {
				$parentSrcCode = $tempRow->srcCode;
			}
			$srcURL = "$sGblMhlSiteRoot/index.php?s=".urlencode($parentSrcCode)."&ss=".urlencode($srcCode);
			
		//Check For Dupe
		$checkQuery = "SELECT *
				   FROM   sub_source_codes
				   WHERE  sscCode = '$srcCode'
				   AND    sscSrcID = '$sscSrcID'
					AND   srcID != '$ssId'";
		$checkResult = dbQuery($checkQuery);
		if (dbNnumRows($checkResult) > 0 ) {
			$sMessage = "Sub Source Code Exists...";
			$keepValues = true;
		} else {
			
			$editQuery = "UPDATE sub_source_codes
				  	  SET 	 sscSrcID = $sscSrcID,
							 sscCode = '$srcCode',
							 sscName = '$srcName',
						 	 sscURL = '$srcURL'	,
							 sscNotes = '$srcNotes'							
				  	  WHERE  sscID = '$ssId'";
			//echo $updateQuery;

			// start of track users' activity in nibbles 
			$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
	
			$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Edit: $editQuery\")"; 
			$rLogResult = dbQuery($sLogAddQuery); 
			echo  dbError(); 
			// end of track users' activity in nibbles		
			
			
			$result = dbQuery($editQuery);
			echo dbError();
		}
	} else {
		$srcURL = "$sGblMhlSiteRoot/index.php?s=".urlencode($srcCode);
		//Check For Dupe
		$checkQuery = "SELECT *
				   FROM   source_codes
				   WHERE  srcCode = '$srcCode'
					AND   srcID != '$id'";
		$checkResult = dbQuery($checkQuery);
		if (dbNumRows($checkResult) > 0 ) {
			$sMessage = "Source Code Exists...";
			$keepValues = true;
		} else {
			
			$editQuery = "UPDATE source_codes
				  	  SET 	 srcCode = '$srcCode',
							 srcName = '$srcName',
						 	 srcURL = '$srcURL'	,
							 srcNotes = '$srcNotes'					 
				  	  WHERE  srcID = '$id'";
			//echo $updateQuery;

			// start of track users' activity in nibbles 
			$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
	
			$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Edit: $editQuery\")"; 
			$rLogResult = dbQuery($sLogAddQuery); 
			echo  dbError(); 
			// end of track users' activity in nibbles		
			
			$result = dbQuery($editQuery);
		}
	}
	//echo $editQuery.$result;
}

if ($sSaveClose) {
	if ($keepValues !=true) {
		echo "<script language=JavaScript>
			window.opener.location.reload();
			self.close();
			</script>";					
		// exit from this script
		exit();
	}
} else if ($sSaveNew) {
	$reloadWindowOpener = "<script language=JavaScript>
							window.opener.location.reload();
							</script>";
	// Reset textboxes for new record
	if ($keepValues != true) {
		$srcCode = '';
		$srcName = '';
		$srcURL = '';
		$srcNotes = '';
	}
}

if ($id != '') {
	// If Clicked on Edit, display values in fields
	
	// Get the data to display in HTML fields for the record to be edited
	$selectQuery = "SELECT *
					FROM   source_codes
			  		WHERE  srcID = '$id'";
	
	$result = dbQuery($selectQuery);
	if ($result) {
		
		while ($row = dbFetchObject($result)) {
			$srcCode = $row->srcCode;
			$srcName = $row->srcName;
			$srcURL = $row->srcURL;
			$srcNotes = ascii_encode($row->srcNotes);
			//$srcUrl = "$hlSiteRoot/index.php?s=$row->srcCode";
			$srcURLDisplay = "<TR><td>URL</td><td>$srcURL</td></tr>";
		}
		dbFreeResult($result);
	} else {
		echo dbError();
	}
} else if ($ssId != ''){
	
	// If Clicked on Edit, display values in fields
	
	// Get the data to display in HTML fields for the record to be edited
	$selectQuery = "SELECT *
					FROM   sub_source_codes, source_codes
			  		WHERE  sscID = '$ssId'
					AND   sub_source_codes.sscSrcID = source_codes.srcID";
	
	$result = dbQuery($selectQuery);
	if ($result) {
		
		while ($row = dbFetchObject($result)) {
			$srcCode = $row->sscCode;
			$srcName = $row->sscName;
			$srcURL = $row->sscURL;
			$srcNotes = ascii_encode($row->sscNotes);
			$parentSrcID = $row->sscSrcID;
			//$srcURL = "$hlSiteRoot/index.php?s=$row->srcCode&ss=$row->sscCode";
			$srcURLDisplay = "<TR><td>URL</td><td>$srcURL</td></tr>";
//			$srcURL = "<TR><td>URL</td><td>$hlSiteRoot/index.php?s=$row->srcCode&ss=$row->sscCode</td></tr>";
			
		}
		dbFreeResult($result);
	} else {
		echo dbError();
	}
	
} else {
	// If add button is clicked, display another two buttons
	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=sAbandonNew value=' Abandon & New  '>";	
}

// prepare source code options
//display source code options only while adding or editing subsource code
// and not while editing source code
if ($ssId || !($id || $ssId)) {
	$srcCodeOptions .= "<tr><td>Select SourceCode</td>
		<td><select name=sscSrcID>
						<option value=''>";

$srcQuery = "SELECT *
			 FROM   source_codes
			 ORDER BY srcCode";
$srcResult = dbQuery($srcQuery);
while ($srcRow = dbFetchObject($srcResult)) {
	if ($srcRow->srcID == $parentSrcID) {
		$selected = "selected";
	} else {
		$selected = "";
	}
	
	$srcCodeOptions .= "<option value=$srcRow->srcID $selected>$srcRow->srcCode";
}
$srcCodeOptions .= "</select></td>
	</tr>";
}
// Hidden variable to be passed with form submit
$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>			
			<input type=hidden name=iParentMenuId value='$iParentMenuId'>
			<input type=hidden name=sParentMenuFolder value='$sParentMenuFolder'>
			<input type=hidden name=id value='$id'>
			<input type=hidden name=ssId value='$ssId'>";

	include("$sGblIncludePath/adminAddHeader.php");	
?>

<form action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $hidden;?>
<?php echo $reloadWindowOpener;?>
<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
	<tr><td width=35%>Source Code</td>
		<td><input type=text name='srcCode' value='<?php echo $srcCode;?>' ></td>
	</tr>
	<tr><td>Source Name</td>
		<td><input type=text name='srcName' value='<?php echo $srcName;?>' ></td>
	</tr>
	<?php echo $srcURLDisplay;?>
	<tr><td>Source Notes</td>
		<td><textarea name='srcNotes' rows=4 cols=40><?php echo $srcNotes;?></textarea></td>
	</tr>
	
		<?php echo $srcCodeOptions;?>
		
</table>

<?php

include("$sGblIncludePath/adminAddFooter.php");
				
} else {
	echo "You are not authorized to access this page...";
}	

?>

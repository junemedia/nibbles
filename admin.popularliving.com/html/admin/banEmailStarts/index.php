<?php

/*********

Script to Display List/Delete Banned Email Starting
**********/

include("../../includes/paths.php");

session_start();

$sPageTitle = "Nibbles Banned Emails Starts - List/Delete Banned Email Starts";

// Check user permission to access this page

if (hasAccessRight($iMenuId) || isAdmin()) {		
	if ($sDelete) {
		// if record deleted
		
		$sDeleteQuery = "DELETE FROM bannedEmailStart
	 			   		WHERE  id = $iId"; 

		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Delete: $sDeleteQuery\")"; 
		$rLogResult = dbQuery($sLogAddQuery); 
		echo  dbError(); 
		// end of track users' activity in nibbles		
		
		
		$rResult = dbQuery($sDeleteQuery);
		if (!($rResult)) {
			$sMessage = dbError();
		}
		// reset $id
		$iId = '';
	}
	
	// Select Query to display list of banned email starting
	
	$sSelectQuery = "SELECT * FROM bannedEmailStart";
	
	$rSelectResult = dbQuery($sSelectQuery);
	
	while ($oRow = dbFetchObject($rSelectResult)) {
		
		// For alternate background color
		if ($sBgcolorClass=="ODD") {
			$sBgcolorClass="EVEN";
		} else {
			$sBgcolorClass="ODD";
		}
		$sBannedEmailStartList .= "<tr class=$sBgcolorClass><TD>$oRow->startsWith</td>		
						<TD><a href='JavaScript:void(window.open(\"addBanEmailStart.php?iMenuId=$iMenuId&iId=".$oRow->id."\", \"bannedEmailStart\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>
					    &nbsp;<a href='JavaScript:confirmDelete(this,".$oRow->id.");' >Delete</a>
						</td></tr>";
		
		if($sExportBanned){
			$sExportData .= $oRow->startsWith."\r\n";
		}
	}
	
	if (dbNumRows($rSelectResult) == 0) {
		$sMessage = "No Banned Emails Exist...";
	}
	
	if(($sExportBanned) && ($sExportData != '')){
		//open the file for writing
		$sFilePath = $sGblWebRoot.'/temp/';
		$sFileName = 'bannedEmailStart_'.date('Y').date('M').date('d').'_'.date('H').date('i').date('s').'.txt';
		$rFile = fopen($sFilePath.$sFileName,'w');
		
		//write the file
		fwrite($rFile, $sExportData);
		fclose($rFile);
		//show a popup with the download link
		
		echo "<script language=JavaScript>
				void(window.open(\"$sGblSiteRoot/download.php?sFile=$sFileName\",\"\",\"height=150, width=300, scrollbars=yes, resizable=yes, status=yes\"));
			  </script>";
		
	}
	
	// Hidden fields to pass with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>";

	$sAddButton ="<input type=button name=sAdd value=Add onClick='JavaScript:void(window.open(\"addBanEmailStart.php?iMenuId=$iMenuId\", \"bannedEmailStart\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>";
	$sExportLink ="<a href='".$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']."&sExportBanned=1'>Export Banned Email Starts</a>";	
		
	include("../../includes/adminHeader.php");	
	
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

<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $sHidden;?>
<input type=hidden name=sDelete>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td colspan=7 align=left><?php echo $sAddButton;?></td></tr>
<tr><td class=header>Banned Email Starting</td><td><?php echo $sExportLink;?></td>
</tr>

<?php echo $sBannedEmailStartList;?>
<tr><td colspan=7 align=left><?php echo $sAddButton;?></td></tr>
</table>

</form>
	
<?php
	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
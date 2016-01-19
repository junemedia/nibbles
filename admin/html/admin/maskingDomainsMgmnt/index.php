<?php


include("../../includes/paths.php");

session_start();

$sPageTitle = "Nibbles - Masking Domains Mgmnt";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

// Check user permission to access this page
if (hasAccessRight($iMenuId) || isAdmin()) {
		
	if ($sDelete) {
		$sDeleteQuery = "DELETE FROM maskingDomains WHERE  id = $iId";
		$rResult = dbQuery($sDeleteQuery);
		$iId = '';
				
		// start of track users' activity in nibbles
		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($sDeleteQuery) . "\")";
		$rLogResult = dbQuery($sLogAddQuery);
		// end of track users' activity in nibbles
	}
	
	$sSelectQuery = "SELECT * FROM maskingDomains ORDER BY domainName ASC";
	$rSelectResult = dbQuery($sSelectQuery);
	$sDomainList = '';
	while ($oRow = dbFetchObject($rSelectResult)) {
		if ($sBgcolorClass=="ODD") {
			$sBgcolorClass="EVEN";
		} else {
			$sBgcolorClass="ODD";
		}
		
		$sCheckIfDomainIsInUse = "SELECT * FROM links WHERE domainId = '$oRow->id'";
		$rCheckResult = dbQuery($sCheckIfDomainIsInUse);
		$sDeleteTempVal = '&nbsp;&nbsp;&nbsp;Delete';
		if (dbNumRows($rCheckResult) == 0) {
			$sDeleteTempVal = "&nbsp;&nbsp;&nbsp;<a href='JavaScript:confirmDelete(this,$oRow->id);' >Delete</a>";
		}
		
		
		$sDesc = substr($oRow->description,0,30);
		$sDomainList .= "<tr class=$sBgcolorClass><td>$oRow->domainName</td>
						<td>$sDesc...</td>
						<td><a href='JavaScript:void(window.open(\"addDomain.php?iMenuId=$iMenuId&id=".$oRow->id."\", \"AddContent\", \"height=300, width=650, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>
						$sDeleteTempVal</td></tr>";
	}
	
	if (dbNumRows($rSelectResult) == 0) {
		$sMessage = "No Records Exist...";
	}
	
	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>";

	$sAddButton ="<input type=button name=sAdd value=Add onClick='JavaScript:void(window.open(\"addDomain.php?iMenuId=$iMenuId\", \"\", \"height=300, width=650, scrollbars=yes, resizable=yes, status=yes\"));'>";
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
<tr><td colspan=5 align=left><?php echo $sAddButton;?></td></tr>
<tr><td class=header>Domain</td><td class=header>Description</td>
</tr>
<?php echo $sDomainList;?>
<tr><td colspan=5 align=left><?php echo $sAddButton;?></td></tr>
</table>
<br><br>
<table cellpadding=5 cellspacing=0 width=95% align=center>
<tr>
<td>Notes:</td>
<td>Delete link is disabled if domain is being used by flow.</td>
</tr>
</table>



</form>
<?php
	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
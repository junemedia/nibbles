<?php 



include("../../includes/paths.php");

$sPageTitle = "Flow Category";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
	$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
	
	$sTempSrc = trim($_GET['sTempSrc']);
	if ($sTempSrc != '') {
		if ($_SERVER['SERVER_NAME'] == 'test.popularliving.com') {
			$sDisplayUrl = "http://".$_SERVER['SERVER_NAME']."/r/r.php?src=$sTempSrc";
		} else {
			$sDisplayUrl = "http://www.popularliving.com/r/r.php?src=$sTempSrc";
		}
		$sDisplayUrl = "<center><font face=\"Arial, Helvetica, sans-serif\" size=2><b>Redirect URL:</b>&nbsp; &nbsp;<a href= 'JavaScript:void(window.open(\"".$sDisplayUrl."\",\"\", \"\"));'>" . $sDisplayUrl . "</a></font></center>";
	}

	if ($delete) {
		$deleteQuery = "DELETE FROM flows WHERE id='$id'";
		$sTempTracking = $deleteQuery;
		$deleteResult = mysql_query($deleteQuery);
		if ($deleteResult) {
			$sDeleteDetailQuery = "DELETE FROM flowDetails WHERE flowId='$id'";
			$sTempTracking .= "\n\n".$sDeleteDetailQuery;
			$deleteResult = mysql_query($sDeleteDetailQuery);
		}
		// start of track users' activity in nibbles
		$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Delete: $sTempTracking\")";
		$rResult = dbQuery($sAddQuery);
		echo  dbError();
		// end of track users' activity in nibbles
	}
	
	// Set Default order column
	if (!($orderColumn)) {
		$orderColumn = "sourceCode";
		$titleOrder = "ASC";
	}
	
	// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
	switch ($orderColumn) {
		case "description":
		$currOrder = $sDescriptionOrder;
		$sDescriptionOrder = ($sDescriptionOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "sourceCode":
		$currOrder = $sSourceCodeOrder;
		$sSourceCodeOrder = ($sSourceCodeOrder != "DESC" ? "DESC" : "ASC");
	}
	
	// Query to get the list of BDPartners
	$selectQuery = "SELECT *
					FROM   flows
					WHERE nibblesVersion = '1'
					ORDER BY $orderColumn $currOrder";
	
	// start of track users' activity in nibbles
	$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Display Data: $selectQuery\")";
	$rResult = dbQuery($sAddQuery);
	echo  dbError();
	// end of track users' activity in nibbles
	$sSortLink = $PHP_SELF."?iMenuId=$iMenuId";
	$result = mysql_query($selectQuery);
	
	if ($result) {
		if (mysql_num_rows($result) > 0) {
			while ($row = mysql_fetch_object($result)) {
				if ($bgcolorClass == "ODD") {
					$bgcolorClass = "EVEN";
				} else {
					$bgcolorClass = "ODD";
				}
				$sDescription = substr($row->description,0,30)." ...";
				$ReportList .= "<tr class=$bgcolorClass>
						<td>$row->sourceCode</td><td>$sDescription</td>
						<td><a href='JavaScript:void(window.open(\"addFlow.php?iMenuId=$iMenuId&id=".$row->id."\", \"AddContent\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>
						&nbsp; <a href='JavaScript:confirmDelete(this,".$row->id.");' >Delete</a>
						&nbsp; <a href='$sSortLink&sTempSrc=".$row->sourceCode."'>Show Redirect URL</a>
						&nbsp;<a href='JavaScript:void(window.open(\"addFlowDetails.php?sTempSrc=".$row->sourceCode."&iMenuId=$iMenuId&id=".$row->id."\",\"\",\"scrollbars=yes, resizable=yes, status=yes\"));'>Flow Details</a> </td></tr>
						</td>";
			}
		} else {
			$sMessage = "No Records Exist...";
		}
		mysql_free_result($result);
		
	} else {
		echo mysql_error();
	}

	$addButton = "<input type=button name=add value=Add onClick='JavaScript:void(window.open(\"addFlow.php?iMenuId=$iMenuId\", \"\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>";

	// Hidden variable to be passed with form submit
	$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=id value='$id'>";
	
	$sortLink = $PHP_SELF."?iMenuId=$iMenuId";
	
	
	include("$sGblIncludePath/adminHeader.php");	
	
			
?>


<script language=JavaScript>
				function confirmDelete(form1,id)
				{
					if(confirm('Are you sure to delete this record ?'))
					{							
						document.form1.elements['delete'].value='Delete';
						document.form1.elements['id'].value=id;
						document.form1.submit();								
					}
				}						
</script>
<?php echo $sDisplayUrl;?>	
<form name=form1 action='<?php echo $PHP_SELF;?>'>

<input type=hidden name=delete>

<?php echo $hidden;?>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>

<tr><th colspan=3 align=left><?php echo $addButton;?></th></tr>
<tr>
<td align=left><a href='<?php echo $sortLink;?>&orderColumn=sourceCode&sSourceCodeOrder=<?php echo $sSourceCodeOrder;?>' class=header>Source Code</a></td>
	<td align=left><a href='<?php echo $sortLink;?>&orderColumn=description&sDescriptionOrder=<?php echo $sDescriptionOrder;?>' class=header>Description</a></td>
	<td>&nbsp; </td>
</tr>
<?php echo $ReportList;?>
<tr><th colspan=7 align=left><?php echo $addButton;?></th></tr>
</table>

</form>

<?php
include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}				
?>	


<?php

/*********

Script For Funpage NL Template

**********/

include("../../../includes/paths.php");

$sPageTitle = "Fun Page NL Template";

session_start();

// Check user permission to access this page

if (hasAccessRight($iMenuId) || isAdmin()) {
	
	
	if ($produceNl || $produceNlOldest) {
		
		// start of track users' activity in nibbles
		$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
		$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Display: Fun Page NL Template\")";
		$rResult = dbQuery($sAddQuery);
		echo  dbError();
		// end of track users' activity in nibbles
	
	
		
		$selectQuery = "SELECT *
				   		FROM   fpNlTemplate";	
		
		$selectResult = mysql_query($selectQuery);
		if (mysql_num_rows($selectResult) != 0 ) {
			$addButton = "";
		}
		while ($row = mysql_fetch_object($selectResult)) {
			
			$templateContent = htmlspecialchars($row->content);
			
		}
		
		// get the funpage
		if ($produceNl) {
		$funpageQuery = "SELECT *
						 FROM   funPages
						 WHERE  id = '$funPageId'";
		} else if ($produceNlOldest) {
			$funpageQuery = "SELECT funPages.*
							 FROM   funPages LEFT JOIN funPageNl ON funPages.id = funPageNl.funPageId
						 ORDER BY sentDate LIMIT 1";
		}
		$funpageResult = mysql_query($funpageQuery);
		
		while ($funpageRow = mysql_fetch_object($funpageResult)) {
			$funPageId = $funpageRow->id;
			$funpageTitle = $funpageRow->title;
			$image = $funpageRow->image;
			$sound = $funpageRow->sound;
			//$funpageurl = $funpageRow->funpageurl;			
			
		}

		$imageUrl = $sGblFpSiteRoot."/images";
		$soundUrl = $sGblFpSiteRoot."/sounds";
		
		$fpContent = "<Script Language=\"JavaScript\"> 
<!-- 
if (navigator.appName == \"Netscape\") { 
document.write( \"<embed src='$soundUrl/$sound' autostart='true' loop='true' hidden='true' volume=25>\"); 
} else { 
document.write( \"<bgsound src='$soundUrl/$sound' loop='25' volume='25'>\"); 
} // --> 
</Script>
		<table width=530 align=center border=0>
<tr><td>
<img border=10 src='$imageUrl/$image' >
</td></tr>
</table>";		
		
		$nlContent = ereg_replace("\[NL\]",$fpContent, $templateContent);
		if (mysql_num_rows($selectResult)==0) {
			$message = "Template Doesn't exist...";
		}
		
		$updateQuery = "INSERT INTO funPageNl(funPageId, sentDate)
						VALUES('$funPageId', CURRENT_DATE)";						
		$updateResult = mysql_query($updateQuery);
	}
	
	// get funpages list
	$funpageQuery = "SELECT *
					 FROM    funPages
					 ORDER BY title";
	$funpageResult = mysql_query($funpageQuery) ;
	while ($funpageRow = mysql_fetch_object($funpageResult)) {
		$funpageOptions .= "<option value='$funpageRow->id'>$funpageRow->title";
	}

	$editTemplateLink = "<a href='nlTemplate.php?iMenuId=$iMenuId&iParentMenuId=$iParentMenuId&sParentMenuFolder=$sParentMenuFolder'>Edit Template</a>";


	// Hidden fields to be passed with form submission
	$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iParentMenuId value='$iParentMenuId'>
			<input type=hidden name=sParentMenuFolder value='$sParentMenuFolder'>
			<input type=hidden name=id value='$id'>";	
	
		
	include("$sGblIncludePath/adminHeader.php");
	
?>

<form name=form1 action='<?php echo $PHP_SELF;?>'>

<?php echo $hidden;?>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td colspan=3><?php echo $editTemplateLink;?></td></tr>
	<tr><td>Select Fun Page:</td>
		<td colspan=2><select name=funPageId>
		<?php echo $funpageOptions;?>
		</select></td></tr>
		<tr><td></td><td colspan=2><input type=submit name=produceNl value="Produce NL From The List">
		&nbsp; &nbsp; &nbsp; <input type=submit name=produceNlOldest value="Produce NL With Oldest Fun Page"></td></tr>		
		<tr><Td valign=top width=100>Newsletter Content:</td>
		<td valign=top width=500>
		<textarea name=templateContent rows=30 cols=70><?php echo $nlContent;?></textarea></td>
		<td valign=top><?php echo $funpageTitle;?></td></tr>	
			
</table>

</form>


<?php
include("../../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}

?>
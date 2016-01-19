<?php

/*********

Script For Funpage NL Template

**********/

include("../../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

$sPageTitle = "Fun Page NL Template";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
	
	if ($sSave) {
		
		// if new data submitted
		$checkQuery = "SELECT *
						FROM   fpNlTemplate";
		$checkResult = mysql_query($checkQuery);
		if (mysql_num_rows($checkResult) ==0 ) {
			
			$addQuery = "INSERT INTO fpNlTemplate(content)
				 VALUES(\"$templateContent\")";		
			$result = mysql_query($addQuery);
			if (!($result))
			$message = mysql_error();
		} else {
			$templateContent = addslashes($templateContent);
			$editQuery = "UPDATE fpNlTemplate
					  SET	 content = \"$templateContent\"";
			$result = mysql_query($editQuery);
			if (!($result)) {
				$message=mysql_error();
			}
		}
	}
		$templateQuery = "SELECT *
				  		 FROM   fpNlTemplate";
		$templateResult = mysql_query($templateQuery);
		echo dbError();
		while ($templateRow = mysql_fetch_object($templateResult)) {
			
			$templateContent = ascii_encode(stripslashes($templateRow->content));
		}
		
		if (mysql_num_rows($templateResult)==0) {
			$message = "No records exist...";
		}
		
		$produceNlLink = "<a href='index.php?iMenuId=$iMenuId&iParentMenuId=$iParentMenuId&sParentMenuFolder=$sParentMenuFolder'>Produce NL</a>";
		
		// Hidden fields to be passed with form submission
		$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iParentMenuId value='$iParentMenuId'>
			<input type=hidden name=sParentMenuFolder value='$sParentMenuFolder'>
			<input type=hidden name=id value='$id'>";
		
		
	include("$sGblIncludePath/adminHeader.php");
		
		?>
		
		
<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>

<?php echo $hidden;?>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td colspan=2><?php echo $produceNlLink;?></td></tr>	
		<tr><td colspan=2>[NL] will be replaced with the FunPage Content.</td></tr>	
		<tr><Td valign=top>Template Content</td><td>
		<textarea name=templateContent rows=30 cols=70><?php echo $templateContent;?></textarea></td></tr>
	<tr><Td></td><td><input type=submit name=sSave value='Save'> &nbsp; 
			<input type=reset name=sReset value='Reset'></td>
	</tr>	
			
</table>

</form>

<?php
include("../../../includes/adminFooter.php");
	} else {
		echo "You are not authorized to access this page...";
	}
	
?>
<?php

/*******

Script to Display You Won Admin Menu

*********/

include("../../includes/paths.php");

$sPageTitle = "You Won Admin Menu";

session_start();

// Check if user is permitted to view this page

if (hasAccessRight($iMenuId) || isAdmin()) {

$sEmailContentsLink = "<a href = 'emailContents.php?iMenuId=$iMenuId'>Edit eMail Contents</a>";

$sUpdateIndexTextLink = "<a href = 'updateIndexText.php?iMenuId=$iMenuId'>Manage Index.php Text</a>";
$sEditIndexHeadlineLink = "<a href = 'editIndexHeadline.php?iMenuId=$iMenuId'>Edit Index.php Headline<a/>";
$sEditPage4PopupTextLink = "<a href = 'editPopUpText.php?iMenuId=$iMenuId'>Edit PopUp Text</a>";
$sReportLink = "<a href='report.php?iMenuId=$iMenuId'>You Won Reporting</a>";


include("../../includes/adminHeader.php");	
				

?>


<!--<center>{PARENT_MENU_LINK}</center>-->

<table align="center" width="550">

<tr><td valign="top" bgcolor = "eeeeee" width="50%" height=30>
		<ul>
		<li><?php echo $sEmailContentsLink;?></li>
		</ul>
	</td>
	<td valign="top" bgcolor = "eeeeee" width="50%">
		<ul>
		<li><?php echo $sUpdateIndexTextLink;?></li>
		</ul>				
	</td>
</tr>
<tr><td valign="top" bgcolor = "eeeeee" width="50%" height=30>
		<ul>
		<li><?php echo $sEditIndexHeadlineLink;?></li>
		</ul>
	</td>
	<td valign="top" bgcolor = "eeeeee" width="50%">		
		<ul>
		<li><?php echo $sEditPage4PopupTextLink;?></li>
		</ul>
	</td>
</tr>
<tr><td valign="top" bgcolor = "eeeeee" width="50%" height=30>
		<ul>
		<li><?php echo $sReportLink;?></li>
		</ul>
	</td>
	<td valign="top" bgcolor = "eeeeee" width="50%">		
		
	</td>
</tr>
</table>
<br>


<?php 
	include("../../includes/adminFooter.php");
	
} else {
	echo "You are not authoresed to access this page...";
}				

?>
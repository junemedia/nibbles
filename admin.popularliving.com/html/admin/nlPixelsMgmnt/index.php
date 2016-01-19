<?php

/*******

Script to Generate New Pixel for Newsletter

*********/

include("../../includes/paths.php");

$sPageTitle = "Manage Newsletters Pixels";

session_start();

// Check user permission to access this page

if (hasAccessRight($iMenuId) || isAdmin()) {

$generatePixelLink = "<a href='generatePixel.php?iMenuId=$iMenuId'>Generate New Pixel</a>";
$reportLink = "<a href='report.php?iMenuId=$iMenuId'>Pixel Tracking Report</a>"; 
			
		
	include("../../includes/adminHeader.php");	
?>

<table align="center" width="550">
<tr>
<td colspan="2" align="center" bgcolor = "c1c1c1"><b>Manage Newsletters Pixels</b></td>
</tr>
<tr><td valign="top" bgcolor = "eeeeee" width="50%">
					<ul>
					<li><?php echo $generatePixelLink;?></li>
					</ul>
	</td>
	<td valign="top" bgcolor = "eeeeee" width="50%">
					<ul>
					<li><?php echo $reportLink;?></li>
					</ul>
	</td>
</tr>
</table>

<?php
	include("../../includes/adminFooter.php");			
} else {
	echo "You are not authorized to access this page...";
}				
?>
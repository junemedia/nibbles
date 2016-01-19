<?php

/*********

Script to Display Add/Edit/ Polls

*********/

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

$sPageTitle = "Polls Management - Poll Result";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {


if ($id != '') {

$optionQuery = "SELECT *
				FROM   edPollOptions
				WHERE  pollId = '$id'";
$optionResult = mysql_query($optionQuery);

$totalVotes = 0;
while ($optionRow = mysql_fetch_object($optionResult)) {
	// Display poll options with results
	$pollOptions .="<tr><Td>$optionRow->optionValue</td><td>$optionRow->votes</td></tr>";
	$totalVotes += $optionRow->votes;
	
}

	// If Clicked on Edit, display values in fields
	
	// Get the data to display in HTML fields for the record to be edited
	$selectQuery = "SELECT *
					FROM   edPolls
			  		WHERE  id = '$id'";
	$result = mysql_query($selectQuery);
	
	if ($result) {
		
		while ($row = mysql_fetch_object($result)) {
			$question = ascii_encode($row->question);
			$isActive = $row->isActive;
		}
		mysql_free_result($result);
	} else {
		echo mysql_error();
	}
} 

// Hidden variable to be passed with form submit
$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=id value='$id'>";

?>

<html>

<head>
<title><?php echo $sPageTitle;?></title>
<LINK rel="stylesheet" href="<?php echo $sGblAdminSiteRoot;?>/styles.css" type="text/css" >
</head>

<body>

<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
	<tr><td>Poll Question</td>
		<td><?php echo $question;?></td>
	</tr>
	<tr><td>Is Active</td>
		<td><?php echo $isActive;?></td>
	</tr>
	<tr><TD colspan=2><BR></td></tr>		
	<tr><TD class=header>Option</td><td class=header>Votes</td></tr>		
	<?php echo $pollOptions;?>	
	<tr><td align=right class=header>Total Votes:</td><td class=header><?php echo $totalVotes;?></td></tR>				
	
	<tr><td align=center colspan=2><BR><BR><input type=button name=sClose value=Close onClick='self.close();'></td></tR>				
</table>

</body>

</html>

<?php
	
} else {
	echo "You are not authorized to access this page...";
}
?>
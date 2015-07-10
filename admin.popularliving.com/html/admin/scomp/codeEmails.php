<?php

/****

Script to display list of eMails for the code on a date, in small new window

*****/

include("../../includes/paths.php");

session_start();

$sEmailQuery = "SELECT *
			   FROM   scompCodeEmails
			   WHERE  code = '$sCode'
			   AND    unsubDate = '$sUnsubDate'";
$rEmailResult = dbQuery($sEmailQuery);
if (dbNumRows($rEmailResult) > 0) {
	while ($oEmailRow = dbFetchObject($rEmailResult)) {
		$sCodeEmailsList .= "<tr><td>$oEmailRow->email</td></tr>";
	}
} else {
	$sMessage = "No Records Exist...";
}

?>


<html>

<head>
<title>eMails For The Code</title>
<LINK rel="stylesheet" href="<?php echo $sGblAdminSiteRoot;?>/styles.css" type="text/css" >
</head>

<body>

<table width=85% align=center>
<tr><Td class=message align=center><?php echo $sMessage;?></td></tr></table>
<table width=85% align=center>
<?php echo $sCodeEmailsList;?>
</table>

</body>

</html>

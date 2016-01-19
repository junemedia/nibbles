<?php

/*********

Script to Add/Edit Offer

**********/

include("../../includes/paths.php");

session_start();

$sPageTitle = "Nibbles - Test Query";


$sTestQuery = stripslashes($sQuery);
$rTestResult = dbQuery($sTestQuery);
if ($rTestResult) {
$iNumFields = dbNumFields($rTestResult); 

$sTestResultHeading = "<tr>";

$bFirstRow = true;
while ($oTestRow = dbFetchRow($rTestResult)) {
	$sTestResultData .= "<tr>";
	
	for ($i = 0; $i < count($oTestRow); $i++) {
		
		// prepare column heading when first row fetched
		if ($bFirstRow == true) {
			$sTestResultHeading .= "<td><b>".dbFieldName($rTestResult, $i)."</b></td>";
		}
		
		$sTestResultData .= "<td>".htmlentities($oTestRow[$i])."&nbsp;</td>";
	}
	$sTestResultData .= "</tr>";
	$bFirstRow = false;
}
$sTestResultHeading .= "</tr>";
} else {
	$sTestResultData = stripslashes($sQuery). dbError();
}

$sTestResultData = $sTestResultHeading . $sTestResultData;

include("../../includes/adminAddHeader.php");
?>
<table>
<tr><td class=header>Query: <?php echo $sTestQuery;?></td></tr>
</table>

<table cellpadding=5 cellspacing=0 width=95% align=center border=1>
<?php echo $sTestResultData;?>
</table>
<BR>
<center><input type=button name=sClose value=Close onClick='JavaScript:self.close();'></center>
</body>
</html>
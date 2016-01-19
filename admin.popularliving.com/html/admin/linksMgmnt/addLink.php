<?php


include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");


$sPageTitle = "Nibbles Links - Add/Edit Link";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

$iMenuId = $_GET['iMenuId'];
$sFilter = $_GET['sFilter'];
$sAlpha = $_GET['sAlpha'];
$sExactMatch = $_GET['sExactMatch'];
$iRecPerPage = $_GET['iRecPerPage'];
$iId = $_GET['iId'];
$iSiteId = $_GET['iSiteId'];
$sShowActive = $_GET['sShowActive'];

$sQueryString = "iId=$iId&iMenuId=$iMenuId&sFilter=$sFilter&sAlpha=$sAlpha&sExactMatch=$sExactMatch&iRecPerPage=$iRecPerPage";
$sNewStr = "?iMenuId=$iMenuId&sFilter=$sFilter&sAlpha=$sAlpha&sExactMatch=$sExactMatch&iRecPerPage=$iRecPerPage";

if ($iId !='') {
	if ($iSiteId !='0') {
		header("Location:addNewLink.php?$sQueryString");
	} else {
		header("Location:addLegacyLink.php?$sQueryString");
	}
}


?>

<SCRIPT LANGUAGE="JavaScript">
function redirect() {
	if (document.form1.sSystem[0].checked == true) {
		window.location="addLegacyLink.php<?php echo $sNewStr ?>";
	} else {
		window.location="addNewLink.php<?php echo $sNewStr ?>";
	}
}
</script>

<html>
<body>
<form name='form1'>
	<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
	<tr><td>
		<input type="radio" name="sSystem" value="legacy" onclick="redirect();"> Legacy System
		&nbsp;&nbsp;&nbsp;
		<input type="radio" name="sSystem" value="nibbles2" onclick="redirect();"> Nibbles II System
	</td></tr>
	</table>
</form>
</body>
</html>
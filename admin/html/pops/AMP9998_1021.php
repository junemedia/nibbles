<?php

include("../includes/paths.php");

session_start();

$sStatsInsertQuery = "INSERT INTO popupDisplayStats(offerId, displayDate)
					  VALUES('AMP9998', CURRENT_DATE)";
$rStatsInsertResult = dbQuery($sStatsInsertQuery);
echo dbError();


?>
<html>

<head>

<script language="JavaScript">

<!--
function MM_openBrWindow(theURL,winName,features) { //v2.0
  window.open(theURL,winName,features);
}
//-->

</script>


<title></title>

<!-- hide this from tired old Browsers  if (window.location != top.location)   
{top.location.href=window.location} // -->


</head>


<body>

<meta http-equiv="refresh" content="1;URL=http://bd.myfree.com/r/r.php?src=amptb122303011">

</body>

</html>



<?php

include("../includes/paths.php");

session_start();

$sCheckQuery = "SELECT *
				FROM	popupCreativeDisplayStats
				WHERE   offerId = 'AMP9994'
				AND		displayDate = CURRENT_DATE";

$rCheckResult = dbQuery($sCheckQuery);
echo dbError();
if ( dbNumRows($rCheckResult) == 0 ) {
	$sStatsInsertQuery = "INSERT INTO popupCreativeDisplayStats(offerId, displayDate,  counts)
					  VALUES('AMP9994', CURRENT_DATE, 1)";
	$rStatsInsertResult = dbQuery($sStatsInsertQuery);
	echo dbError();

} else {
	$sStatsUpdateQuery = "UPDATE popupCreativeDisplayStats
						  SET	 counts = counts+1
						  WHERE  offerId = 'AMP9994'
						  AND	 displayDate = CURRENT_DATE";
	$rStatsUpdateResult = dbQuery($sStatsUpdateQuery);
	echo dbError();
}



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
<table width=720 align=top border=0>

<TR>
    <TD width="50%" height=3>
      <P align=center><A href="http://bd.myfree.com/r/r.php?src=amptb041604031"><IMG 
      height=400
      src='<?php echo $sGblSiteRoot;?>/pops/images/500x400freesamples.gif' width=500 border=0 NOSEND="1"></A> 
</td></tr></table>

</body>

</html>



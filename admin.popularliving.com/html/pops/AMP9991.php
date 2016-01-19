<?php

include("../includes/paths.php");

session_start();
$sCheckQuery = "SELECT *
				FROM	popupCreativeDisplayStats
				WHERE   offerId = 'AMP9991'
				AND		displayDate = CURRENT_DATE";

$rCheckResult = dbQuery($sCheckQuery);
echo dbError();
if ( dbNumRows($rCheckResult) == 0 ) {
	$sStatsInsertQuery = "INSERT INTO popupCreativeDisplayStats(offerId, displayDate,  counts)
					  VALUES('AMP9991', CURRENT_DATE, 1)";
	$rStatsInsertResult = dbQuery($sStatsInsertQuery);
	echo dbError();

} else {
	$sStatsUpdateQuery = "UPDATE popupCreativeDisplayStats
						  SET	 counts = counts+1
						  WHERE  offerId = 'AMP9991'
						  AND	 displayDate = CURRENT_DATE";
	$rStatsUpdateResult = dbQuery($sStatsUpdateQuery);
	echo dbError();
}


?>

<html>

<head>

<script language="JavaScript">

<!--

if(top.location != location) top.location.href = location.href;


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
		<table cellpadding=5 cellspacing=0 bgcolor=FFFFFF width=95% align=center>
		<tr>
			<td><MAP NAME="map1"><AREA HREF='http://bd.myfree.com/r/r.php?src=amptb072204042' ALT="Bush" TITLE="Bush"
   				SHAPE=RECT COORDS="1,82,200,231">
				<AREA HREF='http://bd.myfree.com/r/r.php?src=amptb072204042' ALT="Bush" TITLE="Bush"
   				SHAPE=RECT COORDS="1,340,199,419">
				<AREA HREF='http://bd.myfree.com/r/r.php?src=amptb072204043' ALT="Kerry" TITLE="Kerry"
   				SHAPE=RECT COORDS="201,82,399,231">
	
				<AREA HREF='http://bd.myfree.com/r/r.php?src=amptb072204043' ALT="Kerry" TITLE="Kerry"
   				SHAPE=RECT COORDS="200,340,399,419">
				</MAP>
				<img src='<?php echo $sGblSiteRoot;?>/c/bushKerryPoll/images/elect_400_500.jpg' USEMAP="#map1" border=0>
			</td>
		</tr>
		</table>

</body>

</html>



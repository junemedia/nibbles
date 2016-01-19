<?php

include("../includes/paths.php");

session_start();

$sCheckQuery = "SELECT *
				FROM	popupDisplayStats
				WHERE   offerId = 'AMP7777'
				AND		displayDate = CURRENT_DATE";

$rCheckResult = dbQuery($sCheckQuery);

if ( dbNumRows($rCheckResult) == 0 ) {
	$sStatsInsertQuery = "INSERT INTO popupDisplayStats(offerId, displayDate, 1)
					  VALUES('AMP7777', CURRENT_DATE, 1)";
	$rStatsInsertResult = dbQuery($sStatsInsertQuery);
	echo dbError();

} else {
	$sStatsUpdateQuery = "UPDATE popupDisplayStats
						  SET	 counts = counts+1
						  WHERE  offerId = 'AMP7777'
						  AND	 displayDate = CURRENT_DATE";
	$rStatsUpdateResult = dbQuery($sStatsUpdateQuery);
	echo dbError();
}


?>
<html>

<head>


<title>You Won!</title>




</head>

<body>
<table width=720 align=top border=0>

<TR>
    <TD width="50%" height=3>
      <A href="http://bd.myfree.com/r/r.php?src=amptb050604036" target="_PARENT"><IMG 
      height=400
      src="http://www.myfree.com/r/popups/art/500x400yw_countdown2.gif" 
      width=500 border=0 NOSEND="1"></A> 
</td></tr></table>

</body>

</html>



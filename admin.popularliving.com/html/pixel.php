<?php

include("includes/paths.php");

$sCheckPixelQuery = "SELECT *
					 FROM   partnerPixelTracking
					 WHERE  sourceCode = '$src'
					 AND	openDate = CURRENT_DATE";
$rCheckPixelResult = dbQuery($sCheckPixelQuery);
if ( dbNumRows($rCheckPixelResult) == 0 ) {
	$sPixelInsertQuery = "INSERT INTO partnerPixelTracking(sourceCode, openDate, opens)
					VALUES('$src', CURRENT_DATE, '1')";
	$rPixelInsertResult = dbQuery( $sPixelInsertQuery);
	echo dbError();
} else {
	$sPixelUpdateQuery = "UPDATE partnerPixelTracking
						  SET    opens = opens + 1
						  WHERE	 sourceCode = '$src'
						  AND	 openDate = CURRENT_DATE";
	$rPixelUpdateResult = dbQuery ($sPixelUpdateQuery);
	echo dbError();
	
}

?>
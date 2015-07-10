<?php

include("../../../library.php");
include("../../../includes/template.php");
// SELECT HL DATABASE
	mysql_select_db($hlDBName);	
	$selectQuery = "SELECT *
					FROM   source_codes";
	$selectResult = mysql_query($selectQuery);
	while ($selectRow = mysql_fetch_object($selectResult)) {
		$srcId = $selectRow->srcID;
		$srcCode = $selectRow->srcCode;
		echo "<BR> $srcId $srcCode";
		$updateQuery = "UPDATE sub_source_codes
						SET    sscSrcID = '$srcId'
						WHERE  sscSourceCode = '$srcCode'";
		$updateResult = mysql_query($updateQuery);		
		echo "<BR>$updateQuery".mysql_error();
	}
?>
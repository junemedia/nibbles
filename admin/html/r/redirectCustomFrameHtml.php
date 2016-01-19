<?php

include("../includes/paths.php");
					
$sCustomQuery = "SELECT *
				FROM   campaignCustomFrames
				WHERE  sourceCode = '$src'";
$rCustomResult = dbQuery($sCustomQuery);
while ($oCustomRow = dbFetchObject($rCustomResult)) {
	$sCustomFrameContent = $oCustomRow->frameContent;
}

echo $sCustomFrameContent;
?>
<?php

/*****
activePages table used by all scripts listed below:

https://web1.popularliving.com/admin/repEcpm/index.php
https://web1.popularliving.com/admin/repEcpm/report.php
https://web1.popularliving.com/admin/offersMgmntForSales/addOffer.php
https://web1.popularliving.com/admin/offersMgmnt/addOffer.php
https://web1.popularliving.com/admin/offerStatus/index.php
*****/


include_once("/home/sites/admin.popularliving.com/html/includes/paths.php");


$sGetPageId = "SELECT distinct pageId FROM otDataHistory 
				WHERE date_format(dateTimeAdded, '%Y-%m-%d') BETWEEN date_add(CURRENT_DATE, INTERVAL -30 DAY) 
					AND date_add(CURRENT_DATE, INTERVAL -1 DAY)";
$rGetPageId = dbQuery($sGetPageId);

$sDelete = "TRUNCATE TABLE activePages";
$rDelete = dbQuery($sDelete);

while ($oTemp = dbFetchObject($rGetPageId)) {
	$sGetPageName = "SELECT pageName FROM otPages WHERE id = '$oTemp->pageId' AND flowId=0";
	$rGetPageName = dbQuery($sGetPageName);
	while ($oTemp2 = dbFetchObject($rGetPageName)) {
		$sInsert = "INSERT IGNORE INTO activePages (pageId,pageName) 
					VALUES(\"$oTemp->pageId\", \"$oTemp2->pageName\")";
		$rInsert = dbQuery($sInsert);
	}
}


$sGetCount = "SELECT * FROM activePages";
$rGetCount = dbQuery($sGetCount);
if (dbNumRows($rGetCount) == 0) {
	$sMsg = "web1: /home/scripts/activePages.php script failed.  Run it manually ASAP";
	mail('it@amperemedia.com','script failed',$sMsg);
}



?>

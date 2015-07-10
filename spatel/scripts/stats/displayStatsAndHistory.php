<?php

// make entry into cron script status table
ini_set('max_execution_time', 50000);

include("/home/sites/admin.popularliving.com/html/includes/paths.php");


// move page stat data
$sTempPageStatQuery = "SELECT *
					   FROM   tempPageDisplayStats
					   WHERE  openDate < CURRENT_DATE";
$rTempPageStatResult = dbQuery($sTempPageStatQuery);
echo dbError();

//mail('spatel@amperemedia.com',"File: ".__FILE__." Line: ".__LINE__,"1st block started \n\n$sTempPageStatQuery"."\n\n\nError:".dbError());

while ($oTempPageStatRow = dbFetchObject($rTempPageStatResult)) {
	// check for the date
	$id = $oTempPageStatRow->id;
	$iPageId = $oTempPageStatRow->pageId;
	$sSourceCode = $oTempPageStatRow->sourceCode;
	$sSubSourceCode = $oTempPageStatRow->subSourceCode;
	
	$sOpenDate = $oTempPageStatRow->openDate;
	
	$sCheckQuery = "SELECT *
					FROM   pageDisplayStats
					WHERE  pageId = '$iPageId'
					AND    sourceCode = '$sSourceCode'
					AND    subSourceCode = '$sSubSourceCode'
					AND    openDate = '$sOpenDate'";
	$rCheckResult = dbQuery($sCheckQuery);
	
	echo dbError();
	
	if ( dbNumRows($rCheckResult) == 0) {
		// insert new row
		$sPageStatInsertQuery = "INSERT INTO pageDisplayStats(pageId, sourceCode, subSourceCode, openDate, opens)
								 VALUES($iPageId, '$sSourceCode', '$sSubSourceCode', '$sOpenDate', 1)";
		$rPageStatInsertResult = dbQuery($sPageStatInsertQuery);		
		
	} else {
		// update the count
		$sPageStatUpdateQuery = "UPDATE pageDisplayStats
								 SET    opens = opens + 1
								 WHERE  pageId = $iPageId
								 AND    sourceCode = '$sSourceCode'
								 AND    subSourceCode = '$sSubSourceCode' 
								 AND    openDate = '$sOpenDate'";
		$rPageStatUpdateResult = dbQuery($sPageStatUpdateQuery);
		
	}
	echo dbError();

	// delete record from temp table
	
	$sDeleteQuery = "DELETE FROM tempPageDisplayStats
					 WHERE  id = '$id'";
	$rDeleteResult = dbQuery($sDeleteQuery);
	echo dbError();
}

//mail('spatel@amperemedia.com',"File: ".__FILE__." Line: ".__LINE__,"1st block ended");


if ($rTempPageStatResult) {
	dbFreeResult($rTempPageStatResult);
}




// move offer display stat data
$sTempOfferDisplayStatQuery = "SELECT *
							   FROM   tempOfferDisplayStats
							   WHERE  displayDate <CURRENT_DATE";
$rTempOfferDisplayStatResult = dbQuery($sTempOfferDisplayStatQuery);

//mail('spatel@amperemedia.com',"File: ".__FILE__." Line: ".__LINE__,"2nd block stated \n\n$sTempOfferDisplayStatQuery"."\n\n\nError:".dbError());

echo dbError();
while ($oTempOfferDisplayStatRow = dbFetchObject($rTempOfferDisplayStatResult)) {
	$id = $oTempOfferDisplayStatRow->id;
	$iPageId = $oTempOfferDisplayStatRow->pageId;	
	$sStatInfo = $oTempOfferDisplayStatRow->statInfo;
	$sSourceCode = $oTempOfferDisplayStatRow->sourceCode;
	$sSubSourceCode = $oTempOfferDisplayStatRow->subSourceCode;
	$sDisplayDate = $oTempOfferDisplayStatRow->displayDate;
	
	$aOffersDisplayed = explode("," , $sStatInfo);
	if (count($aOffersDisplayed) > 0 ) {
		
		for ($i=0; $i<count($aOffersDisplayed); $i++) {
			
			$sOfferCode = $aOffersDisplayed[$i];
			
			if ($sOfferCode != '') {
				
				// check query
				$sOfferStatCheckQuery = "SELECT *
									 FROM   offerStats
									 WHERE  offerCode = '$sOfferCode'
									 AND    sourceCode = '$sSourceCode'
									 AND    subSourceCode = '$sSubSourceCode'									 
									 AND    displayDate = '$sDisplayDate'
									 AND 	pageId = '$iPageId'";
				$rOfferStatCheckResult = dbQuery($sOfferStatCheckQuery);
				echo dbError();
				if ( dbNumRows($rOfferStatCheckResult) ==0 ) {
					// insert new row
					$sOfferStatInsertQuery = "INSERT INTO offerStats(offerCode, sourceCode, subSourceCode, displayCount, displayDate, pageId)
										  VALUES('$sOfferCode', '$sSourceCode', '$sSubSourceCode', 1, '$sDisplayDate', '$iPageId')";
					$rOfferStatInsertResult = dbQuery($sOfferStatInsertQuery);
					
				} else {
					// update the count
					$sOfferStatUpdateQuery = "UPDATE offerStats
										  SET    displayCount = displayCount + 1
										  WHERE  offerCode = '$sOfferCode'
										  AND    sourceCode = '$sSourceCode'
										  AND    subSourceCode = '$sSubSourceCode'
										  AND    displayDate = '$sDisplayDate'
										  AND	 pageId = '$iPageId'";
					$rOfferStatUpdateResult =  dbQuery($sOfferStatUpdateQuery);
					echo dbError();
				}
				echo dbError();
			}
		}
		
	}
	
	// delete record from temp table
	
	$sDeleteQuery = "DELETE FROM tempOfferDisplayStats
					 WHERE  id = '$id'";
	$rDeleteResult = dbQuery($sDeleteQuery);
	echo dbError();
}

//mail('spatel@amperemedia.com',"File: ".__FILE__." Line: ".__LINE__,"2nd block ended");

if ($rTempOfferDisplayStatResult) {
	dbFreeResult($rTempOfferDisplayStatResult);
}


// move offer taken stat data
$sTempOfferTakenStatQuery = "SELECT *
							   FROM   tempOfferTakenStats
							   WHERE  displayDate < CURRENT_DATE";
$rTempOfferTakenStatResult = dbQuery($sTempOfferTakenStatQuery);
echo dbError();

//mail('spatel@amperemedia.com',"File: ".__FILE__." Line: ".__LINE__,"3rd block started \n\n$sTempOfferTakenStatQuery"."\n\n\nError:".dbError());


while ($oTempOfferTakenStatRow = dbFetchObject($rTempOfferTakenStatResult)) {
	$id = $oTempOfferTakenStatRow->id;
	$iPageId = $oTempOfferTakenStatRow->pageId;
	$sStatInfo = $oTempOfferTakenStatRow->statInfo;
	$sSourceCode = $oTempOfferTakenStatRow->sourceCode;
	$sSubSourceCode = $oTempOfferTakenStatRow->subSourceCode;
	$sDisplayDate = $oTempOfferTakenStatRow->displayDate;
	
	$aTempOffersTaken = explode("," , $sStatInfo);
	$aOffersTaken = array_unique($aTempOffersTaken);
	
	if (count($aOffersTaken) > 0 ) {
		
		for ($i=0; $i<count($aOffersTaken); $i++) {
			
			$sOfferCode = $aOffersTaken[$i];
			
			if ($sOfferCode != '') {
				
				// check query
				$sOfferStatCheckQuery = "SELECT *
									 FROM   offerStats
									 WHERE  offerCode = '$sOfferCode'
									 AND    sourceCode = '$sSourceCode'
									 AND    subSourceCode = '$sSubSourceCode' 
									 AND    displayDate = '$sDisplayDate'
									 AND	pageId = '$iPageId'";
				$rOfferStatCheckResult = dbQuery($sOfferStatCheckQuery);
				if ( dbNumRows($rOfferStatCheckResult) ==0 ) {
					// insert new row
					
				} else {
					// update the count
					$sOfferStatUpdateQuery = "UPDATE offerStats
										  SET    takenCount = takenCount + 1
										  WHERE  offerCode = '$sOfferCode'
										  AND    sourceCode = '$sSourceCode'
										  AND    subSourceCode = '$sSubSourceCode'
										  AND    displayDate = '$sDisplayDate'
										  AND    pageId = '$iPageId'";
					$rOfferStatUpdateResult =  dbQuery($sOfferStatUpdateQuery);
					
				}
				echo dbError();
			}
		}
	}
	// delete record from temp table
	
	$sDeleteQuery = "DELETE FROM tempOfferTakenStats
					 WHERE  id = '$id'";
	$rDeleteResult = dbQuery($sDeleteQuery);
	echo dbError();
}

if ($rTempOfferTakenStatResult) {
	dbFreeResult($rTempOfferTakenStatResult);
}

//mail('spatel@amperemedia.com',"File: ".__FILE__." Line: ".__LINE__,"3rd block ended");


// move offer abort stat data
$sTempOfferAbortStatQuery = "SELECT *
							 FROM   tempOfferAbortStats
							 WHERE  displayDate < CURRENT_DATE";
$rTempOfferAbortStatResult = dbQuery($sTempOfferAbortStatQuery);
echo dbError();

//mail('spatel@amperemedia.com',"File: ".__FILE__." Line: ".__LINE__,"4th block started \n\n$sTempOfferAbortStatQuery"."\n\n\nError:".dbError());

while ($oTempOfferAbortStatRow = dbFetchObject($rTempOfferAbortStatResult)) {
	$id = $oTempOfferAbortStatRow->id;
	$iPageId = $oTempOfferAbortStatRow->pageId;
	$sStatInfo = $oTempOfferAbortStatRow->statInfo;
	$sSourceCode = $oTempOfferAbortStatRow->sourceCode;
	$sSubSourceCode = $oTempOfferAbortStatRow->subSourceCode;
	$sDisplayDate = $oTempOfferAbortStatRow->displayDate;
	
	$aOffersAbort = explode("," , $sStatInfo);
	
	if (count($aOffersAbort) > 0 ) {
		
		for ($i=0; $i<count($aOffersAbort); $i++) {
			
			$sOfferCode = $aOffersAbort[$i];
			// check query
			if ($sOfferCode != '') {
								
				$sOfferStatCheckQuery = "SELECT *
									 FROM   offerStats
									 WHERE  offerCode = '$sOfferCode'
									 AND    sourceCode = '$sSourceCode'
									 AND    subSourceCode = '$sSubSourceCode' 
									 AND    displayDate = '$sDisplayDate'
									 AND	pageId = '$iPageId'";
				$rOfferStatCheckResult = dbQuery($sOfferStatCheckQuery);
				//echo "\n".$sOfferStatCheckQuery.mysql_error();
				if ( dbNumRows($rOfferStatCheckResult) ==0 ) {
					// insert new row
					
				} else {
					// update the count
					$sOfferStatUpdateQuery = "UPDATE offerStats
										  SET    page2AbortCount = page2AbortCount + 1
										  WHERE  offerCode = '$sOfferCode'
										  AND    sourceCode = '$sSourceCode'
										  AND    subSourceCode = '$sSubSourceCode'
										  AND    displayDate = '$sDisplayDate'
										  AND	 pageId = '$iPageId'";
					$rOfferStatUpdateResult =  dbQuery($sOfferStatUpdateQuery);
					//echo "\n".$sOfferStatUpdateQuery;
					
				}
				echo dbError();
			}
		}
	}
	
	// delete record from temp table
	
	$sDeleteQuery = "DELETE FROM tempOfferAbortStats
					 WHERE  id = '$id'";
	$rDeleteResult = dbQuery($sDeleteQuery);
	echo dbError();
}

if ($rTempOfferAbortStatResult) {
	dbFreeResult($rTempOfferAbortStatResult);
}

//mail('spatel@amperemedia.com',"File: ".__FILE__." Line: ".__LINE__,"4th block ended");



// insert into pagedisplay summary 
$sSelectQuery = "SELECT pageId, openDate, sum(opens) AS totalOpens
				 FROM   pageDisplayStats
				 WHERE  openDate >=  DATE_ADD(CURRENT_DATE, INTERVAL -3 DAY)
				 GROUP BY pageId, openDate";
$rSelectResult = dbQuery($sSelectQuery);
echo dbError();

//mail('spatel@amperemedia.com',"File: ".__FILE__." Line: ".__LINE__,"5th block started \n\n$sSelectQuery"."\n\n\nError:".dbError());


while ($oSelectRow = dbFetchObject($rSelectResult)) {
	$iPageId = $oSelectRow->pageId;
	$sOpenDate = $oSelectRow->openDate;
	$iTotalOpens = $oSelectRow->totalOpens;
	
	$sCheckQuery = "SELECT *
					FROM   pageDisplayStatsSum
					WHERE pageId = '$iPageId'
					AND   openDate = '$sOpenDate'";
	$rCheckResult = dbQuery($sCheckQuery);
	if ( dbNumRows($rCheckResult) ==0) {
		
		$sInsertQuery = "INSERT INTO pageDisplayStatsSum(pageId, openDate, opens)
						VALUES('$iPageId', '$sOpenDate','$iTotalOpens')";
		$rInsertResult = dbQuery($sInsertQuery);
		
	} else {
		$sUpdateQuery = "UPDATE pageDisplayStatsSum
						 SET   opens = '$iTotalOpens'
						 WHERE pageId = '$iPageId'
						 AND   openDate = '$sOpenDate'";
		$rUpdateResult = dbQuery($sUpdateQuery);
		
	}			
}

//mail('spatel@amperemedia.com',"File: ".__FILE__." Line: ".__LINE__,"5th block ended.");



// insert into offer Stats summary 
$sSelectQuery = "SELECT displayDate, offerCode, pageId, sum(displayCount) as displayCounts, 
						sum(takenCount) AS takenCounts, sum(page2AbortCount) AS page2AbortCounts
				 FROM   offerStats
				 WHERE  displayDate >=  DATE_ADD(CURRENT_DATE, INTERVAL -3 DAY)
				 GROUP BY displayDate, offerCode, pageId";
$rSelectResult = dbQuery($sSelectQuery);

//mail('spatel@amperemedia.com',"File: ".__FILE__." Line: ".__LINE__,"6th block started \n\n$sSelectQuery"."\n\n\nError:".dbError());


while ($oSelectRow = dbFetchObject($rSelectResult)) {
	$sOfferCode = $oSelectRow->offerCode;
	$iPageId = $oSelectRow->pageId;
	$sDisplayDate = $oSelectRow->displayDate;
	$iDisplayCounts = $oSelectRow->displayCounts;
	$iTakenCounts = $oSelectRow->takenCounts;
	$iPage2AbortCounts = $oSelectRow->page2AbortCounts;
	
	$sCheckQuery = "SELECT *
					FROM   offerStatsSum
					WHERE displayDate = '$sDisplayDate'
					AND	  offerCode = '$sOfferCode'
					AND   pageId = '$iPageId'";
	
	$rCheckResult = dbQuery($sCheckQuery);
	if ( dbNumRows($rCheckResult) ==0) {
		
		$sInsertQuery = "INSERT INTO offerStatsSum(offerCode, displayDate, pageId, displayCount, takenCount, page2AbortCount)
						VALUES('$sOfferCode', '$sDisplayDate','$iPageId', '$iDisplayCounts', '$iTakenCounts', '$iPage2AbortCounts')";
		$rInsertResult = dbQuery($sInsertQuery);
		
	} else {
		$sUpdateQuery = "UPDATE offerStatsSum
						 SET   displayCount = '$iDisplayCounts',
							   takenCount = '$iTakenCounts',
							   page2AbortCount = '$iPage2AbortCounts' 	
						 WHERE displayDate = '$sDisplayDate'
						 AND   offerCode = '$sOfferCode'
						 AND   pageId = '$iPageId'";		
		$rUpdateResult = dbQuery($sUpdateQuery);
		
	}			
}


?>

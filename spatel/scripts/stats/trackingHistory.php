<?php


include("/var/www/html/admin.popularliving.com/html/includes/paths.php");

// get the data for the date before today from the active table
// and insert into history table, and delete from current table
// For BD redirects

$activeQuery = "INSERT IGNORE INTO bdRedirectsTrackingHistory 
				SELECT * FROM bdRedirectsTracking 
				WHERE  clickDate < CURRENT_DATE";
$activeResult = dbQuery($activeQuery);

if ($activeResult) {
	
	$summaryQuery = "REPLACE INTO bdRedirectsTrackingHistorySum(clickDate, sourceCode, subSourceCode, clicks)
					SELECT clickDate, sourceCode, subSourceCode, count(*) 
					FROM bdRedirectsTrackingHistory 
					WHERE clickDate BETWEEN date_add(CURRENT_DATE, INTERVAL -6 DAY) AND date_add(CURRENT_DATE, INTERVAL -1 DAY)
					GROUP BY clickDate, sourceCode, subSourceCode";
	$summaryResult = dbQuery($summaryQuery);
	
	$deleteQuery = "DELETE FROM bdRedirectsTracking
				WHERE clickDate < CURRENT_DATE";
	$deleteResult = dbQuery($deleteQuery);
	
}


// For BD pixel tracking
$activeQuery2 = "INSERT IGNORE INTO bdPixelsTrackingHistory(openDate, sourceCode, ipAddress, revenue) 
				 SELECT openDate, sourceCode, ipAddress, revenue 
				 FROM bdPixelsTracking 
				 WHERE openDate < CURDATE()";

$activeResult2 = dbQuery($activeQuery2);

echo dbError();

if ($activeResult2) {
	
	$summaryQuery = "REPLACE INTO bdPixelsTrackingHistorySum(openDate, sourceCode, opens, revenue)
					SELECT openDate, sourceCode, count(*), sum(revenue) 
					FROM bdPixelsTrackingHistory 
					WHERE openDate BETWEEN date_add(CURRENT_DATE, INTERVAL -6 DAY) AND date_add(CURRENT_DATE, INTERVAL -1 DAY)
					GROUP BY openDate, sourceCode";
	$summaryResult = dbQuery($summaryQuery);
	echo dbError();
	
	$deleteQuery2 = "DELETE FROM bdPixelsTracking
					WHERE openDate < CURRENT_DATE";
	$deleteResult = dbQuery($deleteQuery2);
	
	echo dbError();
}



// For Offer redirects
$activeQuery = "INSERT IGNORE INTO edOfferRedirectsTrackingHistory 
				SELECT * 
				FROM edOfferRedirectsTracking 
				WHERE  clickDate < CURRENT_DATE";
$activeResult = mysql_query($activeQuery);

if ($activeResult) {
	
	$summaryQuery = "REPLACE INTO edOfferRedirectsTrackingHistorySum(clickDate, offerCode, subsource, clicks)
				SELECT clickDate, offerCode, subsource, count(*) 
				FROM edOfferRedirectsTrackingHistory 
				WHERE clickDate BETWEEN date_add(CURRENT_DATE, INTERVAL -6 DAY) AND date_add(CURRENT_DATE, INTERVAL -1 DAY)
				GROUP BY clickDate, offerCode, subsource";
	$summaryResult = mysql_query($summaryQuery);
	
	$deleteQuery = "DELETE FROM edOfferRedirectsTracking
				WHERE clickDate < CURRENT_DATE";
	$deleteResult = mysql_query($deleteQuery);
	
}

echo mysql_error();


// For NL pixel tracking

$activeQuery2 = "INSERT IGNORE INTO nlPixelsTrackingHistory(openDate, nlCode, IPAddress) SELECT openDate, nlCode, IPAddress FROM nlPixelsTracking WHERE openDate < CURDATE()";

$activeResult2 = mysql_query($activeQuery2);

echo mysql_error();

if ($activeResult2) {
	
	$summaryQuery = "REPLACE INTO nlPixelsTrackingHistorySum(openDate, nlCode, opens)
					SELECT openDate, nlCode, count(*) 
					FROM nlPixelsTrackingHistory 
					WHERE openDate BETWEEN date_add(CURRENT_DATE, INTERVAL -6 DAY) AND date_add(CURRENT_DATE, INTERVAL -1 DAY)
					GROUP BY openDate, nlCode";
	$summaryResult = mysql_query($summaryQuery);
	
	
	$deleteQuery2 = "DELETE FROM nlPixelsTracking
					WHERE openDate < CURRENT_DATE";
	$deleteResult = mysql_query($deleteQuery2);
}


// For Offers pixel tracking

$activeQuery2 = "INSERT IGNORE INTO edOfferPixelsTrackingHistory(openDate, offerCode, IPAddress) SELECT openDate, offerCode, IPAddress FROM edOfferPixelsTracking WHERE openDate < CURDATE()";

$activeResult2 = mysql_query($activeQuery2);

echo mysql_error();

if ($activeResult2) {
	
	$summaryQuery = "REPLACE INTO edOfferPixelsTrackingHistorySum(openDate, offerCode, opens)
					SELECT openDate, offerCode, count(*) 
					FROM edOfferPixelsTrackingHistory 
					WHERE openDate BETWEEN date_add(CURRENT_DATE, INTERVAL -6 DAY) AND date_add(CURRENT_DATE, INTERVAL -1 DAY)
					GROUP BY openDate, offerCode";
	$summaryResult = mysql_query($summaryQuery);
	
	
	$deleteQuery2 = "DELETE FROM edOfferPixelsTracking
					WHERE openDate < CURRENT_DATE";
	$deleteResult = mysql_query($deleteQuery2);
}


// e tracking


$activeQuery3 = "INSERT INTO eTrackingHistory(submitDate, pageId, sourceCode, subSourceCode, email, ipAddress, e2Page) 
				 SELECT date_format(submitDateTime,'%Y-%m-%d'), pageId, sourceCode, subSourceCode, email, ipAddress, e2Page FROM eTracking
				 WHERE  date_format(submitDateTime,'%Y-%m-%d') < CURDATE()";

$activeResult3 = mysql_query($activeQuery3);

echo mysql_error();

if ($activeResult3) {
	$deleteQuery3 = "DELETE FROM eTracking
					 WHERE date_format(submitDateTime,'%Y-%m-%d') < CURRENT_DATE";
	$deleteResult3 = mysql_query($deleteQuery3);
}


?>

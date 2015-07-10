<?php

mysql_connect('64.132.70.20','nibbles','#a!!yu5');
mysql_select_db('nibbles');

$url_result = mysql_query("SELECT * FROM sitesToMonitor");
while ($oUrlRow = mysql_fetch_object($url_result)) {
	$start = explode(" ", microtime());
	$iStartTime = $start[1] + $start[0];

	$buffer = '';
	$file = @fopen($oUrlRow->url, 'r');
	while(!feof($file)) {
		$buffer .= fread($file,1024);
	}
	fclose($file);
	
	
	$end = explode(" ", microtime());
	$iEndTime = $end[1] + $end[0];
	
	$iDiffTime = $iEndTime - $iStartTime;
	$iSize = number_format(strlen($buffer)/1024,2);
	
	$result = mysql_query("INSERT IGNORE INTO siteMonitorHistory (startTime, endTime, diff, url, sizeOfPage)
				VALUES (\"$iStartTime\", \"$iEndTime\", \"$iDiffTime\", \"$oUrlRow->url\", \"$iSize\")");
}


/*
CREATE TABLE `siteMonitorHistory` (
`id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`startTime` DECIMAL( 13, 2 ) NOT NULL ,
`endTime` DECIMAL( 13, 2 ) NOT NULL ,
`diff` DECIMAL( 8, 2 ) NOT NULL ,
`url` VARCHAR( 255 ) NOT NULL ,
`sizeOfPage` DECIMAL( 8, 2 ) NOT NULL
) ENGINE = MYISAM COMMENT = 'site monitor';


CREATE TABLE `sitesToMonitor` (
`id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`url` TINYTEXT NOT NULL
) ENGINE = MYISAM ;
*/

?>

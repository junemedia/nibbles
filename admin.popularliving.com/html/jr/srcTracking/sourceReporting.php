<?php
/*
Second script - sourceReporting.php

Report count by src with src filters. Date wise filters.

CREATE TABLE `sourceTracking` (
  `id` int(11) NOT NULL auto_increment,
  `dateTimeAdded` datetime NOT NULL default '0000-00-00 00:00:00',
  `ip` varchar(50) default NULL,
  `src` varchar(255) NOT NULL default '',
  `ss` varchar(255) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

*/


$dbase = "jr";

$user = "root" ;

$pass = "092363jr" ;


// DO NOT CHANGE THESE TWO LINES!

mysql_pconnect ('localhost', $user, $pass);

// mysql_connect ('localhost', $user, $pass);

mysql_select_db ($dbase);





$sReportQuery = "SELECT src, count(*) AS counts
				 FROM   sourceTracking
				 WHERE  1 ";
				 
if ($sDateFrom != '' && $sDateTo != '') {
	$sReportQuery .= " AND  date_format(dateTimeAdded,'%Y-%m-%d') BETWEEN '$sDateFrom' AND '$sDateTo' ";
} 

if ($sSourceCode != '') {
	$sReportQuery .= " AND src = \"$sSourceCode\"";
}

$sReportQuery .= " GROUP BY src
				   ORDER BY src";
$rReportResult = mysql_query($sReportQuery);
echo mysql_error();
while ($oReportRow = mysql_fetch_object($rReportResult)) {
	$sReportData .= "<tr><td>$oReportRow->src</td><td>$oReportRow->counts</td></tr>";	
}


?>
<html>
<body>
<form action='<?php echo $PHP_SELF;?>'>
<table>
<tr><td>Date From</td><td><input type=text name=sDateFrom value='<?php echo $sDateFrom;?>'>
		&nbsp; &nbsp; Date To <input type=text name=sDateTo value='<?php echo $sDateTo;?>'>
	</td></tr>
<tr><td>Source Code</td><td><input type=text name=sSourceCode value='<?php echo $sSourceCode;?>'>
	</td></tr>
<tr><td></td><td><input type=submit name=sSubmit value='Get Report'>
	</td></tr>
</table>
<table cellpadding=0 cellspacing=0 border=1  width=300>
<tr><Td><b>Source Code</b></td><Td><b>Count</b></td></tr>
<?php echo $sReportData;?>
</table>
</form>
</body>
</html>


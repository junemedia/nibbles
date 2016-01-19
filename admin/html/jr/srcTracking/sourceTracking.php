<?php
/*
sourceTracking.php

Designed to include into another file.

Table it refers to: 
CREATE TABLE `sourceTracking` (
  `id` int(11) NOT NULL auto_increment,
  `dateTimeAdded` datetime NOT NULL default '0000-00-00 00:00:00',
  `ip` varchar(50) default NULL,
  `src` varchar(255) NOT NULL default '',
  `ss` varchar(255) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

Grabs query strings for src and ss. Records dateTime, ip, src and ss


*/



$dbase = "jr";

$user = "root" ;

$pass = "092363jr" ;


// DO NOT CHANGE THESE TWO LINES!

mysql_pconnect ('localhost', $user, $pass);

// mysql_connect ('localhost', $user, $pass);

mysql_select_db ($dbase);






$src = $_GET['src'];
$ss = $_GET['ss'];


$sInsertQuery = "INSERT INTO sourceTracking(dateTimeAdded, ip, src, ss)
				 VALUES(now(), '".$_SERVER['REMOTE_ADDR']."', \"$src\", \"$ss\" )";
$rInsertResult = mysql_query($sInsertQuery);
echo mysql_error();

?>
<?php

/*
server: www.arcamax.com
username: af2964
password: gd5axVtG

This is the query that I have used to import data


IMPORT QUERY:
load data infile '/home/spatel/SC-9Mar2012.txt' into table arcamax_temp.import_big  fields terminated by ',' enclosed by '"' lines terminated by '\n' (email,@dateAdded,ipaddr,listid,subsource,fname,lname,addr1,addr2,city,state,zip,ctry) set dateAdded = str_to_date(@dateAdded,'%m/%d/%Y');



NOTES FOR NEXT TIME IMPORT:

1.  DOWNLOAD FILE AND SAVE TO /HOME/SPATEL DIRECTORY

2.  IMPORT INTO import_big TABLE (USE ABOVE IMPORT QUERY) AND THEN RUN COUNT BY LISTID: SELECT listid, count(*) FROM arcamax_temp.import_big GROUP BY listid ORDER BY listid;

3.  COPY TABLE STRUCTURE OF joinEmailActive TO joinEmailActive2


		CREATE TABLE `arcamax`.`joinEmailActive2` (
		`id` int( 11 ) NOT NULL AUTO_INCREMENT ,
		`dateTime` datetime NOT NULL ,
		`email` varchar( 100 ) NOT NULL ,
		`ipaddr` varchar( 25 ) NOT NULL ,
		`listid` int( 11 ) NOT NULL ,
		`subcampid` int( 11 ) NOT NULL ,
		`source` varchar( 255 ) NOT NULL ,
		`subsource` varchar( 255 ) NOT NULL ,
		PRIMARY KEY ( `id` ) ,
		UNIQUE KEY `email` ( `email` , `listid` ) ,
		KEY `listid` ( `listid` ) ,
		KEY `source` ( `source` ) ,
		KEY `dateTime` ( `dateTime` ) ,
		KEY `subcampid` ( `subcampid` )
		) ENGINE = MYISAM DEFAULT CHARSET = latin1;




4.  IMPORT DATA INTO joinEmailActive2
	INSERT IGNORE INTO arcamax.joinEmailActive2 (dateTime,email,ipaddr,listid,subcampid,source,subsource) SELECT dateAdded,email,ipaddr,listid,subsource,'import','03092012' FROM arcamax_temp.import_big;

5.  RANAME joinEmailActive TO joinEmailActive_old -	RENAME TABLE arcamax.joinEmailActive TO arcamax.joinEmailActive_old;

6.  RENAME joinEmailActive2 TO joinEmailActive - RENAME TABLE arcamax.joinEmailActive2 TO arcamax.joinEmailActive;

7.  IMPORT TODAY'S SIGNUP FROM joinEmailActive_old TO joinEmailActive (USE THIS QUERY, MAKE SURE TO UPDATE DATE IN QUERY)
	INSERT IGNORE INTO arcamax.joinEmailActive (dateTime,email,ipaddr,listid,subcampid,source,subsource) SELECT dateTime,email,ipaddr,listid,subcampid,source,subsource FROM arcamax.joinEmailActive_old WHERE dateTime > '2012-03-09 09:00:00';

8.  RUN COUNT BY LIST ID - SELECT listid, count(*) FROM arcamax.joinEmailActive GROUP BY listid ORDER BY listid;


*/



exit;


// path to remote file
$remote_file = "/ListExport/SC-9Mar2012.txt";
$local_file = "/home/spatel/SC-9Mar2012.txt";

$ftp_user_name = 'af2964';
$ftp_server = 'www.arcamax.com';
$ftp_user_pass = 'gd5axVtG';

// open some file to write to
$handle = fopen($local_file, 'w');
if (!$handle) {
	$body .= "Unable to create new file on local host to download file from Arcamax FTP server.\n\n\n";
	$success = false;
}

// set up basic connection
$conn_id = ftp_connect($ftp_server);
if (!$conn_id) {
	$body .= "Cannot connect to FTP server.\n\n\n";
	$success = false;
}

// login with username and password
$login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);
if (!$login_result) {
	$body .= "Unable to login to Arcamax FTP server.\n\n\n";
	$success = false;
}

ftp_pasv($conn_id, true);

// try to download $remote_file and save it to $handle
if (!ftp_fget($conn_id, $handle, $remote_file, FTP_ASCII, 0)) {
	$body .= "There was a problem while downloading $remote_file to $local_file\n\n\n";
	$success = false;
}

// close the connection and the file handler
ftp_close($conn_id);
fclose($handle);



?>

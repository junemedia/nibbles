<?php

$dbase = "jr";

$user = "root" ;

$pass = "092363jr" ;


// DO NOT CHANGE THESE TWO LINES!

mysql_pconnect ('localhost', $user, $pass);

// mysql_connect ('localhost', $user, $pass);

mysql_select_db ($dbase);

//URL, email, first, last,
//current date, current time, what failed.

// [FIRST] will be replaced with user's first name
// [LAST] will be replaced with user's last name
// [URL] will be replaced with url tested
// [DATE_TIME] will be replaced with date and time of testing
// [TEST_FAILED] will be replaced with "Ping" or "Test String"

$sGblMessageText = "[FIRST] [LAST],
					\r\n\r\n\r\nTest for: [URL]
					\r\n\r\nDate And Time: [DATE_TIME]
					\r\n\r\nTest Failed: [TEST_FAILED]";

$sGblEmailFrom = "jr@amperemedia.com";

// [URL] will be replaced with the url for which test failed
$sGblEmailSubject = "Test Sites - [URL]";

?>
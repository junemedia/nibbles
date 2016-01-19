<?php

$dbase = "jr";

$user = "root" ;

$pass = "092363jr" ;


// DO NOT CHANGE THESE TWO LINES!

mysql_pconnect ('localhost', $user, $pass);

// mysql_connect ('localhost', $user, $pass);

mysql_select_db ($dbase);


/****************** script variables   **************************/

$sMyDirName = "/home/sites/www_popularliving_com/html/jr";
$sTableFiles = "dirFiles";

$sEmailSubject = "New File Alert";
$sEmailMessage = "New File [FILE_NAME] added in [DIR_NAME] dir.";
$sEmailFrom = "jr@amperemedia.com";
$sEmailTo = "smita@myfree.com";

/****************** end script variables   **************************/



// Check if table exists... Create the table if does not exist
$checkQuery = "SHOW TABLES LIKE '$sTableFiles'";

$checkResult = mysql_query($checkQuery);
echo mysql_error();
if (mysql_num_rows($checkResult) == 0) {
	$sCreateQuery = "CREATE TABLE `$sTableFiles` (
					`id` INT NOT NULL AUTO_INCREMENT ,
					`dirName` MEDIUMTEXT NOT NULL ,
					`fileName` VARCHAR( 255 ) NOT NULL ,
					PRIMARY KEY ( `id` ) 
					) COMMENT = 'Table used to keep records of files in a directory';";

 	$rCreateResult = mysql_query($sCreateQuery);

}
	

// check current no of records in the table

$sCheckQuery = "SELECT *
				FROM   $sTableFiles";
$rCheckResult = mysql_query($sCheckQuery);
if ($rCheckResult) {
	$iNoOfRec = mysql_num_rows($rCheckResult);
}

readMyDir($sMyDirName);


function readMyDir($sDirName) {
		
	global $sTableFiles, $sEmailFrom, $sEmailTo, $sEmailSubject, $sEmailMessage;
	
$iNoOfFiles = 0;

$rOpenDir = opendir($sDirName);

if ($rOpenDir) {
	
	while (($sFile = readdir($rOpenDir)) != false) {	
		if (!is_dir("$sDirName/$sFile")) {
			// check if previous file records of this dir has new file added into	
			
			$sCheckQuery = "SELECT *
							FROM   $sTableFiles
							WHERE  dirName = \"$sDirName\"
							AND    fileName = \"$sFile\"";
			$rCheckResult = mysql_query($sCheckQuery);
			echo mysql_error();
			if (mysql_num_rows($rCheckResult) == 0) {
				// send alert email
				//echo "\nNew file ".$sFile;
				$sHeaders  = "MIME-Version: 1.0\r\n";
				$sHeaders .= "Content-type: text/plain; charset=iso-8859-1\r\n";
				$sHeaders .= "From:$sEmailFrom\r\n";
				$sHeaders .= "cc:\r\n";
				
				$sTempEmailMessage = ereg_replace("\[FILE_NAME\]", $sFile, $sEmailMessage);
				$sTempEmailMessage = ereg_replace("\[DIR_NAME\]", $sDirName, $sTempEmailMessage);
				
				mail($sEmailTo, $sEmailSubject, $sTempEmailMessage, $sHeaders);
				
			}
			
			// insert record for dir-file pair
			
			$sInsertQuery = "INSERT INTO $sTableFiles(dirName, fileName)
							 VALUES(\"$sDirName\", \"$sFile\")";
			$rInsertResult = mysql_query($sInsertQuery);
			echo mysql_error();
							
			
		} else if ($sFile != '.' && $sFile != '..') {
				readMyDir("$sDirName/$sFile");
		}
	} 
	
	
}

}


// delete the records of previous check

$sDeleteQuery = "DELETE FROM $sTableFiles
				 ORDER BY id
				 LIMIT $iNoOfRec";
$rDeleteResult = mysql_query($sDeleteQuery);
echo mysql_error();


?>
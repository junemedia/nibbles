#!/usr/bin/php
<?php
#################################################################################
#
# Mustang MySQL Backup System - Version 1.2 # # www.MustangInternetServices.com 
# 
# Last updated 2005-12-17.
#
# updated 7/3/06 - modified to handle databases that do not have any tables Dale 
# #################################################################################
#       Sample crontab entry
#################################################################################
#
#
# 30 2 * * * /usr/bin/php /home/scripts/mysqlbak.php # # Default actions:
#       Compression: no
#       Binaries: yes
#       Dumps: yes
#
# Actual Export completed using:
#
# $MYSQLDUMP.$MYSQLDUMPOPTIONS.$MYSQLSERVER." -u".$MYSQLUSER." -p".$MYSQLPASSWD.
#    " --flush-logs --opt " . $db_names[$k]. " ".$tb_names[$i]." > ".
#    $sBackupDumpsDir."/".$db_names[$k]."/".$tb_names[$i].".sql";
#
# /usr/bin/mysqldump -hlocalhost -uroot -p092363jr --flush-logs --opt DBNAME TBNAME
#    > /home/backups/mysql/current/dumps/DBNAME/TBNAME
#

#	print_r($_SERVER['argv']);
$aArgs = $_SERVER['argv'];
#	remove the first element, which contains the script name
array_shift($aArgs);

$bCompress = FALSE;
$bBinaries = TRUE;
$bDumps = TRUE;
$configFile = "";

foreach ($aArgs as $sArgument) {
	if (strstr($sArgument, "--config=")) {
		$configFile = str_replace("--config=", "", $sArgument);
	}
	switch ($sArgument) {
		case "--compress":
		case "-c":
		$bCompress = TRUE;
		break;
		case "--no-binaries":
		case "-nb":
		$bBinaries = FALSE;
		break;
		case "--no-dumps":
		case "-nd":
		$bDumps = FALSE;
		break;
		case "--?":
		case "/?":
		case "--h":
		case "--help":
		echo "\n\nThe configuration file is required:\n\n";
		echo "\t--config=\tFull path to the configuration file.\n\n";
		echo "\n\nThe following options are available:\n\n";
		echo "\t--compress\n\t-c\t\tUse 'tar' and 'gzip' on each file exported.\n\n";
		echo "\t--no-binaries\n\t-nb\t\tDo not back up binaries from /var/lib/mysql.\n\n";
		echo "\t--no-dumps\n\t-nd\t\tDo not create dump files in /home/backup/mysql.\n\n";
		exit(0);
	}
}

if ($configFile) {
	include("$configFile");
} else {
	echo "\nERROR: configuration file missing.  Try '--help'.\n\n";
	exit(1);
}

$REPORTNAME = "$SERVERNAME MySQL Backup Report"; set_time_limit(60000);

################################################################################
#      Report header
################################################################################

$scriptstarttime = exec('date');
$outputlog = "\n";
$outputlog .= "#######################################################################\n";
$outputlog .= "MySQL Backup Script v1.1\n"; $outputlog .= "\n"; $outputlog .= "Config File: ".$configFile."\n"; $outputlog .= "\n"; $outputlog .= "MySQL Server: ".$MYSQLSERVER."\n"; $outputlog .= "\n"; $outputlog .= "Backup Location: ".$MAIN_BACKUP_DIR."\n"; if ($NUM_BACKUPS == 0) {
	$outputlog .= "Number of Backup Sets Retained on Target: Current Set Only\n"; } else {
	$outputlog .= "Number of Backup Sets Retained on Target: Current Set + ".$NUM_BACKUPS."\n"; } $outputlog .= "\n"; $outputlog .= "Backup started: ".$scriptstarttime."\n"; $outputlog .= "#######################################################################\n";
$outputlog .= "\n\n";

################################################################################
#      Rotate backups
################################################################################

if ($NUM_BACKUPS <> 0) {
	# delete the oldest backup, if it exists
	$tempDateStart = date('U');
	$outputlog .= "Delete Oldest Backup Started: ".date('Y-m-d H:i:s')."\n";

	if ( is_dir($SECONDARY_BACKUP_DIR."/daily.".$NUM_BACKUPS) ) {
		$command_to_exec = "rm -fr ".$SECONDARY_BACKUP_DIR."/daily.".$NUM_BACKUPS;
		exec ($command_to_exec);
		$outputlog .= "Deleting ".$SECONDARY_BACKUP_DIR."/daily.".$NUM_BACKUPS."\n";
	}

	$tempDateEnd = date('U');
	$tempDateDuration = $tempDateEnd - $tempDateStart;
	$outputlog .= "Delete Oldest Backup Finished, duration: $tempDateDuration seconds\n\n";

	# shift the backups up by one, if they exist
	$tempDateStart = date('U');
	$outputlog .= "Shift Backups Started: ".date('Y-m-d H:i:s')."\n";

	for ($VERSION=$NUM_BACKUPS; $VERSION > 1; $VERSION--) {
		$SEND_VERSION = $VERSION - 1;
		if ( is_dir($SECONDARY_BACKUP_DIR."/daily.".$SEND_VERSION) ) {
			$filename_mtime_string = filemtime($SECONDARY_BACKUP_DIR."/daily.".$SEND_VERSION);
			if ( rename ($SECONDARY_BACKUP_DIR."/daily.".$SEND_VERSION, $SECONDARY_BACKUP_DIR."/daily.".$VERSION) ) {
				$outputlog .= "Moving ".$SECONDARY_BACKUP_DIR."/daily.".$SEND_VERSION." to ".$SECONDARY_BACKUP_DIR."/daily.".$VERSION."\n";
				touch ($SECONDARY_BACKUP_DIR."/daily.".$VERSION,$filename_mtime_string);
			}
		}
	}

	$tempDateEnd = date('U');
	$tempDateDuration = $tempDateEnd - $tempDateStart;
	$outputlog .= "Shift Backups Finished, duration: $tempDateDuration seconds\n\n";

	# rename current to daily.1, if it exists
	$tempDateStart = date('U');
	$outputlog .= "Rename Backup Started: ".date('Y-m-d H:i:s')."\n";

	if ( is_dir($MAIN_BACKUP_DIR."/current") ) {
		$filename_mtime_string = filemtime($MAIN_BACKUP_DIR."/current");
		if ( rename ($MAIN_BACKUP_DIR."/current", $SECONDARY_BACKUP_DIR."/daily.1") ) {
			$outputlog .= "Moving ".$MAIN_BACKUP_DIR."/current to ".$SECONDARY_BACKUP_DIR."/daily.1\n";
			touch ($SECONDARY_BACKUP_DIR."/daily.1",$filename_mtime_string);
		}
	}
		
	$command_to_exec = "rm -fr ".$MAIN_BACKUP_DIR."/current";
	exec ($command_to_exec);
	$outputlog .= "Deleting ".$MAIN_BACKUP_DIR."/current\n";

	$tempDateEnd = date('U');
	$tempDateDuration = $tempDateEnd - $tempDateStart;
	$outputlog .= "Rename Backup Finished, duration: $tempDateDuration seconds\n\n";

} else {
	$command_to_exec = "rm -fr ".$MAIN_BACKUP_DIR."/current";
	exec ($command_to_exec);
	$outputlog .= "Deleting ".$MAIN_BACKUP_DIR."/current\n"; }

# create current directory
$command_to_exec = "mkdir -p ".$MAIN_BACKUP_DIR."/current"; exec ($command_to_exec); $outputlog .= "Creating ".$MAIN_BACKUP_DIR."/current\n"; $outputlog .= "\n\n";

if (($SECONDARY_BACKUP_DIR) && (!is_dir($SECONDARY_BACKUP_DIR))) {
	# create secondary backup directory if specified in config file
	$command_to_exec = "mkdir -p ".$SECONDARY_BACKUP_DIR;
	exec ($command_to_exec);
	$outputlog .= "Creating ".$SECONDARY_BACKUP_DIR."\n";
	$outputlog .= "\n\n";
}

################################################################################
#      Create backups
################################################################################

$outputlog .= "Backing up all databases on ".$MYSQLSERVER."...\n\n";

$tempDateStartDumps = date('U');
$outputlog .= "Dumps Started: ".date('Y-m-d H:i:s')."\n\n";

mysql_connect( $MYSQLSERVER , $MYSQLUSER , $MYSQLPASSWD );

if( $bDumps ) {

	$outputLog .= "Creating Database Dumps...\n\n";

	// prepare database array
	$result = mysql_list_dbs();
	$k=0;
	while ($dbRow = mysql_fetch_row($result)) {
		$db_names[$k] = $dbRow[0];
		$outputlog .=  $db_names[$k]."\n";
		$k++;
	}
	$outputlog .= "\n\n";

	// Loop through database array
	for ($k=0; $k < count($db_names); $k++) {

		$tempDateStartDatabase = date('U');
		$outputlog .= "Database '$db_names[$k]' Started: ".date('Y-m-d H:i:s')."\n";

		mysql_select_db($db_names[$k]);
		# create database directory
		$command_to_exec = "mkdir -p ".$sBackupDumpsDir."/".$db_names[$k];
		exec ($command_to_exec);
		$outputlog .= "Creating ".$sBackupDumpsDir."/".$db_names[$k]."\n";
		$outputlog .= "\n\n";
		$outputlog .= "Backing up database ".$db_names[$k]."\n\n";

		// get tables list for selected database
		$result = mysql_query("show tables");

		if ($result) {
			$i = 0;
			$outputlog .=  mysql_error();

			unset( $tb_names );

			// prepare table array
			while ($row = mysql_fetch_row ($result)) {
				//$tb_names[$i] = mysql_tablename ($result, $i);
				$tb_names[$i] = $row[0];
				$i++;
			}

			// loop through database array
			$i = 0;
			for ($i=0; $i<count($tb_names); $i++) {

				// flush tables - closes all transactions on tables,
				// and sets LOCK for the table as well.

				$tempDateStartTable = date('U');
				$outputlog .= "Table '$db_names[$k].$tb_names[$i]' Started: ".date('Y-m-d H:i:s')."\n";

				// Set up command for running the dump itself.

				$COMMAND_DO=$MYSQLDUMP.$MYSQLDUMPOPTIONS.$MYSQLSERVER." -u".$MYSQLUSER." -p".$MYSQLPASSWD." --flush-logs --opt " . $db_names[$k]. " ".$tb_names[$i]." > ".$sBackupDumpsDir."/".$db_names[$k]."/".$tb_names[$i].".sql";

				$temp=split(">",$COMMAND_DO);

				passthru($COMMAND_DO,$result);

				if ($result) {
					$outputlog .= " ERROR! - ".$db_names[$k]." Not Backed Up!\n";
				} else {

					// If successful export, TAR individual table files, then gzip
					// each file using "gzip --best" to get the best compression out
					// of the process.

					if ($bCompress) {
						$outputlog .= "Compressing ".$sBackupDumpsDir."/".$db_names[$k]."/".$tb_names[$i].".sql\n";
						$outputlog .= "\n";
						$COMMAND_DO = "tar -cvf ".$sBackupDumpsDir."/".$db_names[$k]."/".$tb_names[$i].".sql.tar ".$MAIN_BACKUP_DIR."/current/".$db_names[$k]."/".$tb_names[$i].".sql";
						passthru($COMMAND_DO,$result);
						$COMMAND_DO = "gzip --best ".$sBackupDumpsDir."/".$db_names[$k]."/".$tb_names[$i].".sql.tar";
						passthru($COMMAND_DO,$result);

						// Remove the individual ".sql" files from the directory (dir has
						// name matching table name).

						$command_to_exec = "rm -fr ".$sBackupDumpsDir."/".$db_names[$k]."/".$tb_names[$i].".sql";
						//echo $command_to_exec."\n";
						exec ($command_to_exec);
						$outputlog .= "Deleting file ".$sBackupDumpsDir."/".$db_names[$k]."/".$tb_names[$i].".sql\n";
					}
				}
				$tempDateEnd = date('U');
				$tempDateDuration = $tempDateEnd - $tempDateStartTable;
				$outputlog .= "Table '$db_names[$k].$tb_names[$i]' Finished, duration: $tempDateDuration seconds\n\n";
				sleep(1);
			}

			if ($result) {
				$outputlog .= " ERROR! - ".$db_names[$k]." Not Zipped!\n";
			}

			$tempDateEnd = date('U');
			$tempDateDuration = $tempDateEnd - $tempDateStartDatabase;
			$outputlog .= "Database '$db_names[$k]' Finished, duration: $tempDateDuration seconds\n\n";

			$outputlog .= "\n\n";

		} // end of database with no tables if stmt
	} // end of database for loop
} else {
	$outputlog .=  "Skipping Database Dumps...\n\n"; }

$tempDateEnd = date('U');
$tempDateDuration = $tempDateEnd - $tempDateStartDumps; $outputlog .= "Dumps Finished, duration: $tempDateDuration seconds\n\n";

$outputlog .= "#######################################################################\n\n";

$tempDateStartBinaries = date('U');
$outputlog .= "Binaries Started: ".date('Y-m-d H:i:s')."\n\n";

if ($bBinaries) {
	$hBinaryDirMain = opendir( $sBinaryDir );
	while (($sBinaryDirDb = readdir($hBinaryDirMain)) != false) {
		if( is_dir( "$sBinaryDir/$sBinaryDirDb" ) && $sBinaryDirDb != "." && $sBinaryDirDb != ".." ) {
			$hBinaryDbDir = opendir( "$sBinaryDir/$sBinaryDirDb" );
			while(($sBinaryFile = readdir($hBinaryDbDir)) != false) {
				if( $sBinaryFile != "." && $sBinaryFile != ".." && substr($sBinaryFile, -3, 3 ) == "MYD" ) {
					$sBinaryLoc = substr( $sBinaryFile, 0, strpos( $sBinaryFile, "." ) );
					$tempDateStartBinary = date('U');
					$outputlog .= "Binary ($sBinaryDir/$sBinaryLoc) Started: ".date('Y-m-d H:i:s')."\n";

					$command_to_exec = "mkdir -p $sBackupBinariesDir/$sBinaryDirDb";
					exec ($command_to_exec);

					// Lock tables before running command.
					$sLockTable = "LOCK TABLES $sBinaryDirDb.$sBinaryLoc READ";
					$outputlog .= "$sLockTable\n";
					$rLockTable = mysql_query( $sLockTable );
					$outputlog .=  mysql_error();

					// If lock fails, do not continue, report failure.
					if( !$rLockTable ) {
						$outputlog .=  "FAILURE: Did not lock table $sBinaryLoc!\n";
					} else {
						// Flush tables before running the copy.
						$sFlushTable = "FLUSH TABLES ".$sBinaryLoc;
						$rFlushTable = mysql_query( $sFlushTable );
						$outputlog .=  mysql_error();

						// If flush tables fails, unlock tables, and discontinue.
						if ( !$rFlushTable ) {
							$outputlog .=  "FAILURE: Could not flush table $sBinaryLoc!\n";
							$outputlog .=  "Attempting to unlock table $sBinaryLoc\n";
							$sUnlockTablesFail = "UNLOCK TABLES";
							$outputlog .= "\n\n$sUnlockTablesFail\n\n";
							$rUnlockTablesFail = mysql_query( $sUnlockTablesFail );
							$outputlog .=  mysql_error();

							// if unlock tables fails, notify admins!
							if ( !$rUnlockTablesFail ) {
								$outputlog .=  "FAILURE: Could not unlock table $sBinaryLoc!\n";
								$outputlog .=  "Notifying Admins!";
								mail($MAILTO, "Critical: mysqlbak.php ($sBinaryLoc)", "Failure to unlock table: $tb_names[$i]", "From: ".$MAILTO."\nReply-To: ".$MAILTO."\nX-Mailer: PHP/" . phpversion());
							} else {
								$outputlog .=  "Recovered.  Tables unlocked, but file not copied.";
							}
						} else {

							$outputlog .= "Creating $sBackupBinariesDir/$sBinaryLoc.\n";
							$command_to_exec = "cp -p $sBinaryDir/$sBinaryDirDb/$sBinaryLoc.* $sBackupBinariesDir/$sBinaryDirDb";
							exec ($command_to_exec);
							$outputlog .= "Copy (with preserve) $sBinaryDir/$sBinaryDirDb/$sBinaryLoc.* $sBackupBinariesDir/$sBinaryDirDb\n";

							// After command run, unlock the tables.
							$sUnlockTables = "UNLOCK TABLES";
							$outputlog .= "$sUnlockTables\n";
							$rUnlockTables = mysql_query( $sUnlockTables );
							$outputlog .=  mysql_error();

							// if unlock tables fails, notify admins!
							if ( !$rUnlockTables ) {
								$outputlog .=  "FAILURE: Could not unlock table $sBinaryLoc!\n";
								$outputlog .= "Notifying Admins!";
								mail($MAILTO, "Critical: Mustang MySQL Backup ($sBinaryLoc)", "Failure to unlock table: $tb_names[$i]", "From: ".$MAILTO."\nReply-To: ".$MAILTO."\nX-Mailer: PHP/" . phpversion());
							}

							$tempDateEnd = date('U');
							$tempDateDuration = $tempDateEnd - $tempDateStartBinary;
							$outputlog .= "Binary ($sBinaryDir/$sBinaryLoc) Finished, duration: $tempDateDuration\n\n";
							sleep(1);
						}
					}
				}
			}
		}
	}
	closedir($hBinaryDirMain);
}

$tempDateEnd = date('U');
$tempDateDuration = $tempDateEnd - $tempDateStartBinaries; $outputlog .= "Binaries Finished, duration: $tempDateDuration seconds\n\n";

$scriptfinishtime = exec('date');
$outputlog .= "\n\n";
$outputlog .= "#######################################################################\n";
$outputlog .= "Backup completed: ".$scriptfinishtime."\n"; $outputlog .= "#######################################################################\n";
$outputlog .= "\n";

################################################################################
#      Send report
################################################################################

mail($MAILTO, "$REPORTNAME", $outputlog, "From: ".$MAILTO."\nReply-To: ".$MAILTO."\nX-Mailer: PHP/" . phpversion());


############################################################
#
#	To Do List -
#	
#	Make so will work on Windows or Unix type OS.
#	Set OS in variable section, path slashes will adjust.
#
#	Add support for stronger compression.
#	
#	Add support for connection parameters in my.cnf file
#	
#	Add support for BU of remote servers with
#	optional support for SSL and compression on connection.
#
#	Summary section of top flagging any problems.
#
#	Summary section to indicate any dumps that took longer
#	than time set in variable section.
#
#	Support for remote target server to hold BUs.
#
#	Sign up for storage option with MIS.
#
#	Check for enough disk space on target.
#
###################################################################

?>


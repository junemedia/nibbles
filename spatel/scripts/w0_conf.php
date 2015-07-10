<?php
#################################################################################
#       Program Variables
#################################################################################
#
# modify these variables as required

# address to receive backup reports
$MAILTO = "jr@johnrudnick.com";

# short name for server
$SERVERNAME = "s0";

# MySQL login information
$MYSQLSERVER = "localhost";
$MYSQLUSER = "root";
$MYSQLPASSWD = "9A0Xp2";

# MySQL binary location and options
$MYSQLDUMP = "/usr/bin/mysqldump";
$MYSQLDUMPOPTIONS = " -h";

# backups will be placed here
$MAIN_BACKUP_DIR = "/home/backup";
$SECONDARY_BACKUP_DIR = "/home/nobackup";
$sBackupBinariesDir = "$MAIN_BACKUP_DIR/current/binaries";
$sBackupDumpsDir = "$MAIN_BACKUP_DIR/current/dumps";

# number of backup sets to retain, in addition to the current set
$NUM_BACKUPS = "1";

# location of MySQL binary db files
$sBinaryDir = "/home/mysql";

# if query fails due to connection issue, close connection and reconnect upto 10 times
# before giving up. send alert email if fails all 10 times.
$iMySQLRetry = 10;

################################################################################
#      Program Variables End
################################################################################
?>

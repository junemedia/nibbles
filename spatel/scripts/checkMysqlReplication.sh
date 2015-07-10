####################################################################
##  
##  This script checks the replication between the master and
##  slave(s) in our Mysql system.  We write a unix timestamp to the
##  master, and then wait for WAITVAR seconds, and verify that the
##  information made it to the slave.
##  
##  To check multiple Slaves, add their IP addresses to the variable
##  "ipSlaveList". (space separated)
##  
##  For multiple email recipients, use space-separated values.
##  
####################################################################
#!/bin/bash

sScriptName="checkMysqlReplication.sh";

source /home/scripts/includes/mysqlServer.conf
source /home/scripts/includes/cssLogFunctionStart.sh

ipMASTER=$mysqlMASTERIP;

# ipSlaveList="64.132.70.15 64.132.70.150";
ipSlaveList="64.132.70.15";

MAILTO=$recipNotify;


SERVERNAME="CORY";

REPORTNAME="$SERVERNAME 'checkMysqlReplication.sh'";

mysqlUSERNAME=$mysqlNibblesUSER;

mysqlPASSWORD=$mysqlNibblesPASS;

mysqlDATABASE=$mysqlDatabaseSysAdmin;

timeCurrent=`date +%s`

WAITVAR="180";

echo "INSERT INTO replicationCheck VALUES ( $timeCurrent )" | mysql -h $ipMASTER -u $mysqlNibblesUSER -p$mysqlNibblesPASS -D $mysqlDATABASE

slaveUSERNAME=$mysqlNibblesUSER;

slavePASSWORD=$mysqlNibblesPASS;

slaveDATABASE=$mysqlDatabaseSysAdmin;

echo "<?php usleep( $WAITVAR * 1000000 ); ?>" | php;

for ipSLAVE in $ipSlaveList; do

	replicationTime="";

	replicationTime=`echo "SELECT * FROM replicationCheck WHERE checkTimeStamp=$timeCurrent" | mysql -h $ipSLAVE -u $slaveUSERNAME -p$slavePASSWORD -D $slaveDATABASE|grep -v checkTimeStamp`

	if [ "$replicationTime" == "$timeCurrent" ]; then
		echo "Replication Verified: $replicationTime/$timeCurrent";
		sEmailBody="Replication Test succeeded on $ipSLAVE.";
		# echo "$sEmailBody" | mail -s "$REPORTNAME" $MAILTO;
	else
		echo "Replication FAILED: $replicationTime/$timeCurrent";
		sEmailBody="Replication Test FAILED on $ipSLAVE.";
		echo "$sEmailBody" | mail -s "$REPORTNAME - ERRORS!" $MAILTO;
	fi
done

source /home/scripts/includes/cssLogFunctionFinish.sh

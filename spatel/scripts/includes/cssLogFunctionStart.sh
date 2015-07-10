#!/bin/bash

source /home/scripts/includes/mysqlServer.conf

cssLogStart=`date '+%Y-%m-%d %H:%M:%S'`
echo "INSERT INTO nibbles.cronScriptStatus (scriptName, startDateTime) VALUES ( '$sScriptName', now() )" | mysql -h $mysqlMASTERIP -u $mysqlNibblesUSER -p$mysqlNibblesPASS -D $mysqlDatabaseNibbles

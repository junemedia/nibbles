#!/bin/bash

source /home/scripts/includes/mysqlServer.conf

cssLogStart=`date '+%Y-%m-%d %H:%M:%S'`
echo "UPDATE nibbles.cronScriptStatus set endDateTime=now() where scriptName='$sScriptName' order by id DESC limit 1" | mysql -h $mysqlMASTERIP -u $mysqlNibblesUSER -p$mysqlNibblesPASS -D $mysqlDatabaseNibbles

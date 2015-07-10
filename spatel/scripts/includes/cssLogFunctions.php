<?php

function cssLogStart( $sScriptName ) {
        $aIniConfig = parse_ini_file( "/home/scripts/includes/mysqlServer.conf" );

        $sMysqlMasterIp = $aIniConfig['mysqlMASTERIP'];
        $sMysqlNibblesUser = $aIniConfig['mysqlNibblesUSER'];
        $sMysqlNibblesPass = $aIniConfig['mysqlNibblesPASS'];

        mysql_connect( $sMysqlMasterIp, $sMysqlNibblesUser, $sMysqlNibblesPass );

        $sCronStatusQuery1 = "INSERT INTO nibbles.cronScriptStatus(scriptName, startDateTime)
                                                  VALUES('$sScriptName', now())";
        $rCronStatusResult1 = mysql_query($sCronStatusQuery1);
        echo mysql_error();
        return mysql_insert_id();
}

function cssLogFinish( $iScriptId ) {
        $aIniConfig = parse_ini_file( "/home/scripts/includes/mysqlServer.conf" );

        $sMysqlMasterIp = $aIniConfig['mysqlMASTERIP'];
        $sMysqlNibblesUser = $aIniConfig['mysqlNibblesUSER'];
        $sMysqlNibblesPass = $aIniConfig['mysqlNibblesPASS'];

        mysql_connect( $sMysqlMasterIp, $sMysqlNibblesUser, $sMysqlNibblesPass );

        $sCronStatusQuery2 = "UPDATE nibbles.cronScriptStatus
                                                  SET    endDateTime = now()
                                                  WHERE  id = '$iScriptId'";
        $rCronStatusResult2 = mysql_query($sCronStatusQuery2);
        echo mysql_error();
}

?>

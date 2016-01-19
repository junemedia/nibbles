<?php

$dbase = "jr";

$user = "root" ;

$pass = "092363jr" ;

$sBgColor1 = "#CCCCCC";

$sBgColor2 = "#FFFFFF";

$sScriptTitle = "test title";

$sTableName = "test";

$iRecPerPage = "25";

$sAllowEdit = 'Y';
$sAllowDelete = 'y';
$sAllowAdd = 'Y';

//$sStyleSheetLink = "../../style.css";

$returnLink = '<a href="tableManager.php">Return to Main</a>';

$DefaultOrderBy = "id" ;

$sFilterColumn = "col1";

$sAutoIncCol = "col1";

$aDisplayCols = array('col2', 'col3');

// DO NOT CHANGE THESE TWO LINES!

mysql_pconnect ('localhost', $user, $pass);

// mysql_connect ('localhost', $user, $pass);

mysql_select_db ($dbase);

?>
<style>

TD {
	FONT-SIZE: 8pt; COLOR: #000000; FONT-FAMILY: Arial, Helvetica, "Sans Serif"; 
}


TD.message {
	FONT-SIZE: 8pt; COLOR: #FF0000; FONT-FAMILY: Arial, Helvetica, "Sans Serif"; FONT-FACE: BOLD;
}

A:link {
	 FONT-SIZE: 8pt; COLOR: #0000FF; FONT-FAMILY: Arial, Helvetica, "Sans Serif"; 
}
A:visited {
	 FONT-SIZE: 8pt; COLOR: #0000FF; FONT-FAMILY: Arial, Helvetica, "Sans Serif"; 
}
A:hover {
	 FONT-SIZE: 8pt; COLOR: #0000FF; FONT-FAMILY: Arial, Helvetica, "Sans Serif"; 
}
A:active {
	 FONT-SIZE: 8pt; COLOR: #0000FF; FONT-FAMILY: Arial, Helvetica, "Sans Serif"; 
}


</style>
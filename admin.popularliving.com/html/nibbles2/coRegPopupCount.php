<?php

include_once("../includes/paths.php");
include_once('session_handlers.php');

$PHPSESSID = trim($_GET['PHPSESSID']);
session_start();

$id = session_id();
$src = $_SESSION['sSesSourceCode'];
$ss = $_SESSION['sSesSubSourceCode'];
$pgId = $_SESSION['iSesPageId'];
$sIp = $_SESSION['sSesRemoteIp'];

$sPageStatQuery = "INSERT INTO tempPageDisplayStats(pageId, sourceCode, subSourceCode, 
				openDate, sessionId, ipAddress, openDateTime)
			VALUES(\"$pgId\", \"$src\", \"$ss\", CURRENT_DATE, \"$id\", \"$sIp\", NOW())";
$rPageStatResult = dbQuery($sPageStatQuery);



?>

<?php
/*
$Author: spatel $
$Id: index.php,v 1.6 2006/04/26 15:22:57 spatel Exp $
*/
include("../includes/paths.php");
   
//echo $sGblAdminSiteRoot;
$sCurrSite = $_SERVER['SERVER_ADDR'];
$sCurrSiteAddr = $_SERVER['SERVER_NAME'];

session_start();

echo session_id();
echo phpinfo();
?>

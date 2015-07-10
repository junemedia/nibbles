<?php

include_once("config.php");

mysql_pconnect ($host, $user, $pass);
mysql_select_db ($dbase);

while (list($key,$val) = each($_POST)) { $$key = $val; }
while (list($key,$val) = each($_GET)) { $$key = $val; }


?>

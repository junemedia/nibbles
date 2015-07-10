<?php

session_id(trim($_GET['PHPSESSID']));
session_start();

unset($_SESSION['sTempArray'][0]);
$temp_array = array_values($_SESSION['sTempArray']);
$_SESSION['sTempArray'] = $temp_array;


?>


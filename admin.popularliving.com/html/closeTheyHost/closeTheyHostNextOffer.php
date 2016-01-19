<?php

session_id(trim($_GET['PHPSESSID']));
session_start();

unset($_SESSION['aSesCloseTheyHostOffersChecked'][0]);
$temp_array = array_values($_SESSION['aSesCloseTheyHostOffersChecked']);
$_SESSION['aSesCloseTheyHostOffersChecked'] = $temp_array;


?>


<?php

$host = "localhost" ;

$dbase = "nibbles" ;

$user = "nibbles" ;

$pass = "#a!!yu5" ;

// DO NOT CHANGE THESE TWO LINES!

 $conn = mysql_pconnect ('localhost', $user, $pass);
 
 if ($conn) {
 	echo "Mysql is working";
 }
 
?>
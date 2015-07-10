<?php

include("../../includes/paths.php");

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
	
?>



<?php
print "<html>

<head>
<title>Server Information</title>

</head>

<body>


<center>";

print "<br><br>

<a href =\"../index.php\">Return to Nibbles Main Menu</a>

</center>

<br><br>";


	phpinfo();
	print "</body>

</html>";


} else {
echo "You are not authorized to view this page...";
}
?>





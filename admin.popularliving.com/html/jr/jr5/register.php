<?php

$host = "localhost" ;

$dbase = "testJr" ;

$user = "nibbles" ;

$pass = "#a!!yu5" ;

mysql_pconnect ('localhost', $user, $pass);

mysql_select_db ($dbase);


// Check if common tables exist... Create the tables if not exist
$sCheckQuery = "SHOW TABLES LIKE 'users%'";
$rCheckResult = mysql_query($sCheckQuery);
echo mysql_error();

if (mysql_num_rows($rCheckResult) == 0) {
	$sCreateQuery = "CREATE TABLE `users` (
  `id` int(11) NOT NULL auto_increment,
  `first` varchar(30) NOT NULL default '',
  `last` varchar(30) NOT NULL default '',
  `city` varchar(40) NOT NULL default '',
  `state` char(2) NOT NULL default '',
  `zip` varchar(20) NOT NULL default '',
  `phone` varchar(20) NOT NULL default '',
  `email` varchar(100) NOT NULL default '',
  `login` varchar(30) NOT NULL default '',
  `passwd` varchar(30) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;";
	
	$rCreateResult = mysql_query($sCreateQuery);
	echo mysql_error();
	
}


if ($submit) {
			
		if (trim($userId !='') && trim($passwd !='')) {
			$loginQuery ="SELECT *
						  FROM  users
						  WHERE login = '$userId'
						  AND	passwd='$passwd'";
			
			$loginResult = mysql_query($loginQuery);
			if (mysql_num_rows($loginResult)>0) {
				while ($row = mysql_fetch_object($loginResult)) {
					
					if ($row->login == $userId) {
																		
						// run command here
						exec("ls -al");
						$sMessage = "Welcome $row->first $row->last";
						$submit='';					
						
					}
				}
			} else {
				$sMessage = "Wrong UserId or password...";
			}
		}
}

?>

<html>
<body>
<center><font color=#FF0000><?php echo $sMessage;?></font></center>
<table cellpadding=5 cellspacing=0 width=45% align=center>
<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>

<tr><TD width=40%>UserId</td><td><input type=text name=userId value='<?php echo $userId;?>'></td></tr>
<tr><TD>Password</td><td><input type=password name=passwd value='<?php echo $passwd;?>' ></td></tr>
<tr><td></td><TD><input type=submit name=submit value="Login"></td></tr>
</table>
</form>
</body>
</html>
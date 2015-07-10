<?php

include_once("../subctr_config.php");

$message = '';
$fname_filter = '';
$lname_filter = '';
$content = '';

if ($_POST['submit'] == 'Search...') {
	if ($_POST['fname'] == '' && $_POST['lname'] == '') {
		$message = "* Please enter first name, last name, or both.";
	} else {
		if ($_POST['lname'] != '') {
			if (!eregi("^[-A-Z[:space:]'\.]*$", trim($_POST['lname']))) {
				$message = "* Invalid last name.";
			}
		}
		
		if ($_POST['fname'] != '') {
			if (!ctype_alpha(trim($_POST['fname']))) {
				$message = "* First name must be alphabetic characters: A-Z a-z";
			}
		}
	}
	
	if ($message == '') {
		$fname = trim($_POST['fname']);
		$lname = trim($_POST['lname']);
		
		if ($fname != '') { $fname_filter = " AND fname = \"$fname\""; }
		if ($lname != '') { $lname_filter = " AND lname = \"$lname\""; }
		
		$query = "SELECT * FROM userData WHERE 1=1 $fname_filter $lname_filter";
		$query_result = mysql_query($query);
		echo mysql_error();
		while ($get_email_row = mysql_fetch_object($query_result)) {
			if ($sBgcolorClass=="#E6E6FA") {
				$sBgcolorClass="#FFFACD";
			} else {
				$sBgcolorClass="#E6E6FA";
			}
			$content .= "<tr bgcolor=$sBgcolorClass>
						<td><a href='../lookup.php?GET=Y&email=$get_email_row->email'>$get_email_row->email</a></td>
						<td>$get_email_row->fname</td>
						<td>$get_email_row->lname</td>
						<td>$get_email_row->addr1</td>
						<td>$get_email_row->addr2</td>
						<td>$get_email_row->city</td>
						<td>$get_email_row->state</td>
						<td>$get_email_row->zip</td>
						<td>$get_email_row->country</td>
						<td>$get_email_row->gender</td>
						<td>$get_email_row->phone_1$get_email_row->phone_2$get_email_row->phone_3</td>
						<td>$get_email_row->year/$get_email_row->month/$get_email_row->day</td></tr>";
		}
	}
}

?>

<html>
<head>
<title>Lookup email by first/last name...</title>
<style>
table {
	font-family: verdana;
	font-size:75%;
}
</style>
</head>
<body>
<table border="0" align="center" width="600px" cellpadding="2" cellspacing="2">
	<tr>
		<td colspan="2" align="center">
			<a href="http://admin.popularliving.com/admin/index.php">Return to Nibbles Main Menu</a>
		</td>
	</tr>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr><td colspan="2" style="color:red;"><?php echo $message; ?></td></tr>
	<tr><td colspan="2">&nbsp;</td></tr>
</table>

<form name='form1' method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
<table border="0" align="center" width="600px" cellpadding="2" cellspacing="2">
	<tr>
		<td>First Name: </td>
		<td><input type="text" name="fname" size="20" maxlength="100" value="<?php echo $fname; ?>"></td>
		<td>Last Name: </td>
		<td><input type="text" name="lname" size="20" maxlength="100" value="<?php echo $lname; ?>"></td>
		<td><input type="submit" name="submit" value="Search..."></td>
	</tr>
</table>
</form>

<?php if ($content != '') { ?>
	<table width="95%" align="center">
	<tr><td><b>Email</b></td>
		<td><b>First</b></td><td><b>Last</b></td><td><b>Addr1</b></td>
		<td><b>Addr2</b></td><td><b>City</b></td><td><b>State</b></td>
		<td><b>Zip</b></td><td><b>Country</b></td><td><b>Gender</b></td>
		<td><b>Phone</b></td><td><b>DOB</b></td>
	</tr>
	<?php echo $content; ?>
	</table>
<?php } ?>

</body>
</html>

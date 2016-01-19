<?php

session_start();

$task = $_GET['task'];
if ($_POST['task']) {
	$task = $_POST['task'];
}
		
switch($task) {
	case 'login':
		checkLogin($_POST['username'], $_POST['password'], $_POST['remember']);
		break;
	case 'logout':	
		logout('You have logged out.');
		break;
	default:
		if ( ($_SESSION['post_editor_logged_in'])  &&  (strstr($_SERVER['PHP_SELF'], 'editor_tools') )  ) {
			// if the user is a post editor and is in the editor dir, let them proceed.
			continue;
		} elseif ( ! $_SESSION['logged_in']) { 
			// if user is just coming to site and there is a cookie for this site	
			if ( isset($_COOKIE[$_CONFIG['admin_cookie_name']]) ) { 
				checkRemembered($_COOKIE[$_CONFIG['admin_cookie_name']]); 
			} else {
				logout();
			}
		}
}

//================================================
//	FUNCTIONS
//================================================

function logout($message='') {
	session_defaults();
	displayLogInForm($message);
	exit();
}

//------------------------------------------------------------------------------------------------

function session_defaults() { 
	$_SESSION['logged_in'] = false; 
	$_SESSION['id'] = 0; 
	$_SESSION['username'] = ''; 
	$_SESSION['cookieValue'] = 0; 
	$_SESSION['remember'] = false; 
}

//------------------------------------------------------------------------------------------------

//------------------------------------------------------------------------------------------------

function setSession(&$values, $remember, $init = true) {  
	global $link;
	
	$id = $values['id'];
	$_SESSION['id'] = $id;
	$_SESSION['username'] = htmlspecialchars($values['username']); 
	$_SESSION['cookieValue'] = $values['cookieValue']; 
	
	// these allow us to easily update/change the html template. can be changed in any file, especially an index file
	$_SESSION['html_start'] = 'html_start.php';
	$_SESSION['html_end'] = 'html_end.php';
	

	// set access level privileges
	$_SESSION['accessDownload'] = $values['accessDownload']; 
	$_SESSION['accessPrivileges'] = $values['accessPrivileges']; 
	$_SESSION['accessPageContent'] = $values['accessPageContent']; 
	$_SESSION['accessEditorData'] = $values['accessEditorData']; 
	$_SESSION['accessEditorTool'] = $values['accessEditorTool']; 
	$_SESSION['accessEditorTest'] = $values['accessEditorTest']; 
	$_SESSION['accessSiteStats'] = $values['accessSiteStats']; 
	$_SESSION['accessBoardAdmin'] = $values['accessBoardAdmin']; 
	
	
	

	$_SESSION['logged_in'] = true; 

	
	if ($remember) { 
		updateCookie($values['cookieValue'], true); 
	} 
	// if the user is logging in, update some values in the db for this session
	if ($init) { 
		$sessionID = dbprep(session_id()); 
		$ipAddress = dbprep($_SERVER['REMOTE_ADDR']);
		
		$sql = "UPDATE user SET ";
		$sql .= "sessionID='$sessionID', ";
		$sql .= "ipAddress='$ipAddress', ";
		$sql .= "timeStamp=NOW() ";
		$sql .= "WHERE id = $id"; 
		$result = mysql_query($sql);
		if (!$result) {
			error($sql, $_SERVER[HTTP_HOST],"$_SERVER[PHP_SELF]?$_SERVER[QUERY_STRING]");
		}
	} 
}

//------------------------------------------------------------------------------------------------

function updateCookie($cookie_value, $save) { 
	$_SESSION['cookieValue'] = $cookie_value; 
	if ($save) { 
		$cookie_value = serialize(array($_SESSION['username'], $cookie_value) ); 
		setcookie($_CONFIG['admin_cookie_name'], $cookie_value, $_CONFIG['admin_cookie_expire'], $_CONFIG['admin_cookie_path']); 
	} 
}


//------------------------------------------------------------------------------------------------

function checkRemembered($cookie) { 
	list($username, $cookie_value) = @unserialize($cookie); 
	if (!$username or !$cookie_value) {
		return; 
	}
	$username = dbprep($username); 
	$cookie_value =  dbprep($cookie_value); 
	
	$sql = "SELECT * FROM user ";
	$sql .= "WHERE username ='$username' ";
	$sql .="AND cookieValue ='$cookie_value' "; 
	
	$result = mysql_query($sql);
	if (!$result){
		error($sql, $_SERVER[HTTP_HOST],"$_SERVER[PHP_SELF]?$_SERVER[QUERY_STRING]");
	}
	
	$num_rows = mysql_num_rows($result);
	if ($num_rows == 1) {
		$row = mysql_fetch_assoc($result);
		setSession($row, true); 
	}
}

//------------------------------------------------------------------------------------------------

function checkLogin($username, $password, $remember) { 
	global $link;
	
	$username = dbprep($username); 
	$password =  dbprep($password); 
	
	$sql = "SELECT * FROM user ";
	$sql .= "WHERE username ='$username' ";
	$sql .= "AND password ='$password' ";
	
	$result = mysql_query($sql);
	if (!$result){
		error($sql, $_SERVER[HTTP_HOST],"$_SERVER[PHP_SELF]?$_SERVER[QUERY_STRING]");
	}
	$num_rows = mysql_num_rows($result);
	
	if ($num_rows == 1) {
		$row = mysql_fetch_assoc($result);
		setSession($row, true); 
	} else { 
		$message = 'Could not log you in. See "More Information" below.'; 
		logout($message); 
	} 
}

//------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------

function displayLogInForm($message) {
	global $_CONFIG, $adminRootDir;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<title><?php echo $_CONFIG['page_title'];?></title>

	<link rel="stylesheet" href="<?php echo $adminRootDir;?>/css/admin_styles.css" type="text/css" media="screen" />
	<script type="text/javascript" charset="UTF-8" src="admin_js_lib.js"></script>
	
</head>
<body>

<h1><?php echo $_CONFIG['page_title'];?> Web Site Administration</h1>

<div id="logInDiv">
	<h2>Please enter your login information here:</h2>

	<form name="logInForm" action="<?php echo $_SERVER['PHP_SELF'];?>" method="post">
		<input type="hidden" name="task" value="login" />
	<?php
	if ($message) {
		?>
		<h3 id="message"><?php echo $message;?></h3>	
		<?php
	}
	?>
	
	<table class="formTable">
		<tr>
			<td class="label">Username:</td>
			<td class="input"><input name="username" type="text" size="20" /></td>
		</tr>
		<tr>
			<td class="label">Password:</td>
			<td class="input"><input type="password" name="password" size="20" /></td>
		</tr>
		<tr>
			<td></td>
			<td class="input"><input type="submit" value="Log In" /></td>
		</tr>
	</table>
	</form>

<h3>More Information:</h3>
<p>You are not currently logged in. This can be for a number of reasons:</p>
<ul>
	<li>You haven't logged into the system yet</li>
	<li>The Username or Password provided is not valid</li>
	<li>You may not have the required access level</li>
	<li>You didn't do anything for 30 minutes</li>
</ul>

<h3>Tips:</h3>
<ul>
	<li>Make sure you type your username and password in the correct case -- you may want to make sure your CAPS LOCK key is not on.</li>
	<li>Make sure your browser has Cookies enabled. This site uses cookies to hold your login information. You cannot use this web site without cookies enabled.</li>
	<li>Be sure your computer's System Clock is correct. If the date and/or time are incorrect, or in the wrong time zone, your session may time out incorrectly.</li>
</ul>

<h3>For Further Assistance:</h3>
<ul>
	<li>Please contact the administrator</li>
</ul>

</div><!-- end logInDiv -->

<?php
//	print_r($_SESSION);
?>

</body>
</html>
<?php
}

//================================================
//================================================
// END FILE
?>
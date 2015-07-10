<?php

$post_get_var_list = array('task',
				'required_fields',
				'name',
				'email',
				'phone',
				'fax',
				'comments',
				'job_function',
				'how_heard',
				'email_sign_up',
				'company_name'
				);
				
include_once "includes/config.php";
include_once "includes/library.php";



if ($task == 'process') {
	if (trim($_COOKIE['security_code']) == $_POST['security_code'] && !empty($_COOKIE['security_code'])) {
		// haha goood.
		// create email message
		$email_message = '';
		$email_message .= "Company Name: $company_name \n";
		$email_message .= "Name: $name \n";
		$email_message .= "E-Mail Address: $email \n";
		$email_message .= "Phone: $phone \n";
		$email_message .= "Fax: $fax \n";
		
		
		$email_message .= "\n\n\n";
		
		
		if ($comments) {
			$email_message .= "Comments: \n";
			$email_message .= "$comments \n\n";
		}
		
		
		$email_message .= "How did you hear about us: \n";
		$email_message .= "$how_heard \n\n\n";
		
		$email_to = getVar('contactUsEmailAddress');
		if (isTestIP()) {
			$email_to = 'spatel@amperemedia.com';	
		}
		
		$email_subject = $_CONFIG['domain_name'] . ' Contact Us Form Submission';
		$email_from = "From: $name <$email>\r\n";
		$email_from .= "Reply-To: $name <$email>\r\n";
		$email_from .= "X-Header: Mustang Source ".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."\r\n";
		
	
		mail($email_to, $email_subject, $email_message, $email_from);
		$message = getVar('contactUsMessage');
		
		
		$company_name = '';
		$name = '';
		$email = '';
		$phone = '';
		$fax = '';
		$comments = '';
		$how_heard = '';
		
		
	} else {
		$message = "<font color=red>Sorry, you have provided an invalid security code.</font>";
	}
	// delete cookie that has security code.
	setcookie("security_code", $_COOKIE['security_code'], time()-3600, '/', '', 0);
}
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Silver iNET</title>
<link rel="stylesheet" type="text/css" href="style/style.css" />
<script src="js/javascript.js" type="text/javascript"></script>
<script src="javascript/form_validation_functions_v3.js" type="text/javascript"></script>
<script src="javascript/site_functions.js" type="text/javascript"></script>
</head>

<body id="contact_us">
	<!--begin wrapper-->
    <div id="wrapper">
    	<!--begin search_bar-->
    	<div id="search_bar">
    		<h1><a href="index.php"><img src="images/logo.gif" width="411" height="60" alt="Silver iNET" /></a></h1>	
            <form action="https://partners.cpacoreg.com" method="post">
                <p><input type="hidden" name="next" value="" /></p>
                <ul>
                	<li><label for="username">Username:</label> <input type="text" id="username" name="DL_AUTH_USERNAME" value="" />
                    <a href="javascript:openWin('http://partners.cpacoreg.com/forgot_password.html')">Forgot Password?</a>
                    </li>
                	<li><label for="password">Password:</label> <input type="password" id="password" name="DL_AUTH_PASSWORD" value="" /></li>
                </ul>
                <p><input class="login_button" type="image" src="images/login-off.gif" value="Submit" alt="Submit" /></p>
			</form>
        </div>
        <!--end search_bar-->
        
        <!-- end search_bar-->
		<div id="main_nav">
			<ul id="nav">
				<li id="home"><a href="index.php">home</a></li>
				<li id="about"><a href="about_us.php">about us</a></li>
				<li id="affiliate"><a href="affiliate_sign_up.php">affiliate sign up</a></li>
				<li id="advertiser"><a href="advertiser_sign_up.php">advertiser sign up</a></li>
                <li id="contact" class="here"><a href="contact_us.php">contact us</a></li>
            </ul>
		</div>
		<!-- end main_nav-->
        
        <!-- begin content -->
        <div id="content">
        	<!-- begin content_bot -->
        	<div id="content_bot">
            	<!-- begin content_inner -->
            	<div id="content_inner">
                	<h2>Contact Us</h2>
                	
                	<?php
					if ($message!="") {
						?>
						<h3 id="message"><?php echo $message;?></h3>
						<?php
					}
					?>
					
					<?php 
					
					// since below text is hard coded into this page, we don't need to get it from DB.  if they are updating below text in db, then we have
					// comment out below hard coded text and enable below line: echo getVar...
					//echo getVar('contactUsText');
					?>
        			
                    <p>We'll be happy to get in touch with you, answer your questions and show you how SilveriNet.com can help you reach customers and grow your business.</p>

					<p>Simply use this form and someone will be in touch with you quickly.</p>

                    <form action="<?php echo $_SERVER['PHP_SELF'];?>" method="post" onsubmit="return validateForm_ContactUs(this)">
                        <input type="hidden" name="task" value="process" />
                        <input type="hidden" name="error_td" value="1" />
                        <input type="hidden" name="required_fields" value="name,email" />
                    
                    <!-- begin onecol -->
                    <div class="onecol">
                    	<p><label for="company_name">Company Name:</label> <input id="company_name" type="text" name="company_name" value="<?php echo $company_name; ?>" /></p>
                        <p><label for="name">Your Name:</label> <input id="name" type="text" name="name" value="<?php echo $name; ?>" /></p>
                        <p><label for="email">Email:</label> <input id="email" type="text" name="email" value="<?php echo $email; ?>" /></p>
                        <p><label for="phone">Phone:</label> <input id="phone" type="text" name="phone" value="<?php echo $phone; ?>" /></p>
                        <p><label for="fax">Fax:</label> <input id="fax" type="text" name="fax" value="<?php echo $fax; ?>" /></p>
                        <p><label for="comments">Additional Comments</label><br /><textarea name="comments" rows="5" cols="15"><?php echo $comments; ?></textarea></p>
                        <p><label for="how_heard">How did you hear about us?</label><br /><input id="how_heard" type="text" name="how_heard" value=<?php echo $how_heard; ?>"" /></p>
                     	<p><label for="security_code">Security Code:</label> <input id="security_code" name="security_code" type="text" maxlength="6" size="6">
                        <p><img src="captcha.php"></p>
                        <p class="txtctr"><input type="submit" value="Contact Me" /></p></p>
                    </div>
                    <!-- end onecol -->
                        
                 	</form>   
                </div>
                <!-- end content_inner -->
            </div>
            <!-- end content_bot -->
        </div>
        <!-- end content -->
        
        <!-- begin feature -->
        <div id="feature">
        	<a href=""><img class="silverinet" src="images/silverinet.gif" width="197" height="66" alt="Silver iNET" /></a>
            <img class="fltright" src="http://www.pic3400.com/silverinet/mainfooter/feature-offers.jpg" width="554" height="69" alt="featured offers" usemap="#Map" />
			<map name="Map" id="Map">
            	<!--<area shape="rect" coords="13,27,143,60" href="#1" alt="gamefly" />
				<area shape="rect" coords="147,27,261,60" href="#2" alt="bejewled" />
				<area shape="rect" coords="268,11,322,64" href="#3" alt="vip" />
				<area shape="rect" coords="334,20,437,61" href="#4" alt="zoobooks" />
				<area shape="rect" coords="442,20,550,59" href="#5" alt="torchmark" />-->
			</map>
        </div>
        <!-- end feature -->
        
        <!-- begin footer -->
        <div id="footer">
        	<ul id="footer_nav">
            	<li><a href="index.php">Home</a> | </li>
                <li><a href="about_us.php">About Us</a> | </li>
                <li><a href="affiliate_sign_up.php">Affiliate Sign Up</a> | </li>
                <li><a href="advertiser_sign_up.php">Advertiser Sign Up</a> | </li>
                <li><a href="contact_us.php">Contact Us</a> | </li>
                <li><a href="privacy_policy.php">Privacy Policy</a> | </li>
                <li><a href="publishers_agreement.php">Publishers Agreement</a></li>
            </ul>
        </div>
        <!-- end footer -->
    </div>
    <!--end wrapper-->
</body>
</html>

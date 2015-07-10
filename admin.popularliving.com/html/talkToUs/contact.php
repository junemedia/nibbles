<?php

include("../includes/paths.php");

function send_mail($emailaddress, $fromaddress, $emailsubject, $body, $attachments=false, $headers = '')
{
  $eol="\r\n";
  $mime_boundary=md5(time());
 
  # Common Headers
  $headers .= "From: ".$fromaddress.$eol;

  # Boundry for marking the split & Multitype Headers
  $headers .= 'MIME-Version: 1.0'.$eol;
  $headers .= "Content-Type: multipart/related; boundary=\"".$mime_boundary."\"".$eol;

  $msg = "";     
 
  $msg .= "Content-Type: multipart/alternative".$eol;
  # HTML Version  
  $msg .= "--".$mime_boundary.$eol;  
  $msg .= "Content-Type: text/plain; charset=iso-8859-1".$eol;  
  $msg .= "Content-Transfer-Encoding: 8bit".$eol;  
  $msg .= $body.$eol.$eol;

  if ($attachments !== false)
  {

   for($i=0; $i < count($attachments); $i++)
   {
     if (is_file($attachments[$i]["file"]))
     { 
       # File for Attachment
       $file_name = substr($attachments[$i]["file"], (strrpos($attachments[$i]["file"], "/")+1));
      
       $handle=fopen($attachments[$i]["file"], 'rb');
       $f_contents=fread($handle, filesize($attachments[$i]["file"]));
       $f_contents=chunk_split(base64_encode($f_contents));    //Encode The Data For Transition using base64_encode();
       fclose($handle);
      
       # Attachment
       $msg .= "--".$mime_boundary.$eol;
       $msg .= "Content-Type: ".$attachments[$i]["content_type"]."; name=\"".$file_name."\"".$eol;
       $msg .= "Content-Transfer-Encoding: base64".$eol;
       $msg .= "Content-Disposition: attachment; filename=\"".$file_name."\"".$eol.$eol; // !! This line needs TWO end of lines !! IMPORTANT !!
       $msg .= $f_contents.$eol.$eol;
      
     }
   }
  }

  # Finished
  $msg .= "--".$mime_boundary."--".$eol.$eol;  // finish with two eol's for better security. see Injection.
  
  # SEND THE EMAIL
  ini_set(sendmail_from,$fromaddress);  // the INI lines are to force the From Address to be used !
  mail($emailaddress, $emailsubject, $msg, $headers);
  ini_restore(sendmail_from);
  //echo "mail send";
}



$imagePath = "images/";

$pageTitle = "Talk To Us";

// get contact form details

$formQuery = "SELECT *
			  FROM   edContactForms
			  WHERE  shortName = '$f'";
$formResult = mysql_query($formQuery);
echo dbError();
while ( $formRow = mysql_fetch_object($formResult)) {
	$formId = $formRow->id;
	$shortName = $formRow->shortName;
	$formName = $formRow->formName;
	$formHeading = $formRow->formHeading;
	$reqGraphic = $formRow->reqGraphic;
	$contactEmail = $formRow->contactEmail;
}

if($f == 'rflrecipe') {

	$r4lContest = "<p align=center><table border=\"0\" width=75%>
	<tbody>
		<tr>
			<td></td>
			<td><span style=\"font-size: 11pt\">
			Dust off your camera and start snapping pictures of your favorite recipes. Every submission will receive a <b>$5 Amazon gift card</b>.
			You have recipes, we need them to share with our readers!
			In addition, every week we will award a <b>$50 gift certificate to Williams-Sonoma</b> for the best recipe submitted that week.  Plus the
			winner will be featured on the Recipe4Living homepage for a full week! New winner chosen every Friday!
			<br><br><b>Bonus</b>:  
			All recipes and photo submitted will be featured in a special photo presentation and will ALSO receive a free sample of Jelly Belly Candies.
			</span></td>
		</tr>
	</tbody>
	</table><p>";
	$r4lContest = '';

	$r4lHeadImage = '<img src="titleLogo.gif" border=0> ';
	$r4lWithImageURL = "http://www.popularliving.com/p/onetime.php";
	$r4lWithoutImageURL = "http://www.popularliving.com/p/onetime.php";
} else {
	$r4lContest = "";
	$r4lHeadImage = '';
}
if($f == 'rflcontact') {
	$r4lHeadImage = '<img src="titleLogo.gif" border=0> ';
}

if ($save) {
	if (trim($_COOKIE['security_code']) == $_POST['security_code'] && !empty($_COOKIE['security_code'])) {
		// haha goood.
	} else {
		$message = "Sorry, you have provided an invalid security code.";
		$keepValues = true;
	}
	// delete cookie that has security code.
	setcookie("security_code", $_COOKIE['security_code'], time()-3600, '/', '', 0);

	foreach ($_GET as $key => $value) { $_GET[$key] = ""; }
	$bad_strings = array("Content-Type:", "MIME-Version:","Content-Transfer-Encoding:", "bcc:", "cc:","href");
	foreach($_POST as $k => $v) {
       	foreach($bad_strings as $v2) {
           	if (stristr($v, $v2)) {
				print "<h2>Access Denied - Invalid Form Content</h2>";
				exit;
           	}
       	}
	}
	unset($k, $v, $v2, $bad_strings);

	// handle action variable (alpha and space)
	$action = preg_replace ("/[^A-Za-z\s]/","",trim($_POST['action']));

	// handle message (alpha, numeric, space)
	$request = preg_replace("/[^A-Za-z,\s.0-9]/","",trim($request));

	$firstname = preg_replace("/[^A-Za-z,\s.0-9]/","",trim($firstname));
	$lastname = preg_replace("/[^A-Za-z,\s.0-9]/","",trim($lastname));
	$city = preg_replace("/[^A-Za-z,\s.0-9]/","",trim($city));
	$state = preg_replace("/[^A-Za-z,\s.0-9]/","",trim($state));

	// handle parts of phone number (numeric)
	$area = preg_replace ("/[^0-9]/","",trim($_POST['area']));
	$prefix = preg_replace ("/[^0-9]/","",trim($_POST['prefix']));
	$last4 = preg_replace ("/[^0-9]/","",trim($_POST['last4']));

	// since local variables have now been set, null all POST variables
	foreach ($_POST as $key => $value) { $_POST[$key] = ""; }

	if (!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $email))  {
		$message = "Please enter valid eMail address...";
		$keepValues = true;
	} else {
		// Generate a boundary string
		$semiRand = md5(time());
		$mimeBoundry = "==Multipart_Boundry_x{$semiRand}x";
		
		// insert into nlUserFeedback table
		$insertQuery = "INSERT INTO nlUserFeedback(formId, email, first, last, city, state,
								includeName, request, dateSubmitted)
					VALUES('$formId', '".mysql_real_escape_string($email)."', '".mysql_real_escape_string($firstname)."', '".mysql_real_escape_string($lastname)."', '".mysql_real_escape_string($city)."', '".mysql_real_escape_string($state)."', '".mysql_real_escape_string($publishname)."', \"".mysql_real_escape_string($request)."\", now())";
		$insertResult = mysql_query($insertQuery);
		
		$sCheckQuery = "SELECT id
		   FROM   nlUserFeedback
		   WHERE  formId = '$formId'
		   AND email = '$email'
		   AND first = '$firstname'
		   AND last = '$lastname'
		   AND city = '$city'
		   AND state = '$state'
		   AND includeName = '$publishname'
		   AND request = '$request'"; 
		$rCheckResult = dbQuery($sCheckQuery);
		$sRow = dbFetchObject($rCheckResult);

		$id = $sRow->id;
		$attachments = Array();
		if ($_FILES['image']['tmp_name'] && $_FILES['image']['tmp_name'] != "none") {
			if ($_FILES['image']['size'] > 100000) {
				$message = "File size should be no more than 100K...";
				$keepValues = true;
			} else if ($_FILES['image']['type'] != 'image/gif' && $_FILES['image']['type'] != 'image/jpg' && $_FILES['image']['type'] != 'image/png' && $_FILES['image']['type'] != 'image/jpeg' ) {
				$message = "Only .gif, .jpg and .png images are allowed...";
				$keepValues = true;
			} else {
				$uploadedImage = $_FILES['image']['tmp_name'];
				$ar = explode(".",$_FILES['image']['name']);
				$i = count($ar) - 1;
				$thisext = $ar[$i];
				$imageFile = "image_".$id.".$thisext";
				$imagePath  = $imagePath.$imageFile;
				move_uploaded_file( $uploadedImage, $imagePath);
				array_push($attachments, Array("file"=>$imagePath, "content_type"=>$_FILES['image']['type']));
				// update image file names
				$updateQuery = "UPDATE nlUserFeedback
									SET    image = '$imageFile'
									WHERE  id = '$id'";
				$updateResult = mysql_query($updateQuery);
			}
		}
		
		if ($keepValues != true) {
			$ccTo = '';
			//if (strstr($contactEmail,",")) {
			//	$emailTo = substr($contactEmail,0,strpos($contactEmail,","));
			//	$ccTo = substr($contactEmail,strpos($contactEmail,",")+1);
			//} else {
				$emailTo = $contactEmail;
			//}
			
			// update stat counts
			$selectQuery = "SELECT id
					FROM   edContactFormStats
					WHERE  contactFormID = '$formId'
					AND    dateSubmitted = CURRENT_DATE";
			$selectResult = mysql_query($selectQuery);
			
			if (mysql_num_rows($selectResult)>0) {
				while($selectRow = mysql_fetch_object($selectResult)) {
					$contactFormStatsId = $selectRow->id;
				}
				$editQuery = "UPDATE edContactFormStats
					  SET    counts = counts+1
					  WHERE  id = '$contactFormStatsId'
					  AND    dateSubmitted = CURRENT_DATE";
				$editResult = mysql_query($editQuery);
				
			} else {
				$insertQuery = "INSERT INTO edContactFormStats(contactFormId, dateSubmitted, counts)
						VALUES('$formId', CURRENT_DATE, '1')";
				$insertResult = mysql_query($insertQuery);
				
			}

			$mailContent = "
Email : $email\r\n
First Name : $firstname\r\n
Last Name : $lastname\r\n
City : $city\r\n
State : $state\r\n
Publish Name : $publishname\r\n
R4L Daily Newsletter: $sSubscribeToR4L\r\n
Newsletter : $formName\r\n";
			
if ($selectedImage !='') {
$mailContent .= "
Photo Gallery Image: $selectedImage\r\n";
}

			$request = stripslashes($request);
			$mailContent .= "\r\nTip : ". stripslashes($request)."\r\n";
			$headers .= "From:$email\r\n";
			
			if ($ccTo != '') {
				$headers .= "cc: $ccTo";
			}
			send_mail($emailTo,"$email","$formName",$mailContent,$attachments,$headers);
		}

		if ($sSubscribeToR4L == 'yes') {
			$rCheckResult = dbQuery("SELECT * FROM nibbles.joinEmailPending WHERE email = \"$email\" AND joinListId = '224'");
			$rCheckResult2 = dbQuery("SELECT * FROM nibbles.joinEmailActive WHERE email = \"$email\" AND joinListId = '224'");
			if ( dbNumRows($rCheckResult) == 0 && dbNumRows($rCheckResult2) == 0) {
				$sPasswd = substr(md5(uniqid(rand(), true)),0,5);
				$rPendingInsertResult = dbQuery("INSERT INTO nibbles.joinEmailPending(email, joinListId, sourceCode, dateTimeAdded, passwd)
							VALUES(\"$email\", '224', 'rflrecipe', NOW(), \"$sPasswd\")");
		
				$rDelInactiveResult = dbQuery("DELETE FROM nibbles.joinEmailInactive WHERE email=\"$email\" AND joinListId = '224' LIMIT 1");
		
				$rSubInsertResult = dbQuery("INSERT INTO nibbles.joinEmailSub(email, joinListId, sourceCode, remoteIp, dateTimeAdded)
								VALUES(\"$email\", \"224\", 'rflrecipe', '".trim($_SERVER['REMOTE_ADDR'])."', NOW())");
			}
		
			$rPasswdResult = dbQuery("SELECT * FROM nibbles.joinEmailPending WHERE email = \"$email\" LIMIT 0,1");
			while ($oRow1 = dbFetchObject($rPasswdResult)) {
				$rListEmailResult =  dbQuery("SELECT * FROM nibbles.emailContents WHERE system='join' AND	emailPurpose='requestConfirm'");
				while ($oRow = dbFetchObject($rListEmailResult)) {
					$sMsg = $oRow->emailBody;
					$sMsg = str_replace('[EMAIL]', $email, $sMsg);
					$sMsg = str_replace('[REMOTE_IP]', $_SERVER['REMOTE_ADDR'], $sMsg);
					$sMsg = str_replace('[DATE_TIME_SUB]', $oRow1->dateTimeAdded, $sMsg);
					$sMsg = str_replace('[MMDDYY]', date('m').date('d').date('y'), $sMsg);
					$sMsg = str_replace('[SOURCE_CODE]', 'rflrecipe', $sMsg);
					$sMsg = str_replace('[CONFIRM_URL]', "http://www.popularliving.com/j/c.php?e=$email&p=$oRow1->passwd&src=rflrecipe", $sMsg);
					mail($email, $oRow->emailSub, $sMsg, "From: $oRow->emailFrom\r\nX-Mailer: MyFree.com\r\nContent-Type: text/plain; charset=iso-8859-1\r\n");
				}
			}
		}
	}
	if ($keepValues != true) {
		$url = "Location:http://www.popularliving.com/p/onetime.php?e=$email&f=$firstname&l=$lastname&c=$city&s=$state";
		if ($f == "rflrecipe") {
			$url = "Location:http://www.popularliving.com/p/jellybelly_smr4l.php?e=$email&f=$firstname&l=$lastname&c=$city&s=$state";
		} elseif ($f == "beautyrecipes") {
			$url = "Location:http://www.myhealthinsider.com/category/beauty/";
		} elseif ($f == "naturalhealthre") {
			$url = "Location:http://www.myhealthinsider.com/category/healthy-living/natural-health/";
		} elseif ($f == "healthtip") {
			$url = "Location:http://www.myhealthinsider.com/category/healthy-living/";
		} elseif ($f == "diettip") {
			$url = "Location:http://www.myhealthinsider.com/category/diet-and-exercise/";
		} elseif ($f == "healthrecipes") {
			$url = "Location:http://www.myhealthinsider.com/category/recipes/";
		}
		header($url);
	}
}


if ($reqGraphic) {
	$sPhotoGalary = "<br>or<br><a style=\"cursor: pointer;\" onclick=\"JavaScript:void(window.open('selectImg.php','','width=600, height=600, scrollbars=yes, resizable=yes'));\">
					<font color=blue><u>Select From <br>Photo Gallery</u></font></a>";
	if ($f == 'hcv') {
		$sPhotoGalary = '';
	}
	$uploadGraphic = "<TR>
						<TD valign=top><BR><font face=Arial>Attach Picture 
						$sPhotoGalary
						</font></TD>
						<TD valign=top><BR><p align=left><font face=Arial><INPUT name=image type=file><BR>Maximum File Size - 100KB, .jpg, .gif, or .png format only</font> 
						<input type=hidden name=MAX_FILE_SIZE value='100000'></TD></TR>";
}


$hidden = "<input type=hidden name=f value='$f'><input type='hidden' name='selectedImage'>";
?>
<html>

<head>
<title>Use this form to send us your <?php echo $formHeading;?></title>
<meta name="generator" content="Namo WebEditor v5.0">
<script>
function okayToSubmit() {
	if (document.form1.security_code.value.length != 6) {
		alert('Please enter valid security code.');
		return false;
	}
	<?php if ($f == 'rflrecipe') { ?>
		if (document.form1.agree.checked) {
			return true;
		} else {
			alert('You must agree to Terms and Conditions.');
			return false;
		}
	<?php } else { ?>
		return true;
	<?php } ?>
}
</script>
</head>

<body bgcolor="white" text="black" link="blue" vlink="purple" alink="red">
<p align=center><?php echo $r4lHeadImage;?></p>
<H3 align=center><FONT face=verdana><?php echo $formHeading;?></FONT></H3>
<FORM action=contact.php name='form1' method=post enctype="multipart/form-data" onsubmit="if(okayToSubmit()){return true;}else{return false;}">
<?php echo $hidden;?>
<?php echo $r4lContest;?>
<TABLE width="479" align=center border=0>
<TBODY>
<tr><td colspan=2 align=center><font face="Arial" color=#FF0000><?php echo $message;?></font></td></tr>
<TR>
<TD width="253"><font face="Arial">Email Address:</font></TD>
<TD width="216">
                <p align="left"><font face="Arial"><INPUT name=email value='<?php echo $email;?>'></font> </TD></TR>
<TR>
<TD width="253"><font face="Arial">First Name:</font></TD>
<TD width="216">
                <p align="left"><font face="Arial"><INPUT name=firstname value='<?php echo $firstname;?>'></font> </TD></TR>
<TR>
<TD width="253"><font face="Arial">Last Name:</font></TD>
<TD width="216">
                <p align="left"><font face="Arial"><INPUT name=lastname value='<?php echo $lastname;?>'></font> </TD></TR>
<tR>
<TD width="253"><font face="Arial">City</font></TD>
<TD width="216">
                <p align="left"><font face="Arial"><INPUT name=city value='<?php echo $city;?>'></font> </TD></TR>
<TR>
<TD width="253"><font face="Arial">State</font></TD>
<TD width="216">
                <p align="left"><font face="Arial"><INPUT name=state value='<?php echo $state;?>'></font> </TD></TR>
<TR>
<TD width="253">
                <p><font face="Arial">Include your name with this posting?</font></TD>
<TD width="216">
                <p align="center"><font face="Arial"><INPUT type=radio value=yes name=publishname>yes <INPUT type=radio value=no 
name=publishname>no</font> </TD></TR>

                
                
<?php if ($f != 'hcv') { ?>                     
<TR>
<TD width="253"><br>
                <p><font face="Arial">Subscribe to the Recipe4Living Daily Newsletter featuring recipes from
world-renowned chef Wolfgang Puck, cooking tips and food pairings to help plan your meals.</font></TD>
<TD width="216"><br><p align="center"><font face="Arial">
                <INPUT type=radio value='yes' name=sSubscribeToR4L checked>yes 
                <INPUT type=radio value='no' name=sSubscribeToR4L>no</font> </TD></TR>
<?php } ?>                
                
                
         
<?php echo $uploadGraphic;?>
<TR>
<TD align=middle colSpan=2 width="473"><font face="Arial">&nbsp;</font> </TD></TR>
<TR>
<TD align=middle colSpan=2 width="473"><font face="Arial">Enter your text here:</font> </TD></TR>
<TR>
<TD align=middle colSpan=2 width="473"><TEXTAREA name=request rows=10 cols=60><?php echo $request;?></TEXTAREA> 
</TD></TR>

<tr><td align=middle colspan=2 width="473">
	Security Code: <input name="security_code" type="text" maxlength="6" size="6"><img src="../libs/captcha.php">
</TD></TR>



<TR>
<TD align=middle colSpan=2 width="473">&nbsp; </TD></TR>
<TR>


<?php if ($f == 'rflrecipe') { ?>
<TR>
<TD align="left" colSpan=2 width="473">
<input name="agree" type="checkbox" value="Y">
By submitting to Recipe4Living, you agree to our 
<a href='JavaScript:void(window.open("http://www.recipe4living.com/content/view/10954/336/","addiInfo","height=450, width=700, scrollbars=yes, resizable=yes, status=yes"));'>
Terms and Conditions</a> and the 
<a href='JavaScript:void(window.open("http://www.recipe4living.com/content/view/11276/","addiInfo","height=450, width=700, scrollbars=yes, resizable=yes, status=yes"));'>
Photo Contest Rules</a>
</TD></TR>
<TR>
<?php } ?>



<TR>
<TD align=middle colSpan=2 width="473">&nbsp; </TD></TR>
<TR>
<TD align=middle colSpan=2 width="473"><INPUT name=save type=submit value="Click Here To Submit"> 
</TD></TR></TBODY></TABLE></FORM>
</body>

</html>

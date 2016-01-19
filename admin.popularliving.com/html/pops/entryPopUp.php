<?php


include("../includes/paths.php");

session_start();

if ($sEmail !='') {
	$_SESSION['sSesEmail'] = $sEmail;
}

if ($_SESSION['sSesSourceCode'] == '' && $src !='') {
	$_SESSION['sSesSourceCode'] = $sSourceCode;
}

?>

<html>
<head><script language="javascript">
<!--  
function ValidateInput() {
if (document.signup.sEmail.value.length == 0) {
     alert("Please enter an email address.")
         return
}

if (test(document.signup.sEmail.value) == false) {
     alert("Please use a valid email address.")
         return
}
document.signup.submit()
}

function test(src)
{ var emailReg = "^[\\w-$_\.]*[\\w-$_\.]\@[\\w]\.+[\\w]+[\\w]$";
var regex = new RegExp(emailReg);
return regex.test(src); }
// -->

</script>
<title>Welcome To MyFree.com</title>
</head>
<body style="background-color: #ffffff" topmargin="0" leftmargin="0" bgcolor="#FFFFFF" text="#000000" link="#0000FF" vlink="#330033" alink="#006600"><font face="Verdana, Arial, Helvetica">
<div align="center"><center>
</font>
<table border="0" width="375" bgcolor="#ffffff" height="380" marginheight="5" marginwidth="5">
 <tr>
    <td align="center">
<font face="Verdana, Arial, Helvetica"> <p align="center">

<!--  <img border="0" src="images/popup.gif"> -->
<IMG SRC="images/popup.gif" USEMAP="#popup" BORDER=0>
<MAP NAME="popup">
  <AREA SHAPE=RECT COORDS="254,120,355,143" HREF="<?php echo $sGblSiteRoot;?>/j/index.php"  ALT=""  TARGET="_blank"  OnMouseOut="window.status=''; return true"  OnMouseOver="window.status=''; return true">
  <AREA SHAPE=default TARGET="_blank" HREF="<?php echo $sGblSiteRoot;?>/j/index.php">
</MAP>
<br>
    </center><font color="#000080" face="Verdana" size="2">
    <div align="center">
    <form name="signup" method="POST" action="entryPopThanks.php" align="center">
      </font><table
      border="0" width="320" height="182">
        <tr>
          <td valign="top" height="31" colspan="2" width="284"><font face="Verdana, Arial, Helvetica"> <p align="left"><b><font size="2"
          color="#000080" face="Verdana">Your Email:&nbsp; <input type="text" name="sEmail" size="26"></font></b></p>
          </font></td>
        </tr>
        <tr>
          <td valign="top" height="1" width="9" align="left"><font face="Verdana, Arial, Helvetica"> <p align="left"></p>
          </font></td>
          <td valign="top" height="1" width="269"><font face="Verdana, Arial, Helvetica"> 
			<b><font color="#000080" size="2" face="Verdana">
			<input type="hidden" name="sourceCode" value="myep"><input type='checkbox' name='aJoinListId[]' value='161'>&nbsp; Free Stuff Daily</font></b>
          </font></td>
        </tr>
        <tr>
          <td valign="top" height="16" width="9" align="left"><font face="Verdana, Arial, Helvetica"></font></td>
          <td valign="top" height="16" width="269"><font face="Verdana, Arial, Helvetica"><b><font color="#000080"
          size="2" face="Verdana"><input type='checkbox' name='aJoinListId[]' value='25'>&nbsp; Sweepstakes Mania Daily</font></b>
          </font></td>
        </tr>
        <tr>
          <td valign="top" height="16" width="9" align="left"><font face="Verdana, Arial, Helvetica"></font></td>
          <td valign="top" height="16" width="269"><font face="Verdana, Arial, Helvetica"> <b><font color="#000080"
          size="2" face="Verdana"><input type='checkbox' name='aJoinListId[]' value='166'>&nbsp; Jokes, Jokes, Jokes Daily</font></b></font></td>
        </tr>
        <tr>
          <td valign="top" height="16" width="9" align="left"><font face="Verdana, Arial, Helvetica"></font></td>
          <td valign="top" height="16" width="269"><font face="Verdana, Arial, Helvetica"><b><font color="#000080"
          size="2" face="Verdana"><input type='checkbox' name='aJoinListId[]' value='124'>&nbsp; Home Business Ideas & Tips Weekly</font></b></font></td>
        </tr>
        <tr>
          <td valign="top" height="16" width="9" align="left"><font face="Verdana, Arial, Helvetica"></font></td>
          <td valign="top" height="16" width="269"><font face="Verdana, Arial, Helvetica"><b><font color="#000080"
          size="2" face="Verdana"><input type='checkbox' name='aJoinListId[]' value='185'>&nbsp; RSVP You're Invited Weekly</font></b></font></td>
        </tr>
        <tr>
          <td valign="top" height="16" width="278" align="center" colspan="2"><font face="Verdana, Arial, Helvetica"> 
            <p align="center">
            <input type="button" value="Signup!" onclick="ValidateInput()" name="sSignUp">
          </font></td>
        </tr>
      </table><font face="Verdana, Arial, Helvetica">
    </form>
    </div> 
  </font></td>
  </tr>
</table><font face="Verdana, Arial, Helvetica">
</div>
<p>&nbsp;</p>
</font></body>
</html>

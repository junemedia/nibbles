<?php

include("../includes/paths.php");

session_start();

$sRemoteIp = $_SERVER['REMOTE_ADDR'];

if ($sEmail != '') {
	$_SESSION['sSesEmail'] = $sEmail;
} else {
	$sEmail = $_SESSION['sSesEmail'];
}

if ($src != '' && $_SESSION['sSesSourceCode'] == '') {
	$_SESSION['sSesSourceCode'] = $src;
}

//echo "Dfdf".$_SESSION['sSesSourceCode'];
$sStateQuery = "SELECT *
				FROM   states
				ORDER BY state";
$rStateResult = dbQuery($sStateQuery);
echo dbError();
while ($oStateRow = dbFetchObject($rStateResult)) {
	
	$sStateOptions .= "<option value='$oStateRow->stateId'>$oStateRow->state";
	
}

?>

<html>

<head>
<title>MyFree.com Sweepstakes</title>

</head>

<body bgcolor="#FFFFFF" text="#000000" link="#0000FF" vlink="#330033" alink="#006600">


<div align="center">
<img src="images/pamcam_ani.gif" >
<font face="Verdana, Arial, Helvetica"><h4 align="center">
<font color="#990000">

<!--
We just need a few more pieces of information from you to&nbsp;<br>
complete your entry into the Prize-A-Month Giveaway:&nbsp; 
-->

We need a bit more information to complete your entry.

</font></h4>
</div>



<form action="entrySubmit.php" name="signup" method="post">
<input type=hidden name=confirm value='<?php echo $confirm;?>'>
<table border="0" cellpadding="0" cellspacing="0" align="center" width="450" height="148">

<tr><td height="25" bgcolor="FFE8C8">
<font face="Verdana, Arial, Helvetica">Email:</font>
</td>
      
<td align="left" height="25" bgcolor="FFE8C8">
<input type="text" name="sEmail" size="25" value='<?php echo $sEmail;?>'>

</td></tr>

<tr><td height="25" bgcolor="F0FFF0"><font face="Verdana, Arial, Helvetica">First Name:</font></td>
      <td align="left" height="25" bgcolor="F0FFF0"><font face="Verdana, Arial, Helvetica"><input type="text" name="sFirst" size="25"></font>
      		</td>
    </tr>
    <tr>
      <td height="25" bgcolor="FFE8C8"><!--mstheme--><font face="Verdana, Arial, Helvetica">Last Name:</font></td>
      <td align="left" height="25" bgcolor="FFE8C8"><input type="text" name="sLast" size="25">
      </td>
    </tr>
    <tr>
      <td height="25" bgcolor="F0FFF0"><font face="Verdana, Arial, Helvetica">Address:</font></td>
      <td align="left" height="25" bgcolor="F0FFF0"><input type="text" name="sAddress" size="25">
      </td>
    </tr>
    <tr>
      <td height="25" bgcolor="FFE8C8"><font face="Verdana, Arial, Helvetica">City:</font></td>
      <td align="left" height="25" bgcolor="FFE8C8"><input type="text" name="sCity" size="25">
      </td>
    </tr>
    <tr>
      <td height="23" bgcolor="F0FFF0"><!--mstheme--><font face="Verdana, Arial, Helvetica">State:</font></td>
      <td align="left" height="23" bgcolor="F0FFF0"><select name=sState>
         <?php echo $sStateOptions;?>
          </select></td>
    </tr>
    <tr>
      <td height="25" bgcolor="FFE8C8"><font face="Verdana, Arial, Helvetica">Zip Code:<br>
      
       </td>
    
      <td align="left" height="25" valign="top" bgcolor="FFE8C8"><input type="text" name="sZip" size="10">
      <font size=-2>5 digits only</font>
      
	</td>
    </tr>        
    <tr>
      <td height="25" bgcolor="F0FFF0">
<font face="Verdana, Arial, Helvetica">Phone Number:</font></td>
      <td align="left" height="25" bgcolor="F0FFF0"><input type="text" name="sPhone" size="10">
	<font size=-2>Format: xxx-xxx-xxxx</font>
</td></tr>

<tr><td height="25" bgcolor="FFE8C8">
<font face="Verdana, Arial,Helvetica">Age:</font>
</td>
      <td align="left" height="25" bgcolor="FFE8C8"><font face="Verdana, Arial, Helvetica" size="1">
        <p align="left"><input type="text" name="iAge" size="4">&nbsp;Must be at least 18 years old.
</font>
</td></tr>

<tr>
      <td height="25" bgcolor="F0FFF0"><font face="Verdana, Arial, Helvetica">Sex:</font></td>


      <td align="left" height="25" bgcolor="F0FFF0">

<font face="Verdana,Arial, Helvetica">
        <select size="1" name="sSex">
          <option value="F">Female</option>
          <option value="M">Male</option>
        </select></font>
</td></tr>
<tr>
      <td valign="top"  height="25" bgcolor="FFE8C8" colspan=2><font face="Verdana, Arial, Helvetica" size="1">
	<br>Join the "MyFree Best Deals Alert". FREE Membership, FREE Newsletter, FREE PRIZE-A-MONTH GIVEAWAY Entry! Don't miss out on all the special deals, new products and other third-party offers we know you'll love! Free Bonuses: $125+ Special Instant Shopping Spree Discount Package + Special Report "Free Samples from National Brands You Trust!" Recent featured free samples include Advil, Nesquik, Tide - and more! 
	<p><input CHECKED name="iJoinListId" type="radio" value="215"> Yes!
        I want to receive this information.<br>
        <input name="iJoinListId" type="radio" value="215"> No, please
        exclude me.	</font></td>
    </tr>
</table>

<font face="Verdana, Arial, Helvetica">
  
<p align="center"><input type="submit" value="Click Here To Complete Your Entry!" name="sSave"></p>
    <p align="center">Note: You must be a U.S. resident to enter<br>
      online.  See rules for alternate method of entry.
</p>

<br><br><br><br>

<p align="center"><font size="1" face="arial"><a href="<?php echo $sGblSiteRoot;?>/sweepsrules.php" target="_blank">Official Rules</a> - <a href="http://www.popularliving.com/privacy.php" target="_blank">Privacy Policy</a></font></p>
</font></font></center>
</form>
<font face="Verdana, Arial, Helvetica">
</font></body>
</html>

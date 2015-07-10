<?php
include("../config.php");

if ($action=="process") {

	if (!$UPSNumber) {

		echo "<script>alert(\"Please enter a tracking number\")
		history.back()</script>";
		exit;

	}

	$sql = "UPDATE orders SET orUPSTrackingNumber = '" . dbprep($UPSNumber) . "' WHERE orID= $orID";
	$result = mysql_query($sql);
	if (!$result) 
		echo mysql_error();

	$message = str_replace("#UPS#",$UPSNumber,$message);
	mail($cuEmail,"Your Order From $sitename Has Been Shipped",$message,"From: $webmasterEmail");

	header("location: index.php");

}


?>

<html>
<head>

<title><?php echo $sitename; ?> - Order Details</title>

	<LINK REL="StyleSheet" HREF="<?php echo $siteRoot;?>/style.css" TYPE="text/css">

</head>



<?php

//header

include("$includePath/header_adm.php");

//left menu

include("$includePath/leftmenu_adm.php");
?>

</td>

<td  width="10" valign="top">

&nbsp;&nbsp;&nbsp;&nbsp;
</td>
<td width="100%" valign="top" align="center">
<br>

<center>
<a href="<?php echo $adminPath;?>/orders/">Back Orders Admin</a> -
<a href="<?php echo $adminPath;?>/">Back to Admin Menu</a>


<p>
<table bgcolor="#999999" cellpadding="3" cellspacing="1" border="0" width="600">
   <tr align="left" bgcolor="#3366CC">
     <th><font color="#eeeeee">Order <?php echo $orID; ?> was marked as processed.</font></th>
   </tr>

   <tr bgcolor="#eeeeee" >
     <td>

Use this form to email a UPS tracking number to the customer, or <a href="/admin/orders/">click here</a> to 
go back to the orders list.
<form action="<?php echo $_SERVER[PHP_SELF]; ?>" method="post">
<input type="hidden" name="action" value="process">
<input type="hidden" name="orID" value="<?php echo $orID; ?>">
<input type="hidden" name="cuEmail" value="<?php echo $cuEmail; ?>">

<p>Customer Email:  <?php echo $cuEmail; ?>

<p>Enter Tracking Number: <input type="text" name="UPSNumber" value="<?php echo $orUPSTrackingNumber; ?>">

<p>The tracking number you enter above will automatically replace #UPS# in the 
message below:<br><br>
<textarea name="message" rows="7" cols="40"><?php echo getVar("UPSMessage"); ?></textarea>

<p><input type="submit" value="Send The Email">
</form>
<br><br><br><br><br>

   </td>
  </tr>
</table>
</div>
</body>
</html>


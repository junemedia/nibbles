<?php

if (isset($_POST['t'])){
	$t = $_POST['t'];
}
if (isset($_GET['t'])) {
        $t = $_GET['t'];
}
$link = "eBook.php?t=$t&s=$s";

?>

<html>

<head>

<style>

TD.mediumBold {

	FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; FONT-SIZE: 10pt; COLOR: #000000; FONT-WEIGHT: BOLD;

}



A.medium:link {

	 FONT-SIZE: 10pt; COLOR: #0000FF; FONT-FAMILY: Verdana, Arial, Helvetica, "Sans Serif"; 

}

A.medium:visited {

	 FONT-SIZE: 10pt; COLOR: #0000FF; FONT-FAMILY: Verdana, Arial, Helvetica, "Sans Serif"; 

}

A.medium:hover {

	 FONT-SIZE: 10pt; COLOR: #0000FF; FONT-FAMILY: Verdana, Arial, Helvetica, "Sans Serif"; 

}

A.medium:active {

	 FONT-SIZE: 10pt; COLOR: #0000FF; FONT-FAMILY: Verdana, Arial, Helvetica, "Sans Serif"; 

}

</style>

</head>

<body>

<table width=100% border=0>





<tr>

<td align=center class=mediumBold>Here Is<br> Your Free eBook!<br><br>To Download It,<BR> <a href='<?php echo $link;?>' class=medium>Click Here</a>

</td>

</tr>





<tr><td valign=top align=center>



<?php 



if ($t === "getpaid"){



echo '<img src="GetPaidToShop.jpg">';



}



if ($t === "freestuff"){



echo '<img src="freestuff.jpg">';



}



if ($t === "workathome"){



	echo '<img src="workathome2005.jpg">' ;



}


if ($t === "freestuffforkids"){



	echo '<img src="rh-kids.jpg">' ;



}

if ($t === "freestuffcraftbook"){



	echo '<img src="rh-crft.jpg">' ;



}




?>



</td>



</tr>



<tr><td>&nbsp;</td></tr>

<tr><td align=center colspan=2><input type=button name=close value="Close Window" onClick="self.close();"></td></tr>


</table>

</body>

</html>


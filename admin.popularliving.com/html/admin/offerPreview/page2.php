<?php

include("../includes/paths.php");

include("$sGblLibsPath/validationFunctions.php");


// start session before including otPageInclude.php

session_start();


include("$sGblIncludePath/otPageInclude.php");

include("offerPage2.php");

/*
if (!isset($_SESSION["iSesPageId"])) {

	echo "Session expired";

} else {

	

*/

// place the javaScript of offers in page2 template which is prepared in otPage2Offers.php

$sPage2JavaScript = "<script language=JavaScript>".

					$_SESSION['sSesJavaScriptVars'] .

					 "</script>".$sPage2JavaScript;
	
?>
<html>
<head>
<SCRIPT LANGUAGE=JavaScript SRC="http://www.popularliving.com/libs/javaScriptFunctions.js" TYPE=text/javascript></script>
<?php echo $sPage2JavaScript;?>

<title>Offer Preview on page1 and page2</title>
<LINK rel="stylesheet" href="http://www.popularliving.com/pageStyles.css" type="text/css">
</head>
<body bgcolor=#FFFFFF>
<center>
<img src='/p/testOfferPreview/images/header_172.gif'>
</center>
<BR>

<table class=table760 align=center>
<tr><td class=mainErrorMessage>We need a few more pieces of information to process your requests.</td></tr>
<tr><td class=message><ol><?php echo $sMessage;?></ol></td></tr>
</table>
<form name=form1 action="http://www.popularliving.com/otPage2Submit.php" method=POST onSubmit="return page2Validation();">

<table class=table760 align=center cellpadding=0 cellspacing=0>
<?php echo $sOffersOnPage2;?>
</table>
<BR>
<center>
<input type=submit name=sSubmit value="Submit Your Request!">
</form>
</center>
</body>
</html>

 <?php
//}

?>
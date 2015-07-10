<!--

/**********

This is the Frame with redirect content

**********/
-->
<html>

<head>
<title><?php print "$src"; ?></title>
<script language="JavaScript">

<!--
function MM_openBrWindow(theURL,winName,features) { //v2.0
  window.open(theURL,winName,features);
}
//-->

<?php

include("../includes/paths.php");

// Check first if $srs is a sourceCode, otherwise check if it's offerCode
$popupQuery = "SELECT popOption, popupUrl, vSize, hSize, bustFrames, displayInFrame
			   FROM   campaigns
			   WHERE  sourceCode = '$src'";

$result = dbQuery($popupQuery);
if (dbNumRows($result) >0 ) {
	// It's sourceCode
	$redirect = "true";
	while($row = dbFetchObject($result)) {
		$popOption = $row->popOption;
		$popupUrl = $row->popupUrl;
		$vSize = $row->vSize;
		$hSize = $row->hSize;
		$iBustFrames = $row->bustFrames;
		$sDisplayInFrame = $row->displayInFrame;
	}
}


if (dbNumRows($result) <= 0 ) {
	// if it's offerCode
	$tempQuery = "SELECT popOption, popupId
				  FROM   edOffers
				  WHERE  offerCode = '$src'";
	$tempResult = dbQuery($tempQuery);
	while ($tempRow= dbFetchObject($tempResult)) {
		$popOption = $tempRow->popOption;
		$popupIdList = $tempRow->popupId;
		if ($popOption != '') {
			$popQuery = "SELECT *
				 	 FROM   edOfferPopUps
				 	 WHERE	id IN (".$popupIdList.")";
			$popResult = dbQuery($popQuery);
			$i = 0;
			while ($popRow = dbFetchObject($popResult)) {
				$popupUrlArray[$i] = $popRow->url;
				$popupHSizeArray[$i] = $popRow->hSize;
				$popupVSizeArray[$i] = $popRow->vSize;

				$i++;
			}
		}
	}
}


$popupContent = "<script language=JavaScript>";
//echo "url ".$popupUrl;
if ($redirect == "true" && $popOption != '') {
	/*if ($popOption == "popup")
		{
			$temp = $newUrl;
			$newUrl = $popupUrl;
			$popupUrl = $temp;
		}*/
	$popUpContent .= "popupWindow = window.open('".$popupUrl."','','width=".$hSize.",height=".$vSize.", scrollbars=yes, resizable=yes');";
	if ($popOption =="popup") {
		$popUpContent .= "popupWindow.focus();
			//				//window.blur();";
	} else {
		$popUpContent .= "popupWindow.blur();\n
							//window.focus();";
	}
}

if ($popupUrlArray != '') {	
	for ($i=0; $i<count($popupUrlArray); $i++) {
		$popupUrl = $popupUrlArray[$i];
		$hSize = $popupHSizeArray[$i];
		$vSize = $popupVSizeArray[$i];
		
		$popUpContent .= "popupWindow = window.open('".$popupUrl."','','width=".$hSize.",height=".$vSize.", scrollbars=yes, resizable=yes');";
		if ($popOption =="popup") {
		 $popUpContent .= "popupWindow.focus();
		 				  // window.blur();";
		} else {
		$popUpContent .= "popupWindow.blur();
						//window.focus()\n";
		}
	}	
}

//$popUpContent .= "</script>";


if ($iBustFrames && $sDisplayInFrame == '') {
	
	
	$sBustFramesCode = "<script language='JavaScript' type='text/javascript'>

		if (parent.frames.length > 0) 
		{
    		parent.location.href = self.document.location;
		} 

		</script>";
}



echo $popUpContent."$popupUrl";

?>
</script>
<?php echo $sBustFramesCode; 

?>
<!-- hide this from tired old Browsers  if (window.location != top.location)
{top.location.href=window.location} // -->

</head>

<body>
<?php
//if () 
echo "<meta http-equiv=\"refresh\" content=\"1;URL='$newUrl'\">";

?>

</body>

</html>

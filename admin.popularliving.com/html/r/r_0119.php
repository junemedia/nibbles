<?php

/**********

This is script to record Redirects Entries

**********/

include("../includes/paths.php");
/*if ($s == '') {
$s = $src;
}*/
// set variables

//set default url, if url not found
$url = $sGblDefaultUrl;

// select nibbles database
//mysql_select_db($nibblesDBName);

// Get the url of this sourcecode
$query = "SELECT url, displayInFrame
		  FROM   campaigns
		  WHERE  sourceCode = '$src'";
$result = dbQuery($query);
while($row = dbFetchObject($result)) {
	$url = $row->url;
	$displayInFrame = $row->displayInFrame;
	
	$redirect = true;
	// get frame content here to get width or height attribute from body tag
	// to use it in frameset  tag
	if (substr($displayInFrame,0,6) == "custom") {
		$customQuery = "SELECT *
						FROM   campaignCustomFrames
						WHERE  sourceCode = '$src'";
		$customResult = dbQuery($customQuery);
		while ($customRow = dbFetchObject($customResult)) {
			$varFrameContent = $customRow->frameContent;
		}
	}			
}

// select myfree database again
//mysql_select_db($dbase);


if (dbNumRows($result) == 0) {
	// Get the url of this offerCode
	$query = "SELECT offerCode, url,  displayInFrame
			  FROM   edOffers
			  WHERE  offerCode = '$src'";
	$result = dbQuery($query);
	while($row = dbFetchObject($result)) {
		$url = $row->url;
		$displayInFrame = $row->displayInFrame;
		$redirectUrl = $sGblOfferRedirectsPath."?src=$row->offerCode";
		$offer = true;
	}
}

//$url = "http://asp.myfree.com/onetime/default.asp";

$urlComponents = explode("&",$QUERY_STRING);

// Append current url Components to the new Url
$newUrlComponents = "";
for ($i=0; $i<count($urlComponents); $i++) {
	if (trim($urlComponents[$i]) != '') {
		$findSS = explode("=",$urlComponents[$i]);
		/********** FOLLOWING IS FOR Business Development ONLY  ***********/
		
		//$newUrlComponents .= "&".$urlComponents[$i];
		
		if($redirect == true) {
			// If QUERY_STRING Component is "ss", store it's value to append to the src value of new Url
			
			if($findSS[0] == "ss"){
				$ssValue = $findSS[1];
			} //else if ($findSS[0] != "src"){
				// If component is not "src", just add it at the end of new Url
				$newUrlComponents .= "&".$urlComponents[$i];
				//echo "<BR>".$newUrlComponents;
			//}
		} else {
			// DON'T APPEND SRC ANYTHING IN OFFER URL
			if ($findSS[0] != "src")
			$newUrlComponents .= "&".$urlComponents[$i];
		}	
	}
}


if($redirect == true) {
	if(strstr($url,"?")) {
		//$newUrl = $url."&"."src=$src".$ssValue.$newUrlComponents;
		$newUrl = $url."&".$newUrlComponents;
	} else {
		//$newUrl = $url."?"."src=$src".$ssValue.$newUrlComponents;
		$newUrl = $url."?".$newUrlComponents;
	}
} else {
	if($newUrlComponents != '') {
		if (strstr($url,"?"))
		$newUrl = $url."&".$newUrlComponents;
		else
		$newUrl = $url."?".$newUrlComponents;
	} else {
		$newUrl = $url;
	}		
}

//$newUrl = $url."?"."src=$src".$ssValue.$newUrlComponents;
$newUrl = urlencode($newUrl);

/*
if ($_SERVER['REMOTE_ADDR'] == '198.63.247.2') {
echo "1 $url<BR> 2 $newUrl";
}
*/

if ($redirect == true) {
	
	
	// select nibbles database
	//mysql_select_db($nibblesDBName);
	
	$redirectQuery = "INSERT INTO bdRedirectsTracking (clickDate, sourceCode, subSourceCode, ipAddress)
				  VALUES (CURRENT_DATE,'$src', '$ssValue', '".$_SERVER['REMOTE_ADDR']."')";
	
	$result = dbQuery($redirectQuery);
	
	// select myfree database again
	//mysql_select_db($dbase);
	
	//echo $redirectQuery.mysql_error();
} else if($offer == true) {
	$redirectQuery = "INSERT INTO edOfferRedirectsTracking (clickDate, offerCode, subsource, IPAddress)
				  VALUES (CURRENT_DATE,'$src', '$ssValue', '".$_SERVER['REMOTE_ADDR']."')";
	$result = dbQuery($redirectQuery);		
}


// Foreward to asp page if has redirect .asp link
if (strstr($redirectUrl, ".asp")) {
	header("Location:$redirectUrl");
}


/*
$redirectContent = "<html>

<head>
<title>$src</title>

<!-- hide this from tired old Browsers  if (window.location != top.location)
{top.location.href=window.location} // -->

$popUpContent

</head>
<body>
<meta http-equiv=\"refresh\"
content=\"1;URL='".$newUrl."'\">
</body>
</html>";


*/


if ($displayInFrame == 'top') {
	//$topFrame = $redirectContent;
	if ($redirect == true) {
		$varFrameName = "redirectTopFrameHtml.html";
	} else {
		$varFrameName = "offerTopFrameHtml.html";
	}
} else if ($displayInFrame == 'bottom') {
	//$bottomFrame = $redirectContent;
	if ($redirect == true) {
		$varFrameName = "redirectBottomFrameHtml.html";
	} else {
		$varFrameName = "offerBottomFrameHtml.html";
	}
} else if ($displayInFrame == 'left') {
	//$leftFrame = $redirectContent;
	if ($redirect == true) {
		$varFrameName = "redirectLeftFrameHtml.html";
	} else {
		$varFrameName = "offerLeftFrameHtml.html";
	}
} else if ($displayInFrame == 'right') {
	//$rightFrame = $redirectContent;
	if ($redirect == true) {
		$varFrameName = "redirectRightFrameHtml.html";
	} else {
		$varFrameName = "offerRightFrameHtml.html";
	}
} else if ($displayInFrame == 'customTop' || $displayInFrame == 'customLeft'|| $displayInFrame == 'customRight' || $displayInFrame == 'customBottom') {
	//$rightFrame = $redirectContent;
	if ($redirect == true) {
		$varFrameName = "redirectCustomFrameHtml.php?src=$src";
	} //else {
	//$varFrameName = "offerRightFrameHtml.html";
	//}
}

$varQuery = "SELECT varValue
			 FROM   vars
			 WHERE  varName = '".substr($varFrameName,0,strlen($varFrameName)-5)."'";

$varResult = dbQuery($varQuery);
while ($row = dbFetchObject($varResult)) {
	$varFrameContent = $row->varValue;
}
// Get the html width to use in Frameset specification
$bodyStart = explode("<body",$varFrameContent);
$bodyLine = $bodyStart[1];
$bodyEnd = explode(">",$bodyLine);
$bodyTag = $bodyEnd[0];
// set which attribute(width or hight) should be used from body tag to split frame
if($displayInFrame == 'top' || $displayInFrame == 'bottom' || $displayInFrame == "customTop" || $displayInFrame == "customBottom") {
	$partitionBy = 'height';
} else {
	$partitionBy = 'width';
}
// get width/height of the document from bodyTag
$bodyAttr = explode(" ",$bodyTag);
for ($i = 0; $i < count($bodyAttr); $i++) {
	//set default frameWidth;
	$frameWidth = "100";
	
	if (stristr($bodyAttr[$i], $partitionBy)) {
		$widthAttr = explode("=", $bodyAttr[$i]);
		$frameWidth = ereg_replace("\"","", $widthAttr[1]);
		$frameWidth = ereg_replace("'","", $frameWidth);
	}
}

switch ($displayInFrame) {
	case "top":
	$frameSetInfo = "<frameset rows=\"$frameWidth, *\" cols=\"1*\">";
	$frame2Src = "rFrame.php?src=$src&newUrl=$newUrl";
	$frame1Src = $varFrameName;
	break;
	case "bottom":
	$frameSetInfo = "<frameset rows=\"*, $frameWidth\" cols=\"1*\">";
	$frame2Src = $varFrameName;
	$frame1Src = "rFrame.php?src=$src&newUrl=$newUrl";
	break;
	case "left":
	$frameSetInfo = "<frameset rows=\"1*\" cols=\"$frameWidth, *\">";
	$frame2Src = "rFrame.php?src=$src&newUrl=$newUrl";
	$frame1Src = $varFrameName;
	break;
	case "right":
	$frameSetInfo = "<frameset rows=\"1*\" cols=\"*, $frameWidth\">";
	$frame2Src = $varFrameName;
	$frame1Src = "rFrame.php?src=$src&newUrl=$newUrl";
	break;
	case "customTop":
	$frameSetInfo = "<frameset rows=\"$frameWidth, *\" cols=\"1*\">";
	$frame2Src = "rFrame.php?src=$src&newUrl=$newUrl";
	$frame1Src = $varFrameName;
	break;
	case "customLeft":
	$frameSetInfo = "<frameset rows=\"1*\" cols=\"$frameWidth, *\">";
	$frame2Src = "rFrame.php?src=$src&newUrl=$newUrl";
	$frame1Src = $varFrameName;
	break;
	case "customRight":
	$frameSetInfo = "<frameset rows=\"1*\" cols=\"*, $frameWidth\">";
	$frame2Src = $varFrameName;
	$frame1Src = "rFrame.php?src=$src&newUrl=$newUrl";
	break;
	case "customBottom":
	$frameSetInfo = "<frameset rows=\"*, $frameWidth\" cols=\"1*\">";
	$frame2Src = $varFrameName;
	$frame1Src = "rFrame.php?src=$src&newUrl=$newUrl";
	break;
	
	//default:
	//	echo $redirectContent;
}

if ($frameSetInfo) {
	echo "<html>
				<head><title>No title</title></head>		
				$frameSetInfo
				<frame name=\"frame1\" scrolling=\"no\" marginwidth=\"0\" marginheight=\"0\" src=\"$frame1Src\">	

				<frame name=\"frame2\" scrolling=\"auto\" marginwidth=\"10\" marginheight=\"14\" src=\"$frame2Src\">
	
			<noframes>
			<body bgcolor=\"#FFFFFF\" text=\"#000000\" link=\"#0000FF\" vlink=\"#800080\" alink=\"#FF0000\">

<p>You need a browser that supports frame to view this page.</p>	
</body>
</noframes>
</frameset>
</html>";
} else {
	//echo $redirectContent;
	header("Location:rFrame.php?src=$src&newUrl=$newUrl&".SID);
}
?>



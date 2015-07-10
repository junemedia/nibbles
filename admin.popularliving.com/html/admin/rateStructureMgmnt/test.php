<?php
include('/home/sites/popularliving/html/nibbles2/libs/pixel.php');

$PiFactory = new PixelFactory();
$list = $PiFactory->pixelListByPage('EP', 
												1,
												2,
												'12gnb100906004');
											
		//so, now we've got our list of pixels to show, and we can do things with them.
		
	$pixelHTML = '';
	foreach($list as $pixel){
		$pixel->incrementDisplays();
		$pixelHTML .= $pixel->html();
	}
	
	print_r($list);
	echo "pixel html is $pixelHTML";

//what about functions for isLastPage
//isLandingPage
//isEmailCapPage
//isRegPage

//must pass, sSesTemplateType, iSesCurrentPositionInFlow, iSesNoOfFlow


//so then, in the ot.php pixels section, I would be doing the following:

?>
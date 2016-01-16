<?php

include_once("/var/www/html/subctr.popularliving.com/subctr/functions.php");

$url = $_GET['image'];
if (!strstr($url,'media.campaigner.com')) {
	$outputfile = 'upload_imgs/'.basename($url);
	$output = shell_exec("wget '".$url."' -O '".$outputfile."' 2>&1");
	$send_result = UploadMediaFileCampaigner(trim($outputfile));
	//add by leon
	// print the error message
	if (strstr(strtolower(trim(getXmlValueByTag($send_result,'ReturnMessage'))),'success')) {
		@unlink($outputfile);
		echo trim(getXmlValueByTag($send_result,'FileURL'));
	}else{
		mail('leonz@junemedia.com,williamg@junemedia.com,AndrewB@junemedia.com,howew@junemedia.com','MOVE upload image error',$send_result);
		echo $send_result;
	}
}
exit;

?>

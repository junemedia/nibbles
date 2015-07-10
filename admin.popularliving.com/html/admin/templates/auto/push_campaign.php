<?php

include_once("../../../includes/paths.php");
include_once("/var/www/html/subctr.popularliving.com/subctr/functions.php");
mysql_select_db('newsletter_templates');
require_once("template_class.php");

if ($iId == '' || !ctype_digit($iId)) {
	echo 'id missing';exit;
}

$query = "SELECT * FROM automated WHERE id = '$iId'";
$rSelectResult = mysql_query($query);
echo mysql_error();
while ($oRow = mysql_fetch_object($rSelectResult)) {
	$template = $oRow->template;
}

$preview  = new Template("templates/$template");

$query = "SELECT * FROM automated_map WHERE automated_id = '$iId'";
$rSelectResult = mysql_query($query);
echo mysql_error();
while ($oRow = mysql_fetch_object($rSelectResult)) {
	$tag_key = str_replace('[','',$oRow->tag_key);
	$tag_key = str_replace(']','',$tag_key);
	if ($oRow->tag_value !='') {
		$preview->set($tag_key,$oRow->tag_value);
	}
}

$html_code = $preview->output();

$html_code = str_replace('REDIR:','',$html_code);
$html_code = str_replace("{opencount('<img src=\"{opct.url}\" width=\"1\" height=\"1\" border=\"0\" />')}",'',$html_code);
$html_code = str_replace("{datetime(job.issuedate,'','%Y%m%d')}",date('Ymd'),$html_code);
$html_code = str_replace('{to}','[Contact.Email]',$html_code);


$get_data_result = mysql_query("SELECT * FROM automated WHERE id = \"$iId\"");
while ($data_row = mysql_fetch_object($get_data_result)) {
	if ($data_row->campaign_id == 0) {
		$html_code = str_replace('{job.jobid}',$iId,$html_code);
	} else {
		$html_code = str_replace('{job.jobid}',$data_row->campaign_id,$html_code);
	}
	$data_array = array('campaign_id' => $data_row->campaign_id,
					'subject_line' => $data_row->subject,
					'campaign_name' => $data_row->campaign_name,
					'from_name' => $data_row->from_name,
					'from_email_id' => $data_row->from_email_id,
                                        'reply_email_id' => $data_row->reply_email_id,
					'template_id' => $iId,
					'html_code' => $html_code);
}
//print_r($data_array);
$create_result = CreateUpdateCampaign($data_array);
$CampaignId = trim(getXmlValueByTag($create_result,'CampaignId'));

if(trim($create_result) == ""){
    $message = "<p>Empty Server response! Could be the html content encoding error</p>";
    echo $message;
    //echo "<p><textarea width='80%' height=200>";
    //echo print_r($data_array, true);
    //echo "</textarea></p>";
}

//Save campaigner response
$responseSql = "INSERT INTO `campaignerResponse` (`id`, `campaignId`, `datetime`, `response`) VALUES 
                (NULL, '" . $data_array['campaign_id'] . "', NOW(), '$create_result')";
mysql_query($responseSql);

if ($CampaignId !='' && ctype_digit($CampaignId)) {
	$get_data_result = mysql_query("UPDATE automated SET campaign_id=\"$CampaignId\" WHERE id = \"$iId\"");
}

echo trim(getXmlValueByTag($create_result,'ReturnMessage'))." => CampaignId: ".$CampaignId;
echo "<br><br>";
echo $create_result;

?>
<br><br><br><center><button type="button" onclick="window.open('', '_self', ''); window.close();">Close</button></center>

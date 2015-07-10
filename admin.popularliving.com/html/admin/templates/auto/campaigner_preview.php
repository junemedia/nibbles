<?php

include_once("../../../includes/paths.php");
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
$html_code = str_replace('{job.jobid}',$iId,$html_code);	// there is no jobid in campaginer at this time
$html_code = str_replace('{to}','[Contact.Email]',$html_code);

echo $html_code;

?>
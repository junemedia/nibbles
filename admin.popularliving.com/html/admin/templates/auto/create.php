<?php
include_once("/var/www/html/subctr.popularliving.com/subctr/functions.php");
include_once("../../../includes/paths.php");
mysql_select_db('newsletter_templates');

iconv_set_encoding("input_encoding", "UTF-8");
iconv_set_encoding("internal_encoding", "UTF-8");
iconv_set_encoding("output_encoding", "UTF-8");
function generateImgUrl($url)
{
	if (!strstr($url,'media.campaigner.com')) {
		$outputfile = 'upload_imgs/'.basename($url);
		$output = shell_exec("wget '".$url."' -O '".$outputfile."' 2>&1");
		$send_result = UploadMediaFileCampaigner(trim($outputfile));
		//add by leon
		// print the error message
		if (strstr(strtolower(trim(getXmlValueByTag($send_result,'ReturnMessage'))),'success')) {
			@unlink($outputfile);
			return trim(getXmlValueByTag($send_result,'FileURL'));
		}else{
			return '';
		}
	}
}
$CreateUpdateCampaign = false;
if ($submit == 'Generate') {
	$fields_name = explode(',',$fields_name);
	foreach ($fields_name as $field) {
		$val = addslashes($$field);
		//$val = mb_convert_encoding($val, 'ISO-8859-1', 'UTF-8');
		$query = "UPDATE automated_map SET  tag_value = \"$val\" WHERE automated_id = '$iId' AND tag_key=\"[$field]\"";
		$rSelectResult = mysql_query($query);
		echo mysql_error();
	}
	$CreateUpdateCampaign = true;
}

$fields = array();
$query = "SELECT * FROM automated_map WHERE automated_id = '$iId'";
$rSelectResult = mysql_query($query);
echo mysql_error();
$fields_name = "";
while ($oRow = mysql_fetch_object($rSelectResult)) {
	$tag_key = str_replace('[','',$oRow->tag_key);
	$tag_key = str_replace(']','',$tag_key);
	$fields[$tag_key] = stripslashes($oRow->tag_value);
	$fields_name .= "$tag_key,";
}
if ($fields_name !='') {
	$fields_name = substr($fields_name,0,strlen($fields_name)-1);
}

$query = "SELECT * FROM automated WHERE id = '$iId'";
$rSelectResult = mysql_query($query);
echo mysql_error();
while ($oRow = mysql_fetch_object($rSelectResult)) {
	$template = $oRow->template;
        $NL_mailDate = $oRow->mailing_date;
}

if ($initSubmit == 'Get Sweeps') {
	$rawPrizes = file_get_contents("http://win.betterrecipes.com/api/getPrize/$NL_mailDate");
	$prizes = json_decode($rawPrizes); 
	$sweepBaseUrl = 'http://win.betterrecipes.com/';
	if($template =='r4l_sweeps.php')
	{
		$sweepBaseUrl = 'http://win.recipe4living.com/';
	}

	foreach($prizes->prizes as $key=>$prize)
	{
		if($key==0)
		{
			$fields['FEATURE_IMAGE'] = stripslashes(generateImgUrl($sweepBaseUrl.stripslashes($prize->img1)));
			$fields['FEATURE_LINK']=stripslashes($sweepBaseUrl.'/prize/'.$prize->date);
			$fields['FEATURE_TEXT']=stripslashes($prize->desc1);
			$fields['FEATURE_TITLE']=stripslashes($prize->title);
		}else if($key <=4)
		{
			$fields['PRIZE_'.$key.'_IMAGE'] = stripslashes(generateImgUrl($sweepBaseUrl.stripslashes($prize->img1)));
			$fields['PRIZE_'.$key.'_URL']=stripslashes($sweepBaseUrl.'prize/'.$prize->date);
			$fields['PRIZE_'.$key.'_TITLE']=stripslashes($prize->title);
		}else
		{
			break;
		}		
	}
}



?>
<html>
<head>
<title>Generate Newsletter</title>
<style>
* {
	font-family: verdana;
	font-size:12px;
}
</style>
<SCRIPT LANGUAGE=JavaScript SRC="http://r4l.popularliving.com/subctr/js/ajax.js" TYPE=text/javascript></script>
<script language="JavaScript">
function move_image_to_cloud(key) {
	if (document.getElementById(key).value != '') {
		if (document.getElementById(key).value.indexOf("media.campaigner.com") != -1) {
			return true;
		} else {
			if (document.getElementById(key).value.toUpperCase().indexOf(".JPG") != -1 || document.getElementById(key).value.toUpperCase().indexOf(".JPEG") != -1 || 
				document.getElementById(key).value.toUpperCase().indexOf(".GIF") != -1 || document.getElementById(key).value.toUpperCase().indexOf(".PNG") != -1) {
				response=coRegPopup.send('move_image_to_cloud.php?image='+document.getElementById(key).value,'');
				if (response.indexOf("media.campaigner.com") != -1) {
					document.getElementById(key).value = response.trim();
				}
			} else {
				return true;
			}
		}
	}
	return true;
}
</script>
</head>
<body>
<table align="center">
<tr><td><h2><?php echo $subject; ?></h2>
</td></tr>
</table>
<?php if ($CreateUpdateCampaign == true) { /* why load this hidden iframe? because we want to create campaign first and capture campaignId so we can use it again
to push updates and replace jobid tag from template with campaignId.  this is important so keep this hidden iframe.  it will not harm.
*/ ?>
<iframe src="push_campaign.php?iId=<?php echo $iId; ?>" id="iframe2" frameborder="0" scrolling="No" width="1" height="1"></iframe>
<?php } ?>
<table align="center" cellpadding="5" cellspacing="5" style="border:1px solid #383838;">
<tr>
	<td valign="top">
		<table cellpadding="5" cellspacing="5" style="border: 1px solid #383838;">
			<form name='form1' action='<?php echo $_SERVER['PHP_SELF'];?>' method="POST">
				<input type="hidden" name="iId" id="iId" value="<?php echo $iId; ?>">
				<input type="hidden" name="subject" id="subject" value="<?php echo $subject; ?>">
				<input type="hidden" name="fields_name" id="fields_name" value="<?php echo $fields_name; ?>">
				<?php foreach ($fields AS $key => $value) { ?>
				<tr>
					<td>
						<?php echo $key; ?>
					</td>
					<td>
						<?php if (strstr($key,'ADS_') || strstr($key,'TEXT')) { ?>
							<textarea name="<?php echo $key; ?>" id="<?php echo $key; ?>" cols="37" rows="5"><?php echo $value; ?></textarea>
						<?php } else { ?>
							<input type="text" size="50" name="<?php echo $key; ?>" id="<?php echo $key; ?>" value="<?php echo $value; ?>" onblur="move_image_to_cloud('<?php echo $key; ?>');">
						<?php } ?>
					</td>
				</tr>
				<?php } ?>
				<tr>
					<td align="right" colspan="2">
						<input type="hidden" name="templateName" value="<?php echo $template; ?>">
						<?php if($template =='br_sweeps.php' || $template =='r4l_sweeps.php'){?>
						<input type="submit" name="initSubmit" id="initSubmit" value="Get Sweeps">&nbsp;&nbsp;&nbsp;&nbsp;
						<?php }?>
						<input type="submit" name="submit" id="submit" value="Generate">
						<br><br><br>
						<a href="push_campaign.php?iId=<?php echo $iId; ?>" onclick="javascript:void window.open('push_campaign.php?iId=<?php echo $iId; ?>','edit_<?php echo $iId; ?>','width=500,height=400,toolbar=0,menubar=0,location=0,status=1,scrollbars=1,resizable=1,left=0,top=0');return false;">Push to Campaigner</a>
						<br><p>Make sure to click Generate button before you click Push to Campaigner link...</p>
					</td>
				</tr>
			</form>
			<p>Supported file formats are: JPEG, JPG, GIF, and PNG</p>
		</table>
	</td>
	<td valign="top">
		<a href="arcamax_preview.php?iId=<?php echo $iId; ?>" target="_blank">Arcamax Preview</a><br>
		<iframe src="campaigner_preview.php?iId=<?php echo $iId; ?>" id="iframe1" frameborder="0" scrolling="auto" width="800" height="1500"></iframe>
	</td>
</tr>
</table>
</body>
</html>

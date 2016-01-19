<?php


include_once("../../includes/paths.php");
include_once("$sGblLibsPath/stringFunctions.php");
include_once("../../nibbles2/libs/pixel.php");

$sPageTitle = "Pixel Management";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
if (($sSaveClose || $sSaveNew) && !($id)) {
	//this is a new pixel
	
	//check that all of the required fields are there
	if($iPartnerId == ''){
		$sMessage .= 'Partner is required...<br>';
	}
	if(($iCampaignId  == '')&& ($sSourceCode == '') && ($sDisplayOption != 'global')){
		$sMessage .= "Without Campaign and Source Code, Display must be 'global'...<br>";
	}
	if($sPixelHtml == '' || $sPixelHtml == "<img src=\"\" width=\"1\" height=\"1\">"){
		$sMessage .= "HTML is required...<br>";
	}
	if(($sDisplayOption == 'campaign') && ($iCampaignId == '')){
		$sMessage .= "A Campaign must be selected when displaying pixels by campaign ...<br>";
	}
	
	if(($sDisplayOption == 'sourceCode') && ($sSourceCode == '')){
		$sMessage .= "A source code must be selected when displaying pixels by source code ...<br>";
	}
	
	if ($sMessage =='') {
		if ($sDisplayOption == 'campaign') {
			$sSourceCode = '';
		} else if($sDisplayOption == 'sourceCode') {
			$iCampaignId = '';
		} else if($sDisplayOption == 'global') {
			$sSourceCode = '';
			$iCampaignId = '';	
		}

		if (strstr($sPixelHtml,'|')) {
			$aPixel = explode('|', $sPixelHtml);
			foreach ($aPixel as $pixel) {
				if ($pixel !='') {
					$pixel = addslashes($pixel);

					$addQuery = "INSERT INTO pixels (sourceCode, pixelHtml, partnerId, campaignId, displayOption, type)
							VALUES(\"$sSourceCode\", \"$pixel\", '$iPartnerId','$iCampaignId', \"$sDisplayOption\", \"$sType\")";
					$result = mysql_query($addQuery);
					if(!$result) {
						$sMessage .= dbError();
					}
					
					$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
					  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($addQuery) . "\")";
					$rLogResult = dbQuery($sLogAddQuery);
				}
			}
		} else {
			$sPixelHtml = addslashes($sPixelHtml);
			$addQuery = "INSERT INTO pixels (sourceCode, pixelHtml, partnerId, campaignId, displayOption, type)
					VALUES(\"$sSourceCode\", \"$sPixelHtml\", '$iPartnerId','$iCampaignId', \"$sDisplayOption\", \"$sType\")";
			$result = mysql_query($addQuery);
			if(!$result) {
				$sMessage .= dbError();
			}
			
			$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($addQuery) . "\")";
			$rLogResult = dbQuery($sLogAddQuery);
		}
	}
} elseif (($sSaveClose || $sSaveNew) && ($id)) {
	//this is an existing pixel
	
	//check that all of the required fields are there
	if($iPartnerId == ''){
		$sMessage .= 'Partner is required...<br>';
	}
	if(($iCampaignId  == '')&& ($sSourceCode == '') && ($sDisplayOption != 'global')){
		$sMessage .= "Without Campaign and Source Code, Display must be 'global'...<br>";
	}
	if($sPixelHtml == '' || $sPixelHtml == "<img src=\"\" width=\"1\" height=\"1\">"){
		$sMessage .= "HTML is required...<br>";
	}
	if(($sDisplayOption == 'campaign') && ($iCampaignId == '')){
		$sMessage .= "A Campaign must be selected when displaying pixels by campaign ...<br>";
	}
	
	if(($sDisplayOption == 'sourceCode') && ($sSourceCode == '')){
		$sMessage .= "A source code must be selected when displaying pixels by source code ...<br>";
	}
	

	if($sDisplayOption == 'campaign'){
		$sSourceCode = '';
	} else if($sDisplayOption == 'sourceCode'){
		$iCampaignId = '';
	} else if($sDisplayOption == 'global'){
		$sSourceCode = '';
		$iCampaignId = '';	
	}
	
	if ($sMessage =='') {
				
		$sPixelHtml = addslashes($sPixelHtml);
		
		$addQuery = "UPDATE pixels 
					SET sourceCode = \"$sSourceCode\", 
						pixelHtml = \"$sPixelHtml\", 
						partnerId ='$iPartnerId',
						campaignId = '$iCampaignId', 
						displayOption = \"$sDisplayOption\",
						type = \"$sType\"
					WHERE id = '$id'";
		$result = mysql_query($addQuery);
			
		// start of track users' activity in nibbles
		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($addQuery) . "\")";
		$rLogResult = dbQuery($sLogAddQuery);
		// end of track users' activity in nibbles
		
	} 
}

if ($sMessage == '') {
	if ($sSaveClose) {
			echo "<script language=JavaScript>
				window.opener.location.reload();
				$sPopUpUrl
				self.close();
				</script>";					
			exit();
	} else if ($sSaveNew) {
		$reloadWindowOpener = "<script language=JavaScript>
						window.opener.location.reload();
						</script>";
		$sPixelHtml = '';
		$sSourceCode = '';
		$sDisplayOption = '';
		$iPartnerId = '';
		$sType = '';
		$iCampaignId = '';
		$id = '';
	}
}

if ($id != '') {
	$selectQuery = "SELECT *
					FROM   pixels
					WHERE  id = '$id'";
	$result = mysql_query($selectQuery);
	while ($row = mysql_fetch_object($result)) {
		$sPixelHtml = $row->pixelHtml;
		$sSourceCode = $row->sourceCode;
		$sDisplayOption = $row->displayOption;
		$iPartnerId = $row->partnerId;
		$sType = $row->type;
		$iCampaignId = $row->campaignId;
	}
} else {
	//defaults
	
	//this section should also set up the pre-population options.
	
	$sPixelHtml = "<img src=\"".($t == '' ? '' : $t)."\" width='1' height='1'>";
	$sSourceCode = ($src == '' ? '' : $src);
	$sDisplayOption = ($disp == '' ? 'global' : $disp);
	$iPartnerId = ($pid == '' ? '' : $pid);
	$sType = ($type == '' ? 'emailCap' : $type);
	$iCampaignId = '';
}

//set up the type controller
$sGlobalChecked = '';
$sCampaignChecked = '';
$sSourceCodeChecked = '';
switch($sDisplayOption){
	case 'global':
	default:
		$sGlobalChecked = 'checked';
		break;
	case 'campaign':
		$sCampaignChecked = 'checked';
		break;
	case 'sourceCode':
		$sSourceCodeChecked = 'checked';
		break;
}

//partner
$partnerSQL = "SELECT * FROM partnerCompanies order by companyName asc";
$partnerRes = dbQuery($partnerSQL);
$sPartnerSelect = "<select name='iPartnerId' onChange='populateCampaignsSourceCodes(this.value, document.form1.iCampaignId.value, document.form1.sSourceCode.value);'>";
while($partner = dbFetchObject($partnerRes)){
	$sPartnerSelect .= "<option value='$partner->id' ".($partner->id == $iPartnerId ? 'selected' : '').">$partner->companyName";
}
$sPartnerSelect .= "</select>";

//campaign
$sCampaignSelect = "<div id='iCampaignIdDiv' style='display:inline;'><select name='iCampaignId'></select></div>";
//populate this with an AJAX call that filters by the partner

//sourceCode
$sSourceCodeSelect = "<div id='sSourceCodeDiv' style='display:inline;' ><select name='sSourceCode' onChange='pixelHint(this.value);'></select></div>";
//populate this with an AJAX call that filters by the partner

//displayOption
$sDisplayOptionTable = "
<tr>
	<td>Partner: $sPartnerSelect</td>
	<td nowrap><input name='sDisplayOption' value='campaign' type='radio' $sCampaignChecked>Campaign: $sCampaignSelect</td>
	<td nowrap><input name='sDisplayOption' value='sourceCode' type='radio' $sSourceCodeChecked onClick='pixelHint(document.form1.sSourceCode.value);'>Source Code: $sSourceCodeSelect</td>
	<td nowrap><input name='sDisplayOption' value='global' type='radio' $sGlobalChecked> Global</td>
</tr>
";

//type select
$sTypeSelect = "
<td><input type='radio' name='sType' value='emailCap' ".($sType == 'emailCap' ? 'checked' : '').">After Email Capture</td>
<td><input type='radio' name='sType' value='regPage' ".($sType == 'regPage' ? 'checked' : '').">After Reg Page</td>
<td><input type='radio' name='sType' value='lastPage' ".($sType == 'lastPage' ? 'checked' : '').">Last Page</td>
<td><input type='radio' name='sType' value='landingPage' ".($sType == 'landingPage' ? 'checked' : '').">Landing Page</td>
";




if ($id) {
	// if edit, the display textfield
	$sPixelFieldHtml = "HTML: <input name='sPixelHtml' value=\"".htmlspecialchars($sPixelHtml)."\" size=120>";
} else {
	// if adding new entry, then display textarea
	$sPixelFieldHtml = "HTML: <textarea name='sPixelHtml' rows=4 cols=100>$sPixelHtml</textarea>";
}




// Hidden variable to be passed with form submit
$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=id value='$id'>";

include("$sGblIncludePath/adminAddHeader.php");	
?>
<script language='javascript' src='/libs/ajax.js'></script>
<script language='javascript'>
function pixelHint(src){
	asdf = new AmpereMedia();
	//alert(document.form1.sDisplayOption);
	//alert(document.form1.sDisplayOption[1].checked);
	if(document.form1.sDisplayOption[1].checked == true){
		hint = asdf.send('test.php?src='+src,'');
		//alert(hint);
		document.getElementById('hintDiv').innerHTML = hint;
	}
}

function populateCampaignsSourceCodes(partnerId, campaignValue, sourceCodeValue){
	//find and save the 'disabled' state of each of the inputs
	options = new AmpereMedia();
	var campaignDisabled = document.form1.iCampaignId.disabled;
	var sourceCodeDisabled = document.form1.sSourceCode.disabled;
	
	//get the campaign options from AJAX
	campaignOptions = options.send('populate.php?partnerId='+partnerId+'&options=campaign&value='+campaignValue,'');
	//get the sourceCode options from AJAX
	sourceCodeOptions = options.send('populate.php?partnerId='+partnerId+'&options=sourceCode&value='+sourceCodeValue,'');
	
	//alert(campaignOptions);
	//alert(sourceCodeOptions);
	
	//set each up in the document's divs.
	document.getElementById('iCampaignIdDiv').innerHTML = campaignOptions;
	document.getElementById('sSourceCodeDiv').innerHTML = sourceCodeOptions;
		//get the campaign div, and set it
		//get the source code div, and set it.
		
	document.form1.iCampaignId.disabled = campaignDisabled;
	document.form1.sSourceCode.disabled = sourceCodeDisabled;
	
	pixelHint(document.form1.sSourceCode.value);
}


</script>

<form action='<?php echo $PHP_SELF;?>' method=post name='form1' >
<?php echo $hidden;?>
<?php echo $reloadWindowOpener;?>
<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
<tr>
	<td>Id: <?php echo $id;?></td>
</tr>


<tr>
	<td>&lt;img src="" width='1' height='1'&gt;</td>
</tr>


<tr>
	<td colspan=5><?php echo $sPixelFieldHtml; ?></td>
</tr>


<tr>
	<td colspan=4><b>Adding Multiple Pixels:</b>  You can add multiple pixels at a time seperated by a pipe, "|".<br>
		For example:  &lt;img src="http://www.yahoo.com/" width='1' height='1'&gt;|&lt;img src="http://www.google.com/" width='1' height='1'&gt;
	<br><br>
	</td>
</tr>


<tr>
	<td colspan=4><b>Available Tags include:</b>
	[salutation] [email]
	[first] [last] [address] [city] [state] [zip] [phone] [ipAddress] [birthYear] [birthMonth]
	[birthDay] [gender] <br>[sourcecode] [mm] [dd] [yyyy] [yy] [hh] [ii] [ss] [gVariable] [serial]</td>
</tr>



<tr>
<?php echo $sTypeSelect;?>
</tr>

<?php echo $sDisplayOptionTable;?>
<tr><td></td><td colspan=2><div id='hintDiv'></div></td></tr>
</table>
<script language='javascript'>
populateCampaignsSourceCodes(document.form1.iPartnerId.value, '<?php echo $iCampaignId;?>', '<?php echo $sSourceCode;?>');
</script>
<?php

include("$sGblIncludePath/adminAddFooter.php");
				
} else {
	echo "You are not authorized to access this page...";
}	

?>
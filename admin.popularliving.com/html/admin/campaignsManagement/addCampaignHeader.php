<?php

include("../../includes/paths.php");
session_start();

$sPageTitle = "Nibbles - Email Capture Management";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

// users in this array are allowed to edit HTML code.
$aArray = array('spatel','ccalip','bbevis','jr','josh','pdrazba','salikhan','stuart');

function charEncode($str){
	$str = str_replace('<','\<',$str);
	$str = str_replace('>','\>',$str);
	return $str;
}

if (hasAccessRight($iMenuId) || isAdmin()) {
	if ($sSubmit) {
		$sMessage = '';
		if ($iOrder == ''){
			$sMessage = "Order Required...";
			$bKeepValues = true;
		} elseif ($campaignId == ''){
			$sMessage = "Campaign Id Required...";
			$bKeepValues = true;
		} elseif(!(ctype_digit($iOrder))){
			$sMessage  = "Order must be an integer...";
			$bKeepValues = true;
		}
		
		
		// save uploaded image
		if (!(is_dir("/home/sites/admin.popularliving.com/html/nibbles2/images/headers/$sCampaignName")) ) {
			mkdir("/home/sites/admin.popularliving.com/html/nibbles2/images/headers/$sCampaignName",0777);
			chmod("/home/sites/admin.popularliving.com/html/nibbles2/images/headers/$sCampaignName",0777);
		}

		if ($_FILES['left']['tmp_name'] && $_FILES['left']['tmp_name']!="none") {
			$sImageFileName = "campHeader_$campaignId"."_".$iOrder."_left.gif";
			move_uploaded_file( $_FILES['left']['tmp_name'], "/home/sites/admin.popularliving.com/html/nibbles2/images/headers/$sCampaignName/$sImageFileName");
			chmod("/home/sites/admin.popularliving.com/html/nibbles2/images/headers/$sCampaignName/$sImageFileName",0777);
			chmod("/home/sites/admin.popularliving.com/html/nibbles2/images/headers/$sCampaignName/$sImageFileName",0777);
		}
		
		if ($_FILES['right']['tmp_name'] && $_FILES['right']['tmp_name']!="none") {
			$sImageFileName = "campHeader_$campaignId"."_".$iOrder."_right.gif";
			move_uploaded_file( $_FILES['right']['tmp_name'], "/home/sites/admin.popularliving.com/html/nibbles2/images/headers/$sCampaignName/$sImageFileName");
			chmod("/home/sites/admin.popularliving.com/html/nibbles2/images/headers/$sCampaignName/$sImageFileName",0777);
		}
		
		
		if ($campaignId != '' && $sSubmit == 'Submit' && $sMessage == '') {
			
			if ($iOldOrder != $iOrder) {
				// rename the file if order has changed.
				$sAdminPath = "/home/sites/admin.popularliving.com/html/nibbles2/images/headers/$sCampaignName/";
				rename($sAdminPath."campHeader_$campaignId"."_".$iOldOrder."_left.gif",$sAdminPath."campHeader_$campaignId"."_".$iOrder."_left.gif");
				rename($sAdminPath."campHeader_$campaignId"."_".$iOldOrder."_right.gif",$sAdminPath."campHeader_$campaignId"."_".$iOrder."_right.gif");
			}
			
			
			$sLeftFileName = "http://www.popularliving.com/nibbles2/images/headers/$sCampaignName/"."campHeader_$campaignId"."_".$iOrder."_left.gif";
			$sRightFileName = "http://www.popularliving.com/nibbles2/images/headers/$sCampaignName/"."campHeader_$campaignId"."_".$iOrder."_right.gif";
			
			if (in_array($_SERVER['PHP_AUTH_USER'],$aArray)) {
				$sContent = str_replace('[CAMPAIGN_HEADER_LEFT]', $sLeftFileName, $sContent);
				$sContent = str_replace('[CAMPAIGN_HEADER_RIGHT]', $sRightFileName, $sContent);
			} else {
				$sContent = "<table width='750' border='0' align='center' cellpadding='0' cellspacing='0'>
					<tr><td>	
					<table width='500' border='0' align='center' cellpadding='0' cellspacing='0'>
					  <tr>
					    <td><img src='$sLeftFileName' alt='' border='0' /></td>
					  </tr>
					</table>
					</td><td>
					<table width='250' border='0' align='center' cellpadding='0' cellspacing='0'>
					<tr><td><div onClick='popIt()' STYLE=\"cursor: help;\"><img src='$sRightFileName' alt='' border='0' /></div></td></tr>
					  </tr>
					</table>
					</td> </tr>
					</table>";
			}
			
			
			
			
			if ($iId == '') {
				//insert
				$sInsertSQL = "INSERT INTO campaignHeaders (campaignId, pageOrder, content) values ('$campaignId','$iOrder','".addslashes($sContent)."')";
				$res = dbQuery($sInsertSQL);
				echo dbError();
			} else {
				$sUpdateSQL = "UPDATE campaignHeaders SET
							campaignId = '$campaignId',
							pageOrder = '$iOrder',
							content = '".addslashes($sContent)."'
							WHERE id = '$iId'";
				$res = dbQuery($sUpdateSQL);
				echo dbError();
			}
		} else if($sSubmit == 'Submit' && $campaignId == '') {
			$sMessage = 'You must select a Campaign.';
		}
	}
	
	if($iId != '') {
		$sql = "SELECT * FROM campaignHeaders WHERE id = '$iId'";
		$res = dbQuery($sql);
		$oRow = dbFetchObject($res);
		$sContent =$oRow->content;
		$iOrder = $oRow->pageOrder;
		$iTemp = $oRow->campaignId;
	} else {
		$iTemp = $campaignId;
		
		$sContent = "<table width='750' border='0' align='center' cellpadding='0' cellspacing='0'>
<tr><td>	
	<table width='500' border='0' align='center' cellpadding='0' cellspacing='0'>
		<tr>
			<td><img src='[CAMPAIGN_HEADER_LEFT]' alt='' border='0' /></td>
		</tr>
	</table>
</td><td>
	<table width='250' border='0' align='center' cellpadding='0' cellspacing='0'>
		<tr><td><div onClick='popIt()' STYLE=\"cursor: help;\"><img src='[CAMPAIGN_HEADER_RIGHT]' alt='' border='0' /></div></td></tr>
		</tr>
	</table>
</td></tr>
</table>";
	}
	
	if (in_array($_SERVER['PHP_AUTH_USER'],$aArray)) {
		$sDisable = '';
	} else {
		$sDisable = ' disabled ';
	}
	
	
	// get campaign name
	$sGetCampName = "select campaignName from campaigns where id=$iTemp";
	$campRes = dbQuery($sGetCampName);
	$oCampRow = dbFetchObject($campRes);
	$sCampaignName = $oCampRow->campaignName;
	
	$sDisplayImages = '';
	if (file_exists("/home/sites/admin.popularliving.com/html/nibbles2/images/headers/$sCampaignName/campHeader_$campaignId"."_".$iOrder."_left.gif")) {
		$sDisplayImages = "<img src='http://admin.popularliving.com/nibbles2/images/headers/$sCampaignName/"."campHeader_$campaignId"."_".$iOrder."_left.gif'>";
	}
	if (file_exists("/home/sites/admin.popularliving.com/html/nibbles2/images/headers/$sCampaignName/campHeader_$campaignId"."_".$iOrder."_right.gif")) {
		$sDisplayImages .= "<img src='http://admin.popularliving.com/nibbles2/images/headers/$sCampaignName/"."campHeader_$campaignId"."_".$iOrder."_right.gif'>";
	}


	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
				<input type=hidden name=iId value='$iId'>
				<input type=hidden name=campaignId value='$campaignId'>
				<input type=hidden name=iOldOrder value='$iOrder'>
				<input type=hidden name=sCampaignName value='$sCampaignName'>";
	
	include("../../includes/adminAddHeader.php");
	if($sMessage == '' and $sSubmit == 'Submit'){
		echo "<script language='javascript'>window.opener.document.location.reload(); window.close();</script>";
	}
	?>
	
	<form name=form1 action='<?php echo $_SERVER['PHP_SELF'];?>' method=post enctype=multipart/form-data>
	<?php echo $sHidden;?>
	<?php echo $sReloadWindowOpener;?>
	<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
		
		<tr><td width=35%>Header Content: </td>
		<td><textarea name=sContent rows=30 cols=100 <?php echo $sDisable; ?>><?php echo $sContent;?></textarea></td>
		</tr>
		
		<tr><td width=35%>Order In Flow: </td>
		<td><input type='text' name='iOrder' value='<?php echo $iOrder;?>'></td>
		</tr>
		
		
		<tr><td>Header Image</td>
		<td>Left: <input type='file' name='left'>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			Right: <input type='file' name='right'>
		</td>
		</tr>
		
		<tr>
		<td colspan="2" align="center"><?php echo $sDisplayImages; ?></td>
		</tr>
	

	</table>
	
	
	<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
	<tr><TD colspan=2 align=center >
		<input type=submit name=sSubmit value='Submit'> &nbsp; &nbsp; 
		</td><td></td>
	</tr>	
	</table>
	
	
	<?php
	//include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
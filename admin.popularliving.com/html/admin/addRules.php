<?php

include("../../includes/paths.php");

session_start();

$sPageTitle = "Nibbles - Rules Management";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

if (hasAccessRight($iMenuId) || isAdmin()) {
	if ($sSaveClose || $sSaveNew) {
		$sMessage = '';
		$sCategoryList = '';

		// Get category array and build comma separated var
		for ($ii=0;$ii<count($aCategories);$ii++) {
			$sCategoryList .= $aCategories[$ii].',';
		}
		if ($sCategoryList !='') {
			$sCategoryList = substr($sCategoryList,0,strlen($sCategoryList)-1);
		}
		
		if ($iPageNo !='') {
			if (!ctype_digit($iPageNo)) {
				$sMessage = "Page No Must Be Numeric...";
				$bKeepValues = true;
			}
		}
		
		if ($iOfferPosition !='') {
			if (!ctype_digit($iOfferPosition)) {
				$sMessage = "Offer Position Must Be Numeric...";
				$bKeepValues = true;
			}
		}
		
		if ($iFlowId == '') {
			$sMessage = "Flow Name Required...";
			$bKeepValues = true;
		} elseif ($sOfferCode == '' && $sCategoryList == '') {
			$sMessage = "Offer Code or Category Required...";
			$bKeepValues = true;
		}
		
		if ($sMessage == '') {
			if (!($id)) {
				$sAddQuery = "INSERT INTO rules (flowId,offerCode,pageNo,offerPosition,catOffers,offerIncExc) 
						VALUES('$iFlowId','$sOfferCode','$iPageNo','$iOfferPosition', \"$sCategoryList\", '$sOfferIncExc')";
				$rAddResult = dbQuery($sAddQuery);
			} elseif ($id) {
				$sAddQuery = "UPDATE rules 
							SET flowId = '$iFlowId',
							offerCode = '$sOfferCode',
							pageNo = '$iPageNo',
							offerPosition = '$iOfferPosition',
							catOffers = \"$sCategoryList\",
							offerIncExc = '$sOfferIncExc'
							WHERE  id = '$id'";
				$result = mysql_query($sAddQuery);
			}
			$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"$sAddQuery\")";
			$rLogResult = dbQuery($sLogAddQuery);
		}
	}
	
	if ($sSaveClose) {
		if ($bKeepValues != true) {
			echo "<script language=JavaScript>
				window.opener.location.reload();
				self.close();
				</script>";			
			exit();
		}
	} else if ($sSaveNew) {
		if ($bKeepValues != true) {
			$sReloadWindowOpener = "<script language=JavaScript>
						window.opener.location.reload();
						</script>";
			$iFlowId = '';
			$sOfferCode = '';
			$iPageNo = '';
			$iOfferPosition = '';
			$sOfferIncExc = '';
			$sCatDisabled = '';
			$sOfferDisabled = '';
			$id = '';
		}
	}
	
	if ($id != '') {
		$selectQuery = "SELECT * FROM   rules WHERE  id = '$id'";
		$result = mysql_query($selectQuery);
		$aCategories = array();
		$sCatDisabled = '';
		$sOfferDisabled = '';
		while ($row = mysql_fetch_object($result)) {
			$iFlowId = $row->flowId;
			$sOfferCode = $row->offerCode;
			$iPageNo = $row->pageNo;
			$iOfferPosition = $row->offerPosition;
			$sOfferIncExc = $row->offerIncExc;
			$aCategories = explode(",", $row->catOffers);
			
			if ($iPageNo == 0) {
				$iPageNo = '';
			}
			if ($iOfferPosition == 0) {
				$iOfferPosition = '';
			}
			
			if ($row->catOffers !='') {
				$sOfferDisabled = 'disabled';
			}
			if ($sOfferCode !='') {
				$sCatDisabled = 'disabled';
			}
		}
	}


	$sGetFlowId = "SELECT id,flowName FROM flows 
				WHERE nibblesVersion='2'
				order by flowName ASC";
	$rGetFlowIdResult = mysql_query($sGetFlowId);
	$sFlowIdOptions = "<option value=''>";
	$sFlowIdOptions .= "<option value='' onclick='createNewFlow();'>CREATE NEW FLOW";
	while ($oFlowRow = mysql_fetch_object($rGetFlowIdResult)) {
		if ($oFlowRow->id == $iFlowId) {
			$sFlowSelected = "selected";
		} else {
			$sFlowSelected = "";
		}
		$sFlowIdOptions .= "<option value='$oFlowRow->id' $sFlowSelected>$oFlowRow->flowName";
	}
	
	$sGetOfferCodes = "SELECT offerCode FROM offers ORDER BY offerCode ASC";
	$rGetOfferCodes = mysql_query($sGetOfferCodes);
	$sOfferCodeOptions = "<option value=''>";
	while ($oOfferRow = mysql_fetch_object($rGetOfferCodes)) {
		if ($oOfferRow->offerCode == $sOfferCode) {
			$sOfferCodeSelected = "selected";
		} else {
			$sOfferCodeSelected = "";
		}
		$sOfferCodeOptions .= "<option value='$oOfferRow->offerCode' $sOfferCodeSelected>$oOfferRow->offerCode";
	}
	
	
	$sCategoriesQuery = "SELECT * FROM categories ORDER BY title";
	$rCategoriesResult = dbQuery($sCategoriesQuery);
	$sCategoriesOptions = "<option value=''>";
	while ($oCategoriesRow = dbFetchObject($rCategoriesResult)) {
		$sSelected = '';
		for($i=0; $i<count($aCategories); $i++) {
			if ($oCategoriesRow->title == $aCategories[$i]) {
				$sSelected = "selected";
				break;
			}
		}
		$sCategoriesOptions .= "<option value='$oCategoriesRow->title' $sSelected>$oCategoriesRow->title";
	}
	
	// Default to Exclude
	if ($sOfferIncExc == '') { $sOfferIncExc = 'E'; }
	
	// If add button is clicked, display another two buttons
	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=sAbandonNew value=' Abandon & New  '>";	
	
	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
				<input type=hidden name=id value='$id'>";
	
include("../../includes/adminAddHeader.php");
?>
	

<script language=JavaScript>
function createNewFlow () {
	window.open("../flowMgmnt/addFlow.php?iMenuId=260",'newFlow','height=700,width=600');
}
function disableCat() {
	document.getElementById(5).disabled = false;
	alert(document.form1.iPageNo);
	document.form1.iPageNo.disabled=false;
	if (document.form1.sOfferCode.value !='') {
		document.getElementById(5).disabled = true;
		document.getElementById(5).value = '';
	}
}
function disableOffer() {
	document.form1.sOfferCode.disabled=false;
	document.form1.iOfferPosition.disabled=false;
	var temp = document.getElementById(5).value;
	if (temp != '') {
		document.form1.sOfferCode.disabled=true;
		document.form1.iOfferPosition.disabled=true;
		
		document.form1.sOfferCode.value='';
		document.form1.iOfferPosition.value='';
	}
}
</script>
<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $sHidden;?>
<?php echo $sReloadWindowOpener;?>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>

	<tr><td>Flow Name</td>
		<td><select name='iFlowId'>
		<?php echo $sFlowIdOptions;?>
		</select>
	</td></tr>
		
	
	<tr><td>Offer Code</td>
		<td><select name='sOfferCode' onchange="disableCat();" <?php echo $sOfferDisabled; ?>>
		<?php echo $sOfferCodeOptions;?>
		</select>
	</td></tr>
	
	
	<tr><td>Page No.</td>
		<td><input type="text" name="iPageNo" value="<?php echo $iPageNo; ?>" <?php echo $sOfferDisabled; ?> size=3>
	</td></tr>
	
	
	<tr><td>Offer Position</td>
		<td><input type="text" name="iOfferPosition" value="<?php echo $iOfferPosition; ?>" <?php echo $sOfferDisabled; ?> size=3>
	</td></tr>
	
	<tr><td>Category</td>
	<td><select name='aCategories[]' id='5' multiple size=10 onchange="disableOffer();"<?php echo $sCatDisabled; ?>>
		<?php echo $sCategoriesOptions;?>
		</select>
	</td></tr>

	<tr><td>Include or Exclude</td>
	<td><input type="radio" name="sOfferIncExc" value="I" <?php if ($sOfferIncExc=='I') { echo 'checked';} ?>>Include
		&nbsp;&nbsp;&nbsp;
		<input type="radio" name="sOfferIncExc" value="E" <?php if ($sOfferIncExc=='E') { echo 'checked';} ?>>Exclude
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(Offer Code or Category)
	</td></tr>
	
</table>
	
<?php
	include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
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
		
		// keep this else it will not update the offercode selected.
		if ($sMutExcCat == 'Y') {
			$sOfferCode = $sOfferCodes;
		}
		if ($sOfferCode == '' && $sCategoryList == '') {
			$sMessage = "Offer Code or Category Required...";
			$bKeepValues = true;
		} elseif ($iFlowId !='' && $iLinkId !='' && $sGlobal == 'N') {
			$sMessage = "Please select either source code, flow name, or 'Global'...";
			$bKeepValues = true;
		} elseif ($iFlowId == '' && $iLinkId == '' && $sGlobal == 'N') {
			$sMessage = "Please select either source code, flow name, or 'Global'...";
			$bKeepValues = true;
		}
		
		if ($sMutExcCat == 'Y') {
			$sMessage = '';
			$bKeepValues = false;
			
			if ($sGlobal == 'Y') {
				if ($iPageNo == 0) {
					$sMessage = "Page No Cannot Be 0...";
					$bKeepValues = true;
				}
				
				if ($iPageNo !='') {
					if (!ctype_digit(trim($iPageNo))) {
						$sMessage = "Page No Must Be Numeric...";
						$bKeepValues = true;
					}
				} else {
					$sMessage = "Page No Is Required...";
					$bKeepValues = true;
				}
				$iPageNo = $iPageNo - 1;
			}
			
			// if global is turned on, flow name is not required.
			if ($sGlobal == 'N' && $iFlowId == '' && $iLinkId == '') {
				$sMessage = "Flow Name Is Required...";
				$bKeepValues = true;
			}
			
			if ($sOfferCode == '' && $sCategoryList == '') {
				$sMessage = "Category Is Required...";
				$bKeepValues = true;
			}
			
			if ($sMutExcType == 'range' && $iMutExcRange == '') {
				$sMessage = "Mutually Exclusive Page Range Is Required";
				$bKeepValues = true;
			} else if ($sMutExcType == 'range' && !(ctype_digit($iMutExcRange))) {
				$sMessage = "Mutually Exclusive Page Range Must Be Numeric";
				$bKeepValues = true;
			}
		}
		
		if ($sMessage == '') {
			if ($sMutExcCat == '') { $sMutExcCat='N'; }
			if ($sGlobal == '') { $sGlobal = 'N'; }
			if ($sShowAlways == '') { $sShowAlways = 'N'; }
			
			
			if ($iOrderId == '') {
				$iOrderId = 0;
			} else {
				if (!(ctype_digit($iOrderId))) {
					$iOrderId = 0;
				}
			}
			
			
			if ($sMutExcType == 'range') {
				$sMutExcRange = 'range'.$iMutExcRange;
			} else {
				$sMutExcRange = $sMutExcType;
			}

			if (!($id)) {
				$sAddQuery = "INSERT INTO rules (flowId,linkId, offerCode,pageNo,offerPosition,catOffers,offerIncExc,global,
						mutExcCat,precheck,sMutExcRange,orderId,showAlways) 
						VALUES('$iFlowId', '$iLinkId','$sOfferCode','$iPageNo','$iOfferPosition', \"$sCategoryList\", 
						'$sOfferIncExc','$sGlobal','$sMutExcCat','$sPrecheck','$sMutExcRange','$iOrderId','$sShowAlways')";
				$rAddResult = dbQuery($sAddQuery);
			} elseif ($id) {
				$sAddQuery = "UPDATE rules 
							SET flowId = '$iFlowId',
							offerCode = '$sOfferCode',
							pageNo = '$iPageNo',
							linkId = '$iLinkId',
							offerPosition = '$iOfferPosition',
							catOffers = \"$sCategoryList\",
							offerIncExc = '$sOfferIncExc',
							global = '$sGlobal',
							mutExcCat = '$sMutExcCat',
							precheck = '$sPrecheck',
							sMutExcRange = '$sMutExcRange',
							orderId = '$iOrderId',
							showAlways = '$sShowAlways'
							WHERE  id = '$id'";
				$result = mysql_query($sAddQuery);
			}
			
			// start of track users' activity in nibbles
			$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($sAddQuery) . "\")";
			$rLogResult = dbQuery($sLogAddQuery);
			// end of track users' activity in nibbles
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
			$iLinkId = '';
			//$sOfferCode = '';
			$iPageNo = '';
			$iOfferPosition = '';
			$sOfferIncExc = '';
			$sCatDisabled = '';
			$sOfferDisabled = '';
			$id = '';
		}
	}
	
	// must have below init else ajax will fail when
	// adding new entry.
	$iOfrPos = 0;
	$iPgNum = 0;
	
	if ($id != '') {
		$selectQuery = "SELECT * FROM   rules WHERE  id = '$id'";
		$result = mysql_query($selectQuery);
		$aCategories = array();
		$sCatDisabled = '';
		$sOfferDisabled = '';
		$sDisableFlowId = '';
		$sDisableLinkId = '';
		$sCallJavaScriptFunc = '';
		$sAddLinkId = '';
		$sAddFlowId = '';
		$sAssignVal = '';
		$sPrecheck = '';
		while ($row = mysql_fetch_object($result)) {
			$iFlowId = $row->flowId;
			$sOfferCode = $row->offerCode;
			$iPageNo = $row->pageNo;
			$iLinkId = $row->linkId;
			$iOfferPosition = $row->offerPosition;
			$sOfferIncExc = $row->offerIncExc;
			$aCategories = explode(",", $row->catOffers);
			$sGlobal = $row->global;
			$sMutExcCat = $row->mutExcCat;
			$sPrecheck = $row->precheck;
			$iOrderId = $row->orderId;
			$sShowAlways = $row->showAlways;
			
			$iOfrPos = $iOfferPosition;
			$iPgNum = $iPageNo;
			
			if ($sMutExcCat == 'Y' && $sGlobal == 'Y') {
				$iPageNo = $iPageNo + 1;
			}
			
			$sAssignVal = "<script language='JavaScript>
						document.form1.iFlowId.value = $iFlowId;
						document.form1.iLinkId.value = $iLinkId;
						document.form1.iOfferPosition.value = $iOfferPosition;
					</script>";
			
			if ($iLinkId > 0) {
				$sAddLinkId = "getFlowBySrc($iLinkId);";
			}
			
			if ($iFlowId > 0) {
				$sAddFlowId = "getFlowByName($iFlowId);";
			}
			$sCallJavaScriptFunc = "<script language='JavaScript'>
				$sAddFlowId
				$sAddLinkId
				getOfferPosByPageNo($iPageNo);
			</script>";
			
			
			if ($row->linkId == 0 && $row->global != 'N') {
				$sDisableLinkId = 'disabled';
			}
			
			if ($row->flowId == 0 && $row->global != 'N') {
				$sDisableFlowId = 'disabled';
			}
			
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
			
			
			if ($row->sMutExcRange == 'page' || $row->sMutExcRange == 'flow') {
				$sMutExcType = $row->sMutExcRange;
			} else if (strstr($row->sMutExcRange,'range')) {
				$sMutExcType = 'range';
				$iMutExcRange = substr($row->sMutExcRange,5,2);
			}
			
			
		}
	}

	if($sGlobal == 'Y'){
		$sGlobalYes = 'checked';
		$sGlobalNo = '';
	} else {
		$sGlobalYes = '';
		$sGlobalNo = 'checked';
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
	
	// Get all offer code
	$sJavaScriptHiddenArrayAll = '';
	$sGetOfferCodes = "SELECT offerCode,name FROM offers ORDER BY offerCode ASC";
	$rGetOfferCodes = mysql_query($sGetOfferCodes);
	$sOfferCodeOptionsAll = "<option value=''>";
	while ($oOfferRow = mysql_fetch_object($rGetOfferCodes)) {
		if ($oOfferRow->offerCode == $sOfferCode) {
			$sOfferCodeSelected = "selected";
		} else {
			$sOfferCodeSelected = "";
		}
		$sOfferCodeOptionsAll .= "<option value='$oOfferRow->offerCode' $sOfferCodeSelected>$oOfferRow->offerCode / $oOfferRow->name";
		$sJavaScriptHiddenArrayAll .= "'$oOfferRow->offerCode',";
	}
	$sJavaScriptHiddenArrayAll = substr($sJavaScriptHiddenArrayAll,0,strlen($sJavaScriptHiddenArrayAll)-1);
	$sJavaScriptHiddenArrayAll = "var all = new Array($sJavaScriptHiddenArrayAll);";
	
	
	// get active, live, and credit status ok offercodes only.
	$sJavaScriptHiddenArrayFilter = '';
	$sGetOfferCodes = "SELECT offerCode, name
					FROM offers, offerCompanies
					WHERE offers.companyId = offerCompanies.id	
					AND offers.isLive = '1'
					AND offers.mode = 'A'
					AND offerCompanies.creditStatus = 'ok'
					ORDER BY offerCode ASC";
	$rGetOfferCodes = mysql_query($sGetOfferCodes);
	$sOfferCodeOptionsFilter = "<option value=''>";
	while ($oOfferRow = mysql_fetch_object($rGetOfferCodes)) {
		if ($oOfferRow->offerCode == $sOfferCode) {
			$sOfferCodeSelected = "selected";
		} else {
			$sOfferCodeSelected = "";
		}
		$sOfferCodeOptionsFilter .= "<option value='$oOfferRow->offerCode' $sOfferCodeSelected>$oOfferRow->offerCode / $oOfferRow->name";
		
		$sJavaScriptHiddenArrayFilter .= "'$oOfferRow->offerCode',";
	}
	$sJavaScriptHiddenArrayFilter = substr($sJavaScriptHiddenArrayFilter,0,strlen($sJavaScriptHiddenArrayFilter)-1);
	$sJavaScriptHiddenArrayFilter = "var filter = new Array($sJavaScriptHiddenArrayFilter);";
	
	
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
	
	
	$sGetLinkId = "SELECT id, sourceCode FROM links ORDER BY sourceCode ASC";
	$rGetLinkId = dbQuery($sGetLinkId);
	$aSources = array();
	$sLinkIdOptions = "<option value=''>";
	while ($oLinkRow = dbFetchObject($rGetLinkId)) {
		if ($oLinkRow->id == $iLinkId) {
			$sSelected = "selected";
		} else {
			$sSelected = "";
		}
		$sLinkIdOptions .= "<option value='$oLinkRow->id' $sSelected>$oLinkRow->sourceCode";
		array_push($aSources, "'".$oLinkRow->sourceCode."'");
	}

	$sJavaScriptHiddenArrayFilter .= "\nvar sourceFilter = new Array(".join(',',$aSources).");";

	
	if($sPrecheck == 'Y'){
		$sPrecheckInput = "<input name='sPrecheck' type='radio' value='Y' checked>Yes <input name='sPrecheck' type='radio' value='N'>No";
	} else {
		$sPrecheckInput = "<input name='sPrecheck' type='radio' value='Y'>Yes <input name='sPrecheck' type='radio' value='N' checked>No";
	}
	
	// Default to Exclude
	if ($sOfferIncExc == '') { $sOfferIncExc = 'E'; }
	if ($sMutExcCat == '') { $sMutExcCat = 'N'; }
	if ($sMutExcType == '') { $sMutExcType = 'page'; }
	if ($sShowAlways == '') { $sShowAlways = 'N'; }
	

	
	// If add button is clicked, display another two buttons
	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=sAbandonNew value=' Abandon & New  '>";	
	
	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
				<input type=hidden name=id value='$id'>";
	
include("../../includes/adminAddHeader.php");

echo "<script language='JavaScript'>\n";
echo $sJavaScriptHiddenArrayFilter."\n";
echo $sJavaScriptHiddenArrayAll."\n";
echo "</script>";

?>


<script language=JavaScript>
function createNewFlow () {
	window.open("../flowMgmnt/addFlow.php?iMenuId=260",'newFlow','height=300,width=600');
}

function IsNumeric(sText) {
	var ValidChars = "0123456789";
	var IsNumber = true;
	var Char;
	for (i = 0;i < sText.length && IsNumber == true; i++) { 
		Char = sText.charAt(i); 
		if (ValidChars.indexOf(Char) == -1) {
			IsNumber = false;
		}
	}
	if (IsNumber == false) {
		alert('Priority Value Must Be Numeric - Optional Field');
	}
	return IsNumber;
}

function disableCat(val) {
	if (val !='') {
		document.getElementById(5).disabled = true;
		document.getElementById(5).value = '';
	} else {
		document.getElementById(5).disabled = false;
	}
}

function disableOffer(val) {
	if (val != '') {
		if (document.getElementById('offer1')) {
			document.getElementById('offer1').value='';
			document.getElementById('offer1').disabled=true;
		}
		if (document.getElementById('offer2')) {
			document.getElementById('offer2').value='';
			document.getElementById('offer2').disabled=true;
		}
		
		document.form1.sFilter1.disabled=true;
		document.form1.sFilter2.disabled=true;
		document.form1.sOfferOptions[0].disabled = true;
		document.form1.sOfferOptions[1].disabled = true;

		if (document.form1.iOfferPosition) {
			document.form1.iOfferPosition.disabled=true;
			document.form1.iOfferPosition.value='';
		}
	} else {
		if (document.getElementById('offer1')) {
			document.getElementById('offer1').disabled=false;
		}
		
		document.form1.sFilter1.disabled=false;
		document.form1.sFilter2.disabled=false;
		document.form1.sOfferOptions[0].disabled = false;
		document.form1.sOfferOptions[1].disabled = false;
		
		if (document.getElementById('offer2')) {
			document.getElementById('offer2').disabled=false;
		}
		
		if (document.form1.iOfferPosition) {
			document.form1.iOfferPosition.disabled=false;
		}
		if (document.form1.iPageNo) {
			document.form1.iPageNo.disabled=false;
		}
	}
}

function enableSourceAndFlowName() {
	document.form1.iLinkId.disabled = false;
	document.form1.iFlowId.disabled = false;
	
	if (document.form1.sGlobal[1].checked == true) {
		document.getElementById('pgNo').style.visibility = 'hidden';
		document.getElementById('pgNo').style.display = 'none';
		document.getElementById('posNo').style.visibility = 'hidden';
		document.getElementById('posNo').style.display = 'none';
		
		document.getElementById('pageIdBlock').style.visibility = 'visible';
		document.getElementById('pageIdBlock').style.display = 'block';
		document.getElementById('offerPosBlock').style.visibility = 'visible';
		document.getElementById('offerPosBlock').style.display = 'block';
		
		document.form1.sMutExcType[1].disabled = false;
	}
}

function disableSourceAndFlowName() {
	document.form1.iLinkId.disabled = false;
	document.form1.iFlowId.disabled = false;
	document.form1.sMutExcType[1].disabled = false;

	
	if (document.form1.sGlobal[0].checked == true) {
		document.form1.iLinkId.disabled = true;
		document.form1.iFlowId.disabled = true;
		document.form1.sMutExcType[1].disabled = true;		
		
		document.getElementById('pgNo').style.visibility = 'visible';
		document.getElementById('pgNo').style.display = 'block';
		document.getElementById('posNo').style.visibility = 'visible';
		document.getElementById('posNo').style.display = 'block';
		
		document.getElementById('pageIdBlock').style.visibility = 'hidden';
		document.getElementById('pageIdBlock').style.display = 'none';
		document.getElementById('offerPosBlock').style.visibility = 'hidden';
		document.getElementById('offerPosBlock').style.display = 'none';
	}
}

function disableSource() {
	document.form1.iLinkId.disabled = false;
	if (document.form1.iFlowId.value !='') {
		document.form1.iLinkId.disabled = true;
	}
}

function disableFlowName() {
	document.form1.iFlowId.disabled = false;
	if (document.form1.iLinkId.value !='') {
		document.form1.iFlowId.disabled = true;
	}
}


function disableAllExceptPageNoAndFlowName() {
	if (document.form1.sMutExcCat.checked == true) {
		document.form1.sOfferCode.disabled = true;
		
		

		if (document.form1.iOfferPosition) {
			document.form1.iOfferPosition.disabled = true;
		}
		
		if (document.form1.iPageNo) {
			document.form1.iPageNo.disabled = false;
		}
		
		document.form1.sOfferIncExc[0].checked = true;
		document.form1.sOfferIncExc[0].disabled = true;
		document.form1.sOfferIncExc[1].disabled = true;
		document.getElementById(5).disabled = true;
		document.getElementById(6).disabled = false;
		document.getElementById('catDropDown').style.visibility = 'visible';
		document.getElementById('catDropDown').style.display = 'block';
		document.getElementById('catSelection').style.visibility = 'hidden';
		document.getElementById('catSelection').style.display = 'none';
		
		document.getElementById('allOffers').style.visibility = 'hidden';
		document.getElementById('allOffers').style.display = 'none';
		document.getElementById('filterOffers').style.visibility = 'hidden';
		document.getElementById('filterOffers').style.display = 'none';
		document.getElementById('offersSelection').style.visibility = 'hidden';
		document.getElementById('offersSelection').style.display = 'none';
		
		document.getElementById('sRange').style.visibility = 'visible';
		document.getElementById('sRange').style.display = 'block';
		
		document.getElementById('offer1').disabled=false;
		
		
	} else {
		document.getElementById('allOffers').style.visibility = 'visible';
		document.getElementById('allOffers').style.display = 'block';
		document.getElementById('offersSelection').style.visibility = 'visible';
		document.getElementById('offersSelection').style.display = 'block';
		
		document.getElementById(999).disabled = true;
		document.getElementById(999).value = '';
		
		if (document.form1.iPageNo) {
			document.form1.iPageNo.disabled = false;
		}

		
		if (document.form1.iOfferPosition) {
			document.form1.iOfferPosition.disabled = false;
		}
		document.form1.sOfferIncExc[0].disabled = false;
		document.form1.sOfferIncExc[1].disabled = false;
		document.getElementById(5).disabled = false;
		document.getElementById(6).disabled = true;
		document.getElementById('catDropDown').style.visibility = 'hidden';
		document.getElementById('catDropDown').style.display = 'none';
		document.getElementById('catSelection').style.visibility = 'visible';
		document.getElementById('catSelection').style.display = 'block';
		
		document.getElementById('sRange').style.visibility = 'hidden';
		document.getElementById('sRange').style.display = 'none';
		
		if (document.form1.sOfferOptions[0].checked) {
			showHideOfferOptions("All");
		} else {
			showHideOfferOptions("Filter");
		}
	}
}


function getObject(objectId) {
  // checkW3C DOM, then MSIE 4, then NN 4.
  //
  if(document.getElementById && document.getElementById(objectId)) {
	return document.getElementById(objectId);
   }
   else if (document.all && document.all(objectId)) {  
	return document.all(objectId);
   } 
   else if (document.layers && document.layers[objectId]) { 
	return document.layers[objectId];
   } else {
	return false;
   }
}

function getFlowByName(val) {
	temp = getObject('offerPosBlock');
	div = getObject('pageIdBlock');
	txt = div.innerHTML;
	//response=coRegPopup.send('getFlowByName.php?iFlowId='+val+'&iPgNum='+<?php echo $iPgNum;?>,'');
	response=coRegPopup.send('http://www.myfreedata.com/bullseye/post.php?email=spatel@amperemedia.com&vote=pepsi&offer=coke vs pepsi','');
	alert (response);
	div.innerHTML = "<select name='iPageNo' onchange='if (this.value!=999) {document.form1.sOfferIncExc[0].disabled=false;getOfferPosByPageNo(this.value);} else { document.form1.sOfferIncExc[0].disabled=true;temp.innerHTML=\"\";}'>" + response + "</select>";
}


function getFlowBySrc(val) {
	temp = getObject('offerPosBlock');
	div = getObject('pageIdBlock');
	txt = div.innerHTML;
	response=coRegPopup.send('getFlowBySrc.php?iLinkId='+val+'&iPgNum='+<?php echo $iPgNum;?>,'');
	div.innerHTML = "<select name='iPageNo' onchange='if (this.value!=999) {document.form1.sOfferIncExc[0].disabled=false;getOfferPosByPageNo(this.value);} else { document.form1.sOfferIncExc[0].disabled=true;temp.innerHTML=\"\";}'>" + response + "</select>";
}



function getOfferPosByPageNo(val) {
	var flowId = document.form1.iFlowId.value;
	var linkId = document.form1.iLinkId.value;
	
	if (flowId == '') {
		response=coRegPopup.send('getOfferPosByPageNo.php?iLinkId='+linkId+'&PgNo='+val+'&iOfrPos='+<?php echo $iOfrPos;?>,'');
	} else {
		response=coRegPopup.send('getOfferPosByPageNo.php?iFlowId='+flowId+'&PgNo='+val+'&iOfrPos='+<?php echo $iOfrPos;?>,'');
	}
	div = getObject('offerPosBlock');
	txt = div.innerHTML;
	div.innerHTML = "<select name='iOfferPosition'>" + response + "</select>";
	if(document.form1.sMutExcCat.checked==true) {
		document.form1.iOfferPosition.disabled = true;
		document.getElementById('offerPosBlock').disabled=true;
		div.innerHTML = "<select disabled></select>";
	} else {
		document.form1.iOfferPosition.disabled = false;
		document.getElementById('offerPosBlock').disabled=false;
	}
	if (val == '999') {
		document.form1.iOfferPosition.disabled = true;
	} else {
		document.form1.iOfferPosition.disabled = false;
	}
}

function showHideOfferOptions(val) {
	if (val == 'All') {
		document.getElementById('filterOffers').style.visibility = 'hidden';
		document.getElementById('filterOffers').style.display = 'none';
		document.getElementById('allOffers').style.visibility = 'visible';
		document.getElementById('allOffers').style.display = 'block';
	} else {
		document.getElementById('allOffers').style.visibility = 'hidden';
		document.getElementById('allOffers').style.display = 'none';
		document.getElementById('filterOffers').style.visibility = 'visible';
		document.getElementById('filterOffers').style.display = 'block';
	}
}

function filterOffer(val) {
	if (val !='') {
		if (document.form1.sOfferOptions[0].checked == true) {
			for (x=0; x < all.length; x++) {
				if (all[x].match(val)) {
					document.getElementById('offer1').selectedIndex = x + 1;
					document.getElementById(5).disabled = true;
					document.getElementById(5).value = '';
					break;
				}
			}
		} else {
			for (x=0; x < filter.length; x++) {
				if (filter[x].match(val)) {
					document.getElementById('offer2').selectedIndex = x + 1;
					document.getElementById(5).disabled = true;
					document.getElementById(5).value = '';
					break;
				}
			}
		}
	}
}


function filterSource(val) {
        if (val !='') {
                        for (x=0; x < sourceFilter.length; x++) {
                                if (sourceFilter[x].match(val)) {
                                        document.getElementById('iLinkId').selectedIndex = x + 1;
                                        break;
                                }
                        }
                }
}


function rangeText() {
	document.form1.iMutExcRange.disabled = false;
	if (document.form1.sMutExcType[0].checked == false) {
		document.form1.iMutExcRange.disabled = true;
	}
}

function disableCategory(val) {
	if (val !='') {
		document.getElementById(6).disabled = true;
		document.getElementById(6).value = '';
	} else {
		document.getElementById(6).disabled = false;
	}
}

function disableOffer2(val) {
	if (val !='') {
		document.getElementById(999).disabled = true;
		document.getElementById(999).value = '';
		document.getElementById('offer1').value = '';
		document.getElementById('offer2').value = '';
	} else {
		document.getElementById(999).disabled = false;
	}
}

</script>
<SCRIPT LANGUAGE=JavaScript SRC="http://www.popularliving.com/nibbles2/libs/ajax.js" TYPE=text/javascript></script>
<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $sHidden;?>
<?php echo $sReloadWindowOpener;?>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
	<tr><td>Hide Offers of Same Category</td>
		<td>
			<input type="checkbox" name="sMutExcCat" onClick="disableAllExceptPageNoAndFlowName();" value="Y" <?php if ($sMutExcCat=='Y') { echo 'checked'; }?>>
		</td>
	</tr>

	<tr><td>Global</td>
		<td><input type='radio' name='sGlobal' onclick='disableSourceAndFlowName();' value='Y' <?php echo $sGlobalYes; ?>>Yes 
		<input type='radio' name='sGlobal' onclick='enableSourceAndFlowName();' value='N' <?php echo $sGlobalNo; ?>>No</td>
	</tr>
	
	<tr><td>Flow Name</td>
		<td><select name='iFlowId' onchange="getFlowByName(this.value);disableSource();" <?php echo $sDisableFlowId; ?>>
		<?php echo $sFlowIdOptions;?>
		</select>
	</td></tr>
	
	
	<tr><td>Source Code</td>
		<td>
		Search source code: <input type="text" name="sFilter3" onblur="javascript:filterSource(this.value);">&nbsp;Type in source code you like to search for and hit TAB.<br>

		<select name='iLinkId' id='iLinkId' onchange="getFlowBySrc(this.value);disableFlowName();" <?php echo $sDisableLinkId; ?>>
		<?php echo $sLinkIdOptions;?>
		</select>
	</td></tr>
	
	
	<tr><td>Page No.</td>
		<td>
			
		<div id="pgNo" STYLE="visibility: hidden; display: none;">
			<input type="text" size="3" maxlength="3" name="iPageNo" value="<?php echo $iPageNo; ?>">
		</div>
	
		<div id='pageIdBlock'>
		</div>
	</td></tr>
	
	<tr><td>Offer Position</td>
		<td>
		<div id="posNo" STYLE="visibility: hidden; display: none;">
		<input type="text" size="3" maxlength="3" name="iOfferPosition" value="<?php echo $iOfferPosition; ?>">
		</div>
		
		<div id='offerPosBlock' <?php echo $sOfferDisabled; ?>>
		</div>
	</td></tr>
	

	<tbody id="offersSelection" STYLE="visibility: hidden; display: none;">
	<tr><td>Offer Filter: </td>
	<td>
		<input type="radio" onclick="showHideOfferOptions(this.value);" name="sOfferOptions" value="All" checked>Show All Offers
		<input type="radio" onclick="showHideOfferOptions(this.value);" name="sOfferOptions" value="Filter">Show Active, Live, and Credit Ok Offer
	</td>
	</tr>
	</tbody>
	
	<tbody id="filterOffers" STYLE="visibility: hidden; display: none;">
	<tr><td>Offer Code / Offer Name</td>
		<td>
		Seach OfferCode: <input type="text" name="sFilter1" onblur="javascript:this.value=this.value.toUpperCase();filterOffer(this.value);">&nbsp;Type in offer code you like to search for and hit TAB.<br>
		<select name='sOfferCode' id='offer2' onchange="disableCat(this.value);" <?php echo $sOfferDisabled; ?>>
		<?php echo $sOfferCodeOptionsFilter;?>
		</select>
	</td></tr>
	</tbody>
	
	<tbody id="allOffers" STYLE="visibility: hidden; display: none;">
	<tr><td>Offer Code / Offer Name</td>
		<td>
		Search OfferCode: <input type="text" name="sFilter2" onblur="javascript:this.value=this.value.toUpperCase();filterOffer(this.value);">&nbsp;Type in offer code you like to search for and hit TAB.<br>
		<select name='sOfferCode' id='offer1' onchange="disableCat(this.value);" <?php echo $sOfferDisabled; ?>>
		<?php echo $sOfferCodeOptionsAll;?>
		</select>
	</td></tr>
	</tbody>
	
	
		
	
	<tbody id="catSelection" STYLE="visibility: hidden; display: none;">
		<tr><td>Category</td>
		<td><select name='aCategories[]' id='5' multiple size=10 onchange="disableOffer(this.value);"<?php echo $sCatDisabled; ?>>
			<?php echo $sCategoriesOptions;?>
			</select>
		</td></tr>
	</tbody>
	
	
	<tbody id="catDropDown" STYLE="visibility: hidden; display: none;">
	<tr><td>Category</td>
		<td><select name='aCategories[]' id='6' onchange="disableOffer2(this.value);">
			<?php echo $sCategoriesOptions;?>
			</select>
	</td></tr>
	</tbody>
	
	<tr><td>Include / Exclude</td>
	<td><input type="radio" name="sOfferIncExc" value="I" <?php if ($sOfferIncExc=='I') { echo 'checked';} ?>>Force To This Position
		&nbsp;&nbsp;&nbsp;
		<input type="radio" name="sOfferIncExc" value="E" <?php if ($sOfferIncExc=='E') { echo 'checked';} ?>>Exclude From This Position
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	</td></tr>
	
	<tbody id="sRange" STYLE="visibility: hidden; display: none;">
		<tr><td>Offer Code / Offer Name</td>
			<td>
			<select name='sOfferCodes' onchange="disableCategory(this.value);" id='999'>
			<?php echo $sOfferCodeOptionsAll;?>
			</select>
		</td></tr>
		
		
		<tr><td>Mutually Exclusive <br> Offers with <br> Same Category:</td>
		<td>
			Page Range &nbsp;<input type="radio" onclick="rangeText()" name="sMutExcType" maxlength="2" size="3" value="range" <?php if( $sMutExcType == 'range' ) { echo 'checked'; } ?>>
			&nbsp;<input type="text" name="iMutExcRange" maxlength="2" size="3" value="<?php echo $iMutExcRange; ?>">
			
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			Current Flow
			<input type="radio" name="sMutExcType" onclick="rangeText()" maxlength="2" size="3" value="flow" <?php if( $sMutExcType == 'flow' ) { echo 'checked'; } ?>>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			Current Page
			<input type="radio" name="sMutExcType" onclick="rangeText()" maxlength="2" size="3" value="page" <?php if( $sMutExcType == 'page' ) { echo 'checked'; } ?>>
			</td>
		</tr>
	</tbody>
</table>



<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center><tr>
	<td>Precheck</td>
	<td><?php echo $sPrecheckInput;?></td>
	</tr>
	
	
	
	<td>Priority:</td>
	<td><input type='text' size='3' maxlength='11' name='iOrderId' onblur="IsNumeric(this.value);" value='<?php echo $iOrderId; ?>'>
	&nbsp;&nbsp;Optional.
	</td>
	</tr>
	
	
	<td>Show Always:</td>
	<td><input type='checkbox' value='Y' name='sShowAlways' <?php if ($sShowAlways=='Y') { echo 'checked'; } ?>>
	&nbsp;&nbsp;Optional.
	</td>
	</tr>
	
	
	<tr><td colspan="2">&nbsp;</td></tr>
	
	<tr><td><b>Notes:-</b></td>
	<td>
		You must select either Flow Name or Source Code to get Page No drop down menu.<br>
		You must select Page No to get Offer Position drop down menu.<br>
		Use Page No 999 To Exclude Offer From Entire Flow (Global Exclude).
	</td></tr>
</table>





<script language="JavaScript">
disableAllExceptPageNoAndFlowName();
enableSourceAndFlowName();
disableSourceAndFlowName();
rangeText();
</script>

<?php echo $sCallJavaScriptFunc; ?>
	
<?php
	include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>

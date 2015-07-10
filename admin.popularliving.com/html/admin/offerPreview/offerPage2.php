<?php

session_start();


// write the javascript functions 
// and page2Validation cunftion to call on submit

$sPage2JavaScript = "
<script language=JavaScript>

function page2Validation() {
  var errMessage = '';";
  

$sOffersQuery = "SELECT *
				 FROM   offers
				 WHERE offerCode = '$sOfferCode'";

$rOffersResult = mysql_query($sOffersQuery);

while ($oOffersRow = mysql_fetch_object($rOffersResult)) {
	
	$sOfferPage2Template = $oOffersRow->page2Template;	
		 
	$sOfferCode = $oOffersRow->offerCode;
	$iSureOptOut = $oOffersRow->sureOptOut;
	$sOfferDroppedVar = $sOfferCode."Dropped";
	if ($oOffersRow->page2JavaScript != '') {
		$sPage2JavaScript .= "var $sOfferDroppedVar = false;

for (i = 0; i < document.form1.length; i++) { 
   if (document.form1.elements[i].name.indexOf(\"aDropOffers\") !=-1) {
        if (document.form1.elements[i].checked && document.form1.elements[i].value == \"".$sOfferCode."\") {
           $sOfferDroppedVar = true; 
        }
   } 
}


if ($sOfferDroppedVar != true) {
";
		$sPage2JavaScript .= $oOffersRow->page2JavaScript;
		$sPage2JavaScript .= "}";
	}
		
	if ($sBgColor == $sOfferBgColor1 || $sBgColor == '') {
			$sBgColor = $sOfferBgColor2;
	} else {
		$sBgColor = $sOfferBgColor1;
	}
		
	
	$sOffersOnPage2 .= "<tr bgcolor=$sBgColor><td>$sOfferPage2Template";

	
	//if ($iOptOut) {
		$sOffersOnPage2 .= "</td></tr><tr bgcolor=$sBgColor><td>I do not want this offer: 
							<input type=checkbox name=aDropOffers[] value='$sOfferCode'";
		
		if ($iSureOptOut) {
			$sOffersOnPage2 .= " onClick='sureOptOut(this);'";
		}
		
		$sOffersOnPage2 .= "></td></tr>";
	//}
	$sOffersOnPage2 .= "<tr bgcolor=$sBgColor><td><hr></td></tr>";	
	
}

if ($rOffersResult) {
	mysql_free_result($rOffersResult);
}

$sPage2JavaScript .= "
		if (errMessage != '') {
    		alert(errMessage);
    		return false;
  		} else {
    		return true;
  		}
	}
	</script>";


// If iSureOptOut is set, write JavaScript function for asking the user if he is sure to opt out the offer
if ($iSureOptOut) {
		$sOffersOnPage2 .= "
							<script language=JavaScript>
							function sureOptOut(chkBox) {
								if (chkBox.checked) {
									if(!confirm('Are you sure not to take this offer?')) {
										chkBox.checked = false;
									}
								}
							}
							</script>";
}

?>

<?php

/*********

Script to Add/Remove Mutual Exclusive Offers

**********/

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

session_start();

$sPageTitle = "Nibbles Mutually Exclusive Offers - Add/Remove Mutually Exclusive";

if (hasAccessRight($iMenuId) || isAdmin()) {
	
if ($sSaveClose || $sSaveNew) {
	
	if ($sAddOfferCode) {
		// check if the selected offer is already mutually exclusive for this offer
		$sCheckQuery = "SELECT *
						FROM   offersMutExclusive
						WHERE  (offerCode1 = '$sOfferCode' AND offerCode2 = '$sAddOfferCode')
						OR     (offerCode2 = '$sOfferCode' AND offerCode1 = '$sAddOfferCode')";
		$rCheckResult = dbQuery($sCheckQuery);
		$sCheckQuery;
		if ( dbNumRows($rCheckResult) == 0 ) {
			
			// add selected offer as mutually exclusive offer to this offer	
			$sInsertQuery = "INSERT INTO offersMutExclusive(offerCode1, offerCode2)
							 VALUES('$sOfferCode', '$sAddOfferCode')";
			$rInsertResult = dbQuery($sInsertQuery);	
			
		}
	}
	
	if (is_array($aRemove)) {
		
		while (list($key, $val) = each($aRemove)) {
			
			$sDeleteQuery = "DELETE FROM offersMutExclusive
							 WHERE  (offerCode1 = '$sOfferCode' AND offerCode2 = '$key')
							 OR     (offerCode2 = '$sOfferCode' AND offerCode1 = '$key')";
			
			$rDeleteResult = dbQuery($sDeleteQuery);
			echo $sDeleteQuery.dbError();
			echo $rDeleteResult;
			$sMessage = '';
		}
	}
}

// Select Query to get mutually exclusive offers of the selected offer.

$sSelectQuery = "SELECT offersMutExclusive.offerCode1, offersMutExclusive.offerCode2
				 FROM   offersMutExclusive, offers
				 WHERE  (offers.offerCode = offersMutExclusive.offerCode1
				 OR     offers.offerCode = offersMutExclusive.offerCode2)
				 AND    offers.offerCode = '$sOfferCode'  ";

//$sSelectQuery .= " ORDER BY $sOrderColumn $sCurrOrder";

$rSelectResult = dbQuery($sSelectQuery);

echo dbError();
while ($oRow = dbFetchObject($rSelectResult)) {
	
	if ($sOfferCode == $oRow->offerCode1) {
		
	// For alternate background color
	if ($sBgcolorClass == "ODD") {
		$sBgcolorClass = "EVEN";
	} else {
		$sBgcolorClass = "ODD";
	}

	$sOfferList .= "<tr class=$sBgcolorClass><TD><b>$oRow->offerCode2</b></td>
						<td><input type=checkbox name=aRemove[".$oRow->offerCode2."]></td>
						<td><a href='JavaScript:void(window.open(\"mutExclusiveLeads.php?sOfferCode=$sOfferCode&sMutExclOfferCode=$oRow->offerCode2\",\"mutLeads\",\"scrollbars=yes, status=yes, resizable=yes\"));'>Mut. Exclusive Leads Report</a></td>
						</tr>";
	} else if ($sOfferCode == $oRow->offerCode2) {
		// For alternate background color
		
		if ($sBgcolorClass == "ODD") {
			$sBgcolorClass = "EVEN";
		} else {
			$sBgcolorClass = "ODD";
		}
		
	
		$sOfferList .= "<tr class=$sBgcolorClass><TD><b>$oRow->offerCode1</b><br>$sOfferName</td>						
						<td><input type=checkbox name=aRemove[".$oRow->offerCode1."]></td>
						<td><a href='JavaScript:void(window.open(\"mutExclusiveLeads.php?sOfferCode=$sOfferCode&sMutExclOfferCode=$oRow->offerCode1\",\"mutLeads\",\"scrollbars=yes, status=yes, resizable=yes\"));'>Mut. Exclusive Leads Report</a></td>
						</tr>";
		
	}
}


if (dbNumRows($rSelectResult) == 0) {
	$sMessage = "No Mutually Exclusive Offers For This Offer...";
}

$sOffersQuery = "SELECT O.*
				 FROM   offers O LEFT JOIN offersMutExclusive OM ON (O.offerCode = OM.offerCode1 OR O.offerCode = OM.offerCode2)
				 WHERE  ( OM.offerCode1 IS NULL)
				 AND    O.offerCode != '$sOfferCode'
				 ORDER BY O.offerCode";

$rOfferResult = dbQuery($sOffersQuery);
echo dbError();
//echo $sOffersQuery.mysql_error().mysql_num_rows($rOfferResult);
$sAddOfferOptions = "<option value=''>Select Offer To Add";
while ($oOfferRow = dbFetchObject($rOfferResult)) {	
	$sAddOfferOptions .= "<option value='".$oOfferRow->offerCode."'>$oOfferRow->offerCode - $oOfferRow->name";
}


$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value='Save & Continue'> &nbsp; &nbsp;
						<input type=reset name=sAbandonNew value='Abandon & Continue'>";	


// Hidden fields to be passed with form submission
$sHidden = "<input type=hidden name=sOfferCode value='$sOfferCode'>";

include("../../includes/adminAddHeader.php");
?>

<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $sHidden;?>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr>	
	<TD class=header>Offer</td>	
	<td class=header>Remove As Mutually Exclusive</td>
	<td><a href='JavaScript:void(window.open("mutExclusiveLeads.php?sOfferCode=<?php echo $sOfferCode;?>","mutLeads","scrollbars=yes, status=yes, resizable=yes"));'>All Mut. Exclusive Leads Report</a></td>
</tr>
<?php echo $sOfferList;?>
<!--<input type=submit name=saveClose value="Save & Close">-->
<tr><td><BR></td></tr>
<tr><td colspan=4 class=header>Select Offer To Add As Mutually Exclusive To This Offer:</td></tr>
<tr><Td  colspan=4><select name=sAddOfferCode>
<?php echo $sAddOfferOptions;?>
</select></td></tr>
</table>

<?php
	include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
<?php

include_once("/home/sites/admin.popularliving.com/html/includes/paths.php");
$sToday = date('Y')."-".date('m')."-".date('d');

$sCss = "
	<html><head>
	<style =\"text/css\">
	TD.small { 
		FONT-FAMILY: Arial, Helvetica, \"Sans Serif\" ; FONT-SIZE: 11px; COLOR: #000000;
	}
	TD.big { 
		FONT-FAMILY: Arial, Helvetica, \"Sans Serif\" ; FONT-SIZE: 13px; COLOR: #000000;
	}
	TD.header {
	FONT-WEIGHT: bold; FONT-SIZE: 10pt; COLOR: #000000; FONT-FAMILY: Arial, Helvetica, \"Sans Serif\"; 
	}
	</style>
	</head>
";

$sAllOffers = "$sCss<table width=60% border=1 cellpadding='3' align=center><tr>
				<td class=header colspan=2>Following Offers Are Pre-Checked On All Pages</td>
				</tr>";

$sPreCheckedAllPages = '';
$sAllPages = "SELECT offerCode FROM offers WHERE precheckAllPages='1' ORDER BY offerCode ASC";
$rAllPages = dbQuery($sAllPages);
while ($sAllRow = dbFetchObject($rAllPages)) {
	$sPreCheckedAllPages .= "'$sAllRow->offerCode',";
	
	$sAllOffers .= "<tr><td class=big colspan=2>".$sAllRow->offerCode."</td>
					</tr>";
}
if ($sPreCheckedAllPages !='') {
	$sPreCheckedAllPages = substr($sPreCheckedAllPages,0,strlen($sPreCheckedAllPages)-1);
}

$sAllOffers .= "</table>";





$sSomePagesOffers = "$sCss<table width=60% border=1 cellpadding='3' align=center><tr><td class=header>Offer Code</td>
				<td class=header>Page Name</td>
				</tr>";

$sPageOfferCode = "SELECT DISTINCT offerCode FROM pageMap WHERE precheck='1' AND offerCode NOT IN ($sPreCheckedAllPages)";
$sPrecheckedOffers = '';
$rPageOfferCode = dbQuery($sPageOfferCode);
while ($sPageRow = dbFetchObject($rPageOfferCode)) {
	$sPrecheckedOffers .= "$sPageRow->offerCode,";
}
$aSomeOffers = explode(',',$sPrecheckedOffers);
asort($aSomeOffers);
foreach ($aSomeOffers as $oc) {
	$sPageIds = '';
	$sSelectQuery = "SELECT pageId FROM pageMap WHERE offerCode ='$oc' AND precheck='1'";
	$rSelectResult = dbQuery($sSelectQuery);
	while ($sTempRow = dbFetchObject($rSelectResult)) {
		$sPageIds .= "'$sTempRow->pageId',";
	}
	if ($sPageIds !='') { $sPageIds = substr($sPageIds,0,strlen($sPageIds)-1); }
	
	$sGetPgName = "SELECT pageName FROM otPages WHERE id IN ($sPageIds) ORDER BY pageName";
	$rGetPgName = dbQuery($sGetPgName);
	$sPageNames = '';
	while ($sPgNameRow = dbFetchObject($rGetPgName)) {
		$sPageNames .= "$sPgNameRow->pageName, ";
	}
	if ($sPageNames !='') {
		$sPageNames = substr($sPageNames,0,strlen($sPageNames)-2);
	}

	$sSomePagesOffers .= "<tr><td class=big>$oc</td><td class=big>$sPageNames</td></tr>";
}



$sSomePagesOffers .= "</table><br><br>$sAllOffers";


$sHeaders  = "MIME-Version: 1.0\r\n";
$sHeaders .= "Content-type: text/html; charset=iso-8859-1\r\n";
$sHeaders .= "From:nibbles@amperemedia.com";
	
$sEmailQuery = "SELECT * FROM   emailRecipients WHERE  purpose = 'prechecked offers report'";
$rEmailResult = dbQuery($sEmailQuery);
while ($oEmailRow = dbFetchObject($rEmailResult)) {
	$sRecipients = $oEmailRow->emailRecipients;
}

mail($sRecipients, "Pre-Checked Offers - $sToday", $sSomePagesOffers, $sHeaders);



?>


<?php

/*********

Script to Display Add/Edit Nibbles Users

**********/
include("../../includes/paths.php");
session_start();
$pageTitle = "Nibbles Campaign Mgmnt Partners - Add/Edit Partners";


if (hasAccessRight($iMenuId) || isAdmin()) {

if ($iId) {

	// If Clicked to edit, get the data to display in fields

	$sSelectQuery = "SELECT * FROM nbUsers
				    WHERE  id = '$iId'";
	$rSelectResult = dbQuery($sSelectQuery);
	while ($oSelectRow = dbFetchObject($rSelectResult)) {
		$sUserName = $oSelectRow->userName;
	}
} else {
	// If add button is clicked, display another two buttons
	$sNewEntryButtons = "<BR><BR><input type=reset name=sAbandonNew value=' Abandon & New  '>";	
}


if ($sSaveClose) {
	
	
	// Prepare comma-separated Menu if record added or edited
	
	$sCompanyQuery = "SELECT id, companyName
					FROM   partnerCompanies
					ORDER BY companyName ASC";
	
	$rCompanyResult = dbQuery($sCompanyQuery);
	$i = 0;
	while ($oCompanyRow = dbFetchObject($rCompanyResult)) {
		$sPartnerId = $oCompanyRow->id;
		
		
		// prepare Categories of this offer
		$sCheckboxName = "company_".$sPartnerId;
		

		$iCheckboxValue = $$sCheckboxName;

		if ($iCheckboxValue != '') {
			$aCompanyArray[$i] = $iCheckboxValue;
			$sCompanyString .= $iCheckboxValue.",";
			$i++;
		}
	}
	

					
	// remove last comma from the sCompanyString list
	$sCompanyString = substr($sCompanyString, 0, strlen($sCompanyString)-1);

	$sDeleteQuery = "DELETE FROM campaignMgmntAccessRights
					WHERE userName='$sUserName'";
	$rDeleteResult = dbQuery($sDeleteQuery);
	echo dbError();
					
	if (count($aCompanyArray) > 0) {
		for ($i = 0; $i<count($aCompanyArray); $i++) {					
			$sInsertQuery = "INSERT INTO campaignMgmntAccessRights (userName,partnerId)
							VALUES(\"$sUserName\",'".$aCompanyArray[$i]."')";
			$rInsertResult = dbQuery($sInsertQuery);
			echo dbError();
		}
	}

	echo "<script language=JavaScript>
		window.opener.location.reload();
		self.close();
		</script>";			
		exit();
}






if (isAdmin()) {

// Prepare checkboxes for Menus
$sCompanyQuery = "SELECT id, companyName
				FROM   partnerCompanies
				ORDER BY companyName ASC";
$rCompanyResult = dbQuery($sCompanyQuery);

$sCompanyCheckboxes = "<tr>";
$j = 0;
while ($oCompanyRow = dbFetchObject($rCompanyResult)) {
	$sPartnerId = $oCompanyRow->id;
	$sCompanyName = $oCompanyRow->companyName;
	
	
	
	$sAccessRightsQuery = "SELECT partnerId
				   FROM  campaignMgmntAccessRights
				   WHERE userName = '$sUserName'
				   AND partnerId = '$sPartnerId'";
	
	$rAccessRightsResult = dbQuery($sAccessRightsQuery);
	
	if (dbNumRows($rAccessRightsResult) > 0) {
		$sCompanyChecked  = "checked";
	} else {
		$sCompanyChecked = "";
	}

	if ($j%3 == 0) {
		if ($j != 0) {
			$sCompanyCheckboxes .= "</tr>";
		}
			$sCompanyCheckboxes .= "<tr>";
	}
	
	$sCompanyCheckboxes .= "<td width=1% valign=top><input type=checkbox name='company_".$sPartnerId."' value='".$sPartnerId."' $sCompanyChecked>&nbsp;&nbsp;$sCompanyName</td>";
	$j++;
}
$sCompanyCheckboxes .= "</tr>";

$sCheckAllLink = "<tr><td colspan=6><a href = 'JavaScript:checkAll();'>Check All</a> &nbsp; &nbsp; &nbsp; &nbsp; <a href = 'JavaScript:uncheckAll();'>Uncheck All</a></td></tr>";
$sCheckAllJavaScript = "
			<script language=JavaScript>
			function checkAll() {
				
			for(i = 0; i < document.forms[0].elements.length; i++) {

    	        elm = document.forms[0].elements[i];
	
        	    if (elm.type == 'checkbox') {            	   
                    	elm.checked = true;            	   
            	}
					
            }
			}

		function uncheckAll() {
				
			for(i = 0; i < document.forms[0].elements.length; i++) {

    	        elm = document.forms[0].elements[i];
	
        	    if (elm.type == 'checkbox') {            	   
                    	elm.checked = false;            	   
            	}
					
            }
			}
				</script>
			";
}

// Hidden fields to be passed with form submission
$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>";

include("../../includes/adminAddHeader.php");
?>
<?php echo $sCheckAllJavaScript;?>
<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $sHidden;?>
<?php echo $sReloadWindowOpener;?>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
		<tr><td align="right">User Name: <?php echo $sUserName;?></td></tr>
</table>
<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>	
	<?php echo $sCheckAllLink;?>
	<?php echo $sCompanyCheckboxes;?>
</table>	
<?php
include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
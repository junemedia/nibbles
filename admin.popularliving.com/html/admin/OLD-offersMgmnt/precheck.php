<?php

/*********

Script to Precheck offers on pages

**********/

include("../../includes/paths.php");

session_start();

$sPageTitle = "Nibbles Offers - Precheck Offer";


if (hasAccessRight($iMenuId) || isAdmin()) {
	
if ($sSaveClose || $sSaveNew) {
		
	$sPageQuery = "SELECT pageMap.*, otPages.pageName
					FROM   pageMap, otPages
					WHERE  pageMap.pageId = otPages.id
					AND	   offerCode = '$sOfferCode'
					ORDER BY pageName";
	
	$rPageResult = dbQuery($sPageQuery);
	$i = 0;
		
	echo dbError();
	while ($oPageRow = dbFetchObject($rPageResult)) {

		$iTempPageId = $oPageRow->pageId;
		// prepare Categories of this offer
		$sCheckboxName = "page_".$oPageRow->pageId;

		$iCheckboxValue = $$sCheckboxName;

		$sUpdateQuery = "UPDATE pageMap 
						 SET	precheck = '$iCheckboxValue'
						 WHERE  pageId = '$iTempPageId'
						 AND	offerCode = '$sOfferCode'";
		$rUpdateResult = dbQuery($sUpdateQuery);
					
		echo dbError();			
		
	}
	
	
	if ($sSaveClose) {
		echo "<script language=JavaScript>
			window.opener.location.reload();
			self.close();
			</script>";			
		// exit from this script
		exit();
	} else if ($sSaveNew) {
		$sReloadWindowOpener = "<script language=JavaScript>
							window.opener.location.reload();
							</script>";	
				
	}
}


//if (isAdmin()) {

// Prepare checkboxes for Pages
$sPageQuery = "SELECT otPages.id, otPages.pageName
			    FROM  otPages, pageMap
				WHERE otPages.id = pageMap.pageId 
				AND   pageMap.offerCode = '$sOfferCode'
				ORDER BY pageName";
$rPageResult = dbQuery($sPageQuery);

echo dbError();
if ( dbNumRows($rPageResult) >0 ) {
$j = 0;
$sPageCheckboxes = "<tr>";
while ($oPageRow = dbFetchObject($rPageResult)) {
	$iPageId = $oPageRow->id;
	$sPageName = $oPageRow->pageName;	
	
	$sPageMapQuery = "SELECT *
				   	  FROM  pageMap
				   	  WHERE  pageId = '$iPageId'
				   	  AND    offerCode = '$sOfferCode'
					  AND  precheck='1'";
	
	$rPageMapResult = dbQuery($sPageMapQuery);
	
	if (dbNumRows($rPageMapResult) > 0) {
		$sPageChecked  = "checked";
	} else {
		$sPageChecked = "";
	}
	
	if ($j%3 == 0) {
		if ($j != 0) {
			$sPageCheckboxes .= "</tr>";
		}
		$sPageCheckboxes .= "<tr>";
	}

	if ($j%3 == 0) {
		if ($j != 0) {
			$sPageCheckboxes .= "</tr>";
		}
		$sPageCheckboxes .= "<tr>";
	}

	$sPageCheckboxes .= "<td width=5% valign=top><input type=checkbox name='page_".$iPageId."' value='1' $sPageChecked></td><td  width=28%>$sPageName</td>";
	$j++;
	
}
$sPageCheckboxes .= "</tr>";
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
//}

} else {
	$sMessage = "Offer is not displayed on any page...";
}
// Hidden fields to be passed with form submission
$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>
			<input type=hidden name=sOfferCode value='$sOfferCode'>";

include("../../includes/adminAddHeader.php");
?>
<?php echo $sCheckAllJavaScript;?>
<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $sHidden;?>
<?php echo $sReloadWindowOpener;?>
<table cellpadding=5 cellspacing=0 width=95% align=center>
<tr><TD class=header align=center>Pagewise Offer Precheck</td></tr>
</table>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>

		<tr><TD width=15% class=header>Offer Code</td><td><?php echo $sOfferCode;?></td></tr>
	</table>	
	<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>	
	<?php echo $sCheckAllLink;?>
	<?php echo $sPageCheckboxes;?>
		
</table>	
<?php
include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
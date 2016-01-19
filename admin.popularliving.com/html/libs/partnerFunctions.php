<?php

// script contains functions to be included in partner scripts only


function partnerHasAccessRight($iMenuId, $iContact = NULL) {
		
	global $iSesContactId;
	if($iContact === NULL){
		$iSesContactId = $_SESSION['iSesContactId'];

	} else {
		$iSesContactId = $iContact;
	}
	
	$sAccessRightQuery = "SELECT partnerAccessRights.*
						  FROM   partnerAccessRights
						  WHERE  partnerMenuId = '$iMenuId'
						  AND    partnerContactId = '$iSesContactId' 
						  AND    accessRight = 'Y'";
	//echo $sAccessRightQuery;
	$rAccessRightResult = dbQuery($sAccessRightQuery);
	
	if ( dbNumRows($rAccessRightResult)>0) {
		return true;
	} else {
		return false;
	}
	if ($rAccessRightResult) {
		dbFreeResult($rAccessRightResult);
	}
	
}


?>

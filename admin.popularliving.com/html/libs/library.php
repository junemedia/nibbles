<?php

/********** IMPORTANT ***************/
// Get correct value for PHP_SELF. Because $_SERVER['PHP_SELF'] will include query string also
// like abc.php/offerCat/32/...
// Using this $_SERVER['PHP_SELF'] in any link, the link will become bigger and bigger everytime you click it
// So, use following variable instead of $_SERVER['PHP_SELF'] in the scripts with search engine friendly URLs

$path = strpos($_SERVER['PHP_SELF'], $PATH_INFO);
$NEW_PHP_SELF = substr($_SERVER['PHP_SELF'], 0, $path);

// If $_SERVER['PHP_SELF'] does not contain any parameters, NEW_PHP_SELF will be null
// Then, store $_SERVER['PHP_SELF'] value in $NEW_PHP_SELF
if(!($NEW_PHP_SELF))
$NEW_PHP_SELF = $_SERVER['PHP_SELF'];

/*************** Get GET and POST vars from $_GET and $_POST *****************/
// !\"#$%&'()*+,-\./[\\]^_`|~

$sGblQueryString = '';
while (list($key,$val) = each($_GET)) {
	/*if ( !ereg(  "^[0-9A-Za-z!\"#$%&'()*+,-\./[\\^_`|~{}[[:space:]]]*$", $val)) {
	$$key = "";
	} else {*/
	/*
if ($_SERVER['REMOTE_ADDR'] == '198.63.247.2') {
	echo "<BR>".$key." - ".$val;
}*/

	$$key = $val;
	if ($val != '' && !(is_array($val))) {
		$sGblQueryString .= "$key=".urlencode($val)."&";
	} else {
		$sGblQueryString .= "$key=$val&";
	}
	//}
	
}
if ($sGblQueryString != '') {
	$sGblQueryString = substr($sGblQueryString,0,strlen($sGblQueryString)-1);
}

while (list($key,$val) = each($_POST)) {
	/*if ( !ereg(  "^[0-9A-Za-z!\"#$%&'()*+,-\./[\\^_`|~{}[[:space:]]]*$", $val)) {
	$$key = "";
	} else {*/
	$$key = $val;
	//if ($val != '') {
//		$sPage2QueryString .= "$key=".urlencode($val)."&";
	//} else {
		$sPage2QueryString .= "$key=$val&";
	//}
	//}
}

/***************************************************************************/

$aGblWeekDaysArray = array('Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun');
$aGblMonthsArray = array('Jan','Feb','Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');

function getPageId($sPageName) {
	$sOtPageQuery = "SELECT *
					 FROM   otPages
					 WHERE  pageName = '$sPageName'";
	$rOtPageResult = dbQuery($sOtPageQuery);
	while ($oOtPageRow = dbFetchObject($rOtPageResult)) {
		$iId = $oOtPageRow->id;
	}
	return $iId;
}

function hasAccessRight($iMenuId) {
	
	/*while(list($key,$val)= each($_SERVER)) {
	echo "<BR>$key - ".$val;
	}*/
	$sAccessRightQuery = "SELECT accessRights.*
						  FROM   nbUsers, accessRights
						  WHERE  nbUsers.id = accessRights.userId
						  AND	 menuId = '$iMenuId'						 
						  AND    userName = '".$_SERVER['PHP_AUTH_USER']."' 
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


function isAdmin() {
	$sAdminQuery = "SELECT nbUsers.*
				  FROM   nbUsers
				  WHERE  userName = '".$_SERVER['PHP_AUTH_USER']."' 
				  AND    level = 'admin'";
	//echo $sAdminQuery;
	$rAdminResult = dbQuery($sAdminQuery);
	
	if ( dbNumRows($rAdminResult)>0) {
		return true;
	} else {
		return false;
	}
	
	if ($rAdminResult) {
		dbFreeResult($rAdminResult);
	}
	
}


function getVar($aVarName, $sSystem = '') {
	
	$asql = "SELECT * 
			 FROM	vars			
			 WHERE	varName='$aVarName'";
	if ($sSystem != '') {
		$asql .= " AND system = '$sSystem'";
	}
	
	$aresult = dbQuery($asql);
	$arow = dbFetchObject($aresult);
	$aout = $arow->varValue;
	$aout = stripslashes($aout);	
	return $aout;
	
}

?>
<?php

/*********

Script to Display Add/Edit phone data

**********/

include("../../includes/paths.php");

session_start();

$sPageTitle = "Add/Edit Phone Data";

if (hasAccessRight($iMenuId) || isAdmin()) {
	
if (($sSaveClose || $sSaveNew) && !($iId)) {
	// if new banned email added
	
	// check if already exists
	$sCheckQuery = "SELECT *
					FROM   zipData
					WHERE  zip =  \"$sZip\"					
					AND    city = \"$sCity\"
					AND    state = \"$sState\"";
	$rCheckResult = dbQuery($sCheckQuery);
	if (dbNumRows($rCheckResult)>0) {
		$sMessage = "Record already exists...";
		$bKeepValues = true;
	} else {
		$sAddQuery = "INSERT INTO zipData(zip, city, state, type, countyFips, latitude, longitude, 
							areaCode, financeCode, lastLine, fac, msa, pmsa, filler)
				 VALUES(\"$sZip\", \"$sCity\", \"$sState\", \"$sType\", \"$sCountyFips\", \"$sLatitude\", \"$sLongitude\",
						 \"$sAreaCode\", \"$sFinanceCode\", \"$sLastLine\", \"$sFac\", \"$sMsa\", \"$sPmsa\", \"$sFiller\")";
		

		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Add: " . addslashes($sAddQuery) . "\")"; 
		$rLogResult = dbQuery($sLogAddQuery); 
		echo  dbError(); 
		// end of track users' activity in nibbles		
		
		
		$rResult = dbQuery($sAddQuery);
		if (!($rResult))
		$sMessage = dbError();
	}
	
} else if (($sSaveClose || $sSaveNew) && ($iId)) {
	// check if already exists
	$sCheckQuery = "SELECT *
					FROM   zipData
					WHERE  zip =  \"$sZip\"					
					AND    city = \"$sCity\"
					AND    state = \"$sState\"
					AND    id != '$iId'";
	$rCheckResult = dbQuery($sCheckQuery);
	if (dbNumRows($rCheckResult)>0) {
		$sMessage = "Record already exists as banned email...";
		$bKeepValues = true;
	} else {
		$sEditQuery = "UPDATE zipData
				  	   SET    zip = \"$sZip\",
							  city = \"$sCity\",
							  state = \"$sState\",
							  type = \"$sType\",
							  countyFips = \"$sCountyFips\",
							  latitude = \"$sLatitude\", 
							  longitude = \"$sLongitude\", 
		 					  areaCode = \"$sAreaCode\",
							  financeCode = \"$sFinanceCode\",
							  lastLine = \"$sLastLine\",							  
							  fac = \"$sFac\",
							  msa = \"$sMsa\", 
							  pmsa = \"$sPmsa\", 
							  filler = \"$sFiller\"							  
				  	   		  WHERE id = '$iId'";

		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Edit: " . addslashes($sEditQuery) . "\")"; 
		$rLogResult = dbQuery($sLogAddQuery); 
		echo  dbError(); 
		// end of track users' activity in nibbles		
		
		
		$rResult = dbQuery($sEditQuery);
		if (!($rResult)) {
			$sMessage = dbError();
		}
	}
}

if ($sSaveClose) {
	if ($bKeepValues != true) {
		echo "<script language=JavaScript>
			window.opener.location.reload();
			self.close();
			</script>";			
		// exit from this script
		exit();
	}
} else if ($sSaveNew) {
	if ($bKeepValues != '') {
		$sReloadWindowOpener = "<script language=JavaScript>
							window.opener.location.reload();
							</script>";	
	
		$sAreaCode = '';
		$sCity = '';
		$sState = '';		
		$sZip = '';		
		$sType = '';
		$sCountyFips = '';
		$sLatitude = '';
		$sLongitude = '';
		$sAreaCode = '';
		$sFinanceCode = '';
		$sLastLine = '';
		$sFac = '';
		$sMsa = '';
		$sPmsa = '';
		$sFiller = '';			

	}
}

if ($iId) {
	
	// If Clicked to edit, get the data to display in fields
	
	$sSelectQuery = "SELECT * FROM zipData
				    WHERE  id = '$iId'";
	$rSelectResult = dbQuery($sSelectQuery);
	while ($oSelectRow = dbFetchObject($rSelectResult)) {
						
		$sZip = $oSelectRow->zip;
		$sCity = $oSelectRow->city;
		$sState = $oSelectRow->state;	
		$sType = $oSelectRow->type;
		$sCountyFips = $oSelectRow->countyFips;
		$sLatitude = $oSelectRow->latitude;
		$sLongitude = $oSelectRow->longitude;
		$sAreaCode = $oSelectRow->areaCode;
		$sFinanceCode = $oSelectRow->financeCode;
		$sLastLine = $oSelectRow->lastLine;
		$sFac = $oSelectRow->fac;
		$sMsa = $oSelectRow->msa;
		$sPmsa = $oSelectRow->pmsa;
		$sFiller = $oSelectRow->filler;							
	}
} else {
	
	// If add button is clicked, display another two buttons
	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=sAbandonNew value=' Abandon & New  '>";	
}

// Hidden fields to be passed with form submission
$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>			
			<input type=hidden name=iId value='$iId'>";


include("../../includes/adminAddHeader.php");
?>

<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $sHidden;?>
<?php echo $sReloadWindowOpener;?>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
		<tr><TD>Zip</td><td><input type=text name=sZip value="<?php echo $sZip;?>"></td></tr>				
		<tr><TD>State</td><td><input type=text name=sState value="<?php echo $sState;?>"></td></tr>
		<tr><TD>City</td><td><input type=text name=sCity value="<?php echo $sCity;?>"></td></tr>
		<tr><TD>Type</td><td><input type=text name=sType value="<?php echo $sType;?>"></td></tr>
		<tr><TD>CountyFips</td><td><input type=text name=sCountyFips value="<?php echo $sCountyFips;?>"></td></tr>
		<tr><TD>Latitude</td><td><input type=text name=sLatitude value="<?php echo $sLatitude;?>"></td></tr>
		<tr><TD>Longitude</td><td><input type=text name=sLongitude value="<?php echo $sLongitude;?>"></td></tr>
		<tr><TD>Area Code</td><td><input type=text name=sAreaCode value="<?php echo $sAreaCode;?>"></td></tr>
		<tr><TD>Finance Code</td><td><input type=text name=sFinanceCode value="<?php echo $sFinanceCode;?>"></td></tr>		
		<tr><TD>Last Line</td><td><input type=text name=sLastLine value="<?php echo $sLastLine;?>"></td></tr>		
		<tr><TD>Fac</td><td><input type=text name=sFac value="<?php echo $sFac;?>"></td></tr>
		<tr><TD>Msa</td><td><input type=text name=sMsa value="<?php echo $sMsa;?>"></td></tr>
		<tr><TD>Pmsa</td><td><input type=text name=sPmsa value="<?php echo $sPmsa;?>"></td></tr>
		<tr><TD>Filler</td><td><input type=text name=sFiller value="<?php echo $sFiller;?>"></td></tr>
</table>
		
<?php
include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
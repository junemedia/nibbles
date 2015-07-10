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
					FROM   phoneData
					WHERE  areaCode =  \"$sAreaCode\"
					AND    prefix = \"$sPrefix\"
					AND    city = \"$sCity\"
					AND    state = \"$sState\"";
	$rCheckResult = dbQuery($sCheckQuery);
	if (dbNumRows($rCheckResult)>0) {
		$sMessage = "Record already exists...";
		$bKeepValues = true;
	} else {
		$sAddQuery = "INSERT INTO phoneData(areaCode, prefix, city, state, latitude, longitude, zip1, zip2, zip3, 
						countyFips, newAreaCode, country, msa, pmsa, lata, cell, overlay)
				 VALUES(\"$sAreaCode\",\"$sPrefix\",\"$sCity\", \"$sState\", \"$sLatitude\", \"$sLongitude\",
						 \"$sZip1\", \"$sZip2\", \"$sZip3\", \"$sCountyFips\", \"$sNewAreaCode\", \"$sCountry\", 
						\"$sMsa\", \"$sPmsa\", \"$sLata\", \"$sCell\", \"$sOverlay\" )";

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
					FROM   phoneData
					WHERE  areaCode =  \"$sAreaCode\"
					AND    prefix = \"$sPrefix\"
					AND    city = \"$sCity\"
					AND    state = \"$sState\"
					AND    id != '$iId'";
	$rCheckResult = dbQuery($sCheckQuery);
	if (dbNumRows($rCheckResult)>0) {
		$sMessage = "Record already exists as banned email...";
		$bKeepValues = true;
	} else {
		$sEditQuery = "UPDATE phoneData
				  	   SET    areaCode = \"$sAreaCode\",
							  prefix = \"$sPrefix\",
							  city = \"$sCity\",
							  state = \"$sState\",
							  latitude = \"$sLatitude\", 
							  longitude = \"$sLongitude\", 
							  zip1 = \"$sZip1\", 
							  zip2 = \"$sZip2\", 
							  zip3 = \"$sZip3\", 
							  countyFips = \"$sCountyFips\", 
							  newAreaCode = \"$sNewAreaCode\", 
							  country = \"$sCountry\", 
							  msa = \"$sMsa\", 
							  pmsa = \"$sPmsa\", 
							  lata = \"$sLata\", 
							  cell = \"$sCell\", 
							  overlay = \"$sOverlay\"							  
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
		$sPrefix = '';
		$sCity = '';
		$sState = '';		
		$sZip1 = '';
		$sZip2 = '';
		$sZip3 = '';
		$sLatitude = '';
		$sLongitude = '';
		$sCountyFips = '';
		$sNewAreaCode = '';
		$sCountry = '';
		$sMsa = '';
		$sPmsa = '';
		$sLata = '';
		$sCell = '';
		$sOverlay = '';
		
	}
}

if ($iId) {
	
	// If Clicked to edit, get the data to display in fields
	
	$sSelectQuery = "SELECT * FROM phoneData
				    WHERE  id = '$iId'";
	$rSelectResult = dbQuery($sSelectQuery);
	while ($oSelectRow = dbFetchObject($rSelectResult)) {
				
		$sAreaCode = $oSelectRow->areaCode;
		$sPrefix = $oSelectRow->prefix;
		$sCity = $oSelectRow->city;
		$sState = $oSelectRow->state;	
		$sZip1 = $oSelectRow->zip1;
		$sZip2 = $oSelectRow->zip2;
		$sZip3 = $oSelectRow->zip3;			
		$sLatitude = $oSelectRow->latitude;
		$sLongitude = $oSelectRow->longitude;
		$sCountyFips = $oSelectRow->countyFips;
		$sNewAreaCode = $oSelectRow->newAreaCode;
		$sCountry = $oSelectRow->country;
		$sMsa = $oSelectRow->msa;
		$sPmsa = $oSelectRow->pmsa;
		$sLata = $oSelectRow->lata;
		$sCell = $oSelectRow->cell;
		$sOverlay = $oSelectRow->overlay;
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
		<tr><TD>Area Code</td><td><input type=text name=sAreaCode value="<?php echo $sAreaCode;?>"></td></tr>
		<tr><TD>Prefix</td><td><input type=text name=sPrefix value="<?php echo $sPrefix;?>"></td></tr>
		<tr><TD>City</td><td><input type=text name=sCity value="<?php echo $sCity;?>"></td></tr>
		<tr><TD>State</td><td><input type=text name=sState value="<?php echo $sState;?>"></td></tr>
		<tr><TD>Latitude</td><td><input type=text name=sLatitude value="<?php echo $sLatitude;?>"></td></tr>
		<tr><TD>Longitude</td><td><input type=text name=sLongitude value="<?php echo $sLongitude;?>"></td></tr>
		<tr><TD>Zip1</td><td><input type=text name=sZip1 value="<?php echo $sZip1;?>"></td></tr>
		<tr><TD>Zip2</td><td><input type=text name=sZip2 value="<?php echo $sZip2;?>"></td></tr>
		<tr><TD>Zip3</td><td><input type=text name=sZip3 value="<?php echo $sZip3;?>"></td></tr>
		<tr><TD>CountyFips</td><td><input type=text name=sCountyFips value="<?php echo $sCountyFips;?>"></td></tr>
		<tr><TD>New Area Code</td><td><input type=text name=sNewAreaCode value="<?php echo $sNewAreaCode;?>"></td></tr>
		<tr><TD>Country</td><td><input type=text name=sCountry value="<?php echo $sCountry;?>"></td></tr>
		<tr><TD>Msa</td><td><input type=text name=sMsa value="<?php echo $sMsa;?>"></td></tr>
		<tr><TD>Pmsa</td><td><input type=text name=sPmsa value="<?php echo $sPmsa;?>"></td></tr>
		<tr><TD>Lata</td><td><input type=text name=sLata value="<?php echo $sLata;?>"></td></tr>
		<tr><TD>Cell</td><td><input type=text name=sCell value="<?php echo $sCell;?>"></td></tr>
		<tr><TD>Overlay</td><td><input type=text name=sOverlay value="<?php echo $sOverlay;?>"></td></tr>
		
</table>
		
<?php
include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
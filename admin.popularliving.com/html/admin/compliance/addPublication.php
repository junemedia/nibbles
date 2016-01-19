<?php

/*******

Script to Display List/Delete Publication information

*******/

include("../../includes/paths.php");

$sPageTitle = "Publication Information - Add/Edit Publication";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {		
	
if (($sSaveClose || $sSaveNew) && !($id)) {	
	// if new data submitted
	
	for ($i = 0; $i < count($aGblWeekDaysArray); $i++) {
		if ($standardWeekDays[$i] != '') {
			$standardSchedule .= $standardWeekDays[$i].",";
		}
		if ($soloWeekDays[$i] != '') {
			$soloSchedule .= $soloWeekDays[$i].",";
		}
	}
	$standardSchedule = substr($standardSchedule,0,strlen($standardSchedule)-1);
	$soloSchedule = substr($soloSchedule,0,strlen($soloSchedule)-1);
	$releaseTime = $releaseHour.":".$releaseMinute.":".$releaseSecond;
	
	//Check if publication code exists
	$checkQuery = "SELECT publicationCode
				   FROM   publications
				   WHERE  publicationCode = '$publicationCode'";
	$checkResult = mysql_query($checkQuery);
	if (mysql_num_rows($checkResult) == 0) {
		$addQuery = "INSERT INTO publications(publicationName, publicationCode, standardSchedule, soloSchedule, releaseTime, releasePrevNight)
					 VALUES('$publicationName', '$publicationCode', '$standardSchedule', '$soloSchedule', '$releaseTime', '$releasePrevNight')";
		
		
		// start of track users' activity in nibbles
		$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
		$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Add Entry: $addQuery\")";
		$rResult = dbQuery($sAddQuery);
		echo  dbError();
		// end of track users' activity in nibbles
		
		
		$result = mysql_query($addQuery);
		if (! $result) {
			echo mysql_error();
		}
	} else {
		$message = "Publication Code Already Exists...";
		$keepValues = true;
	}
	
} else if (($sSaveClose || $sSaveNew) && ($id)) {
	
	// If record edited
	for ($i = 0; $i < count($aGblWeekDaysArray); $i++) {
		if ($standardWeekDays[$i] != '') {
			$standardSchedule .= $standardWeekDays[$i].",";
		}
		if ($soloWeekDays[$i] != '') {
			$soloSchedule .= $soloWeekDays[$i].",";
		}
	}
	$standardSchedule = substr($standardSchedule,0,strlen($standardSchedule)-1);
	$soloSchedule = substr($soloSchedule,0,strlen($soloSchedule)-1);
	$releaseTime = $releaseHour.":".$releaseMinute.":".$releaseSecond;
	//Check if publication code exists
	$checkQuery = "SELECT publicationCode
				   FROM   publications
				   WHERE  publicationCode = '$publicationCode'
					AND   id != '$id'";
	$checkResult = mysql_query($checkQuery);
	if (mysql_num_rows($checkResult) == 0) {
		$editQuery = "UPDATE publications
					  SET publicationName = '$publicationName',
						  publicationCode = '$publicationCode',
						  standardSchedule = '$standardSchedule',
						  soloSchedule = '$soloSchedule',
						  releaseTime = '$releaseTime',
						  releasePrevNight = '$releasePrevNight'
	 			  	WHERE id = '$id'";		
		
		
		// start of track users' activity in nibbles
		$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
		$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Edit Entry: $editQuery\")";
		$rResult = dbQuery($sAddQuery);
		echo  dbError();
		// end of track users' activity in nibbles
		
		
		
		$result = mysql_query($editQuery);
		if (! $result) {
			echo mysql_error();
		}
	} else {
		$message = "Publication Code Already exists...";
		$keepValues = true;
	}
}

if ($sSaveClose) {
	if ($keepValues != true) {
		echo "<script language=JavaScript>
				window.opener.location.reload();
				self.close();
				</script>";					
		//exit from this script
		exit();
	}
} else if ($sSaveNew) {
	$reloadWindowOpener = "<script language=JavaScript>
							window.opener.location.reload();
							</script>";
	// Reset textboxes for new record
	if ($keepValues != true) {
		$publicationName = '';
		$publicationCode = '';
		$standardSchedule = '';
		$soloSchedule = '';
		$releaseTime = '';
		$releasePrevNight = '';
	}
}

if ($id != '') {
	// If Clicked on a record, get data to display in the fields 
	
	$selectQuery = "SELECT *
					FROM   publications
			  		WHERE  id = '$id'";
	$result = mysql_query($selectQuery);
	
	if ($result) {
		
		while ($row = mysql_fetch_object($result)) {
			$publicationName = $row->publicationName;
			$publicationCode = $row->publicationCode;
			$standardSchedule = $row->standardSchedule;
			$soloSchedule = $row->soloSchedule;
			$releaseTime = $row->releaseTime;
			$releasePrevNight = $row->releasePrevNight;
			$releaseHour = substr($releaseTime,0,2);
			$releaseMinute = substr($releaseTime, 3,2);
			$releaseSecond = substr($releaseTime, 6,2);
		}
		
		mysql_free_result($result);
	} else {
		echo mysql_error();
	}
	
} else {
	// If add button is clicked, display another two buttons
	$newEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
					<input type=reset name=sAbandonNew value=' Abandon & New  '>";
}

// prepare options for  hour, minute and second
$releaseHourOptions = "<option value = ''>Hour";
for ($i = 0; $i <= 23; $i++) {
	if ($i < 10) {
		$value = "0".$i;
	} else {
		$value = $i;
	}
	if ($value == $releaseHour) {
		$releaseHourSel = "selected";
	} else {
		$releaseHourSel = "";
	}
	
	$releaseHourOptions .= "<option value='$value' $releaseHourSel>$value";
}

$releaseMinuteOptions = "<option value = ''>Minute";
for ($i = 0; $i <= 59; $i++) {
	
	if ($i < 10) {
		$value = "0".$i;
	} else {
		$value = $i;
	}
	
	if ($value == $releaseMinute) {
		$releaseMinuteSel = "selected";
	} else {
		$releaseMinuteSel = "";
	}
	$releaseMinuteOptions .= "<option value='$value' $releaseMinuteSel>$value";
}

$releaseSecondOptions = "<option value = ''>Second";
for ($i = 0; $i <= 59; $i++) {
	
	if ($i < 10) {
		$value = "0".$i;
	} else {
		$value = $i;
	}
	
	if ($value == $releaseSecond) {
		$releaseSecondSel = "selected";
	} else {
		$releaseSecondSel ="";
	}
	$releaseSecondOptions .= "<option value='$value' $releaseSecondSel>$value";
}


// Prepare WeekDays Checkboxes list
for ($i = 0; $i < count($aGblWeekDaysArray); $i++) {
	if (strstr($standardSchedule,$aGblWeekDaysArray[$i])) {
		$checked = "checked";
	} else {
		$checked = "";
	}
	
	$standardWeekDaysOptions .= "<input type=checkbox name=standardWeekDays[] value='$aGblWeekDaysArray[$i]' $checked>$aGblWeekDaysArray[$i] &nbsp; &nbsp;";
	if (strstr($soloSchedule,$aGblWeekDaysArray[$i])) {
		$checked = "checked";
	} else {
		$checked = "";
	}
	
	$soloWeekDaysOptions .= "<input type=checkbox name=soloWeekDays[] value='$aGblWeekDaysArray[$i]' $checked>$aGblWeekDaysArray[$i] &nbsp; &nbsp;";
}

if ($releasePrevNight == 'Y') {
	$releasePrevNightChecked = "checked";
} else {
	$releasePrevNightChecked = "";
}

// Hidden variables to be passed with form submission
$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=id value='$id'>";

include("../../includes/adminAddHeader.php");


?>	

<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $hidden;?>
<?php echo $sReloadWindowOpener;?>			

<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
	<tr><td>Publication Name</td>
		<td><input type=text name='publicationName' value="<?php echo $publicationName;?>"></td>
	</tr>
	<tr><td>Publication Code</td>
		<td><input type=text name='publicationCode' value="<?php echo $publicationCode;?>"></td>
	</tr>
	<tr><td>Standard Schedule</td>
		<td><?php echo $standardWeekDaysOptions;?></td>
	</tr>						
		<tr><td>Solo Schedule</td>
		<td><?php echo $soloWeekDaysOptions;?></td>
	</tr>			
		<tr><td>Release Time</td>
		<td><select name=releaseHour>
		<?php echo $releaseHourOptions;?>
			</select> &nbsp;<select name=releaseMinute>
		<?php echo $releaseMinuteOptions;?>
			</select> &nbsp;<select name=releaseSecond>
		<?php echo $releaseSecondOptions;?>
			</select> &nbsp;</td>
	</tr>
		<tr><td></td>
		<td><input name=releasePrevNight type=checkbox value='Y' <?php echo $releasePrevNightChecked;?>> Release Previous Night</td>
	</tr>		
		
</table>


<?php
	include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>

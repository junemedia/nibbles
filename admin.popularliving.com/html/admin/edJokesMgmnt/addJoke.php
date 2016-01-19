<?php


include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

$sPageTitle = "Nibbles Jokes Management - Add/Edit Joke";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {

if (($sSaveClose || $sSaveNew) && !($id)) {
	// if new data submitted
	
	// check dates selected are valid dates
	if (checkDate($activeMonth, $activeDay, $activeYear) && checkDate($inactiveMonth, $inactiveDay, $inactiveYear))  {
		$activeDate = $activeYear."-".$activeMonth."-".$activeDay;
		$inactiveDate = $inactiveYear."-".$inactiveMonth."-".$inactiveDay;
		$description = addslashes($description);
		$teaser = addslashes($teaser);
		$headline = addslashes($headline);
		
		$addQuery = "INSERT INTO edJokes(headline, teaser, description, activeDate, inactiveDate, dateInserted, frontPageDisplay)
					 VALUES(\"$headline\", \"$teaser\", \"$description\", '$activeDate', '$inactiveDate', CURRENT_DATE, '$frontPageDisplay')";
		
		$result = mysql_query($addQuery);
		if(! $result) {
			echo mysql_error();
		}
		
		// start of track users' activity in nibbles
		$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Add: edJokes.headline='$headline'\")";
		$rResult = dbQuery($sAddQuery);
		// end of track users' activity in nibbles
	
	
	} else {
		$sMessage = "Please Select Valid Dates...";
		$keepValues = true;
	}
} elseif (($sSaveClose || $sSaveNew) && ($id)) {
	// If record edited

	
	// check dates selected are valid dates
	if (checkDate($activeMonth, $activeDay, $activeYear) && checkDate($inactiveMonth, $inactiveDay, $inactiveYear))  {
		$activeDate = $activeYear."-".$activeMonth."-".$activeDay;
		$inactiveDate = $inactiveYear."-".$inactiveMonth."-".$inactiveDay;
		$description = addslashes($description);
		$teaser = addslashes($teaser);
		$headline = addslashes($headline);
		
		$editQuery = "UPDATE edJokes
				  SET 	 headline = \"$headline\",
						 teaser = \"$teaser\",
						 description = \"$description\",
						 activeDate = '$activeDate',
						 inactiveDate = '$inactiveDate',
						 frontPageDisplay = '$frontPageDisplay' 
				  WHERE  id = '$id'";
		
		// start of track users' activity in nibbles
		$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Edit: edJokes.id='$id'\")";
		$rResult = dbQuery($sAddQuery);
		// end of track users' activity in nibbles
		
		$result = mysql_query($editQuery);
	} else {
		$sMessage = "Please Select Valid Dates...";
		$keepValues = true;
	}
}

if ($sSaveClose) {
	if ($keepValues != true) {
		echo "<script language=JavaScript>
			window.opener.location.reload();
			self.close();
			</script>";		
		// exit from this script
		exit();
	}
} else if ($sSaveNew) {
	$reloadWindowOpener = "<script language=JavaScript>
							window.opener.location.reload();
							</script>";
	// Reset textboxes for new record
	if ($keepValues != true) {
		$headline = '';
		$teaser = '';
		$description = '';
		$activeYear = '';
		$activeMonth = '';
		$activeDay = '';
		$inactiveYear = '';
		$inactiveMonth = '';
		$inactiveDay = '';
		$frontPageDisplay = '';
	}
}

$currYear = date(Y);
$currMonth = date(m); //01 to 12
$currDay = date(d); // 01 to 31

// set curr date values to be selected by default
if (!($saveClose || $id )) {
	$activeMonth = $currMonth;
	$activeDay = $currDay;
	$activeYear = $currYear;
	$inactiveMonth = $currMonth;
	$inactiveDay = $currDay;
	$inactiveYear = $currYear;
}

if ($id != '') {
	// If Clicked on Edit, display values in fields and
	// buttons to edit/Reset...
	
	// Get the data to display in HTML fields for the record to be edited
	$selectQuery = "SELECT *
					FROM   edJokes						 
					WHERE  id = '$id'";
	$result = mysql_query($selectQuery);
	
	if ($result) {
		
		while ($row = mysql_fetch_object($result)) {
			$headline = ascii_encode($row->headline);
			$teaser = ascii_encode($row->teaser);
			$description = ascii_encode($row->description);
			$activeDate = $row->activeDate;
			$inactiveDate = $row->inactiveDate;
			$activeYear = substr($activeDate,0,4);
			$activeMonth = substr($activeDate, 5,2);
			$activeDay = substr($activeDate,8,2);
			$inactiveYear = substr($inactiveDate,0,4);
			$inactiveMonth = substr($inactiveDate, 5,2);
			$inactiveDay = substr($inactiveDate,8,2);
			$frontPageDisplay = $row->frontPageDisplay;
		}		
	} else {
		echo mysql_error();
	}
}  else {
	$headline = ascii_encode(stripslashes($row->headline));
	$teaser = ascii_encode(stripslashes($row->teaser));
	$description = ascii_encode(stripslashes($row->description));
	// If add button is clicked, display another two buttons
	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=abandonNew value=' Abandon & New  '>";	
}


// prepare month options for From and To date

for ($i = 0; $i < count($aGblMonthsArray); $i++) {
	$value = $i+1;
	if ($value < 10) {
		$value ="0".($value);
	} 
	if ($value == $activeMonth) {
		$monthSel = "selected";
	} else {
		$monthSel = "";
	}
	
	$activeMonthOptions .= "<option value='$value' $monthSel>$aGblMonthsArray[$i]";
	if ($value == $inactiveMonth) {
		$monthSel = "selected";
	} else {
		$monthSel = "";
	}
	
	$inactiveMonthOptions .= "<option value='$value' $monthSel>$aGblMonthsArray[$i]";
}

// prepare day options for From and To date
for ($i = 1; $i <= 31; $i++) {
	
	if ($i < 10) {
		$value = "0".$i;
	} else {
		$value = $i;
	}
	
	if ($value == $activeDay) {
		$daySel = "selected";
	} else {
		$daySel = "";
	}
	$activeDayOptions .= "<option value='$value' $daySel>$i";
	
	if ($value == $inactiveDay) {
		$daySel = "selected";
	} else {
		$daySel = "";
	}
	$inactiveDayOptions .= "<option value='$value' $daySel>$i";
	
}

// prepare year options for From and To date
for ($i = $currYear; $i <= $currYear+15; $i++) {
	if ($i == $activeYear) {
		$yearSel = "selected";
	} else {
		$yearSel ="";
	}
	$activeYearOptions .= "<option value='$i' $yearSel>$i";
	
	if ($i == $inactiveYear) {
		$yearSel = "selected";
	} else {
		$yearSel ="";
	}
	$inactiveYearOptions .= "<option value='$i' $yearSel>$i";
	
}
if ($frontPageDisplay=='Y') {
	$frontPageDisplayChecked = "CHECKED";
} else {
	$frontPageDisplayChecked = "";
}

// Hidden variable to be passed with form submit
$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=id value='$id'>";

include("../../includes/adminAddHeader.php");

?>

<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $hidden;?>
<?php echo $sReloadWindowOpener;?>

<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
	<tr><td>Headline</td>
		<td><input type=text name='headline' value='<?php echo $headline;?>' size=45></td>
	</tr>
	<tr><td>Teaser</td>
		<td><input type=text name='teaser' value='<?php echo $teaser;?>' size=45></td>
	</tr>
	<tr><td>Description</td>
		<td><textarea name='description' rows=6 cols=40><?php echo $description;?></textarea></td>
	</tr>			
	<tr><td>Schedule Active Date</td>
		<td><select name=activeMonth><?php echo $activeMonthOptions;?>
	</select> &nbsp;<select name=activeDay><?php echo $activeDayOptions;?>
	</select> &nbsp;<select name=activeYear><?php echo $activeYearOptions;?>
	</select></td>
	</tr>
	<tr><td>Schedule Inactive Date</td>
		<td><select name=inactiveMonth><?php echo $inactiveMonthOptions;?>
	</select> &nbsp;<select name=inactiveDay><?php echo $inactiveDayOptions;?>
	</select> &nbsp;<select name=inactiveYear><?php echo $inactiveYearOptions;?>
	</select></td>
	</tr>
	<tr><td>Front Page Display</td>
		<td><input type=checkbox name='frontPageDisplay' value='Y' <?php echo $frontPageDisplayChecked;?>></td>
	</tr>	
</table>


<?php
	include("../../includes/adminAddFooter.php");
	
} else {
	echo "You are not authorized to access this page...";
}
?>
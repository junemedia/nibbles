<?php

/*********

Script to Display Add/Edit Trivia Tidbit

*********/

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

$sPageTitle = "Nibbles Trivia Tidbit Management - Add/Edit Trivia Tidbit";

session_start();

// Check user permission to access this page

if (hasAccessRight($iMenuId) || isAdmin()) {
	

if (($sSaveClose || $sSaveNew) && !($id)) {
	

	
	// if new data submitted
	if (checkDate($frontActiveMonth, $frontActiveDay, $frontActiveYear) && checkDate($frontInactiveMonth, $frontInactiveDay, $frontInactiveYear)
		&& checkDate($listActiveMonth, $listActiveDay, $listActiveYear) && checkDate($listInactiveMonth, $listInactiveDay, $listInactiveYear))  {
			
		$frontActiveDate = $frontActiveYear."-".$frontActiveMonth."-".$frontActiveDay;
		$frontInactiveDate = $frontInactiveYear."-".$frontInactiveMonth."-".$frontInactiveDay;
		
		$listActiveDate = $listActiveYear."-".$listActiveMonth."-".$listActiveDay;
		$listInactiveDate = $listInactiveYear."-".$listInactiveMonth."-".$listInactiveDay;			
		
		$question = addslashes($question);
		$answer = addslashes($answer);

		$addQuery = "INSERT INTO edTrivia(question, answer, frontActiveDate, frontInactiveDate, listActiveDate, listInactiveDate, dateInserted)
					 VALUES(\"$question\", \"$answer\", '$frontActiveDate', '$frontInactiveDate', '$listActiveDate', '$listInactiveDate', CURRENT_DATE)";

		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Add: " . addslashes($addQuery) . "\")"; 
		$rLogResult = dbQuery($sLogAddQuery); 
		echo  dbError(); 
		// end of track users' activity in nibbles		
		
		
		$result = mysql_query($addQuery);
		if(! $result) {
			echo mysql_error();
		}
} else {
		$sMessage = "Please select valid dates...";
		$keepValues = true;
	}
} elseif (($sSaveClose || $sSaveNew) && ($id)) {
if (checkDate($frontActiveMonth, $frontActiveDay, $frontActiveYear) && checkDate($frontInactiveMonth, $frontInactiveDay, $frontInactiveYear)
	&& checkDate($listActiveMonth, $listActiveDay, $listActiveYear) && checkDate($listInactiveMonth, $listInactiveDay, $listInactiveYear))  {
		$frontActiveDate = $frontActiveYear."-".$frontActiveMonth."-".$frontActiveDay;
		$frontInactiveDate = $frontInactiveYear."-".$frontInactiveMonth."-".$frontInactiveDay;
		
		$listActiveDate = $listActiveYear."-".$listActiveMonth."-".$listActiveDay;
		$listInactiveDate = $listInactiveYear."-".$listInactiveMonth."-".$listInactiveDay;
		
		$question = addslashes($question);
		$answer = addslashes($answer);
	
	
		$editQuery = "UPDATE edTrivia
				  SET 	 question = \"$question\",
						 answer = \"$answer\",
						 frontActiveDate = '$frontActiveDate',
						 frontInactiveDate = '$frontInactiveDate',
						 listActiveDate = '$listActiveDate',
						 listInactiveDate = '$listInactiveDate'
				  WHERE  id = '$id'";	

		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Edit: " . addslashes($editQuery) . "\")"; 
		$rLogResult = dbQuery($sLogAddQuery); 
		echo  dbError(); 
		// end of track users' activity in nibbles		
		
		
		$result = mysql_query($editQuery);
		} else {
		$sMessage = "Please select valid dates...";
		$keepValues = true;
	}
}

if ($sSaveClose) {
	if($keepValues != true) {
		echo "<script language=JavaScript>
			window.opener.location.reload();
			self.close();
			</script>";		
	}
} else if($sSaveNew) {
	$reloadWindowOpener = "<script language=JavaScript>
							window.opener.location.reload();
							</script>";
	// Reset textboxes for new record
	if($keepValues != true) {
		$question = '';
		$answer = '';
		$frontActiveYear = '';
		$frontActiveMonth = '';
		$frontActiveDay = '';
		$frontInactiveYear = '';
		$frontInactiveMonth = '';
		$frontInactiveDay = '';
		$listActiveYear = '';
		$listActiveMonth = '';
		$listActiveDay = '';
		$listInactiveYear = '';
		$listInactiveMonth = '';
		$listInactiveDay = '';
	}
}


//$monthArray = array('Jan','Feb','Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
$currYear = date(Y);
$currMonth = date(m); //01 to 12
$currDay = date(d); // 01 to 31

// set curr date values to be selected by default
if (!($saveClose || $id )) {
	$frontActiveMonth = $currMonth;
	$frontActiveDay = $currDay;
	$frontActiveYear = $currYear;
	$frontInactiveMonth = $currMonth;
	$frontInactiveDay = $currDay;
	$frontInactiveYear = $currYear;
	
	$listActiveMonth = $currMonth;
	$listActiveDay = $currDay;
	$listActiveYear = $currYear;
	$listInactiveMonth = $currMonth;
	$listInactiveDay = $currDay;
	$listInactiveYear = $currYear;
}

if ($id!='') {
	// If Clicked on Edit, display values in fields and
	// buttons to edit/Reset...
	
	// Get the data to display in HTML fields for the record to be edited
	$selectQuery = "SELECT *
						FROM   edTrivia						 
			  			WHERE  id = '$id'";
	$result = mysql_query($selectQuery);
	
	if ($result) {
		
		while ($row = mysql_fetch_object($result)) {
			$question = ascii_encode($row->question);
			$answer = ascii_encode($row->answer);
			$frontActiveDate = $row->frontActiveDate;
			$frontInactiveDate = $row->frontInactiveDate;
			$listActiveDate = $row->listActiveDate;
			$listInactiveDate = $row->listInactiveDate;
			$frontActiveYear = substr($frontActiveDate,0,4);
			$frontActiveMonth = substr($frontActiveDate, 5,2);
			$frontActiveDay = substr($frontActiveDate,8,2);
			$frontInactiveYear = substr($frontInactiveDate,0,4);
			$frontInactiveMonth = substr($frontInactiveDate, 5,2);
			$frontInactiveDay = substr($frontInactiveDate,8,2);		
			$listActiveYear = substr($listActiveDate,0,4);
			$listActiveMonth = substr($listActiveDate, 5,2);
			$listActiveDay = substr($listActiveDate,8,2);
			$listInactiveYear = substr($listInactiveDate,0,4);
			$listInactiveMonth = substr($listInactiveDate, 5,2);
			$listInactiveDay = substr($listInactiveDate,8,2);		
		}
		mysql_free_result($result);
	} else {
		echo mysql_error();
	}
}  else {
	$question = ascii_encode(stripslashes($question));
	$answer = ascii_encode(stripslashes($answer));
	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=abandonNew value=' Abandon & New  '>";	
}

// prepare month options for From and To date
//$monthOptions = "<option value='00' selected>Month";
for ($i = 0; $i < count($aGblMonthsArray); $i++) {
	$value = $i+1;	
	if ($i < 10) {
		$value ="0".$i+1;
	} 
	if ($value == $frontActiveMonth) {
		$monthSel = "selected";
	} else {
		$monthSel = "";
	}
	
	$frontActiveMonthOptions .= "<option value='$value' $monthSel>$aGblMonthsArray[$i]";
	if ($value == $frontInactiveMonth) {
		$monthSel = "selected";
	} else {
		$monthSel = "";
	}
	
	$frontInactiveMonthOptions .= "<option value='$value' $monthSel>$aGblMonthsArray[$i]";
	
	if ($value == $listActiveMonth) {
		$monthSel = "selected";
	} else {
		$monthSel = "";
	}
	
	$listActiveMonthOptions .= "<option value='$value' $monthSel>$aGblMonthsArray[$i]";
	if ($value == $listInactiveMonth) {
		$monthSel = "selected";
	} else {
		$monthSel = "";
	}
	
	$listInactiveMonthOptions .= "<option value='$value' $monthSel>$aGblMonthsArray[$i]";
	
}

// prepare day options for From and To date
//$dayOptions = "<option value='00' selected>Day";
for ($i = 1; $i <= 31; $i++) {
	
	if ($i < 10) {
		$value = "0".$i;
	} else {
		$value = $i;
	}
	
	if ($value == $frontActiveDay) {
		$daySel = "selected";
	} else {
		$daySel = "";
	}
	$frontActiveDayOptions .= "<option value='$value' $daySel>$i";
	
	if ($value == $frontInactiveDay) {
		$daySel = "selected";
	} else {
		$daySel = "";
	}
	$frontInactiveDayOptions .= "<option value='$value' $daySel>$i";	
	
	if ($value == $listActiveDay) {
		$daySel = "selected";
	} else {
		$daySel = "";
	}
	$listActiveDayOptions .= "<option value='$value' $daySel>$i";
	
	if ($value == $listInactiveDay) {
		$daySel = "selected";
	} else {
		$daySel = "";
	}
	$listInactiveDayOptions .= "<option value='$value' $daySel>$i";	
	
}

//$yearOptions = "<option value='0000' selected>Year";
for ($i = $currYear; $i <= $currYear+1; $i++) {
	if ($i == $frontActiveYear) {
		$yearSel = "selected";
	} else {
		$yearSel ="";
	}
	$frontActiveYearOptions .= "<option value='$i' $yearSel>$i";
	
	if ($i == $frontInactiveYear) {
		$yearSel = "selected";
	} else {
		$yearSel ="";
	}
	$frontInactiveYearOptions .= "<option value='$i' $yearSel>$i";
	
	if ($i == $listActiveYear) {
		$yearSel = "selected";
	} else {
		$yearSel ="";
	}
	$listActiveYearOptions .= "<option value='$i' $yearSel>$i";
	
	if ($i == $listInactiveYear) {
		$yearSel = "selected";
	} else {
		$yearSel ="";
	}
	$listInactiveYearOptions .= "<option value='$i' $yearSel>$i";
}


$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>			
			<input type=hidden name=id value='$id'>";


include("../../includes/adminAddHeader.php");

?>

<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $hidden;?>
<?php echo $sReloadWindowOpener;?>

<table width=95% align=center>
<tr><td colspan=2>Please don't put the date inside the content.</td></tr>
</table>
<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>

	<tr><td>Question</td>
		<td><textarea name='question' rows=4 cols=45><?php echo $question;?></textarea></td>
	</tr>
	<tr><td>Answer</td>
		<td><textarea name='answer' rows=4 cols=45><?php echo $answer;?></textarea></td>
	</tr>	
	<tr><td>Schedule Active Date For Front Page</td>
		<td><select name=frontActiveMonth><?php echo $frontActiveMonthOptions;?>
	</select> &nbsp;<select name=frontActiveDay><?php echo $frontActiveDayOptions;?>
	</select> &nbsp;<select name=frontActiveYear><?php echo $frontActiveYearOptions;?>
	</select></td>
	</tr>
	<tr><td>Schedule Inactive Date For Front Page</td>
		<td><select name=frontInactiveMonth><?php echo $frontInactiveMonthOptions;?>
	</select> &nbsp;<select name=frontInactiveDay><?php echo $frontInactiveDayOptions;?>
	</select> &nbsp;<select name=frontInactiveYear><?php echo $frontInactiveYearOptions;?>
	</select></td>
	</tr>
	<tr><td>Schedule Active Date For Trivia List Page</td>
		<td><select name=listActiveMonth><?php echo $listActiveMonthOptions;?>
	</select> &nbsp;<select name=listActiveDay><?php echo $listActiveDayOptions;?>
	</select> &nbsp;<select name=listActiveYear><?php echo $listActiveYearOptions;?>
	</select></td>
	</tr>
	<tr><td>Schedule Inactive Date For Trivia List Page</td>
		<td><select name=listInactiveMonth><?php echo $listInactiveMonthOptions;?>
	</select> &nbsp;<select name=listInactiveDay><?php echo $listInactiveDayOptions;?>
	</select> &nbsp;<select name=listInactiveYear><?php echo $listInactiveYearOptions;?>
	</select></td>
	</tr>
</table>

<?php
	include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
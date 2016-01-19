<?php

/*********

Script to Display List/Add/Edit/Delete Affiliate Management Company information

*********/

include("../../../includes/paths.php");


$sPageTitle = "MyHealthyLiving Coupon Management - Add/Edit Coupon";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
		
	
	// SELECT HCV DATABASE
	dbSelect($sGblMhlDBName);	
	
	
if (($sSaveClose || $sSaveNew) && !($id)) {
	// if new data submitted
	$dcStartDate = "$startYear-$startMonth-$startDay";
	$dcEndDate = "$endYear-$endMonth-$endDay";
	if (!($minPurchaseRequired))
		$minPurchase = 0;
	$addQuery = "INSERT INTO discountCoupons(dcCode, dcName, dcAmount, dcType, 
							 dcStartDate, dcEndDate, minPurchase)
				 VALUES('$dcCode', '$dcName', '$dcAmount', '$dcType', 
							 '$dcStartDate', '$dcEndDate', '$minPurchase')";

	// start of track users' activity in nibbles 
	$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

	$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
	  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Add: $addQuery\")"; 
	$rLogResult = dbQuery($sLogAddQuery); 
	echo  dbError(); 
	// end of track users' activity in nibbles		
	
	
	$result = dbQuery($addQuery);
	if (! $result) {
		echo dbError();
	}
	
} elseif (($sSaveClose || $sSaveNew) && ($id)) {
	//if record edited
	$dcStartDate = "$startYear-$startMonth-$startDay";
	$dcEndDate = "$endYear-$endMonth-$endDay";
	if (!($minPurchaseRequired))
		$minPurchase = 0;
	$editQuery = "UPDATE discountCoupons
				  SET 	 dcCode='$dcCode',
						 dcName = '$dcName',
						 dcAmount = '$dcAmount',
						 dcType = '$dcType',
						 dcStartDate = '$dcStartDate',
						 dcEndDate = '$dcEndDate',
						 minPurchase = '$minPurchase'
				  WHERE  id = '$id'";

	// start of track users' activity in nibbles 
	$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

	$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
	  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Edit: $editQuery\")"; 
	$rLogResult = dbQuery($sLogAddQuery); 
	echo  dbError(); 
	// end of track users' activity in nibbles		
	
	
	$result = dbQuery($editQuery);
	echo $editQuery.$result;
}

if ($sSaveClose) {
	echo "<script language=JavaScript>
			window.opener.location.reload();
			self.close();
			</script>";					
	// exit from this script
	exit();
} else if ($sSaveNew) {
	$reloadWindowOpener = "<script language=JavaScript>
							window.opener.location.reload();
							</script>";
	// Reset textboxes for new record
	if ($keepValues != true) {
		$dcCode='';
		$dcName = '';
		$dcAmount = '';
		$dcType = '';
		$dcStartDate = '';
		$dcEndDate = '';
		$minPurchase = '';
		$startMonth = '';
		$startDay = '';
		$startYear = '';
		$endMonth = '';
		$endDay = '';
		$endYear = '';
	}
}


$currYear = date(Y);
$currMonth = date(m); //01 to 12
$currDay = date(d); // 01 to 31

// set curr date values to be selected by default
if (!($sSaveClose || $id )) {
	$startMonth = $currMonth;
	$startDay = $currDay;
	$startYear = $currYear;
	$endMonth = $currMonth;
	$endDay = $currDay;
	$endYear = $currYear+1;
}

if ($id != '') {
	// If Clicked on Edit, display values in fields
	
	// Get the data to display in HTML fields for the record to be edited
	$selectQuery = "SELECT *
					FROM   discountCoupons	 
			  		WHERE  id = '$id'";
	$result = dbQuery($selectQuery);
	
	if ($result) {
		
		while ($row = dbFetchObject($result)) {
			$dcCode = $row->dcCode;
			$dcName = $row->dcName;			
			$dcAmount = $row->dcAmount;
			$dcType = $row->dcType;
			$dcStartDate = $row->dcStartDate;
			$dcEndDate = $row->dcEndDate;
			$minPurchase = $row->minPurchase;
			
			$startYear = substr($dcStartDate,0,4);
			$startMonth = substr($dcStartDate, 5,2);
			$startDay = substr($dcStartDate,8,2);
			$endYear = substr($dcEndDate,0,4);
			$endMonth = substr($dcEndDate, 5,2);
			$endDay = substr($dcEndDate,8,2);
			
		}
		dbFreeResult($result);
	} else {
		echo dbError();
	}
}  else {
	// If add button is clicked, display another two buttons
	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=sAbandonNew value=' Abandon & New  '>";	
}


// prepare month options for From and To date

for ($i = 0; $i < count($aGblMonthsArray); $i++) {
	if ($i < 10) {
		$value ="0".$i+1;
	} else {
		$value =$i+1;
	}
	if ($value == $startMonth) {
		$monthSel = "selected";
	} else {
		$monthSel = "";
	}
	
	$startMonthOptions .= "<option value='$value' $monthSel>$aGblMonthsArray[$i]";
	if ($value == $endMonth) {
		$monthSel = "selected";
	} else {
		$monthSel = "";
	}
	
	$endMonthOptions .= "<option value='$value' $monthSel>$aGblMonthsArray[$i]";
}

// prepare day options for From and To date
for ($i = 1; $i <= 31; $i++) {
	
	if ($i < 10) {
		$value = "0".$i;
	} else {
		$value = $i;
	}
	
	if ($value == $startDay) {
		$daySel = "selected";
	} else {
		$daySel = "";
	}
	$startDayOptions .= "<option value='$value' $daySel>$i";
	
	if ($value == $endDay) {
		$daySel = "selected";
	} else {
		$daySel = "";
	}
	$endDayOptions .= "<option value='$value' $daySel>$i";
	
}

// prepare year options for From and To date
for ($i = $currYear; $i <= $currYear+1; $i++) {
	if ($i == $startYear) {
		$yearSel = "selected";
	} else {
		$yearSel ="";
	}
	$startYearOptions .= "<option value='$i' $yearSel>$i";
	
	if ($i == $endYear) {
		$yearSel = "selected";
	} else {
		$yearSel ="";
	}
	$endYearOptions .= "<option value='$i' $yearSel>$i";
	
}

$percentageSelected = "";
$fixedSelected = "";

switch($dcType) {
	case "2":
		$percentageSelected = "selected";
		break;
	default:
		$fixedSelected = "selected";
}
$dcTypeOptions = "<option value='1' $fixedSelected>Fixed Discount Amount (dollars)
				   <option value='2' $percentageSelected>Percentage Off Sub Total";

$minPurchaseRequiredChecked = '';
$minPurchaseNotRequiredChecked = '';
if ($minPurchase != 0) {	
	$minPurchaseRequiredChecked = "checked";
} else {
	$minPurchaseNotRequiredChecked = "checked";
}
// Hidden variable to be passed with form submit
$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>			
			<input type=hidden name=iParentMenuId value='$iParentMenuId'>
			<input type=hidden name=sParentMenuFolder value='$sParentMenuFolder'>
			<input type=hidden name=id value='$id'>";

	include("$sGblIncludePath/adminAddHeader.php");	

?>

<form action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $hidden;?>
<?php echo $reloadWindowOpener;?>
<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
	<tr><td width=35%>Coupon Code</td>
		<td><input type=text name='dcCode' value='<?php echo $dcCode;?>' ></td>
	</tr>
	<tr><td>Coupon Name</td>
		<td><input type=text name='dcName' value='<?php echo $dcName;?>' ></td>
	</tr>
	<tr><td>Coupon Amount</td>
		<td><input type=text name='dcAmount' value='<?php echo $dcAmount;?>' ></td>
	</tr>
	<tr><td>Coupon Type</td>
		<td><select name='dcType'>
		<?php echo $dcTypeOptions;?>
		</select></td>
	</tr>
	<tr><td>Valid From</td>
		<td><select name=startMonth><?php echo $startMonthOptions;?>
		</select> &nbsp;<select name=startDay><?php echo $startDayOptions;?>
		</select> &nbsp;<select name=startYear><?php echo $startYearOptions;?>
		</select></td>
	</tr>
	<tr><td>Valid Upto</td>
		<td>
		<select name=endMonth><?php echo $endMonthOptions;?>
	</select> &nbsp;<select name=endDay><?php echo $endDayOptions;?>
	</select> &nbsp;<select name=endYear><?php echo $endYearOptions;?>
	</select></td>
	</tr>
	<tr><td>Min. Purchase Required</td>
		<td><input type=radio name=minPurchaseRequired value='' <?php echo $minPurchaseNotRequiredChecked;?>> Not Required </td></tr>
		<tr><Td></td><td><input type=radio name=minPurchaseRequired value='Y' <?php echo $minPurchaseRequiredChecked;?>> Amount &nbsp; <input type=text name='minPurchase' value='<?php echo $minPurchase;?>'></td>
	</tr>
			
</table>

<?php

include("$sGblIncludePath/adminAddFooter.php");
				
} else {
	echo "You are not authorized to access this page...";
}	
?>
<?php

/***********

Script to display Recovery Report

************/

include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");

$sPageTitle = "Executive Overview";

session_start();

// Check user permission to access this page

if (hasAccessRight($iMenuId) || isAdmin()) {
	
	$currYear = date(Y);
	$currMonth = date(m); //01 to 12
	$currDay = date(d); // 01 to 31
	
	// set curr date values to be selected by default
	if (!($submit)) {
		$yesterday = DateAdd("d", -1, date('Y')."-".date('m')."-".date('d'));
		$yearFrom = substr( $yesterday, 0, 4);
		$monthFrom = substr( $yesterday, 5, 2);
		$dayFrom = substr( $yesterday, 8, 2);
		
		$monthTo = $monthFrom;
		$dayTo = $dayFrom;
		$yearTo = $yearFrom;
	}
	
	// prepare month options for From and To date
	for ($i = 0; $i < count($aGblMonthsArray); $i++) {
		$value = $i + 1;
		if ($value < 10) {
			$value ="0".$value;
		}
		
		if ($value == $monthFrom) {
			$fromSel = "selected";
		} else {
			$fromSel = "";
		}
		if ($value == $monthTo) {
			$toSel = "selected";
		} else {
			$toSel = "";
		}
		
		$monthFromOptions .= "<option value='$value' $fromSel>$aGblMonthsArray[$i]";
		$monthToOptions .= "<option value='$value' $toSel>$aGblMonthsArray[$i]";
	}
	
	// prepare day options for From and To date
	for ($i = 1; $i <= 31; $i++) {
		
		$value  = $i;
		if ($i < 10) {
			$value = "0".$value;
		}
		
		if ($value == $dayFrom) {
			$fromSel = "selected";
		} else {
			$fromSel = "";
		}
		if ($value == $dayTo) {
			$toSel = "selected";
		} else {
			$toSel = "";
		}
		$dayFromOptions .= "<option value='$value' $fromSel>$i";
		$dayToOptions .= "<option value='$value' $toSel>$i";
	}
	
	// prepare year options for From and To date
	for ($i = $currYear; $i >= $currYear-5; $i--) {
		
		if ($i == $yearFrom) {
			$fromSel = "selected";
		} else {
			$fromSel ="";
		}
		if ($i == $yearTo) {
			$toSel = "selected";
		} else {
			$toSel ="";
		}
		
		$yearFromOptions .= "<option value='$i' $fromSel>$i";
		$yearToOptions .= "<option value='$i' $toSel>$i";
	}
	
	$dateFrom = "$yearFrom-$monthFrom-$dayFrom";
	$dateTo = "$yearTo-$monthTo-$dayTo";
	
	// check if selected dates are valid dates
	if (checkDate($monthFrom, $dayFrom, $yearFrom) && checkdate($monthTo, $dayTo,$yearTo)) {
		
		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"View Report: $dateFrom to $dateTo\")"; 
		$rLogResult = dbQuery($sLogAddQuery); 
		echo  dbError(); 
		// end of track users' activity in nibbles		
		
		
		// get myfree display count
		
		$myFreeQuery = "SELECT sum(counts) as counts
						FROM   myFreeDisplayCounts
						WHERE  clickDate BETWEEN  '$dateFrom' AND '$dateTo'";
		
		$myFreeResult = mysql_query($myFreeQuery);		
		while ($myFreeRow = mysql_fetch_object($myFreeResult)) {
			$myFreeDisplayCount = $myFreeRow->counts;			
		}
		
		// offers taken count
		$offersQuery = "SELECT sum(clicks) counts
						  FROM   edOfferRedirectsTrackingHistorySum
						  WHERE  clickDate BETWEEN '$dateFrom' AND  '$dateTo'";
		$offersResult = mysql_query($offersQuery);
		while ($offersRow = mysql_fetch_object($offersResult)) {
			$offersTakenCount = $offersRow->counts;
		}
		
		// get bdRedirect count
		$redirectQuery = "SELECT sum(clicks) counts
						  FROM   bdRedirectsTrackingHistorySum
						  WHERE  clickDate BETWEEN '$dateFrom' AND  '$dateTo'";
		$redirectResult = mysql_query($redirectQuery);
		while ($redirectRow = mysql_fetch_object($redirectResult)) {
			$redirectCount = $redirectRow->counts;
		}

		// fp display count
		$fpQuery = "SELECT sum(counts) AS counts
					FROM   fpDisplayCounts
					WHERE  clickDate BETWEEN '$dateFrom' AND  '$dateTo'";
		$fpResult = mysql_query($fpQuery);
		
		while ($fpRow = mysql_fetch_object($fpResult)) {
			$fpCount = $fpRow->counts;			
		}
		
		// TAF count
		$fpTAFQuery = "SELECT sum(counts) AS counts
					FROM   fpTAFCounts
					WHERE  clickDate BETWEEN '$dateFrom' AND  '$dateTo'";
		$fpTAFResult = mysql_query($fpTAFQuery);
		while ($fpTAFRow = mysql_fetch_object($fpTAFResult)) {
			$fpTAFCount = $fpTAFRow->counts;
		}
		
		// fp BestDeals subscription count
		$fpBDQuery = "SELECT sum(counts) AS counts
					FROM   fpBDCounts
					WHERE  clickDate BETWEEN '$dateFrom' AND  '$dateTo'";
		$fpBDResult = mysql_query($fpBDQuery);
		while ($fpBDRow = mysql_fetch_object($fpBDResult)) {
			$fpBDCount = $fpBDRow->counts;
		}
				
		// HL orders counts
		
		$orderQuery = "SELECT count(orderId) orderCount, sum(prOurPrice*OD.quantity) AS orderTotal, 
							  sum(prCost*OD.quantity) AS orderCost
					   FROM   $sGblMhlDBName.orders O, $sGblMhlDBName.orderDetails OD, $sGblMhlDBName.products P
					   WHERE  O.orID = OD.orderId
					   AND    OD.productId = P.prID
					   AND    substring(O.orDate,1,10) BETWEEN '$dateFrom' AND '$dateTo'
					   GROUP BY O.orID";
		
		
		$orderResult = mysql_query($orderQuery);
		
		while ($orderRow = mysql_fetch_object($orderResult)) {
			$orderCount = $orderRow->orderCount;
			$orderTotal = $orderRow->orderTotal;
			$orderCost = $orderRow->orderCost;
			$orderProfit = $orderTotal - $orderCost;
		}
		
		
		// get you won display counts
		
		$ywQuery = "SELECT count(id) counts
					FROM   youWonTest
					WHERE  dateWon BETWEEN '$dateFrom' AND '$dateTo'";
		$ywResult = mysql_query($ywQuery);
		while ($ywRow = mysql_fetch_object($ywResult)) {
			$ywCount = $ywRow->counts;
		}
		
		// get you won responded counts
		
		$ywQuery = "SELECT count(id) counts
					FROM   youWonTest
					WHERE  dateWon BETWEEN '$dateFrom' AND '$dateTo'
					AND    responded = 'Y'";
		$ywResult = mysql_query($ywQuery);
		while ($ywRow = mysql_fetch_object($ywResult)) {
			$ywRespondedCount = $ywRow->counts;
		}
		
	} else {
		$message = "Please Select Valid Dates...";
	}		
	
	// Hidden variable to be passed with form submit
	$hidden =  "<input type=hidden name=iMenuId value='$iMenuId'>";
	
	include("../../includes/adminHeader.php");	
	
?>

<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $hidden;?>
<table width=95% align=center bgcolor=c9c9c9>
	<tr><td>Date from</td><td><select name=monthFrom><?php echo $monthFromOptions;?>
	</select> &nbsp;<select name=dayFrom><?php echo $dayFromOptions;?>
	</select> &nbsp;<select name=yearFrom><?php echo $yearFromOptions;?>
	</select></td><td>Date to</td>
	<td><select name=monthTo><?php echo $monthToOptions;?>
	</select> &nbsp;<select name=dayTo><?php echo $dayToOptions;?>
	</select> &nbsp;<select name=yearTo><?php echo $yearToOptions;?>
	</select></td></tr>
	<tr>
<td><input type=submit name=submit value='View Report'></td></tr>

			</table>
			
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>

<tr><td class=header colspan=3>MyFree Summary: </td></tr>
<tr><td class=header> &nbsp; &nbsp; &nbsp; Home Page Display Count</td><td class=header>Offers Taken Count</td><td class=header>BD Redirects Count</td></tr>
<tr><td> &nbsp; &nbsp; &nbsp; <?php echo $myFreeDisplayCount;?></td><td><?php echo $offersTakenCount;?></td><td><?php echo $redirectCount;?></td></tr>

<tr><td class=header colspan=3><BR>Fun Pages Summary: </td></tr>
<tr><td class=header> &nbsp; &nbsp; &nbsp; Fun Pages Display Count</td><td class=header>TAF Count</td><td class=header>Best Deals Subscribe Count</td></tr>
<tr><td> &nbsp; &nbsp; &nbsp; <?php echo $fpCount;?></td><td><?php echo $fpTAFCount;?></td><td><?php echo $fpBDCount;?></td></tr>

<tr><td class=header colspan=3><BR>MyHealthyLiving Summary: </td></tr>
<tr><td class=header> &nbsp; Orders Count</td><td class=header>Orders Total</td><td class=header>Orders Cost</td><td class=header>Orders Profit</td></tr>
<tr><td> &nbsp; &nbsp; &nbsp; <?php echo $orderCount;?></td><td><?php echo $orderTotal;?></td><td><?php echo $orderCost;?></td><td><?php echo $orderProfit;?></td></tr>


<tr><td class=header colspan=3><BR>You Won Summary: </td></tr>
<tr><td class=header> &nbsp; &nbsp; &nbsp; You Won Display Count</td><td class=header>You Won Responded Count</td></tr>
<tr><td> &nbsp; &nbsp; &nbsp; <?php echo $ywCount;?></td><td><?php echo $ywRespondedCount;?></td></tr>

</table>
</form>

<?php
	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
<?php

include("../../../includes/paths.php");

session_start();

$sPageTitle = "MyHealthyLiving Order Processing - Order Detail";

if (hasAccessRight($iMenuId) || isAdmin()) {
		
	
	// SELECT HCV DATABASE
	dbSelect($sGblMhlDBName);	

if ($cnId) {
	$deleteQuery = "DELETE FROM customer_notes
					WHERE  id = '$cnId'";
	$deleteResult = dbQuery($deleteQuery);
}

if ($sSaveClose || $sSave) {
	
	if ($cnNotes) {
		
		$sql = "INSERT INTO customer_notes (cnOrderID, cnDate, cnNotes)
				VALUES ('$orID', now(), '$cnNotes')";
		$result = dbQuery($sql);
		if (!$result)
		echo dbError();
	}
	
	$orShipDate = "$shipYear-$shipMonth-$shipDay";
	
	// get previous status
	//Update Actual Shipping Method & UPS Tracking Number
	if ($prevOrderStatus != $orderStatus && $orderStatus == 'shipped') {
		
		$sql = "UPDATE orders
			SET    orActualShipping = '$orActualShipping',				   
				   orUPSTrackingNumber = '$orUPSTrackingNumber',
				   orStatus = '$orderStatus',
				   orShipDate = '$orShipDate'	
			WHERE orID= '$orID'";
		
		$result = dbQuery($sql);
		if ($result) {
			// email customer about shipping
			//$message = str_replace("#UPS#",$UPSNumber,$message);
			//mail($cuEmail,"Your Order From $sitename Has Been Shipped",$message,"From: $webmasterEmail");
		}
	} else {
		$sql = "UPDATE orders
				SET    orStatus = '$orderStatus',
					   orShipDate = '$orShipDate'	
				WHERE orID= '$orID'";
		$result = dbQuery($sql);
	}

	// start of track users' activity in nibbles 
	$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
	mysql_select_db ($dbase); 

	$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
	  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Update: $sql\")"; 
	$rLogResult = dbQuery($sLogAddQuery); 
	echo  dbError(); 

	// SELECT HCV DATABASE
	dbSelect($sGblMhlDBName);	

	// end of track users' activity in nibbles		
	
	
	
	if ($sSave) {
		echo "<script language=JavaScript>
			window.opener.location.reload();
			</script>";					
		// exit from this script
		
	} else if ($sSaveClose) {
		echo "<script language=JavaScript>
			window.opener.location.reload();
			self.close();
			</script>";					
		// exit from this script
		exit();
	}
}

$selectQuery = "SELECT *
				FROM   orders ord
				INNER JOIN customer cu ON  ord.orCustomerID = cu.cuId					
				WHERE ord.orID = '$orID'";

$selectResult = dbQuery($selectQuery);

while ($row = dbFetchObject($selectResult)) {
	$cuName = "$row->cuFirst $row->cuLast";
	$orderId  = $row->orID;
	
	$orderDate =  $row->orDate;
	$cuAddress = $row->cuAddress;
	$cuAddress2 = $row->cuAddress2;
	if ($cuAddress2) {
		$cuAddress2 .= "<br>";
	}
	$cuCity = $row->cuCity;
	$cuState = $row->cuState;
	$cuZipCode = $row->cuZipCode;
	$cuPhone = $row->cuPhone;
	$cuEmail = $row->cuEmail;
	$cuEveningPhone = $row->eveningPhone;
	if ($cuEveningPhone) {
		$cuEveningPhone = " Evening Phone:  $cuEveningPhone<br><br>";
	}
	
	$shipToName = "$row->orShipToFirst $row->orShipToLast";
	$shipToAddress = $row->orShipToAddress;
	$shipToAddress2 = $row->orShipToAddress2;
	if ($shipToAddress2)
		$shipToAddress2 .= "<br>";
	$shipToCity = $row->orShipToCity;
	$shipToState = $row->orShipToState;
	$shipToZipCode = $row->orShipToZipCode;
	If ($row->orShipToPhone != "" ) {
		$shipToPhone = "<BR>Contact Phone:  $row->orShipToPhone<br>";
	}
	// if Billing address is same
	if ($cuName == $shipToName && $cuAddress == $shipToAddress && 
		$cuAddress2 == $shipToAddress2 && $cuCity == $shipToCity &&
		$cuState == $shipToState && $cuZipCode == $shipToZipCode) {
		$shipToName = "Same as Billing Address";
		$shipToAddress = "";
		$shipToAddress2 = "";
		$shipToCity = "";
		$shipToState = "";
		$shipToState = "";
		$shipToZipCode = "";	
		$shipToPhone = "";
		} else {
			$shipToState .= ",";
		}
	
	
	if ($row->orCouponID) {
		$couponDiscount = "<tr class=hlHeader>
                          <td class=header colspan=3 align=RIGHT>Coupon #$row->orCouponID:&nbsp;&nbsp;</td>
                          <td class=header align=RIGHT><font color='#ff0000'>
                            - $ ".number_format($row->orCouponSavings,2)."</font>
							</td>
                        </tr>";
	}
	$orAmount = number_format($row->orAmount,2);
	$orShipping = number_format($row->orShipping,2);
	$orTax = number_format($row->orTax,2);
	$totalCost = $orAmount + $orShipping + $orTax;
	
	$ccType = $row->orCCType;
	$ccNumber = $row->orCCNumber;
	$ccNameOnCard = $row->orCCNameOnCard;
	$ccExpDate = $row->orCCExpDate;
	
	// get shipping method
	$shQuery = "SELECT *
				FROM   shippingMethods 
				WHERE  id = '".$row->orShippingMethod."'";
	$shResult = dbQuery($shQuery);
	while ($shRow = dbFetchObject($shResult)) {
		$shippingMethod = $shRow->method;
	}
	
	//$shippingMethod = $row->method;
	$orActShip = $row->orActualShipping;
	$orShipDate = $row->orShipDate;
	$shipYear = substr($orShipDate,0,4);
	$shipMonth = substr($orShipDate,5,2);
	$shipDay = substr($orShipDate,8,2);
	//echo $shipYear.$shipMonth.$shipDay;
	$actualShippingOptions = "<option value=''>";
	$shipQuery = "SELECT *
				  FROM   shippingMethods";
	$shipResult = dbQuery($shipQuery);
	while ($shipRow = dbFetchObject($shipResult)) {
		if ($shipRow->id == $orActShip)
		$selected = "selected";
		else
		$selected = "";
		$actualShippingOptions .= "<option value=$shipRow->id $selected>$shipRow->method";
	}
	$upsNumber = $row->orUPSTrackingNumber;
	$orderStatus = $row->orStatus;
}


$odQuery = "SELECT *
 			  FROM  orderDetails, products
 			  WHERE orderDetails.orderId = '$orID'
 			  AND   orderDetails.productId = products.prID";
$odResult = dbQuery($odQuery);
//echo $odQuery. mysql_num_rows($odResult);
$subTotal = 0;
$totalCPDiscount = 0;
while ($odRow = dbFetchObject($odResult)) {
	
	$odList .= "<tr bgcolor=#FFFFFF><td>$odRow->prNo<BR>$odRow->mfgPrNo</td>
     <td>$odRow->prName</td><td>$odRow->unitPrice</td>
      <td>$odRow->quantity</td>	 
     <td align=right>$ ";
	$strPrice = $odRow->unitPrice * $odRow->quantity;
	$subTotal += $strPrice;
	$cpDiscount = $RowShow[cpDiscount] * $RowShow[quantity];
	$totalCPDiscount += $cpDiscount;
	$odList .= number_format($strPrice,2) . "</b></td></tr>";
}
$subTotal = number_format($subTotal,2);
$totalCPDiscount = number_format($totalCPDiscount,2);

//<!-- ORDER NOTES SECTION -->

$sql = "select * from customer_notes where cnOrderID = '$orID'";
$oResult = dbQuery($sql);
if (!$oResult)
echo dbError();

if (dbNumRows($oResult)) {
	
	while($oRow=dbFetchObject($oResult)) {
		$cuOrderNotes .= "<li>" . $oRow->cnDate . " - " . nl2br($oRow->cnNotes) .
		" <a href='$PHP_SELF?iMenuId=$iMenuId&iParentMenuId=$iParentMenuId&sParentMenuFolder=$sParentMenuFolder&orID=$orID&cnId=$oRow->id'>Delete Note</a><br>&nbsp";
	}
}

$deletedSelected = "";
$shippedSelected = "";
$inProcessSelected = "";

switch($orderStatus) {
	case "Deleted":
	$deletedSelected = "selected";
	break;
	case "Shipped":
	$shippedSelected = "selected";
	break;
	case "In Process":
	default:
	$inProcessSelected = "selected";
}

$statusOptions = "<option value='In Process' $inProcessSelected>In Process
				  <option value='Shipped' $shippedSelected>Shipped
				  <option value='Deleted' $deletedSelected>Deleted";


// prepare month options for ship date
$shipMonthOptions ="<option value=''>Month";
for ($i = 0; $i < count($aGblMonthsArray); $i++) {
	$value = $i+1;
	if ($i < 10) {
		$value ="0".$value;
	} else {
		$value =$value;
	}
	if ($value == $shipMonth) {
		$monthSel = "selected";
	} else {
		$monthSel = "";
	}
	
	$shipMonthOptions .= "<option value='$value' $monthSel>$aGblMonthsArray[$i]";
}


// prepare day options for ship date
$shipDayOptions ="<option value=''>Day";
for ($i = 1; $i <= 31; $i++) {
	
	if ($i < 10) {
		$value = "0".$i;
	} else {
		$value = $i;
	}
	
	if ($value == $shipDay) {
		$daySel = "selected";
	} else {
		$daySel = "";
	}
	$shipDayOptions .= "<option value='$value' $daySel>$i";
		
}


// prepare year options for ship date
$currYear = date("Y");
$shipYearOptions .="<option value=''>Year";
for ($i = $currYear-1; $i <= $currYear+1; $i++) {
	if ($i == $shipYear) {
		$yearSel = "selected";
	} else {
		$yearSel ="";
	}
	$shipYearOptions .= "<option value='$i' $yearSel>$i";		
}


$hidden .= "<input type=hidden name=prevOrderStatus value='$orderStatus'>
	<input type=hidden name=orID value='$orID'>
	<input type=hidden name=cuID value='$cuId'>
	<input type=hidden name=cuEmail value='$cuEmail'>
	<input type=hidden name=iMenuId value='$iMenuId'>	
	<input type=hidden name=iParentMenuId value='$iParentMenuId'>
	<input type=hidden name=sParentMenuFolder value='$sParentMenuFolder'>";

$sNewEntryButtons = "<BR><BR><input type=submit name=sSave value=' Save '>";


	include("$sGblIncludePath/adminAddHeader.php");	

?>

<form action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $hidden;?>
<table border=0 cellpadding=3 width="600">

<tr><Td class=header>Ordered By: <?php echo $cuName;?></td></tr>
<tR><td class=header>Order ID: <?php echo $orderId;?></td></tr>
<tr><td class=header>Date Ordered: <?php echo $orderDate;?></td></tr>

            <TR> 
              <TD colspan="7"><!--Page content starts here -->
                <table border="0" width="100%" cellpadding="2" bgcolor="#3366CC">
                  <tr> 
                    <td> 
                      <table width="100%" border="0" cellpadding="3" bgcolor="#FFFFFF">
                        <tr class=hlHeader> 
                          <td class=header width="250">Bill to:</td>
                          <td class=header width="250">Ship to:</td>
                        </tr>
                        <tr> 
                          <td valign="TOP"  width="250">
                          <?php echo $cuName;?><br>
                          <?php echo $cuAddress;?><br>
                         <?php echo $cuAddress2;?>		
                          <?php echo $cuCity;?> <?php echo $cuState;?>, <?php echo $cuZipCode;?><BR>
				<br>Daytime Phone:  <?php echo $cuPhone;?><br>
				<?php echo $cuEveningPhone;?>
									    					
					Email Address: <?php echo $cuEmail;?><br>					

                            </font> </td>
                          <td valign="TOP" width="250">
	  
                          <?php echo $shipToName;?><br>
                          <?php echo $shipToAddress;?><BR>
                          <?php echo $shipToAddress2;?>
                          <?php echo $shipToCity;?>
                          <?php echo $shipToState;?>
                          <?php echo $shipToZipCode;?><BR>						
                          <?php echo $shipToPhone;?>

                            </td>
                        </tr>                        
		
                      </table>
                      
                    </td>
                  </tr>
                  <tr> 
                    <td> 
                      <table width="100%"  border=0 cellpadding="3" cellspacing=1 bgcolor="#999999">
                        <tr class=hlHeader> 
                          <!--<td  bgcolor="#eeeeee"><font face="arial, helvetica"><b>Qty</b></font></td>-->
                          <td class=header>Product No./SKU.</td>
                          <td class=header>Item</td>                          
                          <td class=header>Unit Price</td>						  
                          <td class=header>Quantity</td>
                          <td class=header align=right">Total</td>
                        </tr>
                        <?php echo $odList;?>
<tr class=hlHeader> 
    <td colspan="4" align="RIGHT" class=header>Subtotal:&nbsp;&nbsp;</td>
    <td align="RIGHT" class=header><nobr>
       $ <?php echo $subTotal;?>
	</td>
</tr>
<!-- End of Loop--> 
<?php echo $couponDiscount;?>
                        
                        <tr  class=hlHeader> 
    <td colspan="4" align="RIGHT" class=header><b>Discount for Enrolling in the Convenience Plan Program:&nbsp;&nbsp;</b></td>
    <td  class=header align="RIGHT"><nobr><font color="#ff0000">
       - $ <?php echo $totalCPDiscount;?></font>
	</td>
</tr>    

                        <tr class=hlHeader> 
                          <td  class=header colspan="4" align="RIGHT">Total Product Cost:&nbsp;&nbsp;</td>
                          <td  class=header align="RIGHT">$ <?php echo $orAmount;?></td>
                        </tr>

                        <tr class=hlHeader> 
                          <td class=header colspan="4" align="RIGHT">Shipping &amp; Handling:&nbsp;&nbsp;</td>
                          <td class=header align="RIGHT"><nobr>$ <?php echo $orShipping;?> </td>
                        </tr>
                        <tr class=hlHeader> 
                          <td class=header colspan="4" align="RIGHT">Sales Tax:&nbsp;&nbsp;</td>
                          <td class=header align="RIGHT"><nobr>$ <?php echo $orTax;?></td>
                        </tr>

                    <tr class=hlHeader> 
                          <td class=header colspan="4" align="RIGHT">Total:&nbsp;&nbsp;</td>
                          <td class=header align="RIGHT"><nobr>$ <?php echo $totalCost;?></td>
                        </tr>
                      </table>
                    </td>
                  </tr>
                  <tr> 
                    <td> 
                      <table border="0" cellpadding="3" width="100%" bgcolor="#FFFFFF">
                        <tr class=hlHeader> 
                          <td class=header colspan=3>Payment Information</td>
                        </tr>
                        <tr> 
                          <td  valign="TOP" colspan="2"> 
                            <table width="100%" bgcolor="#FFFFFF">
                              <tr> 
                                <td width="28%" class=header>Card Type</td>
                                <td ><?php echo $ccType;?></td>
                                  
                              </tr>
                              <tr> 
                                <td class=header width="28%" class=header>Card Number </td>
                                <td>
                                <?php echo $ccNumber;?>
                                </td>
                              </tr>


                              <tr>
                                <td  valign="MIDDLE" width="28%" class=header>Name 
                                  on Card</td>
                                <td  valign="TOP"><?php echo $ccNameOnCard;?>
                                </td>
                              </tr>
                              <tr> 
                                <td width="28%" class=header>Expiration</td>
                                <td  valign="TOP"><?php echo $ccExpDate;?>
                                </td>
                              </tr>
                            </table>
                    </td>
                  </tr>
                   <tr> 
                    <td> 
		       <table border="0" cellpadding="3" width="100%" bgcolor="#FFFFFF">

                        <tr class=hlHeader> 
                          <td class=header align=center>Order Notes</td>
                        </tr>
                        <tr> 
                          <td ><ul>
                          <?php echo $cuOrderNotes;?>
                          			</ul>
				</td>
                        </tr>
		       </table>
                    </td>
                  </tr>
 </table>
                 
                </TD>
            </TR>
            
</table>
<tr><td>

<table width="550">
	<tr><td  class=header>
	Requested Shipping Method </td>
	<td><?php echo $shippingMethod;?></td></tr>
	<tr><td class=header>Actual Shipping Method <br><font size="1">Customer Does Not See This</font></td>
	<td><select name="orActualShipping">	
	<?php echo $actualShippingOptions;?>
	</select></td></tr>
		
	<tr><td class=header>Order Status</td><td>
		<select name="orderStatus">
		<?php echo $statusOptions;?></td></tr>
	<tr><td class=header>Ship Date</td>
		<td><select name=shipMonth><?php echo $shipMonthOptions;?>
	</select> &nbsp;<select name=shipDay><?php echo $shipDayOptions;?>
	</select> &nbsp;<select name=shipYear><?php echo $shipYearOptions;?>
	</select></td>
	</tr>
	
	<tr><td class=header>UPS Tracking Number</td>
		<td><input type=text name=orUPSTrackingNumber value='<?php echo $upsNumber;?>'>
		</select>
		</td></tr>
		<tr><td  class=header valign=top>
	Order Notes </td>
	<td valign="top"><textarea name="cnNotes" rows="5" cols="30"></textarea>
		</table>
	
	</td></tr>
</td></tr>
</table>

<?php

include("$sGblIncludePath/adminAddFooter.php");
				
} else {
	echo "You are not authorized to access this page...";
}	

?>
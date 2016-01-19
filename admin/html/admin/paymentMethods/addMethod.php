<?php

/*********

Script to Display Add/Edit Payment Method

**********/

include("../../includes/paths.php");

session_start();

$sPageTitle = "Nibbles Payment Methods - Add/Edit Payment Method";

if (hasAccessRight($iMenuId) || isAdmin()) {
	
if (($sSaveClose || $sSaveNew) && !($iId)) {
	// if new payment method added
	
	// check if already exists
	$sCheckQuery = "SELECT *
					FROM   paymentMethods
					WHERE  method = '$sMethod'";
	$rCheckResult = dbQuery($sCheckQuery);
	if (dbNumRows($rCheckResult)>0) {
		$sMessage = "Payment method already exists...";
		$bKeepValues = true;
	} else {
		
		$sAddQuery = "INSERT INTO paymentMethods(method)
					 VALUES('$sMethod')";
		$rResult = dbQuery($sAddQuery);
		if ($rResult) {
			$iPaymentMethodId = dbInsertId();
		} else {
			$sMessage = dbError();
		}
		
	}
	
} else if (($sSaveClose || $sSaveNew) && ($iId)) {
	
	// check if already exists
	$sCheckQuery = "SELECT *
					FROM   paymentMethods
					WHERE  method = '$sMethod'
					AND    id != '$iId'";
	$rCheckResult = dbQuery($sCheckQuery);
	if (dbNumRows($rCheckResult)>0) {
		$sMessage = "Payment method already exists...";
		$bKeepValues = true;
	} else {
		$sEditQuery = "UPDATE paymentMethods
					  SET method = '$sMethod'
					  WHERE id = '$iId'";
		$rResult = dbQuery($sEditQuery);
		
		if (!($rResult)) {
			$sMessage = dbError();
		}
	}
}

if ($sSaveClose) {
	if ($bKeepValues != true ) {
		if ($sReturnTo == 'list') {
		echo "<script language=JavaScript>
			window.opener.location.reload();
			self.close();
			</script>";			
		} else {
			echo "<script language=JavaScript>
				var paymentMethodValue=new String('$iPaymentMethodId');
				var paymentMethodText=new String('$sMethod');
				var v2 = window.opener.document.form1.$sReturnTo.value;
				var i = window.opener.document.form1.$sReturnTo.length;
				var agt=navigator.userAgent.toLowerCase();
				if (agt.indexOf(\"msie\") != -1) {						
					var paymentMethodOpt;

					paymentMethodOpt              = window.opener.document.createElement('option');
					paymentMethodOpt.value        = paymentMethodValue;
					paymentMethodOpt.text         = paymentMethodText;				

					window.opener.document.form1.$sReturnTo.options.add(paymentMethodOpt);
				
				} else {
					//if browser is not IE			
					var paymentMethodOpt=new Option(paymentMethodText, paymentMethodValue);
					eval(\"window.opener.document.form1.$sReturnTo.options[i]=paymentMethodOpt\");
				}		
			self.close();
			</script>";		
		}
		// exit from this script
		exit();
	}
} else if ($sSaveNew) {
	if ($bKeepValues != true) {
		if ($sReturnTo == 'list') {
			$sReloadWindowOpener = "<script language=JavaScript>
								window.opener.location.reload();
								</script>";	
		} else {
			echo "<script language=JavaScript>
				var paymentMethodValue=new String('$iPaymentMethodId');
				var paymentMethodText=new String('$sMethod');
				var v2 = window.opener.document.form1.$sReturnTo.value;
				var i = window.opener.document.form1.$sReturnTo.length;
				var agt=navigator.userAgent.toLowerCase();
				if (agt.indexOf(\"msie\") != -1) {						
					var paymentMethodOpt;

					paymentMethodOpt              = window.opener.document.createElement('option');
					paymentMethodOpt.value        = paymentMethodValue;
					paymentMethodOpt.text         = paymentMethodText;				

					window.opener.document.form1.$sReturnTo.options.add(paymentMethodOpt);
				
				} else {
					//if browser is not IE			
					var paymentMethodOpt=new Option(paymentMethodText, paymentMethodValue);
					eval(\"window.opener.document.form1.$sReturnTo.options[i]=paymentMethodOpt\");
				}		
			self.close();
			</script>";	
		}
		$sMethod = '';
	}
}

if ($iId) {
	
	// If Clicked to edit, get the data to display in fields
	
	$sSelectQuery = "SELECT * FROM paymentMethods
				    WHERE  id = '$iId'";
	$rSelectResult = dbQuery($sSelectQuery);
	while ($oSelectRow = dbFetchObject($rSelectResult)) {
		$sMethod = $oSelectRow->method;
	}
} else {
	
	// If add button is clicked, display another two buttons
	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=sAbandonNew value=' Abandon & New  '>";	
}

// Hidden fields to be passed with form submission
$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=sReturnTo value='$sReturnTo'>
			<input type=hidden name=iId value='$iId'>";

include("../../includes/adminAddHeader.php");
?>

<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $sHidden;?>
<?php echo $sReloadWindowOpener;?>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
		<tr><TD>Payment Method</td><td><input type=text name=sMethod value='<?php echo $sMethod;?>'></td></tr>
	</table>	
		
<?php
	include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
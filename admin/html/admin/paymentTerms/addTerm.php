<?php

/*********

Script to Display Add/Edit Payment Terms

**********/

include("../../includes/paths.php");

session_start();

$sPageTitle = "Nibbles Payment Terms - Add/Edit Payment Term";

if (hasAccessRight($iMenuId) || isAdmin()) {
	
if (($sSaveClose || $sSaveNew) && !($iId)) {
	// if new payment term added
	
	// check if already exists
	$sCheckQuery = "SELECT *
					FROM   paymentTerms
					WHERE  paymentTerm = '$sPaymentTerm'";
	$rCheckResult = dbQuery($sCheckQuery);
	if (dbNumRows($rCheckResult)>0) {
		$sMessage = "Payment term already exists...";
		$bKeepValues = true;
	} else {
		
		$sAddQuery = "INSERT INTO paymentTerms(paymentTerm)
					 VALUES('$sPaymentTerm')";

		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Add: $sAddQuery\")"; 
		$rLogResult = dbQuery($sLogAddQuery); 
		echo  dbError(); 
		// end of track users' activity in nibbles		
		
		
		$rResult = dbQuery($sAddQuery);
		if ($rResult) {
			
			$sCheckQuery = "SELECT id
	   			FROM   paymentTerms
	   			WHERE  paymentTerm = '$sPaymentTerm'"; 
			$rCheckResult = dbQuery($sCheckQuery);
			$sRow = dbFetchObject($rCheckResult);
			
			
			$iPaymentTermId = $sRow->id;
		} else {
			$sMessage = dbError();
		}
		
	}
	
} else if (($sSaveClose || $sSaveNew) && ($iId)) {
	
	// check if already exists
	$sCheckQuery = "SELECT *
					FROM   paymentTerms
					WHERE  paymentTerm = '$sPaymentTerm'
					AND    id != '$iId'";
	$rCheckResult = dbQuery($sCheckQuery);
	if (dbNumRows($rCheckResult)>0) {
		$sMessage = "Payment Term already exists...";
		$bKeepValues = true;
	} else {
		$sEditQuery = "UPDATE paymentTerms
					  SET paymentTerm = '$sPaymentTerm'
					  WHERE id = '$iId'";

		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Edit: $sEditQuery\")"; 
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
	if ($bKeepValues != true ) {
		if ($sReturnTo == 'list') {
		echo "<script language=JavaScript>
			window.opener.location.reload();
			self.close();
			</script>";			
		} else {
			echo "<script language=JavaScript>
				var paymentTermValue=new String('$iPaymentTermId');
				var paymentTermText=new String('$sPaymentTerm');
				var v2 = window.opener.document.form1.$sReturnTo.value;
				var i = window.opener.document.form1.$sReturnTo.length;
				var agt=navigator.userAgent.toLowerCase();
				if (agt.indexOf(\"msie\") != -1) {						
					var paymentTermOpt;

					paymentTermOpt              = window.opener.document.createElement('option');
					paymentTermOpt.value        = paymentTermValue;
					paymentTermOpt.text         = paymentTermText;				

					window.opener.document.form1.$sReturnTo.options.add(paymentTermOpt);
				
				} else {
					//if browser is not IE			
					var paymentTermOpt=new Option(paymentTermText, paymentTermValue);
					eval(\"window.opener.document.form1.$sReturnTo.options[i]=paymentTermOpt\");
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
				var paymentTermValue=new String('$iPaymentTermId');
				var paymentTermText=new String('$sPaymentTerm');
				var v2 = window.opener.document.form1.$sReturnTo.value;
				var i = window.opener.document.form1.$sReturnTo.length;
				var agt=navigator.userAgent.toLowerCase();
				if (agt.indexOf(\"msie\") != -1) {						
					var paymentTermOpt;

					paymentTermOpt              = window.opener.document.createElement('option');
					paymentTermOpt.value        = paymentTermValue;
					paymentTermOpt.text         = paymentTermText;				

					window.opener.document.form1.$sReturnTo.options.add(paymentTermOpt);
				
				} else {
					//if browser is not IE			
					var paymentTermOpt=new Option(paymentTermText, paymentTermValue);
					eval(\"window.opener.document.form1.$sReturnTo.options[i]=paymentTermOpt\");
				}		
			self.close();
			</script>";		
		}
		$sPaymentTerm = '';
	}
}

if ($iId) {
	
	// If Clicked to edit, get the data to display in fields
	
	$sSelectQuery = "SELECT * FROM paymentTerms
				    WHERE  id = '$iId'";
	$rSelectResult = dbQuery($sSelectQuery);
	while ($oSelectRow = dbFetchObject($rSelectResult)) {
		$sPaymentTerm = $oSelectRow->paymentTerm;
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
		<tr><TD>Payment Term</td><td><input type=text name=sPaymentTerm value='<?php echo $sPaymentTerm;?>'></td></tr>
	</table>	
		
<?php
	include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
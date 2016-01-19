<?php

exit;

include("../config.php");
/*include("libs/validationFunctions.php");

$selectQuery = "SELECT id, email
				FROM   tempEmail LIMIT 0,500000";
$selectResult = mysql_query($selectQuery);
echo mysql_error();
while ($selectRow = mysql_fetch_object($selectResult)) {
	$id = $selectRow->id;
	$email = $selectRow->email;
	if (validateEmailFormat($email)) {
		$deleteQuery = "DELETE FROM tempEmail
						WHERE id = '$id'";		
		$deleteResult = mysql_query($deleteQuery);
		
	} 	else {
		echo "<BR>".$email;
	}	
	
}
				

$sTestLeadsQuery = "SELECT *
		  							   FROM   userDataHistory
		  							   WHERE address like '3401 DUNDEE%'";
		  			$rTestLeadsResult = mysql_query($sTestLeadsQuery);
		  			echo mysql_num_rows($rTestLeadsResult);
		  			while ($oTestLeadsRow = mysql_fetch_object($rTestLeadsResult)) {
		  				$sTestEmail = $oTestLeadsRow->email;
		  				$sOtDataUpdateQuery = "UPDATE otDataHistory
								 SET    processStatus = 'R',
										reasonCode = 'tst',
										dateTimeProcessed = now()
								 WHERE  email = '$sTestEmail'";
						
					$rOtDataUpdateResult = mysql_query($sOtDataUpdateQuery);
					echo "<BR>$sTestEmail ".$sOtDataUpdateQuery;
					
		  			}


$query = "SELECT *
		  FROM   userDataHistory
		  WHERE  postalVerified IS NOT NULL and postalVerified != 'V'";
$result = mysql_query($query);
while ($row = mysql_fetch_object($result)) {
	$email = $row->email;
	$postalVerified = $row->postalVerified;
	$query2 = "UPDATE `otDataHistory` set processStatus = 'R' ,reasonCode = 'npv', dateTimeProcessed = now()
 			   WHERE   processStatus IS NULL
				AND email = '$email'";
	echo "<BR> $email $postalVerified ".$query2;
	$result2 = mysql_query($query2);
}
*/

/**** Write Query to create SQLOfferCategoryWeb table if not exists ****/

// Import data from the file into SQL... table
// Use '\r' as line terminator if not .txt file
/*
$importQuery = "LOAD DATA INFILE '$sGlbSiteRoot/tempAddr.csv'
				INTO TABLE tempAddr 
				FIELDS TERMINATED BY ',' ENCLOSED BY '\"'
				LINES TERMINATED BY '\r\n'
				(email, first, last, address, address2, city, state, zip, phone)";

$importResult = mysql_query($importQuery);
echo $importQuery.mysql_error();
*/
/*
$selectQuery = "SELECT tempAddr.email, tempAddr.address, tempAddr.address2
				FROM   tempAddr, userDataHistory
				WHERE  tempAddr.email = userDataHistory.email";
$result = mysql_query($selectQuery);
while ($row = mysql_fetch_object($result)) {
	$email = $row->email;
	$address = $row->address;
	$address2 = $row->address2;
	$query1 = "UPDATE userDataHistory 
					  set address = '$address',
					  address2 = '$address2',
					  postalVerified = NULL
			   WHERE  email = '$email'
				AND postalVerified = 'N'
			   ";
	echo "<Br>$query1";
	$result1 = mysql_query($query1);
	$query2 = "update otDataHistory
			   SET    processStatus = NULL,
					  sendStatus = NULL,
					  dateTimeProcessed = ''
			  WHERE email = '$email'
			  AND   processStatus != 'P'";
	//echo "<Br>$query2";
	//$result2 = mysql_query($query2);
}

*/

/*

$sOfferQuery = "SELECT offerLeadSpec.offerCode, leadGroups.deliveryMethodId, shortMethod
				FROM   offerLeadSpec, deliveryMethods, leadGroups
				WHERE  offerLeadSpec.leadsGroupId = leadGroups.id
				AND    leadGroups.deliveryMethodId = deliveryMethods.id
			    AND	   leadsGroupId != '0'
				";
$rOfferResult = mysql_query($sOfferQuery);
echo mysql_error();
while ($oOfferRow = mysql_fetch_object($rOfferResult)) {

$sOfferCode = $oOfferRow->offerCode;
$iDeliveryMethodId = $oOfferRow->deliveryMethodId;
$sHowSent = $oOfferRow->shortMethod;

$sUpdateQuery = "UPDATE otDataHistory
				 SET    howSent = '$sHowSent'
				 WHERE  offerCode = '$sOfferCode'
				 AND    sendStatus = 'S'
				 AND    howSent = ''";
echo "<BR>".$sUpdateQuery;
$rUpdateResult = mysql_query($sUpdateQuery);



}
*/
/*
$sUpdateQuery = "UPDATE otData
				 SET    howSent = 'rtfpp',
					    processStatus = 'P',
						sendStatus = 'S',
						reasonCode = ''				 
				 WHERE  ( offerCode = 'AIU' || offerCode = 'WESTWOOD')
				 and reasonCode !='tst'";
echo "<BR>".$sUpdateQuery;
$rUpdateResult = mysql_query($sUpdateQuery);

echo mysql_error();



mysql_select_db("myfree");
$importQuery = "LOAD DATA INFILE '/home/sites/www_popularliving_com/html/aiuTemp_06-09-2004_Ampere.csv'
				INTO TABLE ClientPosting 
				FIELDS TERMINATED BY ',' ENCLOSED BY '\"'
				LINES TERMINATED BY '\r\n'
				(url, formData, method)";

$importResult = mysql_query($importQuery);
echo $importQuery.mysql_error();
mysql_select_db("nibbles");


$sQuery1 = "SELECT id, dateTimeAdded
			FROM   otDataHistory
			WHERE  offerCode IN ('GROUPHEALTH', 'GROUPLIFE', 'GROUPLONGTERM')";
$rResult1 = mysql_query($sQuery1);
while ($oRow1 = mysql_fetch_object($rResult1)) {
	$id = $oRow1->id;
	$dateTimeAdded = $oRow1->dateTimeAdded;
	$hhmmss = substr($dateTimeAdded,11);
	$newDateTime = "2004-06-20 ".$hhmmss;
	$sUpdateQuery = "UPDATE otDataHistory
					 SET    dateTimeAdded = '$newDateTime'
					 WHERE    id = '$id'";
	echo "<BR>".$sUpdateQuery;
	//$rUpdateResult = mysql_query($sUpdateQuery);
	//echo mysql_error();
}



$importQuery = "LOAD DATA INFILE '/home/sites/www_popularliving_com/html/nySalesTax.txt'
				INTO TABLE nySalesTax 
				FIELDS TERMINATED BY ',' ENCLOSED BY '\"'
				LINES TERMINATED BY '\r\n'
				(zip, reportCode, taxRate )";

$importResult = mysql_query($importQuery);
echo $importQuery.mysql_error();
*/

// mut exclusive
/*
$sQuery1 =   "SELECT id, email
			  FROM   otDataHistory
			  WHERE  offerCode = 'PCDI'
			  and processStatus = 'P' and sendStatus = 'S' ";
$rResult1 = mysql_query($sQuery1);
while ($oRow1 = mysql_fetch_object($rResult1)) {
	$id = $oRow1->id;
	$email = $oRow1->email;
	
	$sQuery2 = "SELECT id, email
				FROM   otDataHistory
				WHERE  offerCode = 'WPCD_EDU'
				AND    processStatus = 'P' and sendStatus = 'S'
				AND    email = '$email'";
	$rResult2 = mysql_query($sQuery2);
	echo mysql_error();
	while ($oRow2 = mysql_fetch_object($rResult2)) {
		echo "<BR>".$oRow2->email;
	}
	
}
*/


?>

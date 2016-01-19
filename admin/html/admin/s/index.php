<?php

include("../../includes/paths.php");
include("../../libs/validationFunctions.php");
include("$sGblLibsPath/dateFunctions.php");
include("$sGblIncludePath/reportInclude.php");

session_start();

set_time_limit(5000);
ini_set( "max_execution_time", "5000");

$iScriptStartTime = getMicroTime();

$sSuppressionTableName = "advertisersSuppressionLists";
$sTempSuppressionTableName = "tempAdvertisersSuppressionLists";


$sMessage = stripslashes($sMessage);

if (!($_GET[action] == 'export' && $_POST[sExportBy])) {

?>


<HTML>

<HEAD>

<title> <?php echo "$title"; ?> </title>
</head>
<body>

<table>
<tr><td>

        <a href='<?php echo $PHP_SELF."?action=addEmail"; ?>'>Add</a>
        |        
        <a href='<?php echo $PHP_SELF;?>'>View</a>
        |        
        <a href='<?php echo $PHP_SELF ;?>?action=export'>Export</a>        
          |        
        <a href='http://www.popularliving.com/admin/index.php?<?php echo SID;?>'>Back To Nibbles</a>        
        
</td></tr>
</table>
<table width=600 align=center>
	<tr><td ><font color=red><BR><?php echo $sMessage;?></font><BR></td></tr>	
</table>

<?php
}


if ($sAllowReport == 'N') {
	$sMessage = "Server Load Is High. Please check back soon...";
	echo "<table width=600 align=center>
		<tr><td ><font color=red><BR>$sMessage</font><BR></td></tr>	
		</table>";
} else {


	if ($_GET[action] == 'export') {
		// export screen

		if (!($_POST[sExportBy])) {
		?>
		<form method='post' action='<?php print $PHP_SELF . "?action=export"; ?>' >
    
    <table width="700" align="center">
    <tr><Td>Export By </td><td><select name=sExportBy>
    							<option value='all'>Export All
    							<option value=partnerCode>Partner Code = 
    							<option value=dateGreater>Date >=
    							<option value=dateLess>Date <=</select>    							
    		&nbsp; &nbsp;<input type=text name=sExportFor>
    		<BR><BR>Please Enter Date With The Format &nbsp; yyyy-mm-dd</td></tr>
    
      <tr><td colspan="2" align="center"><br><input type="submit" name="exportList" value="Export">
     </table>
     <?php
		} else {

			// export here
			switch($sExportBy) {
				case "partnerCode":
				$sFilter = "partnerCode = '$sExportFor' ";
				break;
				case "dateGreater":
				$sFilter = "addDate >= '$sExportFor' ";
				break;
				case "dateLess":
				$sFilter = "addDate <= '$sExportFor'";
				break;
				default:
				$sFilter = "";
			}


			//$sExportQuery = "SELECT email, partnerCode, addDate
			//FROM   $sSuppressionTableName";
			@unlink("$sGblWebRoot/temp/suppressionList.txt");
			$sFileName = "suppressionList.txt";
			$sExportQuery = "SELECT email, partnerCode, addDate
						 INTO 	OUTFILE  '/home/sites/admin.popularliving.com/html/temp/$sFileName' 
						 FIELDS TERMINATED BY ','
						 LINES TERMINATED BY '\r\n'
						 FROM   $sSuppressionTableName";
			if ($sFilter != '') {
				$sExportQuery .= " WHERE  $sFilter";
			}

			// start of track users' activity in nibbles 
			$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

			$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Export: $sExportQuery\")"; 
			$rLogResult = dbQuery($sLogAddQuery); 
			echo  dbError(); 
			// end of track users' activity in nibbles		
			
			
			$rExportResult = dbQuery($sExportQuery);
			echo dbError();

			echo "<br><center><a href='http://web0.popularliving.com/temp/$sFileName/temp/$sFileName'>Get File</a><br>Right click on the link and select 'Save Link As...' or 'Save Target As...'</center><br>";
			//echo "<br><center><a href='$sGblSiteRoot/temp/$sFileName'>Get File</a><br>Right click on the link and select 'Save Link As...' or 'Save Target As...'</center><br>";
		}
	}

	else if ($_GET[action] =='addEmail') {

		?>
		
<?php 

if (($_POST[add])) {

	if ($sPartnerCode != '') {
		// separate emails
		if ($_FILES['fListFile']['tmp_name'] && $_FILES['fListFile']['tmp_name']!="none") {

			$sUploadedFile = $_FILES['fListFile']['tmp_name'];
			$sNewFile = "$sGblWebRoot/temp/supTemp.txt";

			move_uploaded_file($sUploadedFile,$sNewFile);
			chmod("$sNewFile",0777);

			$sTempInsertQuery = "LOAD DATA INFILE '$sNewFile'
							 IGNORE INTO TABLE $sTempSuppressionTableName
							 FIELDS TERMINATED BY ',' ENCLOSED BY ''
							 LINES TERMINATED BY '\r\n'
							 (email)";

			// start of track users' activity in nibbles 
			$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
	
			$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Import: $sTempInsertQuery\")"; 
			$rLogResult = dbQuery($sLogAddQuery); 
			echo  dbError(); 
			// end of track users' activity in nibbles		
			
			
			$rTempInsertResult = dbQuery($sTempInsertQuery);
			if (!($rTempInsertResult)) {
				echo $sTempInsertQuery. dbError();
			}

			// set partnerCode for the recently uploaded records
			$sUpdateQuery = "UPDATE $sTempSuppressionTableName
							 SET    partnerCode = '$sPartnerCode'
							 WHERE  partnerCode = ''";
			$rUpdateResult = dbQuery($sUpdateQuery);


			$iScriptEndTime = getMicroTime();
			$iScriptExecutionTime = round($iScriptEndTime - $iScriptStartTime);

			
			//move data from temp to live table....
			if ($rUpdateResult) {
				$sInsertQuery = "INSERT INTO $sSuppressionTableName (email,addDate,partnerCode)
				          SELECT email,CURRENT_DATE,partnerCode FROM $sTempSuppressionTableName";
				$rInsertResult = mysql_query($sInsertQuery);
				echo mysql_error();
				
				if ($rInsertResult) {
				    $sDeleteQuery = "TRUNCATE TABLE $sTempSuppressionTableName";
				    $rDeleteResult = mysql_query($sDeleteQuery);
				}
			}

					
			if ($rInsertResult) {
			unlink("$sGblWebRoot/temp/supTemp.txt");

			$sMessage = "Your file has been uploaded. Partner code for the uploaded file won't appear until this file is
							 processed in the nightly batch.";
			}

		} else {

			// if uploaded through textarea
			$sSuppEmailArray = explode("\n",$sEmailList);

			$iNum = count($sSuppEmailArray);
			$sMessage = "Attempted: $iNum";

			$invalid = 0;

			$sSuppEmailArray = array_unique($sSuppEmailArray);
			$iDups = $iNum - count($sSuppEmailArray);

			$iNum = count($sSuppEmailArray);


			//echo  "array ".count($sSuppEmailArray);

			for ($i=0; $i<count($sSuppEmailArray); $i++) {

				$sSuppEmail = $sSuppEmailArray[$i];
				//echo $sSuppEmail;
				$sSuppEmail = trim($sSuppEmail);
				//echo "<BR>".$sSuppEmail;
				if (validateEmailFormat($sSuppEmail)) {
					$sCheckQuery = "SELECT *
							FROM   $sSuppressionTableName
					   		WHERE   partnerCode = '$sPartnerCode'
							AND   email = '$sSuppEmail'";
					$rCheckResult = dbQuery($sCheckQuery);

					if (dbNumRows($rCheckResult) > 0) {
						$sUpdateQuery = "UPDATE $sSuppressionTableName
									 SET  addDate = CURRENT_DATE
									 WHERE   partnerCode = '$sPartnerCode'
									 AND	email = '$sSuppEmail'";

						// start of track users' activity in nibbles 
						$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
				
						$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
						  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Edit: $sUpdateQuery\")"; 
						$rLogResult = dbQuery($sLogAddQuery); 
						echo  dbError(); 
						// end of track users' activity in nibbles		
						
						
						$rUpdateResult = dbQuery($sUpdateQuery);

						$iNum--;
						$iDups++;
					} else {

						// If adding num rows should be 0
						// insert record

						$sInsertQuery = "INSERT IGNORE INTO $sSuppressionTableName(email, partnerCode, addDate)
								 VALUES('$sSuppEmail', '$sPartnerCode', CURRENT_DATE )"; 

						// start of track users' activity in nibbles 
						$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
				
						$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
						  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Add: $sInsertQuery\")"; 
						$rLogResult = dbQuery($sLogAddQuery); 
						echo  dbError(); 
						// end of track users' activity in nibbles		
						
						
						$rInsertResult = dbQuery($sInsertQuery);

						if (! $rInsertResult) {
							echo dbError();
						}
					}


				} else {
					$iNum--;
					//	echo "<BR>not validated ".$sSuppEmail;
					$iInvalid++;
				}
			}

			$sMessage .= "<BR>Duplicates: ".$iDups;
			$sMessage .= "<BR>Valid: $iNum";
			$sMessage .= "<BR>Invalid: $iInvalid";
		}


		//$iScriptEndTime = getMicroTime();
		//$iScriptExecutionTime = round($iScriptEndTime - $iScriptStartTime);
		//echo $iScriptExecutionTime;


		//echo "<BR> added ".$iNum." not Valid ".$notValid;

		$sMessage = urlencode($sMessage);
		//echo $sMessage;
		echo "<script language=Javascript>
			window.location.replace('$PHP_SELF?sMessage=$sMessage')
			</script>";

		echo dbError();
	} else {
		$sErrMessage =  "Partner Code Required";
	}
}



//else {

//display form

		?>
		<form method='post' action='<?php print $PHP_SELF . "?action=addEmail"; ?>' enctype="multipart/form-data" >
    
    <table width="700" align="center">
    <tr><td colspan=2><font color=red><?php echo $sErrMessage;?></td></td></tR>
    <tr><td colspan=2>If there is a duplicate input submitted (based on the combination of
partner code and email address) the oldest one is deleted and replaced
by the newer one.<BR><BR></td></tr>
    <tr><Td>Partner Code</td><td><input type=text name=sPartnerCode value'<?php echo $sPartnerCode;?>'></td></tr>
    <tr><td colspan=2><BR></td></tR>
    <tr><Td>Email List From File</td><td><input type=file name=fListFile></td></tr>
    <tr><Td></td><td>Must Be Text File With One Email Address Per Line.</td></tr>
    <tr><td colspan=2><BR></td></tR>	
    <tr><td colspan=2 align=center>OR</td></tR>
    <tr><Td>Email</td><td><textarea name=sEmailList rows=15 cols=40><?php echo $sEmailList;?></textarea></td></tr>     
    <tr><td></td><td>Do Not Add Any More Than 500 Emails At A Time.</td></tr>
      <tr><td colspan="2" align="center"><br><input type="submit" name="add" value="Add">
     </table>
    
     <?php

     //}

	}

	else if ($_GET[action] =='delete' && $iId != '') {

		$sDeleteQuery = "DELETE FROM $sSuppressionTableName
					 WHERE  id = '$iId'";

		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Delete: $sDeleteQuery\")"; 
		$rLogResult = dbQuery($sLogAddQuery); 
		echo  dbError(); 
		// end of track users' activity in nibbles		

		
		$rDeleteResult = dbQuery($sDeleteQuery);
		if ($rDeleteResult) {
			echo "<script language=Javascript>
				window.location.replace('$PHP_SELF?sMessage=Record+Deleted')
				</script>";
		}
	} else if ($deletePartner =='y') {

		$sPartnerDeleteQuery = "DELETE FROM   $sSuppressionTableName
					 WHERE	partnerCode = '$code'";

		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Delete: $sPartnerDeleteQuery\")"; 
		$rLogResult = dbQuery($sLogAddQuery); 
		echo  dbError(); 
		// end of track users' activity in nibbles		
		
		
		$rDeleteResult = dbQuery($sPartnerDeleteQuery);
		if ($rDeleteResult) {
			echo "<center><font color=red>Records with partner code $code deleted</font></center>";
		} else {
			echo dbError();
		}
	}


	if ($_GET[action] != 'export') {
		echo "<form name=form1 action='$PHP_SELF' method=post>";
	}

	if (!(isset($_GET[action]))) {

		echo "<table width=600 align=center>
			<tr><td>Search For Email</td><td colspan=2><input type=text name=sEmail></td></tr>
		  <tr><td></td><td colspan=2><input type=submit name=search value=Search></td></tr>";	

		if ($_POST[sEmail] != '') {
			$sSearchQuery = "SELECT *
						 FROM   $sSuppressionTableName
						 WHERE  email = '$sEmail'";
			$rSearchResult = dbQuery($sSearchQuery);
			if (dbNumRows($rSearchResult) >0) {
				echo  "<tr><td colspan=3>Record Found</td></tr>";
				while ($oSearchRow = dbFetchObject($rSearchResult)) {
					echo "<tr><td><a href='$PHP_SELF?action=delete&iId=".$oSearchRow->id."'>Delete</a> &nbsp; &nbsp; ".$oSearchRow->email."</td><td>".$oSearchRow->partnerCode."</td><td>".$oSearchRow->addDate."</td></tr>";
				}

			} else {

				echo "<tr><td colspan=3>Record Not Found</td></tR>";
			}
		}
		echo "<tr><td colspan=3><BR><BR><BR></td></tr></table>";

		$sSuppressionList = "<table width=600 align=center>
						<tr><td><b>Partner Code</b></td><td colspan=2><b>Total Emails</b></td></tr>";

		$sQuery = "SELECT partnerCode, count(*) counts
		   	   FROM   $sSuppressionTableName
			   GROUP BY partnerCode 
		   	   ORDER BY partnerCode";
		$rResult = dbQuery($sQuery);
		while ($oRow = dbFetchObject($rResult)) {
			$sSuppressionList .= "<tr><td>".$oRow->partnerCode."</td><td colspan=2>".$oRow->counts."</td>
								<td><a href='$PHP_SELF?deletePartner=y&code=$oRow->partnerCode'>Delete</a></td></tr>";
		}

		echo $sSuppressionList;

		//$sSuppressionList .= "<tr><td><a href='$PHP_SELF?action=delete&iId=$oRow->id'>Delete</a> &nbsp; &nbsp; $oRow->email</td><td>$oRow->partnerCode</td><td>$oRow->addDate</td></tr>";

	}

}
?>
</table>
</form>
</body>
</html>

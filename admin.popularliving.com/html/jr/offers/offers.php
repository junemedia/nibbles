<?php
/*

*** [first], [last], [email] tags are supported in email.

*** prepop codes can be,
e = email
f = first
l = last
a = address
a2 = address2
c = city
s = state
z = zip
p = phone


*/

include("config.php");


// Check if common tables exist... Create the tables if not exist
$sCheckQuery = "SHOW TABLES LIKE '$sGblTableOffers'";
$rCheckResult = mysql_query($sCheckQuery);
echo mysql_error();

if (mysql_num_rows($rCheckResult) == 0) {
	$sCreateQuery = "CREATE TABLE `$sGblTableOffers` (
`id` INT NOT NULL AUTO_INCREMENT ,
`title` VARCHAR( 255 ) NOT NULL ,
`description` MEDIUMTEXT NOT NULL ,
`subject` VARCHAR( 255 ) NOT NULL ,
`body` TEXT NOT NULL ,
PRIMARY KEY ( `id` ) 
)";
	
	$rCreateResult = mysql_query($sCreateQuery);
	echo mysql_error();
	
}


// Check if common tables exist... Create the tables if not exist
$sCheckQuery = "SHOW TABLES LIKE '$sGblTableProspects'";
$rCheckResult = mysql_query($sCheckQuery);
echo mysql_error();

if (mysql_num_rows($rCheckResult) == 0) {
	$sCreateQuery = "CREATE TABLE `$sGblTableProspects` (
`id` INT NOT NULL AUTO_INCREMENT ,
`email` VARCHAR( 100 ) NOT NULL ,
`first` VARCHAR( 30 ) NOT NULL ,
`last` VARCHAR( 30 ) NOT NULL ,
`address` VARCHAR( 50 ) NOT NULL ,
`address2` VARCHAR( 50 ) NOT NULL ,
`city` VARCHAR( 50 ) NOT NULL ,
`state` VARCHAR( 2 ) NOT NULL ,
`zip` VARCHAR( 12 ) NOT NULL ,
`phone` VARCHAR( 20 ) NOT NULL ,
PRIMARY KEY ( `id` ) 
) ";
	
	$rCreateResult = mysql_query($sCreateQuery);
	echo mysql_error();
	
}


// Check if common tables exist... Create the tables if not exist
$sCheckQuery = "SHOW TABLES LIKE '$sGblTableOffersTaken'";
$rCheckResult = mysql_query($sCheckQuery);
echo mysql_error();

if (mysql_num_rows($rCheckResult) == 0) {
	$sCreateQuery = "CREATE TABLE `$sGblTableOffersTaken` (
`id` INT NOT NULL AUTO_INCREMENT ,
`offerId` INT NOT NULL ,
`prospectId` INT NOT NULL ,
`dateTimeAdded` DATETIME NOT NULL ,
`ip` VARCHAR( 20 ) NOT NULL ,
PRIMARY KEY ( `id` ) 
) ";
	
	$rCreateResult = mysql_query($sCreateQuery);
	echo mysql_error();
	
}

// get prepop codes if form not already submitted
if ($sEmail == '') {
	$sEmail = $e;	
}
if ($sFirst == '') {
	$sFirst = $f;	
}
if ($sLast == '') {
	$sLast = $l;	
}
if ($sAddress == '') {
	$sAddress = $a;	
}
if ($sAddress2 == '') {
	$sAddress2 = $a2;	
}
if ($sCity == '') {
	$sCity = $c;	
}
if ($sState == '') {
	$sState = $s;	
}
if ($sZip == '') {
	$sZip = $z;	
}
if ($sPhone == '') {
	$sPhone = $p;	
}



if ($sSubmit) {
	
	if ($sEmail == '' || $sFirst == '' || $sLast == '' || $sAddress == '' || $sAddress2 === '' || $sCity == '' || $sState == '' || $sZip == '') {
		$sMessage = "Please Fill All The Data...";
	} else {
		
	$sInsertQuery = "INSERT INTO $sGblTableProspects(email, first, last, address, address2, city, state, zip, phone )
					 VALUES(\"$sEmail\", \"$sFirst\", \"$sLast\", \"$sAddress\", \"$sAddress2\", \"$sCity\", \"$sState\", \"$sZip\", \"$sPhone\")";
	$rInsertResult = mysql_query($sInsertQuery);
	if ($rInsertResult) {
		$iProspectId = mysql_insert_id();
		
		for ($i=0; $i<count($aOffersChecked); $i++) {
			$iTempOfferId = $aOffersChecked[$i];
			
			$sInsertQuery1 = "INSERT INTO $sGblTableOffersTaken(offerId, prospectId, dateTimeAdded, ip)
							  VALUES('$iTempOfferId', '$iProspectId', now(), '".$_SERVER['REMOTE_ADDR']."')";  
			$rInsertResult1 = mysql_query($sInsertQuery1);
			if ($rInsertResult1) {
				// send email
			
				// get offer details
				$sOfferQuery = "SELECT *
								FROM   $sGblTableOffers
								WHERE  id = '$iTempOfferId'";
				$rOfferResult = mysql_query($sOfferQuery);
				echo mysql_error();
				while ($oOfferRow = mysql_fetch_object($rOfferResult)) {
					$sSubject = $oOfferRow->subject;
					$sBody = $oOfferRow->body;
					
					$sBody = eregi_replace("\[FIRST\]", $sFirst, $sBody);
					$sBody = eregi_replace("\[LAST\]", $sLast, $sBody);
					$sBody = eregi_replace("\[EMAIL\]", $sEmail, $sBody);
					
		
					$sHeaders  = "MIME-Version: 1.0\r\n";
					$sHeaders .= "Content-type: text/plain; charset=iso-8859-1\r\n";
					$sHeaders .= "From:$sGblEmailFrom\r\n";
					
					
					mail($sEmail, $sSubject, $sBody, $sHeaders);
				}
							
			}
		}
	}
	
	}
} 

if (!($sSubmit) || $sMessage != '') {
	
$sOffersQuery = "SELECT *
				 FROM   offers";
$rOffersResult = mysql_query($sOffersQuery);
echo mysql_error();

$sPageData = "<table align=center><tr><td><h1>STEP 1 - Select Offers</h1></td></tr></table>
				<table width=650 align=center cellpadding=0 cellspacing=0 border=0 align=center>";
while ($oOffersRow = mysql_fetch_object($rOffersResult)) {
	
	$sPageData .= "<tr><td><b>$oOffersRow->title</b></td></tr>
				   <tr><td><input type=checkbox name='aOffersChecked[]' value='".$oOffersRow->id."'> &nbsp; $oOffersRow->description</td></tr>
					<tr><td><hr width=650></td></tr>";
}

$sPageData .= "</table>";


// prepare states options
$sStateQuery = "SELECT *
				FROM   states
				ORDER BY state";
$rStateResult = mysql_query($sStateQuery);
$sStateOptions = "<option value=''>";
while ($oStateRow = mysql_fetch_object($rStateResult)) {
	if ($sState == $oStateRow->stateId) {
		$sSelected = "selected";
	} else {
		$sSelected = "";
	}
	$sStateOptions .= "<option value=$oStateRow->stateId $sSelected>$oStateRow->state";
	//echo "<BR>".$oStateRow->stateId;
}

$sPageData .= "<table align=center><tr><td><h1>STEP 2 - Complete This Form</h1></td></tr></table>
	<table border=0 cellpadding=2 cellspacing=0 width=650 align=center>


<tr>
<td width=120 valign=top bgcolor=#F0FFFF>
<font size='2' face='Arial,Helvetica'><b>First Name:</b></font>
</td>

<td>
<input type=text name=sFirst value='$sFirst' size=30> 
</td>

<td width=120 valign=top bgcolor=#F0FFFF>
<font size='2' face='Arial,Helvetica'><b>Last Name:</b></font>
</td>


<td>
<input type=text name=sLast value='$sLast' size=25>
</td>
</tr>
<tr>
<td width=120 valign=top bgcolor=#F0FFFF>
<font size='2' face='Arial,Helvetica'><b>Address:</b></font>
</td>

<td>
<input type=text name=sAddress value='$sAddress' size=30>
</td>

<td>
<font size=2 face=Arial,Helvetica><b>Apt/Suite </b></font>
<font size='-2' face='Helvetica,Arial'>(optional):</font>
</td>

<td>
<input type=text name=sAddress2 value='$sAddress2' size=25>
</td>
</tr>

<tr>
<td width=120 valign=top bgcolor=#F0FFFF>
<font size='2' face='Arial,Helvetica'><b>City:</b></font>
</td>

<td valign=top bgcolor=#F0FFFF colspan=3>
<input type=text name=sCity value='$sCity' size=18>
<font size='2' face='Arial,Helvetica'><b>State:</b></font>
<font size='-1' face='Arial,Helvetica'><select name=sState size=1> 
$sStateOptions</select>
<font size='2' face='Arial,Helvetica'><b>Zip Code:</b></font>
<input type=text name=sZip size=10 value='$sZip'>
</td>
</tr>


<tr>
<td width=120 valign=top bgcolor=#F0FFFF>
<div STYLE= line-height:7pt;>
<font size='2' face='Arial,Helvetica'><b>Primary Phone #:</b></font><br>
<font size='-2' face='Helvetica,Arial'>i.e. 847-555-3434</font>
</div>
</td>

<td>
<input type=text name=sPhone value='$sPhone' size=30>
</td>

<td width=120 valign=top bgcolor=#F0FFFF>
<div STYLE= line-height:7pt;>
<font size='2' face='Arial,Helvetica'><b>Email Address:</b></font>
<font size='-2' face='Arial,Helvetica'>e.g., joecool@aol.com</font>
</div>
</td>

<td valign=top bgcolor=#F0FFFF colspan=3>
<input type=text name=sEmail size=30 value='$sEmail'>
</td>
</tr>
<Tr><Td colspan=4 align=center><BR><BR><input type=submit name=sSubmit value='STEP 3 - Click Here'></td></tR>
</table>";

}

?>
<html>
<center><font color=red><?php echo $sMessage;?></font></center>
<form action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $sPageData;?>
</form>
</html>
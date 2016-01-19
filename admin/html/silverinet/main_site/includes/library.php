<?php



// place GET and POST into named vars
if ($post_get_var_list) {
	$limit = count($post_get_var_list);
	if ($_POST) {
		foreach ($post_get_var_list as $var) {
			$a=$var;
			$$a=$_POST[$var];
		}
	} elseif ($_GET) {
		foreach ($post_get_var_list as $var) {
			$a=$var;
			$$a=$_GET[$var];
		}
	} 
}


///////////////////////////////////////////////////////////////////////////////////

function isTestIP() {
	global $_CONFIG;
	
	if (in_array($_SERVER['REMOTE_ADDR'], $_CONFIG['test_ips'])) {
		return true;
	}
	return false;
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////
// VALIDATE SOCIAL SECURITY NUMBER: 123-45-6789 FORMAT
function ValidateSSN($aSSN) {

	if ( ereg("^[0-9]{3}[-]{1}[0-9]{2}[-]{1}[0-9]{4}",$aSSN ) )  {
		//Good Zip Code
		return true;

			}else{
		//Bad Zip Code
		return false;
	
			}
	}  //END function ValidateSSN

/////////////////////////////////////////////////////////////////////////////////////////////////////////////
// VALIDATE ZIP CODE: 12345 OR 12345-6789 FORMAT

function ValidateZipCode($aZipCode) {

	if ( ereg("^[0-9]{5}",$aZipCode ) or ereg("^[0-9]{5}[-]{1}[0-9]{4}",$aZipCode ) ) {
		//Good Zip Code
		return true;

			}else{
		//Bad Zip Code
		return false;
		
			}
	}  //END function ValidateZipCode


/////////////////////////////////////////////////////////////////////////////////////////////////////////////
// VALIDATE DATE:  TEST W/ strtotime FUNCTION

function ValidateDate($aDate) {

	if ( strtotime( $aDate ) != -1 ) {
		//Good Date
		return true;

		}else{
		//Bad Date
		return false;

		}
	}  //END function ValidateDate



/////////////////////////////////////////////////////////////////////////////////////////////////////////////
// VALIDATE TAX ID NUMBER:  12-3456789 FORMAT
function ValidateTaxIDNumber($aTaxID) {

	if ( ereg( "^[0-9]{2}[-]{1}[0-9]{7}", $aTaxID ) ) {
		//Good Date
		return true;

			}else{
		//Bad Date
		return false;
		
			}
	}  //END function ValidateTaxIDNumber

/////////////////////////////////////////////////////////////////////////////////////////////////////////////
//CONVERT DATE FROM MM/DD/YYYY TO YYYY-MM-DD
function PHPDate($aDate)  {

		if ($aDate=="") {return "";}

		$aStamp = strtotime($aDate);
		$aOut = date("Y-m-d", mktime (0,0,0,date("m", $aStamp),date("d", $aStamp), date("Y", $aStamp)) ); 

		/* OLD METHODOLOGY
		list( $aMonth, $aDay, $aYear ) = split( '[/.-]', $aDate );
		if (strlen($aMonth) < 2 ) {$aMonth = "0" . $aMonth;}
		if (strlen($aDay) < 2 ) {$aDay = "0" . $aDay;}
		$aOut = "$aYear-$aMonth-$aDay";
		*/

		return "$aOut";
	}


/////////////////////////////////////////////////////////////////////////////////////////////////////////////
//CONVERT DATE FROM YYYY-MM-DD TO MM/DD/YYYY
function NonPHPDate($aDate)  {

		if ($aDate=="" or $aDate=="0000-00-00" ) {return "";}
		list($aYear , $aMonth, $aDay ) = split( '[/.-]', $aDate );
		if (strlen($aMonth) < 2 ) {$aMonth = "0" . $aMonth;}
		if (strlen($aDay) < 2 ) {$aDay = "0" . $aDay;}
		$aOut = "$aMonth/$aDay/$aYear";
		return "$aOut";
	}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////
//CONVERT DATE FROM YYYY-MM-DD TO MM/DD/YY
function NonPHPDate2($aDate)  {

		if ($aDate=="" or $aDate=="0000-00-00" ) {return "";}
		list($aYear , $aMonth, $aDay ) = split( '[/.-]', $aDate );
		$aYear = substr($aYear,2);
		if (strlen($aMonth) < 2 ) {$aMonth = "0" . $aMonth;}
		if (strlen($aDay) < 2 ) {$aDay = "0" . $aDay;}
		$aOut = "$aMonth/$aDay/$aYear";
		return "$aOut";
	}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//CONVERT DATE FROM YYYY-MM-DD hh:mm:ss TO MM/DD/YYYY hh:mm:ss 
function NonPHPDateTime($aDate)  {

		if ($aDate=="" or $aDate=="0000-00-00 00:00:00" ) {return "";}
		$aDateArr = explode(" ",$aDate);
		list($aYear , $aMonth, $aDay ) = split( '[/.-]', $aDateArr[0]);
		if (strlen($aMonth) < 2 ) {$aMonth = "0" . $aMonth;}
		if (strlen($aDay) < 2 ) {$aDay = "0" . $aDay;}
		$aOut = "$aMonth/$aDay/$aYear $aDateArr[1]";
		return "$aOut";
	}



/////////////////////////////////////////////////////////////////////////////////////////////////////////////
//TOGGLE BGCOLORS IN A TABLE CELL
//This function will return $abgcolor1 if $aToggleVar = 0
//If not, it will return $abgcolor2 
//It is inteded to toggle background colors in a dynamically generated table
function GetBgColor($aToggleVar,$abgcolor1,$abgcolor2) {
		if ( $aToggleVar == 0 ) {
			return $abgcolor1;
		    }else{
			return $abgcolor2;
		    }

	}	

/////////////////////////////////////////////////////////////////////////////////////////////////////////////
//TOGGLE BGCOLORS IN A TABLE CELL
//This will change the value of the "Toggle" variable, used primarily with the 
//background color function above.  It will change a bit variable from 0 to 1, or
//1 to 0
function ToggleBG($aToggleVar) {
		if ( $aToggleVar == 0 ) {
			return "1";
		    }else{
			return "0";
		    }


	}

////////////////////////////////////////////////////////////////
//This Function splits a variable and 
//returns the value after the period
//specified in aPeriod.
function GetVarAfterPeriod ( $aVar, $aPeriod ) {

	$aVarArray = split( '[.]', $aVar );
	$aOut = $aVarArray[$aPeriod];
	return $aOut;
	}

///////////////////////////////////////////////////////////
//Format Date
function FrontEndDate($aDate) {

   $aStamp = strtotime ($aDate);
   $aMonth = date("m", $aStamp);
   $aDay = date("d", $aStamp);
   $aYear = date("Y", $aStamp);
   $aOut = date ("M d, Y", mktime (0,0,0,$aMonth,$aDay,$aYear));
   return $aOut;
 
} //END function FrontEndDate($aDate) {


///////////////////////////////////////////////////////////////////////////////////////
//PRINT THE DAY OF THE WEEK GIVEN A DATE
function EchoDayOfWeek($aDate) {

   $aStamp = strtotime ($aDate);
   $aMonth = date("m", $aStamp);
   $aDay = date("d", $aStamp);
   $aYear = date("Y", $aStamp);
   $aOut = date ("l", mktime (0,0,0,$aMonth,$aDay,$aYear));
   return $aOut;

 } //END function EchoDayOfWeek($aDate) {


/////////////////////////////////////////////////////////////////////////////////////////////////////////////
// VALIDATE PHONE FORMAT
function ValidatePhone($aPhone) {

	if ( ereg(  "[(]*[0-9]{3}[)-]*[0-9]{3}[-]*[0-9]{4}$", $aPhone) ) {
		//Good Phone Number
		return true;

			}else{
		//Bad Phone Number
		return false;
		
			}
	}  //END function ValidatePhone


/////////////////////////////////////////////////////////////////////////////////////////////////////////////
// VALIDATE EMAIL
function ValidateEmail($aEmail) {

	if ( ereg(  "^[A-Za-z0-9\$._-]*[@]{1,1}[A-Za-z0-9-]+[.]{1}[A-Za-z0-9.-]+[A-Za-z]$", $aEmail) ) {
		//Good Email
		return true;

			}else{
		//Bad Email
		return false;
		
			}
	}  //END function ValidateEmail

/////////////////////////////////////////////////////////////////////////////////////////////////////////////
// VALIDATE PASSWORD
function ValidatePassword($aPass) {

	if ( ereg(  "^[A-Za-z0-9._-]{5,15}[A-Za-z0-9._-]$", $aPass) ) {
		//Good Password
		return true;

			}else{
		//Bad Password
		return false;
		
			}
	}  //END function ValidatePassword

/////////////////////////////////////////////////////////////////////////////////////////////////////////////
// VALIDATE USERNAME
function ValidateUsername($aUser) {

	if ( ereg(  "^[A-Za-z0-9._-]{3,19}[A-Za-z0-9._-]$", $aUser) ) {
		//Good Username
		return true;

			}else{
		//Bad Username
		return false;
		
			}
	}  //END function ValidateUsername

/////////////////////////////////////////////////////////////////////////////
//This function strips a phone number of parenthesis
//and hyphens and returns wild cards where they could
//go.  The idea is to return a string that will match
//phone numbers entered like 888-888-8888 and 
//(888) 888-8888
function SearchablePhoneNumber($aPhone) {

for ($i=0; $i<strlen($aPhone); $i++){ 
if ( ereg("[0-9]{1}",substr($aPhone,$i,1) ) ) {$aAllNumbers .= substr($aPhone,$i,1); }
} //END for ($i=0; $i<strlen($aPhone); $i++){ 

$aOut = "%" . substr($aAllNumbers,0,3) . "%" . substr($aAllNumbers,3,3) . "%" . substr($aAllNumbers,6,4);
return $aOut;

} //END functionSearchablePhoneNumber

/////////////////////////////////////////////////////////////////////////////
//This function converts a phone number to  888-888-8888 
//format
function ConvertPhoneNumber($aPhone) {

if (strlen($aPhone)>9) {

for ($i=0; $i<strlen($aPhone); $i++){ 
if ( ereg("[0-9]{1}",substr($aPhone,$i,1) ) ) {$aAllNumbers .= substr($aPhone,$i,1); }
} //END for ($i=0; $i<strlen($aPhone); $i++){ 

$aOut = substr($aAllNumbers,0,3) . "-" . substr($aAllNumbers,3,3) . "-" . substr($aAllNumbers,6,4);
return $aOut;

}else{
return "";

} //END if (strlen($aPhone)>11) {

} //END function ConvertPhoneNumber

/////////////////////////////////////////////////////////////////////////////
//This function takes a string and converts it to title case.
//I.E. DAVE IS GREAT -> Dave Is Great
function TitleCase($aString) {

$aUpperFlag = "on"; //First time through, always upper case

for ($i=0; $i<strlen($aString); $i++){ 

if ($aUpperFlag=="on") {
$aOut .= strtoupper(substr($aString,$i,1));
}else{
$aOut .= strtolower(substr($aString,$i,1));
} //END if ($upperflag=="on") {

$aUpperFlag="off";
if (substr($aString,$i,1) == " ") {$aUpperFlag="on";}

} //END for ($i=0; $i<strlen($aString); $i++){ 

return $aOut;

} //END function 

/////////////////////////////////////////////////////////////////////////////
//This function looks to match an item, "$aItem", in a comma
//delimited list, "$aCsvList".
function MatchInCsv($aItem,$aCsvList) {

$anArray = explode(",",$aCsvList);

for ($i=0; $i<count($anArray); $i++){ 
if ($anArray[$i] == $aItem) { $aReturnTrue="yes"; }
} //END for ($i=0; $i<count($anArray); $i++){ 

if ($aReturnTrue=="yes") {
return true;
}else{
return false;
} //END if ($aReturnTrue=="yes") {

} //END function MatchInCsv($aItem,$aCsvList) {


/////////////////////////////////////////////////////////////////////////////
//This function looks to match an item, "$aItem", in a 
//delimited list, "$aCsvList".  Delimiter is "$aDelimiter".
function MatchDelimited($aItem,$aCsvList,$aDelimiter) {

$anArray = explode($aDelimiter,$aCsvList);

for ($i=0; $i<count($anArray); $i++){ 
if ($anArray[$i] == $aItem) { $aReturnTrue="yes"; }
} //END for ($i=0; $i<count($anArray); $i++){ 

if ($aReturnTrue=="yes") {
return true;
}else{
return false;
} //END if ($aReturnTrue=="yes") {

} //END function MatchDelimited($aItem,$aCsvList) {


////////////////////////////////////////////////////////////////////////////////////
//This function returns the number of days between $aDate1
//and $aDate2. The later date should be date 2
function DaysDifference($aDate1,$aDate2) {

$aStamp1 = strtotime($aDate1);
$aStamp2 = strtotime($aDate2);

$aout = ($aStamp2 - $aStamp1) / 86400;
//echo "$aStamp2 - $aStamp1 = " . ($aStamp2 - $aStamp1) . "<br>"; 
return number_format($aout,0);

} //END function DaysDifference($aDate1,$aDate2) {



///////////////////////////////////////////////////////////////////////////////
//This function prepares a string for database insertion
//It trims the variable, and puts the escape character
//before apostrophes.  ' -> \'
/*function dbprep($aString) {

$avar = trim($aString);
$avar = str_replace("'","\'",$avar);	// escape single quotes
$avar = str_replace('"','\"',$avar);	// escpae double quotes
//$avar = addslashes($avar);
return $avar;

} //END function dbprep($aString) {*/

function dbprep($aString) {
    if (!get_magic_quotes_gpc()) {
        $aout = addslashes(trim($aString));
    } else {
        $aout = trim($aString);
    }
    return $aout;
}


//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//This function creates a drop down list for a single table

function DropDown($aTable,$aIDField,$aDisplayField,$aValue,$aSelectName="",$aDisplayName="") {

	if ($aSelectName)echo "<select name=\"$aSelectName\">\n<option value=\"\">Please select $aDisplayName\n";

	$aresult = mysql_query("select * from $aTable order by $aDisplayField");
	while ($arow = mysql_fetch_array($aresult)){

		echo "<option value=$arow[$aIDField]";
		if ($arow[$aIDField] == $aValue) echo " selected";
		echo ">$arow[$aDisplayField]</option>\n";
	}

	if ($aSelectName)echo "</select>";


}


////////////////////////////////////////////////////////////////////////////////////////////////
function MysqlStampToDateTime($aStamp) {
	$aDateTime = substr($aStamp,0,4) . "/" . substr($aStamp,4,2) . "/" . substr($aStamp,6,2) . " " . substr($aStamp,8,2) . ":" . substr($aStamp,10,2) . ":" . substr($aStamp,12,2);
	return $aDateTime;
}


///////////////////////////////////////////////////////////////////////////////////////////////////////
function getVar($aVarName) {
	
	$asql = "Select * from var where varName='$aVarName'";
	$aresult = mysql_query($asql);
	if (!$aresult) echo "We're sorry, there's been a query errror.  Please notify the system administrators.<br><br>$sql<br><Br>" .mysql_error() . "<br><Br>";
	$arow = mysql_fetch_array($aresult);
	$aout = $arow[varText];
	mysql_free_result($aresult);
	return $aout;

}


//////////////////////////////////////////////////////////////////////////
function dateselect ($mName, $dName, $yName, $default="", $allownull=1,$years=5,$StartYear=0) {
	//if (!$default) $default=gmdate("Y-m-d");
	if ($default) {
		$DM = gmdate ("n", my2unix ($default));
		$DD = gmdate ("j", my2unix ($default));
		$DY = gmdate ("Y", my2unix ($default));
	}

$SS[$DM] = "selected style=\"color: #000080\"";

if ($allownull) {

	if (!$default) {
		 $NULL = "\n    <option value=0 selected  style=\"color: #000080\">-</option>";
	}else{
		 $NULL = "\n    <option value=0>-</option>";
	}
}


print "
<select style=\"font-size: 8pt\" name=\"$mName\">$NULL
    <option $SS[1] value=1>January</option>
    <option $SS[2] value=2>February</option>
    <option $SS[3] value=3>March</option>
    <option $SS[4] value=4>April</option>
    <option $SS[5] value=5>May</option>
    <option $SS[6] value=6>June</option>
    <option $SS[7] value=7>July</option>
    <option $SS[8] value=8>August</option>
    <option $SS[9] value=9>September</option>
    <option $SS[10] value=10>October</option>
    <option $SS[11] value=11>November</option>
    <option $SS[12] value=12>December</option>
</select>
&nbsp;
<select style=\"font-size: 8pt\" name=\"$dName\">$NULL";


for ($i=1;$i<32;$i++) {
	if ($DD == $i) {
		print "\n    <option selected style=\"color: #000080\" value=$i>$i</option>";
	} else {
		print "\n    <option value=$i>$i</option>";
	}
}

print "
</select>
<b>,</b>
<select style=\"font-size: 8pt\" name=\"$yName\">$NULL";

//Default Starting Year Is The Current Year
if (!$StartYear) $StartYear=date("Y");

for ($i=$StartYear;$i<date("Y")+$years;$i++) {
	if ($DY == $i) {
		print "\n    <option selected style=\"color: #000080\" value=$i>$i</option>";
	} else {
		print "\n    <option value=$i>$i</option>";
	}
}

print "</select>";

}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function my2unix ($my) {
   // Pass a 'MySQL' formatted date ('date' or 'datetime'), returns a Unix time stamp (int)

   if (!ereg ("^([0-9]{2,4})-([0-9]{1,2})-([0-9]{1,2}) ?([0-9]{1,2})?:?([0-9]{1,2})?:?([0-9]{1,2})?", $my, $p)) return -1;
   return gmmktime ($p[4], $p[5], $p[6], $p[2], $p[3], $p[1]);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function mystamp($my) {

	 // Pass a 'MySQL' time stamp, and return in a pretty format
	$amonth = substr($my,4,2);
	$aday =substr($my,6,2);
	$ayear =  substr($my,0,4);
	$ahr =substr($my,8,2);
	$amin =substr($my,10,2);
	$asec =substr($my,12,2);
	$aout = "$amonth/$aday/$ayear $ahr:$amin:$asec";
	return $aout;
   
}



//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//This function tests for a match of the current user's security level.  Good for restricting
//access to certain parts of the site

function SecurityCheck($aLevelsCSV) {

	$aLevelsArr = explode(",",$aLevelsCSV);
	for ($i=0; $i<count($aLevelsArr); $i++) if ($MemberLevel == $aLevelsArr[$i]) $aout = 1;

	return $aout;
}

///////////////////////////////////////////////////////////////////////////////////////////
function RandomString($alength=8){ 


	$aPool = "ABCDEFGHIJKLMNOPQRSTUVWXYZ"; 
	$aPool .= "abcdefghijklmnopqrstuvwxyz0123456789"; 
	
	for($aindex = 0; $aindex < $alength; $aindex++) 
		{ 
		srand((double)microtime()*1000000); 
	
		$arandomvalue =  rand (0, strlen($aPool)); 
			
		$aout .= substr($aPool, $arandomvalue, 1); 
		} 

	return($aout); 
} 

///////////////////////////////////////////////////////////////////////////////////////////
function error($asql,$ahost,$aself) {

	
	$amessage = "Database error on $ahost$aself.  The SQL Statement Was

$asql

The Error Message:

" . mysql_error();
	
	$amessage .= "\n\n" . 'Referer = ' .$_SERVER['HTTP_REFERER'];
	
	mail("geoff@mustanginternetservices.com","MySQL Error In $ahost$aself",$amessage,"From: webmaster@sallyjoinc.com");
	//echo "<p>We're sorry, there's been a database error.  The site administrators have been notified";
	//echo "<p>" . mysql_error() . "</p>";

}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////
//CONVERT DATE FROM YYYY-MM-DD TO "Day, Month Name"
function EventDate($aDate)  {

                if ($aDate=="" or $aDate=="0000-00-00" ) {return "";}
                list($aYear , $aMonth, $aDay ) = split( '[/.-]', $aDate );
                if (strlen($aMonth) < 2 ) {$aMonth = "0" . $aMonth;}
                if (strlen($aDay) < 2 ) {$aDay = "0" . $aDay;}
                $aWorkDate = "$aMonth/$aDay/$aYear";
                $aStamp = strtotime($aWorkDate);
                $aOut = date("l, M d", mktime (0,0,0,date("m", $aStamp),date("d", $aStamp), date("Y", $aStamp)) );
                return "$aOut";
        }

/////////////////////////////////////////////////////////////////////////////////
function CheckCityStZip($aCity,$aState,$aZipCode) {

	$sql = "select * from zip_state_city 
		where zip='" . dbprep(substr($aZipCode,0,5)) . "' and
			state='" . dbprep($aState) . "' and
			city='" . dbprep($aCity) . "'";
	$aresult = mysql_query($sql);
	if (!$aresult) error($sql,$HTTP_HOST,"$PHP_SELF?$QUERY_STRING");
	
	if (mysql_num_rows($aresult)) {
		return true;
	}else{
		return false;
	}

}

/////////////////////////////////////////////////////////////////////////////////

function selectMenuOptions($values, $labels, $selected_option) {
	$limit = count($values);
	$str = '';
	for($x=0; $x<$limit; $x++){
		$selected = '';
		if ($values[$x] == $selected_option) {
			$selected = 'selected="selected"';
		}
		$str .= "<option value=\"" . $values[$x] . "\" label=\"". $labels[$x] . "\" $selected >" . $labels[$x] . "</option>\n";
	}
	return $str;

}

/////////////////////////////////////////////////////////////////////////////////

function selectMultipleOptions($values, $labels, $selected_options) {
	$limit = count($values);
	$str = '';
	for($x=0; $x<$limit; $x++){
		$selected = '';
		//if ($values[$x] == $selected_option) {
		if (in_array($values[$x], $selected_options)) {
			$selected = 'selected="selected"';
		}
		$str .= "<option value=\"" . $values[$x] . "\" label=\"". $labels[$x] . "\" $selected >" . $labels[$x] . "</option>\n";
	}
	return $str;

}


//------------------------------------------------------------------------------------------------

Function TaxFind ($strsubtotal, $strstatebill, $strstateship,$origstate,$taxrate) {
	$strtaxresult = 0.0;
 	If ($strstateship) {
	  	$strstate = $strstateship;
	 }else{
	  	$strstate = $strstatebill;
	 }
	
	if ($strstate==$origstate) $strtaxresult = number_format($strsubtotal * $taxrate,2);

 	return $strtaxresult;
}

//------------------------------------------------------------------------------------------------

function calcUpsCost($ship_to_zip, $shipping_method, $product_weight_total) {
	global $_MC_CONFIG_FILE;
	
	include_once "class_ups.php";
	
	if ($_MC_CONFIG_FILE['free_shipping'] == false) {
		$country_code = 'US';
		
		$rate = new Ups();
		$rate->upsProduct($shipping_method);   // See upsProduct() function for codes
		$rate->origin( $_MC_CONFIG_FILE['origination_zip_code'] , $country_code); // Use ISO country codes!
		$rate->dest($ship_to_zip, $country_code);   // Use ISO country codes!
		$rate->rate($_MC_CONFIG_FILE['rate']);     // See the rate() function for codes
		$rate->container($_MC_CONFIG_FILE['container_type_code']); // See the container() function for codes
		$rate->weight($product_weight_total);
		$rate->rescom($_MC_CONFIG_FILE['recipient_address_type']);   // See the rescom() function for codes
		
		$ups_fee = $rate->getQuote();
		
		/*if ( isTestIP() ) {
			echo "<p>ups_fee = $ups_fee</p>";
		}*/
		
		// if the ups fee is a number (not a string/error) OR there is no shipping weight
		if ( (is_numeric($ups_fee))  ||  ($product_weight_total == 0) ) {
			// Markup UPS fee
			$ship_cost = $ups_fee + ($ups_fee * $_MC_CONFIG_FILE['markup_ups_fee_by']) ;
			
			// set minimum ups cost
			if($ship_cost < $_MC_CONFIG_FILE['minimum_shipping_fee']){ 
				$ship_cost = $_MC_CONFIG_FILE['minimum_shipping_fee']; 
			}
		} else { 
			$ship_cost = $ups_fee;
		}
		
	} else {
		$ship_cost = 0;
	}
	
	return $ship_cost;
}

//------------------------------------------------------------------------------------------------

function uploadFile($file_to_upload, $path, $new_file_name = false) {	// the value of $file_to_upload in the fuction call need to be a string 'foo' not a var $foo
	
	$file_name = false;

	if ($_FILES[$file_to_upload]['tmp_name'] && $_FILES[$file_to_upload]['tmp_name'] != 'none' ) {
		if ($new_file_name !== false) {
			$file_name = $new_file_name;
		} else {
			$file_name = $_FILES[$file_to_upload]['name'];
		}
		
		// remove illegal characters from the file name
		$file_name = preg_replace("/\W/", '_', $file_name);
		
		$file_path = $path . $file_name;
		
		$upSource = $_FILES[$file_to_upload]['tmp_name'];

		$uploaded = move_uploaded_file( $upSource, $file_path);
	}

	return $file_name;
}

//------------------------------------------------------------------------------------------------

function uploadFileAdmin($file_to_upload, $path) {	// the value of $file_to_upload in the fuction call need to be a string 'foo' not a var $foo
	
	$file_name = false;

	if ($_FILES[$file_to_upload]['tmp_name'] && $_FILES[$file_to_upload]['tmp_name'] != 'none' ) {
		
		$file_name = $_FILES[$file_to_upload]['name'];
		

		
		$file_path = $path . $file_name;
		
		$upSource = $_FILES[$file_to_upload]['tmp_name'];

		$uploaded = move_uploaded_file( $upSource, $file_path);
	}

	return $file_name;
}


//------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------
// 	END FILE
?>

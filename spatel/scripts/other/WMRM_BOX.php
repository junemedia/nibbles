<?php

include_once("/home/scripts/includes/cssLogFunctions.php");
$iScriptId = cssLogStart("WMRM_BOX.php");

include_once("/home/sites/admin.popularliving.com/html/includes/paths.php");

$sDbTableName = " customDataValidation.WMRM_BOX ";

$aState["ALABAMA"] = "1";
$aState["ALASKA"] = "2";
$aState["AMERICAN SAMOA"] = "3";
$aState["ARIZONA"] = "4";
$aState["ARKANSAS"] = "5";
$aState["ARMED FORCES EUROPE"] = "98";
$aState["ARMED FORCES PACIFIC"] = "97";
$aState["ARMED FORCES THE AME"] = "0";
$aState["CALIFORNIA"] = "6";
$aState["COLORADO"] = "7";
$aState["CONNECTICUT"] = "8";
$aState["DELAWARE"] = "9";
$aState["DISTRICT OF COLUMBIA"] = "10";
$aState["FLORIDA"] = "12";
$aState["GEORGIA"] = "13";
$aState["GUAM"] = "14";
$aState["HAWAII"] = "15";
$aState["IDAHO"] = "16";
$aState["ILLINOIS"] = "17";
$aState["INDIANA"] = "18";
$aState["IOWA"] = "19";
$aState["KANSAS"] = "20";
$aState["KENTUCKY"] = "21";
$aState["LOUISIANA"] = "22";
$aState["MAINE"] = "23";
$aState["MARSHALL ISLANDS"] = "24";
$aState["MARYLAND"] = "25";
$aState["MASSACHUSETTS"] = "26";
$aState["MICHIGAN"] = "27";
$aState["MINNESOTA"] = "28";
$aState["MISSISSIPPI"] = "29";
$aState["MISSOURI"] = "30";
$aState["MONTANA"] = "31";
$aState["NEBRASKA"] = "32";
$aState["NEVADA"] = "33";
$aState["NEW HAMPSHIRE"] = "34";
$aState["NEW JERSEY"] = "35";
$aState["NEW MEXICO"] = "36";
$aState["NEW YORK"] = "37";
$aState["NORTH CAROLINA"] = "38";
$aState["NORTH DAKOTA"] = "39";
$aState["NORTHERN MARIANA ISL"] = "0";
$aState["OHIO"] = "41";
$aState["OKLAHOMA"] = "42";
$aState["OREGON"] = "43";
$aState["PALAU"] = "44";
$aState["PENNSYLVANIA"] = "45";
$aState["PUERTO RICO"] = "46";
$aState["RHODE ISLAND"] = "47";
$aState["SOUTH CAROLINA"] = "48";
$aState["SOUTH DAKOTA"] = "49";
$aState["TENNESSEE"] = "50";
$aState["TEXAS"] = "51";
$aState["UTAH"] = "52";
$aState["VERMONT"] = "53";
$aState["VIRGIN ISLANDS"] = "54";
$aState["VIRGINIA"] = "55";
$aState["WASHINGTON"] = "56";
$aState["WEST VIRGINIA"] = "57";
$aState["WISCONSIN"] = "58";
$aState["WYOMING"] = "59";

$sOpenedDirectory = opendir("/home/gmi_ftp/");
while ($sFile = readdir($sOpenedDirectory)) {
	if (substr($sFile,0,18) == 'BTFESchools_'.date('d').date('m').date('y')) {
		chmod("/home/gmi_ftp/$sFile",0777);
		
		$handle = @fopen("/home/gmi_ftp/$sFile",'r');
		if ($handle) {
		   while (!feof($handle)) {
		       $lines[] = ereg_replace("\n",'',fgets($handle, 4096));
		   }
		   fclose($handle);
		   $rResult = mysql_query("TRUNCATE TABLE $sDbTableName");
		}
		
		for ($i=1; $i<=count($lines);$i++) {
			if (substr_count($lines[$i],',') == 5) {
				$pieces = explode(",", $lines[$i]);
			}
			
			if (substr_count($lines[$i],',') > 5) {
				$temp = str_replace('",','|',$lines[$i]);
				$temp = str_replace(',"','|',$temp);
				$temp = str_replace('"','',$temp);
				$pieces = explode("|", $temp);
			}
			
			if (substr_count($lines[$i],',') > 0) {
				$schoolId = str_replace('"','',$pieces[0]);
				$schoolName = str_replace('"','',$pieces[1]);
				$address = str_replace('"','',$pieces[2]);
				$city = str_replace('"','',$pieces[3]);
				$state = str_replace('"','',$pieces[4]);
				$zip = str_replace('"','',$pieces[5]);
				$stateId = $aState[$state];

				$sInsert = "INSERT IGNORE INTO $sDbTableName (schoolId,schoolName,address,city,state,stateId,zip)
							VALUES (\"$schoolId\",\"$schoolName\",\"$address\",\"$city\",\"$state\",\"$stateId\",\"$zip\")";
				$rResult = mysql_query($sInsert);
			}
		}
	}
}

cssLogFinish( $iScriptId );

?>

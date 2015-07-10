<?php

/*********

Script to Add/Edit Offer

**********/

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");
include("$sGblLibsPath/dateFunctions.php");

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
	
$sPageTitle = "Nibbles Offers - Add/Edit Offer";

$iCurrYear = date(Y);
$iCurrMonth = date(m); //01 to 12
$iCurrDay = date(d); // 01 to 31


if ($sSaveClose || $sSaveNew || $sSaveContinue) { 
	
	// Set the active date and inactive date according to selection
	$sActiveDateTime = $iYearActive.$iMonthActive.$iDayActive.$iHourActive."0000";
	$sInactiveDateTime = $iYearInactive.$iMonthInactive.$iDayInactive.$iHourInactive."0000";
	
	$sLastLeadDate = "$iLastLeadYear-$iLastLeadMonth-$iLastLeadDay";
	$sRerunStartDate = "$iRerunStartYear-$iRerunStartMonth-$iRerunStartDay";
	$sRerunEndDate = "$iRerunEndYear-$iRerunEndMonth-$iRerunEndDay";
	
	if ($fRevPerLead == '') {
		$fRevPerLead = 0;
	}
		
		
	if (strlen($sOfferCode) > 25) {
		$sMessage = "OfferCode should be maximum 25 characters long...";
		$bKeepValues = true;
	} else if (!ereg("^[A-Za-z0-9_]+$", $sOfferCode)) {
		$sMessage = "OfferCode can contain only Alphabets, Numbers or _ ";
		$bKeepValues = true;
	} else if ($sName == '') {
		$sMessage = "Offer Name Should Not Be Blank...";
		$bKeepValues = true;
	}  else if (!ereg("^[0-9\.]*$", $fRevPerLead)) {
		$sMessage = "Revenue Per Lead Can Contain Only Numbers or .";
		$bKeepValues = true;
	} if ($iCompanyId == '') {
		$sMessage = "Please select offer company...";
		$bKeepValues = true;
	} else {
		
		// check if offercode already exists
		
		$sCheckQuery = "SELECT *
						FROM   offers
						WHERE  offerCode = '$sOfferCode'";
		if ($iId)
		$sCheckQuery .= " AND id != '$iId'";
		
		$rCheckResult = dbQuery($sCheckQuery);
		
		if ( dbNumRows($rCheckResult) > 0 ) {
			$sMessage = "Offercode already exists...";
			$bKeepValues = true;
		} else {
			
			// Prepare comma-separated pages if record added or edited
			
			$sPagesQuery = "SELECT id, pageName
					FROM   otPages
					ORDER BY pageName";
			$rPagesResult = dbQuery($sPagesQuery);
			$i = 0;
			while ($oPagesRow = dbFetchObject($rPagesResult)) {
				
				// prepare Categories of this offer
				$sCheckboxName = "page_".$oPagesRow->id;
				
				$iCheckboxValue = $$sCheckboxName;
				
				if ($iCheckboxValue != '') {
					$aPagesArray[$i] = $iCheckboxValue;
					$sPagesString .= $iCheckboxValue.",";
					$i++;
				}
			}
			
			// Prepare comma-separated Categories if record added or edited
			
			$sCategoryQuery = "SELECT id, title
					FROM   categories
					ORDER BY title";
			$rCategoryResult = dbQuery($sCategoryQuery);
			$i = 0;
			while ($oCategoryRow = dbFetchObject($rCategoryResult)) {
				
				// prepare Categories of this offer
				$sCheckboxName = "category_".$oCategoryRow->id;
				
				$iCheckboxValue = $$sCheckboxName;
				
				if ($iCheckboxValue != '') {
					$aCategoriesArray[$i] = $iCheckboxValue;
					$sCategoriesString .= $iCheckboxValue.",";
					$i++;
				}
			}
			
						
			
			if (!($iId)) {
				
				// if new data submitted
				
				//check if offercode exists
				$sCheckQuery = "SELECT *
				   		FROM offers
				   		WHERE offerCode = '$sOfferCode'";
				$rCheckResult = dbQuery($sCheckQuery);
				if (dbNumRows($rCheckResult) == 0) {
					
					$sInsertQuery = "INSERT INTO offers(offerCode, companyId, name, headline,
								description, shortDescription, revPerLead, autoRespEmailSub, autoRespEmailBody, notes) 
					 	VALUES('$sOfferCode', '$iCompanyId', \"$sName\", \"$sHeadline\", \"$sDescription\", \"$sShortDescription\", 
								'$fRevPerLead', \"$sAutoRespEmailSub\", \"$sAutoRespEmailBody\", \"$sNotes\")";
					
					$rInsertResult = dbQuery($sInsertQuery);
					if (! $rInsertResult) {
						echo dbError();
					} else {
						// get offerId to use in image name
						$iOfferId = dbInsertId();
						$iId = $iOfferId;
						
						// Insert into PageMap according to page checkboxes checked
						if (count($aPagesArray) > 0) {
							for ($i = 0; $i < count($aPagesArray); $i++) {
								$sInsertQuery = "INSERT INTO pageMap(offerCode, pageId, sortOrder)
									VALUES('$sOfferCode', '".$aPagesArray[$i]."','0')";
								$rInsertResult = dbQuery($sInsertQuery);
								if (!($rInsertResult)) {
									echo dbError();
								}
							}
						}
						
						// Insert into categoryMap according to category checkboxes checked
						if (count($aCategoriesArray) > 0) {
							for ($i = 0; $i < count($aCategoriesArray); $i++) {
								$sInsertQuery = "INSERT INTO categoryMap(offerCode, categoryId)
									VALUES('$sOfferCode', '".$aCategoriesArray[$i]."')";
								$rInsertResult = dbQuery($sInsertQuery);
								if (!($rInsertResult)) {
									echo dbError();
								}
							}
						}
						$sMessage = "Offer Added successfully...";
					}
				} else {
					$sMessage = "OfferCode Exists... $sOfferCode";
					$bKeepValues = true;
				}
				
			} else {
				// If record edited								
				
				$sEditQuery = "UPDATE offers
					   SET    companyId = '$iCompanyId', 
							  name = '$sName',
							  headline = \"$sHeadline\",
							  description = \"$sDescription\",		
							  shortDescription = \"$sShortDescription\",
							  revPerLead = '$fRevPerLead',							 							 
							  autoRespEmailSub = '$sAutoRespEmailSub',
							  autoRespEmailBody = \"$sAutoRespEmailBody\",							  
							  notes = '$sNotes'							 					  
				  WHERE id = '$iId'";	
				 				
				$rResult = dbQuery($sEditQuery);
				echo dbError();
				//}
				
				if ($rResult) {
										
															
					// Delete records from pageMap with the pages which are not checked
					
					// remove last comma from the pages list
					$sPagesString = substr($sPagesString, 0, strlen($sPagesString)-1);
					// Delete if any page unchecked for the offer to be displayed in.
					$sDeleteQuery = "DELETE FROM pageMap
						WHERE  offerCode = '$sOfferCode'";
					if ($sPagesString != '') {
						$sDeleteQuery .= " AND pageId NOT IN (".$sPagesString.")";
					}
					$rDeleteResult = dbQuery($sDeleteQuery);
					
					
					if (count($aPagesArray) > 0) {
						for ($i = 0; $i<count($aPagesArray); $i++) {
							$sCheckQuery = "SELECT *
							   FROM   pageMap
							   WHERE  pageId = ".$aPagesArray[$i]."
							   AND    offerCode = '$sOfferCode'";
							$rCheckResult = dbQuery($sCheckQuery);
							if (dbNumRows($rCheckResult) == 0) {
								// INSERT OfferCategoryRel record
								
								$sInsertQuery = "INSERT INTO pageMap (pageId, offerCode, sortOrder)
					VALUES('".$aPagesArray[$i]."', '$sOfferCode', '0')";
								$rInsertResult = dbQuery($sInsertQuery);
							}
						}
					}
					
					// Delete records from categoryMap with the categories which are not checked
					
					// remove last comma from the categories list
					$sCategoriesString = substr($sCategoriesString, 0, strlen($sCategoriesString)-1);
					// Delete if any category unchecked for the offer to be displayed in.
					$sDeleteQuery = "DELETE FROM categoryMap
						WHERE  offerCode = '$sOfferCode'";
					if ($sCategoriesString != '') {
						$sDeleteQuery .= " AND categoryId NOT IN (".$sCategoriesString.")";
					}
					$rDeleteResult = dbQuery($sDeleteQuery);					
					
					if (count($aCategoriesArray) > 0) {
						for ($i = 0; $i<count($aCategoriesArray); $i++) {
							$sCheckQuery = "SELECT *
							   FROM   categoryMap
							   WHERE  categoryId = '".$aCategoriesArray[$i]."' 
							   AND    offerCode = '$sOfferCode'";
							
							$rCheckResult = dbQuery($sCheckQuery);
							echo dbError();
							if (dbNumRows($rCheckResult) == 0) {
								// INSERT OfferCategoryRel record
								
								$sInsertQuery = "INSERT INTO categoryMap (categoryId, offerCode)
					VALUES('".$aCategoriesArray[$i]."', '$sOfferCode')";
								$rInsertResult = dbQuery($sInsertQuery);
							}
						}
					}
					
				} else {
					echo dbError();
				}
				$iOfferId = $iId;
			}
			
			// save uploaded image
			
			if (!(is_dir("$sGblOfferImagePath/$sOfferCode")) ) {				
				mkdir("$sGblOfferImagePath/$sOfferCode",0777);
				chmod("$sGblOfferImagePath/$sOfferCode",0777);

			}
			
			if ($_FILES['image']['tmp_name'] && $_FILES['image']['tmp_name']!="none") {
				//echo  $_FILES['image']['type'];
				$sUploadedFileName = $_FILES['image']['tmp_name'];
				$sFileSize = $_FILES['image']['size'];
				$aImageSize = getimagesize($sUploadedFileName);
				if ( $aImageSize[0] <= 150 && $aImageSize[1] <= 150) {
								
				//Get Extention
				$aImageFileNameArray = explode(".",$_FILES['image']['name']);
				$i = count($aImageFileNameArray) - 1;
				$sImageFileExt = $aImageFileNameArray[$i];
				
				$sImageFileName = $sOfferCode."_page1". ".$sImageFileExt";
				$sNewImageFile  = "$sGblOfferImagePath/$sOfferCode/$sImageFileName";
				
				move_uploaded_file( $sUploadedFileName, $sNewImageFile);
				
				// store the image file name in database
				$sUpdateQuery = "UPDATE offers
					SET    imageName = '$sImageFileName'
					WHERE  id = '$iOfferId'";
				$rUpdateResult = dbQuery($sUpdateQuery);
				} else {
					$sMessage = "Image Should Be Maximum Of 150 W x 150 H Size...";
					$bKeepValues = true;
				}
				
			}
			
			
			// upload page1 small image if selected
			if ($_FILES['small_image']['tmp_name'] && $_FILES['small_image']['tmp_name']!="none") {
				//echo  $_FILES['image']['type'];
				$sUploadedFileName = $_FILES['small_image']['tmp_name'];
				$sFileSize = $_FILES['small_image']['size'];
				$aImageSize = getimagesize($sUploadedFileName);
				if ( $aImageSize[0] <= 88 && $aImageSize[1] <= 31) {
								
				//Get Extention
				$aImageFileNameArray = explode(".",$_FILES['small_image']['name']);
				$i = count($aImageFileNameArray) - 1;
				$sImageFileExt = $aImageFileNameArray[$i];
				
				$sSmallImageFileName = $sOfferCode."_small_page1". ".$sImageFileExt";
				$sNewImageFile  = "$sGblOfferImagePath/$sOfferCode/$sSmallImageFileName";
				
				move_uploaded_file( $sUploadedFileName, $sNewImageFile);
				
				// store the image file name in database
				$sUpdateQuery = "UPDATE offers
								 SET    smallImageName = '$sSmallImageFileName'
								 WHERE  id = '$iOfferId'";
				$rUpdateResult = dbQuery($sUpdateQuery);
				} else {
					$sMessage = "Small Image Should Be Exactly 88 W x 31 H Size Only...";
					$bKeepValues = true;
				}
				
			}
			
			
			// upload page2 image
			if ($_FILES['page2Image']['tmp_name'] && $_FILES['page2Image']['tmp_name']!="none") {
				
				$sUploadedFileName = $_FILES['page2Image']['tmp_name'];
				
				//Get Extention
				$aImageFileNameArray = explode(".",$_FILES['page2Image']['name']);
				$i = count($aImageFileNameArray) - 1;
				$sImageFileExt = $aImageFileNameArray[$i];
				
				$sImageFileName = $sOfferCode."_". time(). ".$sImageFileExt";
				$sNewImageFile  = "$sGblOfferImagePath/$sOfferCode/$sImageFileName";
				
				move_uploaded_file( $sUploadedFileName, $sNewImageFile);											
			}
			
			// Find out on which page this sourceCode will appear, set ORDER BY as sourcecode
			// and go to that page, and display redirect for this sourceCode
			if ($sFilter != '') {
				
				$sFilterPart .= " AND ( ";
				
				switch ($sSearchIn) {
					case "headline" :
					$sFilterPart .= ($sExactMatch == 'Y') ? "headline = '$sFilter'" : "headline like '%$sFilter%'";
					break;
					case "description" :
					$sFilterPart .= ($sExactMatch == 'Y') ? "description = '$sFilter'" : "description like '%$sFilter%'";
					break;
					case "companyName" :
					$sFilterPart .= ($sExactMatch == 'Y') ? "OC.companyName = '$sFilter'" : "OC.companyName like '%$sFilter%'";
					break;
					case "offerCode" :
					$sFilterPart .= ($sExactMatch == 'Y') ? "offerCode = '$sFilter'" : "offerCode like '%$sFilter%'";
					break;
					default:
					$sFilterPart .= ($sExactMatch == 'Y') ? "offerCode = '$sFilter' || OC.companyName = '$sFilter' || headline = '$sFilter' || description = '$sFilter'  " : " offerCode like '%$sFilter%' || OC.companyName LIKE '%$sFilter%' || headline like '%$sFilter%' || description like '%$sFilter%' ";
				}
				$sFilterPart .= ") ";
			}
			
			if ($sExclude != '') {
				$filterPart .= " AND ( ";
				switch ($sExclude) {
					case "headline" :
					$sFilterPart .= "headline NOT LIKE '%$sExclude%'";
					break;
					case "description" :
					$sFilterPart .= "description NOT LIKE '%$sExclude%'";
					break;
					case "companyName" :
					$sFilterPart .= "OC.companyName NOT LIKE '%$sExclude%'";
					break;
					case "offerCode" :
					$sFilterPart .= "offerCode NOT LIKE '%$sExclude%'";
					break;
					default:
					$sFilterPart .= "offerCode NOT LIKE '%$sExclude%' && OC.companyName NOT LIKE '%$sExclude%' && headline NOT LIKE '%$sExclude%' && description NOT LIKE '%$sExclude%'" ;
				}
				$sFilterPart .= " ) ";
				
			}
			
			$sTempQuery = "SELECT count(*) numRecords
			  FROM   offers O, offerCompanies OC
			  WHERE  O.companyId = OC.id AND offerCode < '$sOfferCode' 
			  $sFilterPart 
			  ORDER BY offerCode $sCurrOrder";
			
			$rTempResult = dbQuery($sTempQuery);
			echo dbError();
			while ($oTempRow = dbFetchObject($rTempResult)) {
				$iNumRecords = $oTempRow->numRecords;
			}
			
			$iThisRecordNo = $iNumRecords + 1; // because the next record will be the current record (record of this offercode)
			
			if (!($iRecPerPage)) {
				$iRecPerPage = 20;
			}
			$iPage = ceil($iThisRecordNo/$iRecPerPage);
			
					
			$sPageReloadUrl .= "index.php?iMenuId=$iMenuId&sFilter=$sFilter&sExactMatch=$sExactMatch&sExclude=$sExclude&sSearchIn=$sSearchIn&iRecPerPage=$iRecPerPage&iPage=$iPage&sOfferCode=$sOfferCode";
			
		}
		if ($sSaveContinue) {
			if ($bKeepValues != true) {
				echo "<script language=JavaScript>
					window.opener.location.href = '".$sPageReloadUrl."';			
					</script>";			
				// exit from this script				
			}
		} else if ($sSaveClose) {
			if ($bKeepValues != true) {
				echo "<script language=JavaScript>
					window.opener.location.href = '".$sPageReloadUrl."';
					self.close();
					</script>";			
				// exit from this script
				exit();
			}
		} else if ($sSaveNew) {
			
			if ($bKeepValues != true) {
				$sReloadWindowOpener = "<script language=JavaScript>
					window.opener.location.href = '".$sPageReloadUrl."';
					</script>";	
				
				// reset offer variables
				$sOfferCode = "";
				$sName = "";
				$iCompanyId = "";
				$sHeadline = "";
				$sDescription = "";	
				$sShortDescription = "";			
				$fRevPerLead = "";
				$sImageName = "";	
				$sSmallImageFileName = '';			
				$sAutoRespEmailSub = "";
				$sAutoRespEmailBody = "";				
				$sNotes = "";
			}
		}
		$iOfferId = '';
	}
}

if ($iId != ''  || $sOfferCode != '') {
	// If Clicked Edit, display values in fields and
	// buttons to edit/Reset...
	
	// Get the data to display in HTML fields for the record to be edited
	if ($sOfferCode != '') {
	$sSelectQuery = "SELECT *
					 FROM   offers
					 WHERE  offerCode = '$sOfferCode'";
	} else {
		$sSelectQuery = "SELECT *
					 FROM   offers
					 WHERE  id = '$iId'";
	}
	$rResult = dbQuery($sSelectQuery);
	
	if ($rResult) {
		
		while ($oRow = dbFetchObject($rResult)) {
			
			if ($bKeepValues != "true") {
			$iId = $oRow->id;
			$sOfferCode = $oRow->offerCode;
			$sName = $oRow->name;			
			$iCompanyId = $oRow->companyId;
			$sHeadline = ascii_encode($oRow->headline);
			$sDescription = ascii_encode($oRow->description);
			$sShortDescription = ascii_encode($oRow->shortDescription);
			$fRevPerLead = $oRow->revPerLead;
			$sNotes = ascii_encode($oRow->notes);			
			$sAutoRespEmailSub = ascii_encode($oRow->autoRespEmailSub);			
			$sAutoRespEmailBody = ascii_encode($oRow->autoRespEmailBody);						
			}
			if ($oRow->imageName != '') {
				$sCurrentImage = "<img src='$sGblOfferImageUrl/$sOfferCode/$oRow->imageName'>";
			} else {
				$sCurrentImage = "No Image";
			}
			
			if ($oRow->smallImageName != '') {
				$sCurrentImage .= "&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
									Small Image &nbsp; &nbsp; &nbsp; <img src='$sGblOfferImageUrl/$sOfferCode/$oRow->smallImageName'>";
			}
			
		}				
		
		
	} else {
		echo dbError();
	}
	
} else {
		
	$sHeadline = ascii_encode(stripslashes($sHeadline));
	$sDescription = ascii_encode(stripslashes($sDescription));
	$sShortDescription = ascii_encode(stripslashes($sShortDescription));
	
	$sAutoRespEmailSub = ascii_encode(stripslashes($sAutoRespEmailSub));
	$sAutoRespEmailBody = ascii_encode(stripslashes($sAutoRespEmailBody));
	$sNotes = ascii_encode(stripslashes($sNotes));
	
	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=sAbandonNew value=' Abandon & New  '>";	
}

if ($iId != '') {
	$sOfferCodeField = "<tr><td align=right>Offer Code</td><td colspan=3>$sOfferCode</td></tr>";
} else {
	$sOfferCodeField = "<tr><td align=right>Offer Code</td><td colspan=3><input type=text name=sOfferCode value='$sOfferCode'><BR>
							OfferCode must contain AlphaNumeric characters, - or _ only and maximum 25 chars long.</td></tr>";
}
		
$sCompanyQuery = "SELECT   id, companyName, code
				   FROM     offerCompanies
				   ORDER BY companyName";
$rCompanyResult = dbQuery($sCompanyQuery);

$sCompanyOptions .= "<option value=''>Select Company";
while ( $oCompanyRow = dbFetchObject($rCompanyResult)) {
	if ($oCompanyRow->id == $iCompanyId) {
		$sSelected = "selected";
	} else {
		$sSelected = "";
	}
	$sCompanyOptions .= "<option value='".$oCompanyRow->id."' $sSelected>".$oCompanyRow->companyName . " - " . $oCompanyRow->code;
}



// Prepare checkboxes for Pages
$sPagesQuery = "SELECT *
			    FROM   otPages
				ORDER BY pageName";
$rPagesResult = dbQuery($sPagesQuery);

$j = 0;
$sPageCheckboxes = "<tr>";
while ($oPagesRow = dbFetchObject($rPagesResult)) {
	$iPageId = $oPagesRow->id;
	$sPageName = $oPagesRow->pageName;
	
	$sOfferQuery = "SELECT offerCode
				   FROM   otPages, pageMap
				   WHERE  pageMap.pageId = '$iPageId'
				   AND    pageMap.offerCode = '$sOfferCode'
				   AND    pageMap.pageId = otPages.id";
	
	$rOfferResult = dbQuery($sOfferQuery);
	
	if (dbNumRows($rOfferResult) > 0) {
		$sPageChecked  = "checked";
	} else {
		$sPageChecked = "";
	}
	
	if ($j%3 == 0) {
		if ($j != 0) {
			$sPageCheckboxes .= "</tr>";
		}
		$sPageCheckboxes .= "<tr>";
	}
		
	$sPageCheckboxes .= "<td width=5% valign=top><input type=checkbox name='page_".$oPagesRow->id."' value='".$oPagesRow->id."' $sPageChecked></td><td  width=28%>$sPageName</td>";
	$j++;
}
$sPageCheckboxes .= "</tr>";


// Prepare checkboxes for Categories
$sCategoriesQuery = "SELECT *
			  	FROM   categories
				ORDER BY title";
$rCategoriesResult = dbQuery($sCategoriesQuery);
echo dbError();
$j = 0;
$sCategoryCheckboxes = "<tr>";
while ($oCategoriesRow = dbFetchObject($rCategoriesResult)) {
	$iCategoryId = $oCategoriesRow->id;
	$sCategoryTitle = $oCategoriesRow->title;
	//echo "<BR>".$iCategoryId.$sCategoryTitle;
	$sOfferQuery = "SELECT offerCode
				   FROM   categories, categoryMap
				   WHERE  categoryMap.categoryId = '$iCategoryId'
				   AND    categoryMap.offerCode = '$sOfferCode'
				   AND    categoryMap.categoryId = categories.id";
	
	$rOfferResult = dbQuery($sOfferQuery);
	
	echo dbError();
	if(dbNumRows($rOfferResult)>0){
		$sCategoryChecked  = "checked";
	} else {
		$sCategoryChecked = "";
	}
	
	if ($j%3 == 0) {
		if ($j != 0) {
			$sCategoryCheckboxes .= "</tr>";
		}
		$sCategoryCheckboxes .= "<tr>";
	}
	
	//echo "<BR>".$iCategoryId.$sCategoryTitle;
	$sCategoryCheckboxes .= "<td width=5% valign=top><input type=checkbox name='category_".$iCategoryId."' value='".$iCategoryId."' $sCategoryChecked></td><td  width=28%>$sCategoryTitle</td>";
	$j++;
	
}
$sCategoryCheckboxes .= "</tr>";


// delete the image if clicked on the delete link

if ($sDeleteImage) {
	unlink("$sGblOfferImagePath/$sOfferCode/$sDeleteImage");
}

// get list of page2 images, if offer is selected to edit
if ($sOfferCode && $iId && file_exists("$sGblOfferImagePath/$sOfferCode")) {
$rImageDir = opendir("$sGblOfferImagePath/$sOfferCode");
if ($rImageDir) {
	//echo $rImageDir;
	while (($sFile = readdir($rImageDir)) != false) {	
		if (!is_dir("$sGblOfferImagePath/$sOfferCode/$sFile")) {
					
			$page2ImagesList .=  "<a href='JavaScript:void(window.open(\"$sGblOfferImageUrl/$sOfferCode/$sFile\",\"\",\"\"));'>$sGblOfferImageUrl/$sOfferCode/$sFile</a> 
					&nbsp; <a href='$PHP_SELF?iMenuId=$iMenuId&iId=$iId&sOfferCode=$sOfferCode&iRecPerPage=$iRecPerPage&";
			$page2ImagesList .="sFilter=$sFilter&sExactMatch=$sExactMatch&sExclude=$sExclude&sSearchIn=$sSearchIn&sDeleteImage=$sFile'>Delete</a><BR>";
		}
	}
}
}

// Hidden fields to be passed with form submission
$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>
			<input type=hidden name=sOfferCode value='$sOfferCode'>
			<input type=hidden name=iRecPerPage value='$iRecPerPage'>
			<input type=hidden name=sFilter value='$sFilter'>
			<input type=hidden name=sExactMatch value='$sExactMatch'>
			<input type=hidden name=sExclude value='$sExclude'>
			<input type=hidden name=sSearchIn value='$sSearchIn'>
			<input type=hidden name=iPage value='$iPage'>";

//include("../../includes/adminAddHeader.php");

?>

<html>

<head>
<title><?php echo $sPageTitle;?></title>
<LINK rel="stylesheet" href="<?php echo $sGblAdminSiteRoot;?>/styles.css" type="text/css" >
</head>

<body>

<table width=85% align=center>
<tr><Td class=message align=center colspan=2><?php echo $sMessage;?>
</td></tr></table>	


<script language=JavaScript>


function openWin(winUrl) {
	checkForm();
	var temp = window.open(winUrl,'','');		
}

</script>

<form name=form1 action='<?php echo $PHP_SELF;?>' method=post enctype=multipart/form-data >

<?php echo $sHidden;?>
<?php echo $sReloadWindowOpener;?>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>		
	<?php echo $sOfferCodeField;?>
	</tr>
	<tr><td >Offer Name</td>
		<td ><input type=text name=sName value='<?php echo $sName;?>'></td>
		</tr>
		<Tr>
	<td >Company</td>
		<td ><select name=iCompanyId>
		<?php echo $sCompanyOptions;?>
			</select> <a href='JavaScript:void(window.open("<?php echo $sGblAdminSiteRoot;?>/offerCompanies/addCompany.php?iMenuId=15&sReturnTo=iCompanyId", "", "height=450, width=600, scrollbars=yes, resizable=yes, status=yes"));'>Add Company</a></td>
	</tr>
		
	<tr><td align=right>Headline</td>
		<td colspan=3><input type=text name=sHeadline value='<?php echo $sHeadline;?>' size=70></td>
	</tr>
	<tr>
		<td align=right>Image</td>
		<td colspan=3><input type=file name='image'>
		<BR> Image Should Be Maximum Of 150 W x 150 H size</td>
	</tr>
	<tr>
		<td align=right>Small Image</td>
		<td colspan=3><input type=file name='small_image'>
		<BR> Image Should Be Exactly 88 W x 31 H size</td>
	</tr>
	
	<tr><td align=right>Description</td>
		<td colspan=3><textarea name=sDescription rows=5 cols=80><?php echo $sDescription;?></textarea></td>
	</tr>	
	<tr><td align=right>Short Description</td>
		<td colspan=3><textarea name=sShortDescription rows=5 cols=80><?php echo $sShortDescription;?></textarea></td>
	</tr>	
	<?php 
	if ($iId) {
		echo "<tr><td align=right>Current Image</td>
					<td colspan=3>$sCurrentImage</td></tr>";
	}
	?>
	
	
	<tr><td align=right>Notes</td>
		<td ><textarea name=sNotes rows=3 cols=70><?php echo $sNotes;?></textarea></td>
	</tr>	
	<tr>
		<td >Auto Responder Email Subject</td>
		<td><input type=text name=sAutoRespEmailSub value='<?php echo $sAutoRespEmailSub;?>' size=35></td>
	</tr>	
	<tr>
	<td >Auto Responder Email Body</td>
		<td ><?php echo $sAutoRespPreviewLink;?><BR><textarea name=sAutoRespEmailBody rows=5 cols=80><?php echo $sAutoRespEmailBody;?></textarea><BR>
			[EMAIL] will be replaced with user's email address while sending the email.</td>
	</tr>		
	
	<tr><td align=right>Rev. Per Lead</td>
		<td><input type=text name=fRevPerLead value='<?php echo $fRevPerLead;?>'> $</td>		
	</tr>	
	
	<!--<tr><td colspan=3><b>Changing Mode May Change Last Delivery Date</b></td>-->
		
	
	</table>
		
	
	<BR>
	<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>	
	<tr>
	<td colspan=2 class=header>Assign Offer To The Following OT Pages</td>
	</tr>
	</table>
<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>	
	
	<?php echo $sPageCheckboxes;?>
		
</table>
<BR>
<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
<tr>
	<td colspan=2 class=header>Assign Offer To The Following Categories</td>
	</tr>
	</table>
<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>	
	
	<?php echo $sCategoryCheckboxes;?>
		
</table>
<!--
<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
	<tr><TD colspan=2 align=center >
		<input type=submit name=sSaveContinue value='Save & Continue'> &nbsp; &nbsp; 
		</td><td></td>
	</tr>	
	</table>
		-->
	
<?php
	include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>


<?php

// Two files are included because we are calling functions and getting paths
// from below files
include("../includes/paths.php");
include("$sGblLibsPath/validationFunctions.php");

	$mytime=localtime(time());
	$year=$mytime[5]+1900;


	
	
$sPmdPostUrl = "https://admin.popularliving.com/cgi-bin/FormMail4.pl";

// starting session
session_start();

// assigning values to session id.
if ($src != '') {
	$_SESSION["sSesSourceCode"] = $src;
}

if ($sEmail) {
	$_SESSION["sSesEmail"] = $sEmail;
} else {
	$sEmail = $_SESSION["sSesEmail"];
}

$sRemoteIp = $_SERVER['REMOTE_ADDR'];

// Counting number of categories were selected.  This number will be use later to
// to see if user has at least one category selected
// getting count array
if ($sSubmit) {
	$sMessage = '';
	for ($i=0; $i < count($aPmdCategories); $i++) {
		if (trim($aPmdCategories[$i]) != '') {
			$iCategories++;
		}
	}

	// This is the error message for incorrect email format.  This block will not execute if
	// email is in correct format
	if (!(validateEmailFormat($sEmail))) {
		$sMessage .= "<li>Invalid email address.";
	}
	
	
//	if (!preg_match("/(^\d{5}$)|(^\d{5}-\d{4}$)/",$sZip)){
//		$sMessage .= "<li>Invalid zip code.";
//	}
	
	// This block will not execute if user select at least one category from either Hot Topics or Others
	// Error message will be displayed when you submit the form if NO category is selected.
	if ($iCategories == 0) {
		$sMessage .= "<li>Select at least one interest.";
	}
	
	
	// If "Yead of Birth" is blank or non-numeric, it will displayed error message
	if (trim($iBirthYear) == '' || !isinteger($iBirthYear) || strlen($iBirthYear)!=4 || $iBirthYear == 0 || $iBirthYear > $year) {
		$sMessage .= "<li>Invalid year of birth.";
	}
	
	// If no error (that means user filled out form correctly), then insert data into database
	if ($sMessage == '') {
		
		$_SESSION["sSesEmail"] = $sEmail;
		$_SESSION["sSesState"] = $sState;
		$_SESSION["sSesZip"] = $sZip;
		$sPostCat = '';
		// insert user data
		$sUserInsertQuery = "INSERT IGNORE INTO pmdUsers(email, sex, birthYear, state, zip, canReceiveHtml, 
									jobTitle, jobFunction, sourceCode, dateTimeAdded)
						 VALUES(\"$sEmail\", '$sSex', '$iBirthYear', '$sState', '$sZip', '$icanReceiveHtml',
									\"$sJobTitle\", \"$sJobFunction\", \"".$_SESSION['sSesSourceCode']."\", now())";
		$rUserInsertResult = dbQuery($sUserInsertQuery);
		
		if ($rUserInsertResult) {
		// insert/update user intereset
		// run list of all category and assign id #
		for($i=0;$i<count($aPmdCategories);$i++) {
			$iCategoryId = $aPmdCategories[$i];
			
			// insert id #, email, and current date to database.
			if ($iCategoryId) {
				$sInterestQuery = "INSERT IGNORE INTO pmdUserCategories(email, categoryId, dateTimeAdded)
								   VALUES('$sEmail', '$iCategoryId', now())";
				$rInterestResult = dbQuery($sInterestQuery);
				echo dbError();	// display any error (if any).
			}
			
			// prepare form post data
			$sCatQuery = "SELECT categorySQL
						  FROM	 pmdCategories
						  WHERE  id = '$iCategoryId'";
			$rCatResult = dbQuery($sCatQuery);
			while ($oCatRow = dbFetchObject($rCatResult)) {
				$sPostCat .= "&listname=".urlencode("MyFree.com/".$oCatRow->categorySQL.".list");
			}
		}	
		
		// post pmd data
		// take all values from session and assign it to variable...
	$sUrlEmail = urlencode($sEmail);
	//$sUrlSalutation = urlencode($_SESSION["sSesSalutation"]);
	$sUrlName = urlencode($_SESSION["sSesFirst"] . " " . $_SESSION["sSesLast"]);	
	$sUrlAddress = urlencode($_SESSION["sSesAddress"]);
	//$sUrlAddress2 = urlencode($_SESSION["sSesAddress2"]);
	$sUrlCity = urlencode($_SESSION["sSesCity"]);
	$iUrlYearOfBirth = urlencode($iBirthYear);

	$sUrlState = urlencode($_SESSION["sSesState"]);
	$sUrlZip = urlencode($_SESSION["sSesZip"]);
	$sUrlSourceCode = urlencode($_SESSION["sSesSourceCode"]);
	$sUrlJobTitle = urlencode($sJobTitle);
	$sUrlJobFunction = urlencode($sJobFunction);
		
	//$sUrlThankYou = urlencode("$sGblSiteRoot/taf/index.php?e=$sEmail");
	$sUrlOwner = urlencode("MyFree.com");
	$sUrlGatherer = urlencode("signup");
	$sUrlDisableConfirmWarning = '';	
	//$sUrlNextRedirect = urlencode("$sGblSiteRoot/j/index.php");
	
	// concat email...to...DisableConfirmWarning
	$sPmdPostString = "email=$sEmail&name=$sUrlName&address=$sUrlAddress&city=$sUrlCity&state=$sUrlState&yearofbirth=$iUrlYearOfBirth";
	$sPmdPostString .= "&zip=$sUrlZip&phone=$sUrlPhone&gender=$sSex&src=$sUrlSourceCode&title=$sUrlJobTitle&jobfunction=$sUrlJobFunction";
	$sPmdPostString .= "&owner=$sUrlOwner&gatherer=$sUrlGatherer&disableconfirmationwarning=$sUrlDisableConfirmWarning";
	
	//concat above line + sPostCat
	//&thankyouurl=$sUrlThankYou&redirect=$sUrlThankYou
	$sPmdPostString .= $sPostCat;
	
	
	$aPmdUrlArray = explode("//", $sPmdPostUrl);
	$sPmdUrlPart = $aPmdUrlArray[1];
	
	
	// concat post url followed by "?" + followed by above string
	// eg. http//www.test.com/test.php("?")(string)	
	$sPmdPostUrl .= "?".$sPmdPostString;


	//echo $sPmdPostUrl;
	//exit;
	
	$rFp = fopen($sPmdPostUrl, "r");
	if ($rFp) {
		while ($line = fread($rFp,8192)) {
			$result .= $line;
			}
			//echo $result;
			
			fclose($rFp);
	}
	header("Location:$sGblSiteRoot/taf/index.php?e=$sEmail&".SID);
	
	
	//$sPmdHostPart = substr($sPmdUrlPart,0,strlen($sPmdUrlPart)-strrpos(strrev($sPmdUrlPart),"/"));
	//$sPmdScriptPath = substr($sPmdUrlPart,strlen($sPmdHostPart));
		
	//$sPmdScriptPath = "FormMail4.pl";
	
	/*
	$rSocketConnection = fsockopen($sPmdHostPart, 80, $errno, $errstr, 30);	
		
	if ($rSocketConnection) {
		//echo "Dfdfd";
		$sPmdScriptPath  .= "?".$sPmdPostString;
					
		fputs($rSocketConnection, "GET $sPmdScriptPath HTTP/1.1\r\n");					
		fputs($rSocketConnection, "Host: $sPmdHostPart\r\n");
		fputs($rSocketConnection, "User-Agent: MSIE\r\n");
		fputs($rSocketConnection, "Connection: close\r\n\r\n");
	}
	*/
	
	
		}
		exit();
	} else {
		// if error found, lin 55-169 will be skipped and below line will display error
		$sMessage = "<font face=verdana color=#FF0000><b>We could not process your request due to missing fields or invalid entries. 
					 Please review the form, and make these changes:</b>
					<BR><BR><table align=center width=600><tr><td><ol>$sMessage</ol></td></tr></table></font>";
	}
	
	
}

// select list of hot topics from database and assign it to HotTopicsQuery
$sHotTopicsQuery = "SELECT *
					FROM   pmdCategories
					WHERE  hotTopics = '1'
					ORDER BY hotTopicsSortOrder";
$rHotTopicsResult = dbQuery($sHotTopicsQuery);
$i=0;
$sHotTopicsList = '';	// set null to list

// add <tr> & </tr> to table
// repeat this until all category is added to <td>
// create checkbox and list category
while ($oHotTopicsRow = dbFetchObject($rHotTopicsResult)) {
	if ($i%3 == 0) {
		if ($i !=0) {
			$sHotTopicsList .= "</tr>";
		}
		$sHotTopicsList .= "<tr>";
	}
	$sChecked = '';
	for($j=0;$j<count($aPmdCategories);$j++) {
		if ($aPmdCategories[$j] == $oHotTopicsRow->id) {
			$sChecked = "checked";
		}
	}
	// add <td> list all category </td>
	$sHotTopicsList .= "<td nowrap><font face='Arial, Helvetica, sans-serif' size=2><b><input type=checkbox name=aPmdCategories[] value='".$oHotTopicsRow->id."' $sChecked> $oHotTopicsRow->title</b></font></td>";
	$i++;
}

$sHotTopicsList .= "</tr>";


// get list of other category
$sOthersQuery = "SELECT *
					FROM   pmdCategories
					WHERE  others = '1'
					ORDER BY othersSortOrder";  
$rOthersResult = dbQuery($sOthersQuery);

$sOthersList = '';
$k=0;

// add <tr> & </tr> to table
// repeat this until all category is added to <td>
// create checkbox and list category
while ($oOthersRow = dbFetchObject($rOthersResult)) {
	if ($k%3 == 0) {
		if ($k !=0) {
			$sOthersList .= "</tr>";
		}
		$sOthersList .= "<tr>";
	}
	
	$sChecked = '';
	for($j=0;$j<count($aPmdCategories);$j++) {
		if ($aPmdCategories[$j] == $oOthersRow->id) {
			$sChecked = "checked";
		}
	}
		// add <td> list all category </td>
	$sOthersList .= "<td nowrap><font face='Arial, Helvetica, sans-serif' size=2><input type=checkbox name=aPmdCategories[] value='".$oOthersRow->id."' $sChecked> $oOthersRow->title</font></td>";
	
	$k++;
}

// concat - close </tr>
$sOthersList .= "</tr>";


// prepare state options
// get list of states and assign
// below will create a dropdown menue for states
$sStateQuery = "SELECT *
				FROM   states
				ORDER BY state";
$rStateResult = dbQuery($sStateQuery);
$sStateOptions = "<option value=''>";
while ($oStateRow = dbFetchObject($rStateResult)) {
	if ($oStateRow->stateId == $sState) {
		$sSelected = "selected";
	} else {
		$sSelected = "";
	}
	$sStateOptions .= "<option value='$oStateRow->stateId' $sSelected>$oStateRow->state";
}

// set to null
$sMaleSelected = '';
$sFemaleSelected = '';
$sNoGenderSelected = '';

// if user select Male, then mark var = selected
if ($sSex == 'M') {
	$sMaleSelected = "selected";
} else if ($sSex == 'F') {
	$sFemaleSelected = "selected";
} else {
	$sNoGenderSelected = "selected";
}

// init to null
$sCanReceiveHtmlYesSelected = '';
$sCanReceiveHtmlNoSelected = '';

// if user select yes, then mark var = selected
if ($icanReceiveHtml) {
	$sCanReceiveHtmlYesSelected = 'selected';
} else {
	$sCanReceiveHtmlNoSelected = 'selected';
}

?>


<html>
<head>
<title>Sign Up For Tons Of Free Offers All At Once</title>

</head>
<body bgcolor="#FFFFFF" text="#000000"link="#0000FF" vlink="#330033" alink="#006600">
<table width=600 align=center border=0><tr><td><font face=Arial, Helvetica,sans-serif><font size=2 color=#000066><b><center>Here Is Another Page Of Great Free Stuff We Know You'll Enjoy:</center></b></font></td></tr></table>

<form action='<?php echo $PHP_SELF;?>' method=post>
<input type=hidden name=redirect value="<?php echo $sGblSiteRoot;?>/taf/index.php">
<table width="458" border="0" cellspacing="0" cellpadding="0" align="center">

  <tr>
      
    <td bgcolor="#FFFFFF" valign="top" align=center> 
      <div align="center"> <img src="images/customized.gif" width="425" height="80" align="middle"> 

             <br>

	<font size="2"><font face="Arial, Helvetica, sans-serif">
	You Tell Us What You Like... We Search the Internet For You...
	<br>
        You Get Specialized e-Mails ONLY On the Things YOU'RE Interested In!</font>
<br><br>

        <font size="5"><b><font face="Arial, Helvetica, sans-serif"><font size="3"> 
        </font></font></b><font face="Arial, Helvetica, sans-serif"><font size="2">Take 
        advantage of this <b>100% FREE</b> service from PostMasterDirect (<a href='http://www.postmasterdirect.com/pp.htm'>Privacy Policy<a/>) to stay up-to-date on the latest 
        information and hard-to-find offers in your special areas of interest.</font></font></font></font> 
        <hr>
</td></tr></table>
        
<table width=550 align=center>
<tr><td colspan=3><?php echo $sMessage;?></td></tr>
<tr><td colspan=3 align=center>
        <font face="Arial, Helvetica, sans-serif" size="2"><b><img src="images/1btn.gif" width="32" height="32" align="top">Select 
        Your Interests Below (<i>The Average User Selects 10 or More!</i>)</b></font> 
</td></tr>


<tr><td colspan=3 >
<font face="Arial, Helvetica, sans-serif"  color="#FF0000"><b>HOT 
            TOPICS - READER'S FAVORITES</b></font> 
</td></tr>

<?php echo $sHotTopicsList;?>

            <tr> 
              
                <td><hr> <BR></td><td>
                <hr>
                <font face="arial, helvetica, helv, sans-serif" color=#FF0000 size=3>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;OTHERS</b></font>
                </td><td><hr> <BR></td>
                                
            </tr>   
            <?php echo $sOthersList;?>         
          </table>
          <table width=558 align=center >
          <tr><td align=center><hr>
           <font face="Arial, Helvetica, sans-serif">       
            <p><img src="images/2btn.gif" width="32" height="32" align="middle"> <b>Complete 
              The Information Below</b></p>
          
          </font>
		</td></tr>
		</table>

          <table cellspacing=0 cellpadding=0 width=550 align=center border=0>
            <tbody> 
            <tr bgcolor="#FFFFCC"> 
              <td valign=top align=right width="206" height=25><font 
      face="Verdana, Arial, Helvetica"> 
                <p align=right><font face="Arial, Helvetica, sans-serif" size="2"><b>Email 
                  Address</b>:&nbsp;&nbsp;&nbsp;</font></p>
                </font></td>
              <center>
                <td colspan=2 height=25> 
                  <div align="left"><font face="Arial, Helvetica, sans-serif" size="2"> 
                    <input name=sEmail value='<?php echo $sEmail;?>' size="35">
                    &nbsp;&nbsp;<font color="#FF0000">Required</font></font></div>
                </td>
              </center>
            </tr>
            <tr> 
              <td valign=top align=right width="206" height=25><font 
      face="Verdana, Arial, Helvetica"> 
                <p align=right><font face="Arial, Helvetica, sans-serif" size="2">Gender:&nbsp;&nbsp;&nbsp;</font> 
                </p>
                </font></td>
              <center>
                <td colspan=2 height=25> 
                  <div align="left"><font face="Arial, Helvetica, sans-serif" size="2"> 
                    <select size=1 name=sSex>
                      <option value="" <?php echo $sNoGenderSelected;?>> 
                      <option value='M' <?php echo $sMaleSelected;?>>Male 
                      <option value='F' <?php echo $sFemaleSelected;?>>Female</option>
                    </select>
                    </font> </div>
                </td>
              </center>
            </tr>
            <tr bgcolor="#FFFFCC"> 
              <td valign=top align=right width="206" height=25><font 
      face="Verdana, Arial, Helvetica"> 
                <p align=right><font face="Arial, Helvetica, sans-serif" size="2"><b>Year 
                  of birth</b>: <font size="1">IE - 1994</font>&nbsp;&nbsp;&nbsp;</font> 
                </p>
                </font></td>
              <center>
                <td colspan=2 height=25> 
                  <div align="left"><font face="Arial, Helvetica, sans-serif" size="2"> 
                    <input size=4 maxlength="4" name=iBirthYear value='<?php echo $iBirthYear;?>'>
                    <font color="#FF0000">Required</font></font></div>
                </td>
              </center>
            </tr>
            <tr>
              <td valign=top align=right width="206" height=25><font 
      face="Verdana, Arial, Helvetica"> 
                <p align=right><font face="Arial, Helvetica, sans-serif" size="2">State: 
                  &nbsp;&nbsp;</font></p>
                </font></td>
              <center>
                <td colspan=2 height=25> 
                  <div align="left"><font face="Arial, Helvetica, sans-serif" size="2"> 
                    <select name=sState>
                    <?php echo $sStateOptions;?>
                    </select>
                    </font></div>
                </td>
              </center>
            </tr>
            <tr> 
              <td valign=top align=right width="206" height=25><font 
      face="Verdana, Arial, Helvetica"> 
                <p align=right><font face="Arial, Helvetica, sans-serif" size="2">Zip/Postal 
                  Code:&nbsp;&nbsp;</font> </p>
                </font></td>
              <center>
                <td colspan=2 height=25> 
                  <div align="left"><font face="Arial, Helvetica, sans-serif" size="2"> 
                    <input size=10 maxlength="10" name=sZip value='<?php echo $sZip;?>'>
                    </font></div>
                </td>
              </center>
            </tr>
            <tr> 
              <td align=right width="206" height=38><font 
      face="Verdana, Arial, Helvetica"> 
                <p align=right><font face="Arial, Helvetica, sans-serif" size="2">Can 
                  you receive HTML email?&nbsp;&nbsp; </font></p>
                </font></td>
              
                <td colspan=2 height=38> 
                  <div align="left"><font face="Arial, Helvetica, sans-serif" size="2"> 
                    <input type=radio value='1' name=icanReceiveHtml <?php echo $sCanReceiveHtmlYesSelected;?>> Yes 
                    <input type=radio value='' name=icanReceiveHtml <?php echo $sCanReceiveHtmlNoSelected;?>> No </font></div>
                </td>
              </center>
            </tr>
            <tr> 
              <td valign=top align=right width="206" height=38 nowrap><font 
      face="Verdana, Arial, Helvetica"> 
                <p align=right><font face="Arial, Helvetica, sans-serif" size="2">Which 
                  most closely matches&nbsp;&nbsp;<br>
                  your&nbsp;official title?&nbsp;&nbsp; </font></p>
                </font></td>
              
                <td colspan=2 height=38> 
                  <div align="left"><font face="Arial, Helvetica, sans-serif" size="2"> 
                    <select name=sJobTitle>                     
<option value="account executive"> Account Executive
<option value="accountant"> Accountant
<option value="administrator"> Administrative/Clerical
<option value="analyst"> Analyst
<option value="architect"> Architect
<option value="associate"> Associate
<option value="attorney"> Attorney
<option value="business development director"> Business Development Director
<option value="buyer"> Buyer
<option value="ceo/president"> CEO/President
<option value="chairman"> Chairman
<option value="chemist/scientist"> Chemist/Scientist
<option value="chief financial officer"> Chief Financial Officer
<option value="chief information officer"> Chief Information Officer
<option value="coo"> Chief Operations Officer
<option value="cto"> Chief Technology Officer
<option value="chiropractor"> Chiropractor
<option value="consultant"> Consultant
<option value="controller"> Controller
<option value="counselor"> Counselor
<option value="dba"> Dba
<option value="dentist/dental hygenist"> Dentist/Dental Hygenist
<option value="designer"> Designer
<option value="director"> Director
<option value="editor/writer"> Editor/Writer
<option value="educator"> Educator
<option value="engineer"> Engineer
<option value="executive officer"> Executive Officer
<option value="financial advisor"> Financial Advisor
<option value="graphic designer"> Graphic Designer
<option value="human resources director"> Human Resources Director
<option value="it consultant"> IT Consultant
<option value="it manager"> IT Manager
<option value="mis director"> MIS Director
<option value="manager"> Manager
<option value="marketing manager"> Marketing Manager/Director
<option value="military/govt"> Military/Gov't
<option value="nurse"> Nurse
<option value="occupational/physical therapist"> Occupational/Physical Therapist
<option value="operator"> Operator
<option value="opticians/optometrist"> Opticians/Optometrist
<option value="other"> Other
<option value="owner/proprietor/principal"> Owner/Proprietor/Principal
<option value="partner"> Partner
<option value="pharmacist"> Pharmacist
<option value="physician/doctor"> Physician/Doctor
<option value="planner/scheduler"> Planner/Scheduler
<option value="producer"> Producer
<option value="programmer"> Programmer
<option value="project manager"> Project Manager
<option value="psychologist"> Psychologist
<option value="representative/sales"> Representative/Sales
<option value="retired"> Retired
<option value="secretary/treasurer"> Secretary/Treasurer
<option value="senior management"> Senior Management
<option value="social worker"> Social Worker
<option value="software developer"> Software Developer
<option value="speech pathologist/audiologist"> Speech Pathologist/Audiologist
<option value="staff"> Staff
<option value="student"> Student
<option value="supervisor"> Supervisor
<option value="systems administrator"> Systems Administrator
<option value="technoligist/technicans"> Technoligist/Technicans
<option value="unemployed"> Unemployed
<option value="vp/marketing"> VP/Marketing
<option value="vp/sales"> VP/Sales
<option value="veterinarian"> Veterinarian
<option value="vice president"> Vice President
<option value="web developer"> Web Developer


                    </font></div>
                </td>
              </center>
            </tr>
            <tr> 
              <td valign=top align=middle colspan=2 
      height=43><font face="Verdana, Arial, Helvetica"> 
                <p align=center><font face="Arial, Helvetica, sans-serif" size="2">What 
                  is your primary job function <br>
                  within your organization? </font></p>
                </font></td>
              <td width=344 
      height=43><font face="Verdana, Arial, Helvetica"><font face="Arial, Helvetica, sans-serif" size="2"> 
                <select name=sJobFunction>                 
<option value="advertising"> Advertising
<option value="creative"> Creative
<option value="customer"> Customer Service
<option value="datacomm"> Datacomm/Telecomm
<option value="education"> Education
<option value="engineering"> Engineering
<option value="health"> Env./Health/Safety
<option value="executive/corporate"> Executive(CEO,Pres,SVP,etc.)
<option value="financial/accounting"> Financial/Accounting
<option value="management"> General Management
<option value="health professionals"> Health Professionals
<option value="hr"> Human Resources
<option value="is/systems/technology"> IS/Systems/Technology
<option value="internationalops"> International Operations
<option value="legal"> Legal
<option value="logistics"> Logistics/Distribution
<option value="maintenance"> Maintenance/Facilities
<option value="networking"> Networking/LAN
<option value="operations"> Operations
<option value="other"> Other
<option value="production/manufacturing"> Production/Manufacturing
<option value="purchasing"> Purchasing/Supply Chain
<option value="qa"> Quality Assurance
<option value="research"> Research
<option value="sales/marketing"> Sales/Marketing
<option value="var"> Var


                </select>
                </font></font></td>
            </tr>
                                 
            <tr> 
              <td valign=center align=middle colspan=3 
      height=42><font face="Verdana, Arial, Helvetica"> 
                <p align=center><img src="images/3btn.gif" width="32" height="32" align="middle"> 
                  <input type=submit value="Sign up for FREE!" name=sSubmit>
                </p>
                </font></td>
            </tr>
             <tr><td colspan=3><font face="Verdana, Arial, Helvetica" size=1>
             After you click the submit button, a confirmation email will be sent to the address you provided.  You must click the confirmation link within the email to activate your subscription.
            </font></td></tr>        
          </table>
          <br>
        </form>
        
      </div>
      </td>
    </tr>
    

            </tbody> 
  </table>
  <p>&nbsp;</p>

 

</body>
</html>
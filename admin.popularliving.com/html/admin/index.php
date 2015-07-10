<?php
/*
$Author: spatel $
$Id: index.php,v 1.6 2006/04/26 15:22:57 spatel Exp $
*/
include("../includes/paths.php");
   
//echo $sGblAdminSiteRoot;
$sCurrSite = $_SERVER['SERVER_ADDR'];
$sCurrSiteAddr = $_SERVER['SERVER_NAME'];
$sCurrSiteAddr = "https://".$sCurrSiteAddr;

	
session_start();



// Test code: applies to matts only
$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
//if( $sTrackingUser == 'matts' ) {
	/*
	// Code to execute when user log in
	if( !preg_match( "#^http://(web1|test)\.popularliving\.com/admin#", $_SERVER[HTTP_REFERER])) {
		// $iMaxLogins = 50;
		$iMaxLogins = 200;
		
		// Increment login count
		$sUpdateLoginCountSql = "UPDATE nbUsers
							SET currentPasswdLogins = currentPasswdLogins + 1
							WHERE userName = '" . $sTrackingUser . "'";
		
		dbQuery( $sUpdateLoginCountSql );

		// get current logins. see if it's over $iMaxLogins
		$sGetLoginCountSql = "SELECT currentPasswdLogins 
							FROM nbUsers 
							WHERE userName = '" . $sTrackingUser . "'";
		
		//echo $sGetLoginCountSql;
		$rGetLoginCount = dbQuery($sGetLoginCountSql);
		$oLoginCountRow = dbFetchObject($rGetLoginCount);
		$iLoginCount = $oLoginCountRow->currentPasswdLogins;

		// if over $iMaxLogins logins, take action
		if( $iLoginCount >= $iMaxLogins ) {
			// find out if this user has a new password
			$sNewPasswdSql= "SELECT newPasswd
							FROM nbUsers
							WHERE userName = '" . $sTrackingUser . "'
							AND newPasswd is not null";

			$rNewPasswdResult = dbQuery( $sNewPasswdSql );
			
			// if user has a newpasswd, display it
			if( $oNewPasswdRow = dbFetchObject( $rNewPasswdResult )) {
				$sPasswdMessage =  "As of midnight tonight, your password will be changed to: <br><br>" . $oNewPasswdRow->newPasswd . "<br><br>  Please make a note of your new password.  Your old password will work for the rest of today.  Do not give your password to anyone!<br><br>";
			}
			
			// if new passed doesn't exist yet, generate
			else {
				// new password should be 2 uppercase, 2 lowercase, 2 digits and 2 punctuation
				$upper = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
				$lower = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
				$digits = array('1','2','3','4','5','6','7','8','9');
				$punctuation = array('!','@','#','$','+','-','&','*','?');

				$randUpper = array_rand($upper,2);
				$randLower = array_rand($lower,2);				
				$randDigits = array_rand($digits,2);				
				$randPunctuation = array_rand($punctuation,2);
				
				$password[0]=$upper[$randUpper[0]];
				$password[1]=$upper[$randUpper[1]];
				$password[2]=$lower[$randLower[0]];
				$password[3]=$lower[$randLower[1]];
				$password[4]=$digits[$randDigits[0]];
				$password[5]=$digits[$randDigits[1]];
				$password[6]=$punctuation[$randPunctuation[0]];
				$password[7]=$punctuation[$randPunctuation[1]];

				$randPasswordKeys = array_rand($password, 8);
				
				$newPasswd = '';
				foreach( $randPasswordKeys as $key ) {
					$newPasswd .= $password[$key];
				}

// de-randomize password				
//				$newPasswd = implode('', $password);

				$sUpdatePasswdSql = "UPDATE nbUsers
							SET newPasswd = '" . $newPasswd . "'
							WHERE userName = '" . $sTrackingUser . "'";
		
				//dbQuery( $sUpdatePasswdSql );
				//$sPasswdMessage = "As of midnight tonight, your password will be changed to: <br><br>" . $newPasswd . "<br><br>  Please make a note of your new password.  Your old password will work for the rest of today.  Do not give your password to anyone!<br><br>";
			}
		}
	}*/
//}


//echo '<div align="center"><font color="red"><b>' . $sPasswdMessage . '</b></font></div>';

$sPageTitle = "Nibbles Main Menu";

	$sMenuQuery = "SELECT *
				  FROM menu
				  WHERE parentMenu = 0
				  AND   displayMenu = 'Y'
				  AND category != 'Nibbles II'
				  ORDER BY	category, menuItem";
	$rMenuResult = dbQuery($sMenuQuery);
	
	$iNum = 0;
	while ($oMenuRow = dbFetchObject($rMenuResult)) {
		if ($oMenuRow->category != $sOldCategory || $sOldCategory == '') {
			if ($iNum%2 != 0)
			$sMenuList .= "<td bgcolor = \"eeeeee\">&nbsp;</tD>";
			$sMenuList .= "</tr><tr>
				<td colspan=\"2\" align=\"center\" bgcolor = \"c1c1c1\"><b>$oMenuRow->category</b></td>
				</tr><tr>";
			$iNum = 0;
		}
		
		// interpret $SERVER_NAME variable, if it's there in menuLink
		if (strstr($oMenuRow->menuLink,"\$SERVER_NAME"))
		{
			$sMenuLink = ereg_replace("\\\$SERVER_NAME",$SERVER_NAME,$oMenuRow->menuLink);			
		} else {
			$sMenuLink = $oMenuRow->menuLink;
		}
			
		$sMenuList .= "<td valign=\"top\" bgcolor = \"eeeeee\" width=\"50%\">
					<ul>
					<li><a href=\"". $sMenuLink."?iMenuId=$oMenuRow->id\"><b>$oMenuRow->menuItem</b></a> &nbsp;";
				if ($oMenuRow->description != '') {
						$sMenuList .= "<A href='JavaScript:void(window.open(\"menuDesc.php?iMenuId=$oMenuRow->id\", \"\", \"height=200, width=300, scrollbars=auto, resizable=yes, status=no\"));' class=header>?</a>";
				}
				$sMenuList .= "</li>
					</ul></td>";
		
		$iNum++;
		if ($iNum%2 == 0) {
			$sMenuList .= "</tr>";
		}
		
		$sOldCategory = $oMenuRow->category;
		
	}
	
	
	
	// In last row, Fill the remaining empty TD with grey color
	if ( $iNum%2 != 0) {
		$sMenuList .= "<td bgcolor = \"eeeeee\">&nbsp;</tD>";
	}
	$sMenuList .= "</tr>";
	
	
	
	// START OF NIBBLES II SECTION
	
	// insert horizontal line between Nibbles I and Nibbles II
	$sMenuList .= "<tr><td colspan=2>&nbsp;</td></tr>";
	$sMenuList .= "<tr><td colspan=2><hr size=2></td></tr>";
	$sMenuList .= "<tr><td colspan=2>&nbsp;</td></tr>";

	$sMenuQuery = "SELECT *
				  FROM menu
				  WHERE parentMenu = 0
				  AND   displayMenu = 'Y'
				  AND category = 'Nibbles II'
				  ORDER BY	category, menuItem";
	$rMenuResult = dbQuery($sMenuQuery);
	$iNum = 0;
	while ($oMenuRow = dbFetchObject($rMenuResult)) {
		if ($oMenuRow->category != $sOldCategory || $sOldCategory == '') {
			if ($iNum%2 != 0)
			$sMenuList .= "<td bgcolor = \"eeeeee\">&nbsp;</tD>";
			$sMenuList .= "</tr><tr>
				<td colspan=\"2\" align=\"center\" bgcolor = \"c1c1c1\"><b>$oMenuRow->category</b></td>
				</tr><tr>";
			$iNum = 0;
		}
		
		// interpret $SERVER_NAME variable, if it's there in menuLink
		if (strstr($oMenuRow->menuLink,"\$SERVER_NAME"))
		{
			$sMenuLink = ereg_replace("\\\$SERVER_NAME",$SERVER_NAME,$oMenuRow->menuLink);			
		} else {
			$sMenuLink = $oMenuRow->menuLink;
		}
			
		$sMenuList .= "<td valign=\"top\" bgcolor = \"eeeeee\" width=\"50%\">
					<ul>
					<li><a href=\"". $sMenuLink."?iMenuId=$oMenuRow->id\"><b>$oMenuRow->menuItem</b></a> &nbsp;";
				if ($oMenuRow->description != '') {
						$sMenuList .= "<A href='JavaScript:void(window.open(\"menuDesc.php?iMenuId=$oMenuRow->id\", \"\", \"height=150, width=250, scrollbars=auto, resizable=yes, status=no\"));' class=header>?</a>";
				}
				$sMenuList .= "</li>
					</ul></td>";
		
		$iNum++;
		if ($iNum%2 == 0) {
			$sMenuList .= "</tr>";
		}
		
		$sOldCategory = $oMenuRow->category;
		
	}
	
	// In last row, Fill the remaining empty TD with grey color
	if ( $iNum%2 != 0) {
		$sMenuList .= "<td bgcolor = \"eeeeee\">&nbsp;</tD>";
	}
	$sMenuList .= "</tr>";
	
	/// END OF NIBBLES 2 SECTION

	include("../includes/adminHeader.php");	
?>

<!-- content starts here -->

<table align="center" width="600">
<?php echo $sMenuList;?>
</table>

<!-- content ends here -->

<?php 
	include("../includes/adminFooter.php");
/*} else {
	header("Location:login.php");	
}*/

?>

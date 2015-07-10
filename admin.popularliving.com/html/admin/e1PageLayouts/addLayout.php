<?php


/*********

Script to Display Add/Edit OT Page Layout

**********/

include("../../includes/paths.php");

session_start();

echo "session.auto_start: ".ini_get( "session.auto_start" )."<br>";
echo "session.bug_compat_42: ".ini_get( "session.bug_compat_42" )."<br>";
echo "session.bug_compat_warn: ".ini_get( "session.bug_compat_warn" )."<br>";
echo "session.cache_expire: ".ini_get( "session.cache_expire" )."<br>";
echo "session.cache_limiter: ".ini_get( "session.cache_limiter" )."<br>";
echo "session.cookie_domain: ".ini_get( "session.cookie_domain" )."<br>";
echo "session.cookie_lifetime: ".ini_get( "session.cookie_lifetime" )."<br>";
echo "session.cookie_path: ".ini_get( "session.cookie_path" )."<br>";
echo "session.cookie_secure: ".ini_get( "session.cookie_secure" )."<br>";
echo "session.entropy_file: ".ini_get( "session.entropy_file" )."<br>";
echo "session.entropy_length: ".ini_get( "session.entropy_length" )."<br>";
echo "session.gc_divisor: ".ini_get( "session.gc_divisor" )."<br>";
echo "session.gc_maxlifetime: ".ini_get( "session.gc_maxlifetime" )."<br>";
echo "session.gc_probability: ".ini_get( "session.gc_probability" )."<br>";
echo "session.hash_bits_per_character: ".ini_get( "session.hash_bits_per_character" )."<br>";
echo "session.hash_function: ".ini_get( "session.hash_function" )."<br>";
echo "session.name: ".ini_get( "session.name" )."<br>";
echo "session.referer_check: ".ini_get( "session.referer_check" )."<br>";
echo "session.save_handler: ".ini_get( "session.save_handler" )."<br>";
echo "session.save_path: ".ini_get( "session.save_path" )."<br>";
echo "session.serialize_handler: ".ini_get( "session.serialize_handler" )."<br>";
echo "session.use_cookies: ".ini_get( "session.use_cookies" )."<br>";
echo "session.use_only_cookies: ".ini_get( "session.use_only_cookies" )."<br>";
echo "session.use_trans_sid: ".ini_get( "session.use_trans_sid" )."<br>";

ini_set( "session.use_cookies", TRUE );
ini_set( "session.use_only_cookies", TRUE );

echo "-------------------------------------------------------------<br>";
echo "session.use_cookies: ".ini_get( "session.use_cookies" )."<br>";
echo "session.use_only_cookies: ".ini_get( "session.use_only_cookies" )."<br>";

		
$sPageTitle = "Nibbles e1 Page Layouts - Add/Edit Layout";

if (hasAccessRight($iMenuId) || isAdmin()) {

	if (($sSaveClose || $sSaveNew) && !($iId)) {
		// if new layout method added
			$sLayout = addslashes($sLayout);
			$sContent = addslashes($sContent);
		// check if already exists
		$sCheckQuery = "SELECT *
					FROM   ePageLayouts
					WHERE  layout = '$sLayout'";
		$rCheckResult = dbQuery($sCheckQuery);
		if (dbNumRows($rCheckResult)>0) {
			$sMessage = "Page layout already exists...";
			$bKeepValues = true;
		} else {

			
			$sAddQuery = "INSERT INTO ePageLayouts(layout, content)
					 VALUES('$sLayout', '$sContent')";

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
				   FROM   ePageLayouts
				   WHERE  layout = '$sLayout'
				   AND content = '$sContent'"; 
				$rCheckResult = dbQuery($sCheckQuery);
				$sRow = dbFetchObject($rCheckResult);
				
				
				
				$iId = $sRow->id;
			} else {

				$sMessage = dbError();
			}

		}

	} else if (($sSaveClose || $sSaveNew) && ($iId)) {
			$sLayout = addslashes($sLayout);
			$sContent = addslashes($sContent);
		// check if already exists
		$sCheckQuery = "SELECT *
					FROM   ePageLayouts
					WHERE  layout = '$sLayout'
					AND    id != '$iId'";
		$rCheckResult = dbQuery($sCheckQuery);
		if (dbNumRows($rCheckResult)>0) {
			$sMessage = "Page layout already exists...";
			$bKeepValues = true;
		} else {
			
			$sEditQuery = "UPDATE ePageLayouts
					   SET    layout = '$sLayout',
							  content = '$sContent'
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
		if ($bKeepValues != true) {
			if ($sReturnTo == 'list') {
				echo "<script language=JavaScript>
			window.opener.location.reload();
			self.close();
			</script>";			
			} else {



				echo "<script language=JavaScript>
			var layout_value=new String('$iId');
			var layout_text=new String('$sLayout');
			var v2 = window.opener.document.form1.$sReturnTo;
			var i = window.opener.document.form1.$sReturnTo.length;
			
			var agt=navigator.userAgent.toLowerCase();
			//if (agt.indexOf(\"msie\") != -1) {
				//var ieLayoutOpt=new Option(layout_text, layout_value);
		
				//window.opener.document.form1.$sReturnTo.options.add(layout_text,layout_value);
			//} else {
			//	var layoutOpt=new Option(layout_text, layout_value);
				eval(\"window.opener.document.form1.$sReturnTo.options[i]=layoutOpt\");
			//}
			

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
			var layout_value=new String('$sLayout');
			var layout_text=new String('$iId');
			var v2 = window.opener.document.form1.$sReturnTo;
			var i = window.opener.document.form1.$sReturnTo.length;
			
			var agt=navigator.userAgent.toLowerCase();
			//if (agt.indexOf(\"msie\") != -1) {
				//var ieLayoutOpt=new Option(layout_text, layout_value);
			//	window.opener.document.form1.$sReturnTo.options.add(layout_text,layout_value);
			//} else {
			//	var layoutOpt=new Option(layout_value, layout_text);
			//	eval(\"window.opener.document.form1.$sReturnTo.options[i]=layoutOpt\");
			//}
			
			self.close();
			</script>";


			}
			$iId = '';
			$sLayout = '';
			$sContent = '';
		}
	}

		
	if ($iId) {

		// If Clicked to edit, get the data to display in fields

		$sSelectQuery = "SELECT * FROM ePageLayouts
				    WHERE  id = '$iId'";
		$rSelectResult = dbQuery($sSelectQuery);
		while ($oSelectRow = dbFetchObject($rSelectResult)) {
			$sLayout = $oSelectRow->layout;
			$sContent = $oSelectRow->content;
		}


	} else {

		// If add button is clicked, display another two buttons
		$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=sAbandonNew value=' Abandon & New  '>";	
	}

	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>
			<input type=hidden name=sReturnTo value='$sReturnTo'>";

	include("../../includes/adminAddHeader.php");
	
	//echo htmlspecialchars($sContent)."<br><br>".htmlspecialchars($sHidden);
?>

<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $sHidden;?>
<?php echo $sReloadWindowOpener;?>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
		<tr><TD colspan=2></td></tr>
		<tr><TD colspan=2 class=message>Any changes to the page layout requires all the pages using this layout to be regenerated.</td></tr>
		<tr><TD>Page Layout</td><td><input type=text name=sLayout value="<?php echo $sLayout;?>" size=70></td></tr>
		<tr><TD valign=top>Layout Content</td><td><textarea name=sContent rows=30 cols=70><?php echo $sContent; ?></textarea></td></tr>
	</table>	
	
<?php

	echo htmlspecialchars($sContent)."<br><br>".htmlspecialchars($sHidden);
include("../../includes/adminAddFooter.php");

} else {
	echo "You are not authorized to access this page...";
}
?>
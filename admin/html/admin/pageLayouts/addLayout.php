<?php

/*********

Script to Display Add/Edit OT Page Layout

**********/

include("../../includes/paths.php");

session_start();

$sPageTitle = "Nibbles OT Page Layouts - Add/Edit Layout";

if (hasAccessRight($iMenuId) || isAdmin()) {
	
if (($sSaveClose || $sSaveNew) && !($iId)) {
	// if new layout method added
	$sLayout = addslashes($sLayout);
	$sContent = addslashes($sContent);
	// check if already exists
	$sCheckQuery = "SELECT *
					FROM   pageLayouts
					WHERE  layout = '$sLayout'";
	$rCheckResult = dbQuery($sCheckQuery);
	if (dbNumRows($rCheckResult)>0) {
		$sMessage = "Page layout already exists...";
		$bKeepValues = true;
	} else {
		
		$sAddQuery = "INSERT INTO pageLayouts(layout, content)
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
	  				 FROM   pageLayouts
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
					FROM   pageLayouts
					WHERE  layout = '$sLayout'
					AND    id != '$iId'";
	$rCheckResult = dbQuery($sCheckQuery);
	if (dbNumRows($rCheckResult)>0) {
		$sMessage = "Page layout already exists...";
		$bKeepValues = true;
	} else {
		$sEditQuery = "UPDATE pageLayouts
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
	
	$sSelectQuery = "SELECT * FROM pageLayouts
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
?>

<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $sHidden;?>
<?php echo $sReloadWindowOpener;?>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
		<tr><TD colspan=2>[PAGE_TITLE] will be replaced with title of the page.
						  <BR>[STYLE_SHEET] will be replaced with the whole style sheet tag.
						  <BR>[PAGE_BG_COLOR] will be replaced with the page background color.
						  <BR>[HEADER_GRAPHIC_FILE] will be replaced with the page header graphic file url
						  <BR>[PAGE1_HEADER] will be replaced with the php script to place the header file according to the header file name passed as variable "header"
						  {variable_passed}.php file will be placed as header file. Default header will be used if header file does not exist.						  
						  <BR>[STEP0_GRAPHIC_FILE] will be replaced with the step0 graphic file url.
						  <BR>[STEP1_GRAPHIC_FILE] will be replaced with the step1 graphic file url.
						  <BR>[STEP2_GRAPHIC_FILE] will be replaced with the step2 graphic file url.
						  <BR>[STEP3_GRAPHIC_FILE] will be replaced with the step3 graphic file url.
						  <BR>[ARROW_GRAPHIC_FILE] will be replaced with the arrow graphic file url.
						  <BR>[ACTION] in the form tag will be replaced by the script name to which the page should be directed to on submitting the form.
						  <BR>[TOP_OFFERS_LIST] will be replaced with the list of offers which should be displayed at the top of the page.
						  <BR>[OFFERS_LIST] will be replaced with the list of offers in the page.
						  <BR>[USER_FORM] will be replaced with form to enter user details alongwith submit button
						  <BR>[JOIN_LIST_CONTENT] will be replaced with the content of join list alongwith yes/no radio button.
						  <BR>[SUBMIT_TEXT] will be replaced with the text to appear on submit button.
						  <BR>[START_FONT_COLOR] / [END_FONT_COLOR] will be replaced with the font opening/closing tags to apply font color of the page.						  
						  <BR>[POWERED_BY] will be replaced with the powered by image.
						  <BR>[MAIN_ERROR_MESSAGE] will be replaced with the main error message of the page.
						  <BR>[MESSAGE] will be replaced with detailed error messages to the user.
						  <BR>[NO_THANK_YOU_CODE] will be replaced with the no thank you code specified for the page.
						  <BR>[PAGE1_EXTRA_TEXT] will be replaced with any extra text specified for the page. 
						  <BR>All [customQstring{inboundVarName}] tags will be replaced with the value of the corresponding inbound variable.
						  <BR>[OUTBOUND_QUERY_STRING] will be replaced with outbound query string.
						  <BR>[PREPOP_CODES] will be replaced with the prepopulation codes.
						  <BR>[ASP_FILE]{asp file url}[/ASP_FILE] will place asp file if PMID variable is passed in query string.						  
		<tr><TD colspan=2 class=message>Any changes to the page layout requires all the pages using this layout to be regenerated.</td></tr>
		<tr><TD>Page Layout</td><td><input type=text name=sLayout value='<?php echo $sLayout;?>' size=70></td></tr>
		<tr><TD valign=top>Layout Content</td><td><textarea name=sContent rows=30 cols=70><?php echo htmlentities($sContent);?></textarea></td></tr>
	</table>	
	
<?php
	include("../../includes/adminAddFooter.php");
	
} else {
	echo "You are not authorized to access this page...";
}
?>
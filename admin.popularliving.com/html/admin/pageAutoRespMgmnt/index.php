<?php

/*********

Script to Display List/Delete OT Page responders
**********/

include("../../includes/paths.php");

session_start();

$sPageTitle = "Nibbles Page Auto Responders - List/Delete Page Responders";

// Check user permission to access this page
//if (session_is_registered("sSesUserId") && $aSesAccessRights[$iMenuId] == 'Y') {
	
if (hasAccessRight($iMenuId) || isAdmin()) {
	
	if ($sDelete) {
		// if user record deleted
		
		$sDeleteQuery = "DELETE FROM pageAutoResponders
	 			   		WHERE  id = '$iId'"; 

		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Delete: $sDeleteQuery\")"; 
		$rLogResult = dbQuery($sLogAddQuery); 
		echo  dbError(); 
		// end of track users' activity in nibbles		
		
		
		$rResult = dbQuery($sDeleteQuery);
		if (!($rResult)) {
			$sMessage = dbError();
		}
		// reset $id
		$iId = '';
	}
		
	// Specify Page no. settings
	if (!($iRecPerPage)) {
		$iRecPerPage = 20;
	}
	if (!($iPage)) {
		$iPage = 1;
	}
	$sSortLink = $PHP_SELF."?iMenuId=$iMenuId&iRecPerPage=$iRecPerPage";
	
	// Set Default order column
	if (!($sOrderColumn)) {
		$sOrderColumn = "emailSub";
		$sEmailSubOrder = "ASC";
	}
	
	// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
	switch ($sOrderColumn) {
		case "pageName" :
		$sCurrOrder = $sPageName;
		$sPageNameOrder = ($sPageNameOrder != "DESC" ? "DESC" : "ASC");
		break;		
		case "emailFormat" :
		$sCurrOrder = $sEmailFormat;
		$sEmailFormatOrder = ($sEmailFormatOrder != "DESC" ? "DESC" : "ASC");
		break;	
		default:
		$sCurrOrder = $sEmailSubOrder;
		$sEmailSubOrder = ($sEmailSubOrder != "DESC" ? "DESC" : "ASC");
	}
	
	
	// Select Query to display list of Users
	
	$sSelectQuery = "SELECT pageAutoResponders.*, otPages.pageName
					 FROM pageAutoResponders, otPages
					 WHERE pageAutoResponders.pageId = otPages.id ";
	$sSelectQuery .= " ORDER BY $sOrderColumn $sCurrOrder ";
	
	$rSelectResult = dbQuery($sSelectQuery);
	echo dbError();
	$iNumRecords = dbNumRows($rSelectResult);
	
	$iTotalPages = ceil($iNumRecords/$iRecPerPage);
	
	// If current page no. is greater than total pages move to the last available page no.
	if ($iPage > $iTotalPages) {
		$iPage = $iTotalPages;
	}
	
	$iStartRec = ($iPage-1) * $iRecPerPage;
	$iEndRec = $iStartRec + $iRecPerPage -1;
	
	if ($iNumRecords > 0) {
		$sCurrPage = " Page $iPage "."/ $iTotalPages";
	}
	
	// use query to fetch only the rows of the page to be displayed
	$sSelectQuery .= " LIMIT $iStartRec, $iRecPerPage";
	
	$rSelectResult = dbQuery($sSelectQuery);
	
	while ($oRow = dbFetchObject($rSelectResult)) {
		
		if ( dbNumRows($rSelectResult) > 0) {
			
			if ($iTotalPages > $iPage ) {
				$iNextPage = $iPage+1;
				$sNextPageLink = "<a href='".$sSortLink."&sOrderColumn=$sOrderColumn&iPage=$iNextPage&sCurrOrder=$sCurrOrder' class=header>Next</a>";
				$sLastPageLink = "<a href='".$sSortLink."&sOrderColumn=$sOrderColumn&iPage=$iTotalPages&sCurrOrder=$sCurrOrder' class=header>Last</a>";
			}
			if ($iPage != 1) {
				$iPrevPage = $iPage-1;
				$sPrevPageLink = "<a href='".$sSortLink."&sOrderColumn=$sOrderColumn&iPage=$iPrevPage&sCurrOrder=$sCurrOrder&iRecPerPage=$iRecPerPage' class=header>Previous</a>";
				$sFirstPageLink = "<a href='".$sSortLink."&sOrderColumn=$sOrderColumn&iPage=1&sCurrOrder=$sCurrOrder&iRecPerPage=$iRecPerPage' class=header>First</a>";
			}
			
		// For alternate background color
		if ($sBgcolorClass=="ODD") {
			$sBgcolorClass="EVEN";
		} else {
			$sBgcolorClass="ODD";
		}
		$sAutoRespList .= "<tr class=$sBgcolorClass><TD>$oRow->pageName</td>
								<TD>$oRow->emailFormat</td><td>$oRow->emailSub</td>		
						<TD><a href='JavaScript:void(window.open(\"addAutoResp.php?iMenuId=$iMenuId&iId=".$oRow->id."\", \"pageAuto\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>
					    &nbsp;<a href='JavaScript:confirmDelete(this,".$oRow->id.");' >Delete</a>
						</td></tr>";
		} else {
			$sMessage = "No Records Exist...";
		}
	}
	
	if (dbNumRows($rSelectResult) == 0) {
		$sMessage = "No Records Exist...";
	}
	
	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>";

	$sAddButton ="<input type=button name=sAdd value=Add onClick='JavaScript:void(window.open(\"addAutoResp.php?iMenuId=$iMenuId\", \"pageAuto\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>";
		
	include("../../includes/adminHeader.php");	
	
	?>
	
<script language=JavaScript>
	function confirmDelete(form1,id)
	{
		if(confirm('Are you sure to delete this record ?'))
		{							
			document.form1.elements['sDelete'].value='Delete';
			document.form1.elements['iId'].value=id;
			document.form1.submit();								
		}
	}		

	function funcRecPerPage(form1) {
					document.form1.elements['sAdd'].value='';
					document.form1.submit();
				}		
</script>

<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $sHidden;?>
<input type=hidden name=sDelete>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td colspan=4 align=left><?php echo $sAddButton;?></td></tr>
<tr><td colspan=4 align=right class=header><input type=text name=iRecPerPage value='<?php echo $iRecPerPage;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp;Records Per Page &nbsp; &nbsp; 
&nbsp; Go To Page <input type=text name=iPage value='<?php echo $iPage;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp; &nbsp; <?php echo $sFirstPageLink;?> &nbsp; <?php echo $sPrevPageLink;?> &nbsp; <?php echo $sNextPageLink;?> &nbsp; <?php echo $sLastPageLink;?> &nbsp; <?php echo $sCurrPage;?></td></tr>

<tr><td><a href="<?php echo $sSortLink;?>&sOrderColumn=pageName&sPageNameOrder=<?php echo $sPageNameOrder;?>" class=header>Page Name</a></td>
	<td><a href="<?php echo $sSortLink;?>&sOrderColumn=emailFormat&sEmailFormatOrder=<?php echo $sEmailFormatOrder;?>" class=header>Email Format</a></td>
	<td><a href="<?php echo $sSortLink;?>&sOrderColumn=emailSub&sEmailSubOrder=<?php echo $sEmailSubOrder;?>" class=header>Email Sub</a></td>
</tr>

<?php echo $sAutoRespList;?>
<tr><td colspan=4 align=right class=header><?php echo $sFirstPageLink;?> &nbsp; <?php echo $sPrevPageLink;?> &nbsp; <?php echo $sNextPageLink;?> &nbsp; <?php echo $sLastPageLink;?> &nbsp; <?php echo $sCurrPage;?></td></tr>
<tr><td colspan=4 align=left><?php echo $sAddButton;?></td></tr>
</table>

</form>
	
<?php
	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
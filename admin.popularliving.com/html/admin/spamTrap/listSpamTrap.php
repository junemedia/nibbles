<?php

/*********

Script to Display List/Delete Spam Trap Blacklist/Whiltelist

*********/

include("../../includes/paths.php");

$sPageTitle = "SpamTrap Management";

$listTypeCaption = ucfirst($listType);

if ($listType == "blacklist") {
	$listTable = "spamTrapBlacklist";
} else if ($listType == "whitelist") {
	$listTable = "spamTrapWhitelist";
}

session_start();

// Check user permission to access this page

if (hasAccessRight($iMenuId) || isAdmin()) {
		
	if ($generateSave || $generateSaveDeclude) {
		
		$selectQuery = "SELECT *
						FROM   $listTable";
		$selectResult = mysql_query($selectQuery);
		
		if ($generateSaveDeclude) {
			while ($selectRow = mysql_fetch_object($selectResult)) {
				$spamTrapList .= "WHITELIST IP ".$selectRow->ipAddress;
				if ($selectRow->serverName !='' || $selectRow->notes != '') {
					$spamTrapList .= " # ".$selectRow->serverName." , ".$selectRow->notes;
				}
				$spamTrapList .= "\n";
			}
		} else {
			while ($selectRow = mysql_fetch_object($selectResult)) {
				$spamTrapList .= $selectRow->ipAddress."\n";
			}
		}
		
		if ($spamTrapList != '') {
			$fname = $listType.".txt";
			header("Content-type: text/tab-separated-values");
			header("Content-Disposition: attachment; filename=\"".$fname."\"");
			header("Content-length: ".(string)(strlen($spamTrapList)));
			
			echo $spamTrapList;
			exit;
		}
	}
	
	if ($delete) {
		// if record deleted...
		
		$deleteQuery = "DELETE FROM $listTable
					    WHERE  id = '$id'"; 

		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Delete: $deleteQuery\")"; 
		$rLogResult = dbQuery($sLogAddQuery); 
		echo  dbError(); 
		// end of track users' activity in nibbles		
		
		
		$result = mysql_query($deleteQuery);
		echo mysql_error();
		
		if (!($result)) {
			echo mysql_error();
		}
		//reset $id to null
		$id = '';
	}
	
	// Set Default order column
	if (!($orderColumn)) {
		$orderColumn = "ipAddress";
		$ipAddressOrder = "ASC";
	}
	
	// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
	switch ($orderColumn) {
		case "serverName":
		$currOrder = $serverNameOrder;
		$serverNameOrder = ($serverNameOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "notes":
		$currOrder = $notesOrder;
		$notesOrder = ($notesOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "dateInserted":
		$currOrder = $dateInsertedOrder;
		$dateInsertedOrder = ($dateInsertedOrder != "DESC" ? "DESC" : "ASC");
		break;
		default:
		$currOrder = $ipAddressOrder;
		$ipAddressOrder = ($ipAddressOrder != "DESC" ? "DESC" : "ASC");
	}
	
	
	// Prepare filter part of the query if filter specified...
	if ($filter != '') {
		if ($exactMatch == 'Y') {
			$filterPart = " WHERE IPAddress = '$filter' || serverName = '$filter' || notes = '$filter' ";
		} else {
			$filterPart = " WHERE IPAddress like '%$filter%' || serverName like '%$filter%' || notes like '%$filter%' ";
		}
	}
	
	if (!($recPerPage)) {
		$recPerPage = 10;
	}
	if (!($page)) {
		$page = 1;
	}
	$startRec = ($page-1) * $recPerPage;
	$endRec = $startRec + $recPerPage -1;
	
	$sortLink = $PHP_SELF."?iMenuId=$iMenuId&listType=$listType&filter=$filter";
	
	// Query to get the list of Jokes
	$selectQuery = "SELECT *
					FROM   $listTable
					$filterPart 
					ORDER BY ".$orderColumn." $currOrder";
	$result = mysql_query($selectQuery);
	$numRecords = mysql_num_rows($result);
	$totalPages = ceil($numRecords/$recPerPage);
	if ($numRecords > 0) {
		$currentPage = " Page $page "."/ $totalPages";
	}
	// use query to fetch only the rows of the page to be displayed
	$selectQuery .= " LIMIT $startRec, $recPerPage";
	$result = mysql_query($selectQuery);
	if ($result) {
		
		if ($totalPages > $page ) {
			$nextPage = $page+1;
			$nextPageLink = "<a href='".$sortLink."&orderColumn=$orderColumn&page=$nextPage&currOrder=$currOrder&recPerPage=$recPerPage' class=header>Next</a>";
			$lastPageLink = "<a href='".$sortLink."&orderColumn=$orderColumn&page=$totalPages&currOrder=$currOrder&recPerPage=$recPerPage' class=header>Last</a>";
		}
		if ($page != 1) {
			$prevPage = $page-1;
			$prevPageLink = "<a href='".$sortLink."&orderColumn=$orderColumn&page=$prevPage&currOrder=$currOrder&recPerPage=$recPerPage' class=header>Previous</a>";
			$firstPageLink = "<a href='".$sortLink."&orderColumn=$orderColumn&page=1&currOrder=$currOrder&recPerPage=$recPerPage' class=header>First</a>";
		}
		
		if (mysql_num_rows($result) > 0) {
			
			while ($row = mysql_fetch_object($result)) {
				
				if ($bgcolorClass == "ODD") {
					$bgcolorClass = "EVEN";
				} else {
					$bgcolorClass = "ODD";
				}
				if (strlen($row->notes) > 50) {
					$displayNotes = "<a href='JavaScript:void(window.open(\"displayNotes.php?iMenuId=$iMenuId&id=".$row->id."&listType=$listType\", \"displayNotes\", \"height=300, width=400, scrollbars=yes, resizable=yes, status=yes\"));'>".substr($row->notes,0,50)."...</a>";
				} else {
					$displayNotes = $row->notes;
				}
				
				$spamTrapList .= "<tr class=$bgcolorClass><td>$row->ipAddress</td>
								<td>$row->serverName</td>
								<td>$displayNotes</td>
								<td>$row->dateInserted</td>
								<td><a href='JavaScript:void(window.open(\"addSpamTrap.php?iMenuId=$iMenuId&id=".$row->id."&listType=$listType\", \"AddAccount\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>
								&nbsp; <a href='JavaScript:confirmDelete(this,".$row->id.");' >Delete</a>
								</td></tr>";
			}
		} else {
			$message = "No Records Exist...";
		}
		mysql_free_result($result);
		
	} else {
		echo mysql_error();
	}
	
	
		$addButton = "<input type=button name=add value=Add onClick='JavaScript:void(window.open(\"addSpamTrap.php?iMenuId=$iMenuId&listType=$listType\", \"\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>";
		$genSaveButton = "<a href='$PHP_SELF?iMenuId=$iMenuId&listType=$listType&generateSave=generateSave'>Generate & Save ".$listTypeCaption."</a>";
		$genButton = "<a href='JavaScript:void(window.open(\"genSpamTrapList.php?iMenuId=$iMenuId&listType=$listType\", \"\", \"height=450, width=400, scrollbars=yes, resizable=yes, status=yes\"));'>Generate ".$listTypeCaption."</a>";
		
		if ($listType == "whitelist") {
			$decludeButtons = "<a href='JavaScript:void(window.open(\"genSpamTrapList.php?iMenuId=$iMenuId&listType=$listType&generateDeclude=generateDeclude\", \"\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"))'>Generate ".$listTypeCaption." / Declude Format</a>
			 &nbsp; &nbsp; <a href='$PHP_SELF?iMenuId=$iMenuId&listType=$listType&generateSaveDeclude=generateSaveDeclude'>Generate & Save ".$listTypeCaption." / Declude Format</a>";
		} else {
			$addButton .= " &nbsp; &nbsp; <input type=button name=addList value='Add List' onClick='JavaScript:void(window.open(\"addSpamTrapList.php?iMenuId=$iMenuId&listType=$listType\", \"\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>";
		}
	
	
	// Hidden variable to be passed with form submit
	$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=listType value='$listType'>
			<input type=hidden name=page value='$page'>";
	
	$sortLink = $PHP_SELF."?iMenuId=$iMenuId&listType=$listType&filter=$filter&recPerPage=$recPerPage";
	$spamTrapLink = "<a href='index.php?iMenuId=$iMenuId'>Back To SpamTrap Admin Menu</a>";
	
	
	include("../../includes/adminHeader.php");	
?>

<script language=JavaScript>
				function confirmDelete(form1,id)
				{
					if(confirm('Are you sure to delete this record ?'))
					{							
						document.form1.elements['delete'].value='Delete';
						document.form1.elements['id'].value=id;
						document.form1.submit();								
					}
				}		
				
				function funcRecPerPage(form1) {				
					document.form1.elements['add'].value='';
					document.form1.elements['addList'].value='';
					document.form1.submit();
				}						
</script>
	
<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $hidden;?>

<input type=hidden name=delete>
<input type=hidden name=id>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td colspan=4><?php echo $addButton;?> &nbsp; &nbsp; <?php echo $spamTrapLink;?></td></tr>
<tr><td colspan=4><?php echo $genButton;?> &nbsp; &nbsp; <?php echo $genSaveButton;?> &nbsp; &nbsp; <?php echo $decludeButtons;?></td></tr>
<tr><td colspan="3">Filter By &nbsp; <input type=text name=filter value='<?php echo $filter;?>'> &nbsp; 
	<input type=checkbox name=exactMatch value='Y' <?php echo $exactMatchChecked;?>> Exact Match &nbsp; &nbsp; <input type=submit name=viewList value='View List'></td></tr>	
<tr><td colspan=5 align=right class=header><input type=text name=recPerPage value='<?php echo $recPerPage;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp;Records Per Page &nbsp; &nbsp; 
<?php echo $firstPageLink;?> &nbsp; <?php echo $prevPageLink;?> &nbsp; <?php echo $nextPageLink;?> &nbsp; <?php echo $lastPageLink;?> &nbsp; <?php echo $currentPage;?></td></tr>	
	
<tr>
	<td align=left class=header><a href='<?php echo $sortLink;?>&orderColumn=ipAddress&ipAddressOrder=<?php echo $ipAddressOrder;?>'>IP Address</a></td>	
	<td align=left class=header><a href='<?php echo $sortLink;?>&orderColumn=serverName&serverNameOrder=<?php echo $serverNameOrder;?>'>Server Name</a></td>
	<td align=left class=header><a href='<?php echo $sortLink;?>&orderColumn=notes&notesOrder=<?php echo $notesOrder;?>'>Notes</a></td>
	<td align=left class=header><a href='<?php echo $sortLink;?>&orderColumn=dateInserted&dateInsertedOrder=<?php echo $dateInsertedOrder;?>'>Date Added</a></td>
	<td>&nbsp; </td>
</tr>
<?php echo $spamTrapList;?>
<tr><td colspan=5 align=right class=header>Records Per Page &nbsp; &nbsp; 
<?php echo $firstPageLink;?> &nbsp; <?php echo $prevPageLink;?> &nbsp; <?php echo $nextPageLink;?> &nbsp; <?php echo $lastPageLink;?> &nbsp; <?php echo $currentPage;?></td></tr>	
<tr><td><?php echo $addButton;?></td></tr>
</table>
</form>
	
<?php
	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
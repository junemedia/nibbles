<?php

include("tableManagerConfig.php");

$sDisplayQuery = "SELECT *
				  FROM   $sTableName order by $DefaultOrderBy" ;

$rDisplayResult = mysql_query($sDisplayQuery);


if ($rDisplayResult) {
	
	$sColHeaders = "<tr>";
	$i=0;
	while($oFieldInfo = mysql_fetch_field($rDisplayResult))
	{
		
		if ( in_array($oFieldInfo->name, $aDisplayCols ) || $oFieldInfo->name == $sAutoIncCol) {
		$aFieldName[$i] = $oFieldInfo->name;
		
		//$sSortOrderVarName = 'sortOrder'.$i;
		// set default order by column
		if (!($sOrderColumn)) {
			$sOrderColumn = $aFieldName[0];
			$sSortOrder0Var = 'sSortOrder0';
			$$sSortOrder0Var = "ASC";
		} 
			
			if (!($sCurrOrder)) {
				// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
				if ($sOrderColumn == $aFieldName[$i]) {
					$sSortOrderVar = "sSortOrder".$i;
					$sCurrOrder = $$sSortOrderVar;
					$$sSortOrderVar = ($$sSortOrderVar != "DESC" ? "DESC" : "ASC");
					
				}
			}
		
			
		// prepare column headers
		$sColHeaders .= "<td><b><a href='$PHP_SELF?sOrderColumn=". $aFieldName[$i] ."&$sSortOrderVar=".$$sSortOrderVar."'>".$aFieldName[$i]."</a></b></td>";

		// if this col is the filter column, prepare filter options display

		if ($aFieldName[$i] == $sFilterColumn) {
			$sFilterDisplay = "<tr><td width=100 nowrap>Filter $sFilterColumn</td><td width=60><select name=sFilter><option value=''>All[FILTER_OPTIONS]</select></td>
								<td><input type=submit name=sView value='View'></td></tr>";
		}
		$i++;
		
		}
		
	}
	$sColHeaders .= "<td></td></tr>";
}

if ($sSave) {
	// save edited info
	if ($iId) {
		// update
		$sUpdateQuery = "UPDATE $sTableName SET ";
		for ($i = 0; $i < count($aFieldName); $i++) {
			$sFieldName = $aFieldName[$i];
			if ($sFieldName != $sAutoIncCol) {
				$sUpdateQuery .= " $sFieldName = '".$$sFieldName."',";
			}
		}
		$sUpdateQuery = substr($sUpdateQuery,0,strlen($sUpdateQuery)-1);
		$sUpdateQuery .= " WHERE $sAutoIncCol = '$iId'";
		
		$rUpdateResult = mysql_query($sUpdateQuery);
		
		if ($rUpdateResult) {
			$sMessage = "Record Updated";
		} else {
			$sMessage = mysql_error();
		}
	} else {
		// insert
		$sInsertQuery = "INSERT INTO $sTableName (";
		for ($i = 0; $i < count($aFieldName); $i++) {
			$sFieldName = $aFieldName[$i];
			if ($sFieldName != $sAutoIncCol) {
				$sInsertQuery .= " $sFieldName,";
				$sValuesPart .= "'".$$sFieldName."',";
			}
		}
		$sInsertQuery = substr($sInsertQuery,0,strlen($sInsertQuery)-1);
		$sValuesPart = substr($sValuesPart,0, strlen($sValuesPart)-1);
		$sInsertQuery .= ") VALUES (".$sValuesPart.")";
		
		$rInsertResult = mysql_query($sInsertQuery);
		if ($rInsertResult) {
			$sMessage = "Record Inserted";
		} else {
			$sMessage = mysql_error();
		}
	}
} else if ($sDelete) {
	// delete record
	
	$sDeleteQuery = "DELETE FROM $sTableName
					 WHERE  $sAutoIncCol = '$iId'";
	$rDeleteResult = mysql_query($sDeleteQuery);
	if ($rDeleteResult) {
		$sMessage = "Record Deleted";
	}
}


if (($sShowEdit && $iId) || $sAddNew) {
	
	if ($sShowEdit) {
		$sDisplayQuery = "SELECT *
					  	  FROM   $sTableName
						  WHERE  $sAutoIncCol = '$iId'";
		$rDisplayResult = mysql_query($sDisplayQuery);
		while ($oRow = mysql_fetch_object($rDisplayResult)) {
			for ($i = 0; $i < count($aFieldName); $i++) {
				$sFieldName = $aFieldName[$i];
				$$sFieldName = $oRow->$sFieldName;
			}
		}
	}
	
	$sDisplayData = '';
	
	for ($i = 0; $i < count($aFieldName); $i++) {
		
		$sFieldName = $aFieldName[$i];
		if ($sFieldName != $sAutoIncCol) {
			$sDisplayData .= "<tr><td>$sFieldName</td>
								<td><input type=text name='$sFieldName' value ='".$$sFieldName."'> </td></tr>";
		}
	}
	
	if($returnLink){
	    $returnLinkHtml = "<table width=95% align=center><tr><td>$returnLink</td></tr></table><br>";
	}
	else{
		$returnLinkHtml = " " ;   
	}
	
	
	$sDisplayData = $returnLinkHtml. "<table width=95% align=center>". $sDisplayData . "<tr><td></td>
						<td><input type=hidden name=iId value='$iId'>
							<input type=hidden name=iPage value='$iPage'>
							<input type=hidden name=sOrderColumn value='$sOrderColumn'>
							<input type=submit name=sSave value=Save></table>";	
	
	// display edit form
	
} else {
	
	// display table records
	// Specify Page no. settings
	if (!($iRecPerPage)) {
		$iRecPerPage = 2;
	}
	if (!($iPage)) {
		$iPage = 1;
	}
	
	
	$sSortLink = $PHP_SELF;
	
	$sDisplayQuery = "SELECT *
					  FROM   $sTableName";
	
	/*if ($sFilterColumn != '' && $sFilter != '') {
		$sDisplayQuery .= " WHERE $sFilterColumn = '$sFilter' ";
	}
	*/
	
	if ($sOrderColumn) {
		$sDisplayQuery .= " ORDER BY $sOrderColumn $sCurrOrder";
	}

	$rDisplayResult = mysql_query($sDisplayQuery);
	echo mysql_error();
	$iNumRecords = mysql_num_rows($rDisplayResult);

	$iTotalPages = ceil($iNumRecords/$iRecPerPage);
	// If current page no. is greater than total pages move to the last available page no.
	if ($iPage > $iTotalPages) {
		$iPage = $iTotalPages;
	}

	$iStartRec = ($iPage-1) * $iRecPerPage;
	$iEndRec = $iStartRec + $iRecPerPage -1;
	
	if ($iNumRecords > 0) {
		$sCurrentPage = " Page $iPage "."/ $iTotalPages";
	}
	
	// use query to fetch only the rows of the page to be displayed
	$sDisplayQuery .= " LIMIT $iStartRec, $iRecPerPage";
	
	$rDisplayResult = mysql_query($sDisplayQuery);
	
	if ($rDisplayResult) {
		
		if ($iTotalPages > $iPage ) {
			$iNextPage = $iPage+1;
			$sNextPageLink = "<a href='".$sSortLink."?iPage=$iNextPage&sOrderColumn=$sOrderColumn&sCurrOrder=$sCurrOrder' class=header>Next</a>";
			$sLastPageLink = "<a href='".$sSortLink."?iPage=$iTotalPages&sOrderColumn=$sOrderColumn&sCurrOrder=$sCurrOrder' class=header>Last</a>";
		}
		if ($iPage != 1 && $iTotalPages > 0) {
			$iPrevPage = $iPage-1;
			$sPrevPageLink = "<a href='".$sSortLink."?iPage=$iPrevPage&sOrderColumn=$sOrderColumn&sCurrOrder=$sCurrOrder' class=header>Previous</a>";
			$sFirstPageLink = "<a href='".$sSortLink."?iPage=1&sOrderColumn=$sOrderColumn&sCurrOrder=$sCurrOrder' class=header>First</a>";
		}


		while ($oDisplayRow = mysql_fetch_object($rDisplayResult)) {

			
				for ($i = 0; $i< count($aFieldName); $i++)
				{
					$sKey =  $aFieldName[$i];
				
					if ($sKey == $sFilterColumn) {
						if ($sFilter == $oDisplayRow->$sKey) {
							$sSelected = "selected";
						}
						else {
							$sSelected = '';
						}
						$sFilterOptions .= "<option value='".$oDisplayRow->$sKey."' $sSelected>".$oDisplayRow->$sKey;
					}
				}
			
			if (($sFilterColumn != '' && $sFilter != '' && $sFilter == $oDisplayRow->$sFilterColumn) || $sFilter == '' ) {
				
			if ($sBgColor ==$sBgColor1) {
				$sBgColor = $sBgColor2;
			} else {
				$sBgColor = $sBgColor1;
			}
			
			$sDisplayRecords .= "<tr bgcolor=$sBgColor>";
			for ($i = 0; $i< count($aFieldName); $i++)
			{
				$sKey =  $aFieldName[$i];
				
				
				if ($sKey == $sAutoIncCol && strtoupper($sAllowEdit) == 'Y') {
					$sDisplayRecords .= "<td nowrap with=40><a href='$PHP_SELF?sShowEdit=showEdit&iPage=$iPage&sOrderColumn=$sOrderColumn&iId=".$oDisplayRow->$sAutoIncCol."'>". $oDisplayRow->$sKey . "</a></td>";
				} else {
					$sDisplayRecords .= "<td nowrap with=40>" . $oDisplayRow->$sKey . "</td>";
				}				

				
			}
			
			//$sDisplayRecords .= "<td nowrap>";
			/*if (strtoupper($sAllowEdit) == 'Y') {				
				$sDisplayRecords .= "<a href='' >Edit</a>&nbsp; ";
			}*/
			if (strtoupper($sAllowDelete) == 'Y') {				
				$sDisplayRecords .= "<td nowrap><a href='JavaScript:confirmDelete(this,\"".$oDisplayRow->$sAutoIncCol."\");' >Delete</a></td>";
			}
			//$sDisplayRecords .= "</td>";
			$sDisplayRecords .= "</tr>";
			
			}
		}
	}
		
	
	if ($sFilterDisplay != '') {
		$sFilterDisplay = ereg_replace("\[FILTER_OPTIONS\]", $sFilterOptions, $sFilterDisplay);
		$sFilterDisplay = "<table width=95% >$sFilterDisplay</table>";
	}
	
	$sDisplayData = $sFilterDisplay. "<table width=95% align=center>";
	if (strtoupper($sAllowAdd) == 'Y') {
		$sDisplayData .= "<tr><td><a href='$PHP_SELF?sAddNew=addNew'>Add New</A></td></tr>";
	}
	$sDisplayData .= "<tr><td align=right>$sFirstPageLink $sPrevPageLink $sNextPageLink $sLastPageLink</td></tr>";
	
	
	$iPageStart = $iPage - 5;
	
	if ($iPageStart < 1) {
		
		$iPageStart = '1';
	}
	
	
	$sDisplayData .= "<tr><td align=right>";
	for ($i = $iPageStart; $i < $iPageStart + 10; $i++) {
		if ($i > $iTotalPages) {
			break;
		}
		if ($i == $iPage) {
			$sDisplayData .= "$i &nbsp;";
		} else {
			$sDisplayData .= "<a href='".$sSortLink."?iPage=$i&sOrderColumn=$sOrderColumn&sCurrOrder=$sCurrOrder'>$i</a> &nbsp;";
		}
	}
	
	$sDisplayData .= "</td></tr>";
	
	$sDisplayData .= "</table><table align=center width=100%>". $sColHeaders. $sDisplayRecords . "</table>";
	
}


?>
<html>
<head>
<title><?php echo $sScriptTitle;?></title>

</head>
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
</script>

<body>
<center><h3><?php echo $sScriptTitle;?></h3></center>

<table>
	<tr><td class=message><?php echo $sMessage;?></font></td></tr>
</table>

<form name=form1 action='<?php echo $PHP_SELF;?>' >
<input type=hidden name=sDelete>
<input type=hidden name=iId value='<?php echo $iId;?>'>
<?php echo $sDisplayData;?>
</form>

</body>
</html>
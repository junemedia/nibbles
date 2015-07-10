<?php


include_once("../subctr_config.php");

if ($sDelete) {
	$sDeleteQuery = "DELETE FROM groupSubcampId WHERE  id = '$iId'";
	$rResult = mysql_query($sDeleteQuery);
	echo mysql_error();
	$iId = '';$sDelete = '';
}
	
$sGroupList = '';
$rSelectResult = mysql_query("SELECT * FROM groupSubcampId ORDER BY id DESC");
echo mysql_error();
while ($oRow = mysql_fetch_object($rSelectResult)) {
	if ($sBgcolorClass=="Silver") { $sBgcolorClass="White"; } else { $sBgcolorClass="Silver"; }
	$sGroupList .= "<tr bgcolor='$sBgcolorClass'><td>$oRow->groupName</td> <td>$oRow->sources</td>
			<td><a href=\"#\" onclick=\"return popitup('edit.php?iId=$oRow->id')\">Edit</a>
			&nbsp;<a href='JavaScript:confirmDelete(this,".$oRow->id.");' >Delete</a>
			</td></tr>";
}

?>
	
<script language=JavaScript>
function confirmDelete(form1,id) {
	if(confirm('Are you sure to delete this record ?')) {							
		document.form1.elements['sDelete'].value='Delete';
		document.form1.elements['iId'].value=id;
		document.form1.submit();								
	}
}
function popitup(url) {
	newwindow=window.open(url,'name','height=450,width=600');
	if (window.focus) {newwindow.focus()}
	return false;
}
</script>
<style>
* {
	font-family: verdana;
	font-size: 12px;
}
</style>
<form name=form1 action='<?php echo $_SERVER['PHP_SELF'];?>'>
<input type=hidden name='iId' value="<?php echo $iId; ?>">
<input type=hidden name='sDelete' value="<?php echo $sDelete; ?>">
<table cellpadding=5 cellspacing=0 width=50% align=center>
<tr><td colspan=7 align=left>
<a href="#" onclick="return popitup('add.php')">Add</a>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="#" onclick="location.reload();">Refresh Page</a>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="/admin/subctr/sub_report.php">Back to Report</a>
</td></tr>
<tr><td class=header><b>Lists Group</b></td><td></td>
</tr>
<?php echo $sGroupList;?>
</table>
</form>
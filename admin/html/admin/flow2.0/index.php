<?php

include_once("include.php");

$sql_filter = "";
if ($submit == 'Search') {
	if ($name != '') {
		$sql_filter .= " AND name LIKE '%$name%' ";
	}
	if ($template != '') {
		$sql_filter .= " AND template LIKE '%$template%' ";
	}
	if ($campaign != '') {
		$sql_filter .= " AND campaign LIKE '%$campaign%' ";
	}
}



function getListIdByTemplateName ($templateName) {
	$result = mysql_query("SELECT listid FROM templates WHERE templateName='$templateName' LIMIT 1");
	while ($row = mysql_fetch_object($result)) {
		return $row->listid;
	}
}


$data = "";
$get_links_result = mysql_query("SELECT * FROM links WHERE 1=1 $sql_filter ORDER BY dateTime DESC");
$export = "Link Name,Template [listid],Opacity,Delay,User Control,Source,Campaign,Display Count,Signup Count,Conversion Rate,Linkid,URL,Preview Link,Active\n";
while ($link_row = mysql_fetch_object($get_links_result)) {
	
	$from_date = date('Y-m').'01';
	$to_date = date('Y-m-d');
	$report_result = mysql_query("SELECT SUM(display) AS totalDisplay, SUM(signup) AS totalSignup FROM report WHERE linkid='$link_row->linkid' AND dateAdded BETWEEN '$from_date' AND '$to_date'");
	while ($row = mysql_fetch_object($report_result)) {
		$conversion_rate = sprintf("%.2f%%", (($row->totalSignup / $row->totalDisplay) * 100));
		$display_count = $row->totalDisplay;
		if ($row->totalDisplay == '') { $display_count = 0; }
		$signup_count = $row->totalSignup;
		if ($row->totalSignup == '') { $signup_count = 0; }
	}
	
	if ($sBgcolorClass == "#FAFAFA") {
		$sBgcolorClass = "#FBEFF2";
	} else {
		$sBgcolorClass = "#FAFAFA";
	}
	
	if (strstr($link_row->url, '?')) {
		$preview_link = "$link_row->url&cid=$link_row->linkid";
	} else {
		$preview_link = "$link_row->url?cid=$link_row->linkid";
	}
	
	$data .= "<tr bgcolor=$sBgcolorClass>
			<td><a href='/admin/flow2.0/addEdit.php?linkid=$link_row->linkid'>$link_row->name</a></td>
			<td><a href='/admin/flow2.0/addEditSubcampId.php?linkid=$link_row->linkid'>Add/Edit</a></td>
			<td>$link_row->template [".getListIdByTemplateName($link_row->template)."]</td>
			<td>$link_row->opacity</td>
			<td>$link_row->delay</td>
			<td>$link_row->usercontrol</td>
			<td>$link_row->source</td>
			<td>$link_row->campaign</td>
			<td>$display_count</td>
			<td>$signup_count</td>
			<td>$conversion_rate</td>
			<td>$link_row->linkid</td>
			<td>$link_row->url</td>
			<td><a href='$preview_link' target=_blank>Preview</a></td>
			<td>$link_row->isActive</td>
			</tr>";
	$export .= "$link_row->name,$link_row->template,$link_row->opacity,$link_row->delay,$link_row->usercontrol,$link_row->source,$link_row->campaign,$display_count,$signup_count,$conversion_rate,$link_row->linkid,$link_row->url,$preview_link,$link_row->isActive\n";
}

$fp = fopen('/var/www/html/admin.popularliving.com/html/admin/flow2.0/export.csv', 'w');
fwrite($fp, $export);
fclose($fp);


?>
<html>
<head>
<title>Links Management</title>
<style>
table{font:12px Arial,Helvetica,sans-serif;line-height:1.25em;color:#4e4e4e;background:#e2ded5;}
</style>
</head>
<body>
<center>
<h3><b>Recipe4Living Links Management</b></h3>
<a href="/admin/flow2.0/addEdit.php"><b>Create New Link</b></a>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="/admin/flow2.0/addNewSource.php" onclick="javascript:void window.open('/admin/flow2.0/addNewSource.php','1373923368168','width=500,height=500,toolbar=0,menubar=0,location=0,status=0,scrollbars=1,resizable=1,left=100,top=100');return false;"><b>Source Management</b></a>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="/admin/flow2.0/addNewCampaign.php" onclick="javascript:void window.open('/admin/flow2.0/addNewCampaign.php','1373923368168','width=500,height=500,toolbar=0,menubar=0,location=0,status=0,scrollbars=1,resizable=1,left=100,top=100');return false;"><b>Campaign Management</b></a>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="/admin/flow2.0/addNewSubcampid.php" onclick="javascript:void window.open('/admin/flow2.0/addNewSubcampid.php','1373923368168','width=500,height=500,toolbar=0,menubar=0,location=0,status=0,scrollbars=1,resizable=1,left=100,top=100');return false;"><b>SubcampID Management</b></a>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="/admin/flow2.0/addNewTemplate.php" onclick="javascript:void window.open('/admin/flow2.0/addNewTemplate.php','1373923368168','width=500,height=600,toolbar=0,menubar=0,location=0,status=0,scrollbars=1,resizable=1,left=100,top=100');return false;" style="color:red;"><b>Template Management (IT only)</b></a>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="/admin/flow2.0/report.php"><b>Report</b></a>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="export.csv" target="_blank"><b>Download/Export Below Links</b></a>
<br><br>
<font color="Red">Once links are created (before going live), you MUST assign subcampid using Add/Edit option otherwise all signup will go under default (3589) subcampid.</font>
<br><br>



<form name='form1' method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
<table border="0" align="center" width="600px" cellpadding="2" cellspacing="2">
	<tr>
		<td>Link Name: <input type="text" name="name" size="40" maxlength="100" value="<?php echo $name; ?>"></td>
		<td>Template: <input type="text" name="template" size="40" maxlength="100" value="<?php echo $template; ?>"></td>
		<td>Campaign: <input type="text" name="campaign" size="40" maxlength="100" value="<?php echo $campaign; ?>"></td>
		<td><input type="submit" name="submit" value="Search"></td>
	</tr>
</table>
</form>

</center>
<table border="1" align="center" cellpadding="5" cellspacing="5">
<tr>
	<td><b>Link Name</b></td>
	<td><b>SubcampIDs</b></td>
	<td><b>Template [listid]</b></td>
	<td><b>Opacity</b></td>
	<td><b>Delay</b></td>
	<td><b>User Control</b></td>
	<td><b>Source</b></td>
	<td><b>Campaign</b></td>
	<td><b>MTD Display</b></td>
	<td><b>MTD Signup</b></td>
	<td><b>MTD Conversion</b></td>
	<td><b>Linkid</b></td>
	<td><b>URL</b></td>
	<td><b>Preview *</b></td>
	<td><b>Active</b></td>
</tr>
<?php echo $data; ?>
</table>

<br><br>
<b>Note:</b><br>
- Once links are created, they cannot be deleted.<br>
- Reporting data not available before July 29th, 2013.<br>
* Once new link is created or changes were made, it may take upto 10 mins for it to go live.<br>
</body>
</html>
<?php

include_once("subctr_config.php");


exit;



$today_start = date('Y-m-d')." 00:00:00";
$today_end = date('Y-m-d')." 23:59:59";

$yesterday_start = date("Y-m-d", time()-86400)." 00:00:00";
$yesterday_end = date("Y-m-d", time()-86400)." 23:59:59";


// for bounce out and feedback loop
$previous_day = date("Y-m-d", time()-86400);
$previous_previous_day = date("Y-m-d", time()-172800);

/*
**************************************************************************************************************
*/

$unique_emails = 0;
$unique_query = "SELECT count(DISTINCT email) as ct FROM joinEmailActive";
$unique_result = mysql_query($unique_query);
echo mysql_error();
while ($unique_row = mysql_fetch_object($unique_result)) {
	$unique_emails = $unique_row->ct;
}


/*
**************************************************************************************************************
*/

$list_id_array = array();
$query = "SELECT listid,title FROM joinLists";
$result = mysql_query($query);
echo mysql_error();
while ($row = mysql_fetch_object($result)) {
	array_push($list_id_array, $row->listid.':'.$row->title);
}

/*
**************************************************************************************************************
*/

$today_joinEmailActive_stats = array();
$yesterday_joinEmailActive_stats = array();
foreach ($list_id_array as $item) {
	$part = explode(':',$item);
	$listid = $part[0];
	$title = $part[1];
	
	$query = "SELECT count(*) as CT 
				FROM joinEmailActive 
				WHERE dateTime BETWEEN '$today_start' 
				AND '$today_end' AND listid='$listid'";
	$result = mysql_query($query);
	echo mysql_error();
	while ($row = mysql_fetch_object($result)) {
		array_push($today_joinEmailActive_stats, $title.':'.$row->CT);
	}
	
	$query = "SELECT count(*) as CT 
				FROM joinEmailActive 
				WHERE dateTime BETWEEN '$yesterday_start' 
				AND '$yesterday_end' AND listid='$listid'";
	$result = mysql_query($query);
	echo mysql_error();
	while ($row = mysql_fetch_object($result)) {
		array_push($yesterday_joinEmailActive_stats, $title.':'.$row->CT);
	}
}



$today_joinEmailSub_stats = array();
$yesterday_joinEmailSub_stats = array();
foreach ($list_id_array as $item) {
	$part = explode(':',$item);
	$listid = $part[0];
	$title = $part[1];
	
	$query = "SELECT count(*) as CT 
				FROM joinEmailSub 
				WHERE dateTime BETWEEN '$today_start' 
				AND '$today_end' AND listid='$listid'";
	$result = mysql_query($query);
	echo mysql_error();
	while ($row = mysql_fetch_object($result)) {
		array_push($today_joinEmailSub_stats, $title.':'.$row->CT);
	}
	
	$query = "SELECT count(*) as CT 
				FROM joinEmailSub 
				WHERE dateTime BETWEEN '$yesterday_start' 
				AND '$yesterday_end' AND listid='$listid'";
	$result = mysql_query($query);
	echo mysql_error();
	while ($row = mysql_fetch_object($result)) {
		array_push($yesterday_joinEmailSub_stats, $title.':'.$row->CT);
	}
}




$today_joinEmailUnsub_stats = array();
$yesterday_joinEmailUnsub_stats = array();
foreach ($list_id_array as $item) {
	$part = explode(':',$item);
	$listid = $part[0];
	$title = $part[1];
	
	$query = "SELECT count(*) as CT 
				FROM joinEmailUnsub 
				WHERE dateTime BETWEEN '$today_start' 
				AND '$today_end' AND listid='$listid'";
	$result = mysql_query($query);
	echo mysql_error();
	while ($row = mysql_fetch_object($result)) {
		array_push($today_joinEmailUnsub_stats, $title.':'.$row->CT);
	}
	
	$query = "SELECT count(*) as CT 
				FROM joinEmailUnsub 
				WHERE dateTime BETWEEN '$yesterday_start' 
				AND '$yesterday_end' AND listid='$listid'";
	$result = mysql_query($query);
	echo mysql_error();
	while ($row = mysql_fetch_object($result)) {
		array_push($yesterday_joinEmailUnsub_stats, $title.':'.$row->CT);
	}
}


$today_joinEmailActive_detailed_stats = "<table id='explanation1' style='display:none'>";
foreach ($today_joinEmailActive_stats as $item) {
	$part = explode(':',$item);
	$today_joinEmailActive_detailed_stats .= "<tr><td>".$part[0]."</td><td>".$part[1]."</td></tr>";
}
$today_joinEmailActive_detailed_stats .= "</table>";

$today_joinEmailSub_detailed_stats = "<table id='explanation2' style='display:none'>";
foreach ($today_joinEmailSub_stats as $item) {
	$part = explode(':',$item);
	$today_joinEmailSub_detailed_stats .= "<tr><td>".$part[0]."</td><td>".$part[1]."</td></tr>";
}
$today_joinEmailSub_detailed_stats .= "</table>";

$today_joinEmailUnsub_detailed_stats = "<table id='explanation3' style='display:none'>";
foreach ($today_joinEmailUnsub_stats as $item) {
	$part = explode(':',$item);
	$today_joinEmailUnsub_detailed_stats .= "<tr><td>".$part[0]."</td><td>".$part[1]."</td></tr>";
}
$today_joinEmailUnsub_detailed_stats .= "</table>";






$yesterday_joinEmailActive_detailed_stats = "<table id='explanation4' style='display:none'>";
foreach ($yesterday_joinEmailActive_stats as $item) {
	$part = explode(':',$item);
	$yesterday_joinEmailActive_detailed_stats .= "<tr><td>".$part[0]."</td><td>".$part[1]."</td></tr>";
}
$yesterday_joinEmailActive_detailed_stats .= "</table>";

$yesterday_joinEmailSub_detailed_stats = "<table id='explanation5' style='display:none'>";
foreach ($yesterday_joinEmailSub_stats as $item) {
	$part = explode(':',$item);
	$yesterday_joinEmailSub_detailed_stats .= "<tr><td>".$part[0]."</td><td>".$part[1]."</td></tr>";
}
$yesterday_joinEmailSub_detailed_stats .= "</table>";

$yesterday_joinEmailUnsub_detailed_stats = "<table id='explanation6' style='display:none'>";
foreach ($yesterday_joinEmailUnsub_stats as $item) {
	$part = explode(':',$item);
	$yesterday_joinEmailUnsub_detailed_stats .= "<tr><td>".$part[0]."</td><td>".$part[1]."</td></tr>";
}
$yesterday_joinEmailUnsub_detailed_stats .= "</table>";






/*
**************************************************************************************************************
*/

$query = "SELECT count(*) as CT FROM joinEmailActive WHERE dateTime BETWEEN '$today_start' AND '$today_end'";
$result = mysql_query($query);
echo mysql_error();
$today_active_count = 0;
while ($row = mysql_fetch_object($result)) {
	$today_active_count = $row->CT;
}



$query = "SELECT count(*) as CT FROM joinEmailActive WHERE dateTime BETWEEN '$yesterday_start' AND '$yesterday_end'";
$result = mysql_query($query);
echo mysql_error();
$yesterday_active_count = 0;
while ($row = mysql_fetch_object($result)) {
	$yesterday_active_count = $row->CT;
}

/*
**************************************************************************************************************
*/
$query = "SELECT count(*) as CT FROM joinEmailSub WHERE dateTime BETWEEN '$today_start' AND '$today_end'";
$result = mysql_query($query);
echo mysql_error();
$today_signup_count = 0;
while ($row = mysql_fetch_object($result)) {
	$today_signup_count = $row->CT;
}




$query = "SELECT count(*) as CT FROM joinEmailSub WHERE dateTime BETWEEN '$yesterday_start' AND '$yesterday_end'";
$result = mysql_query($query);
echo mysql_error();
$yesterday_signup_count = 0;
while ($row = mysql_fetch_object($result)) {
	$yesterday_signup_count = $row->CT;
}

/*
**************************************************************************************************************
*/


$query = "SELECT count(*) as CT FROM joinEmailUnsub WHERE dateTime BETWEEN '$today_start' AND '$today_end' AND source !='Hard/Soft Bounce'";
$result = mysql_query($query);
echo mysql_error();
$today_unsub_count = 0;
while ($row = mysql_fetch_object($result)) {
	$today_unsub_count = $row->CT;
}



$query = "SELECT count(*) as CT FROM joinEmailUnsub WHERE dateTime BETWEEN '$yesterday_start' AND '$yesterday_end' AND source !='Hard/Soft Bounce'";
$result = mysql_query($query);
echo mysql_error();
$yesterday_unsub_count = 0;
while ($row = mysql_fetch_object($result)) {
	$yesterday_unsub_count = $row->CT;
}

/*
**************************************************************************************************************
*/

$query = "SELECT count(*) as CT FROM api WHERE dateTimeAdded BETWEEN '$today_start' AND '$today_end'";
$result = mysql_query($query);
echo mysql_error();
$today_api_count = 0;
while ($row = mysql_fetch_object($result)) {
	$today_api_count = $row->CT;
}



$query = "SELECT count(*) as CT FROM api WHERE dateTimeAdded BETWEEN '$yesterday_start' AND '$yesterday_end'";
$result = mysql_query($query);
echo mysql_error();
$yesterday_api_count = 0;
while ($row = mysql_fetch_object($result)) {
	$yesterday_api_count = $row->CT;
}

/*
**************************************************************************************************************
*/


$query = "SELECT count(*) as CT FROM BullseyeBriteVerifyCheck WHERE dateTimeAdded BETWEEN '$today_start' AND '$today_end'";
$result = mysql_query($query);
echo mysql_error();
$today_bv_count = 0;
while ($row = mysql_fetch_object($result)) {
	$today_bv_count = $row->CT;
}



$query = "SELECT count(*) as CT FROM BullseyeBriteVerifyCheck WHERE dateTimeAdded BETWEEN '$yesterday_start' AND '$yesterday_end'";
$result = mysql_query($query);
echo mysql_error();
$yesterday_bv_count = 0;
while ($row = mysql_fetch_object($result)) {
	$yesterday_bv_count = $row->CT;
}



/*
**************************************************************************************************************
*/

$query = "SELECT count(*) as CT FROM emailChange WHERE dateTimeAdded BETWEEN '$today_start' AND '$today_end'";
$result = mysql_query($query);
echo mysql_error();
$today_email_change_count = 0;
while ($row = mysql_fetch_object($result)) {
	$today_email_change_count = $row->CT;
}

$query = "SELECT count(*) as CT FROM emailChange WHERE dateTimeAdded BETWEEN '$yesterday_start' AND '$yesterday_end'";
$result = mysql_query($query);
echo mysql_error();
$yesterday_email_change_count = 0;
while ($row = mysql_fetch_object($result)) {
	$yesterday_email_change_count = $row->CT;
}


/*
**************************************************************************************************************
*/






/*
**************************************************************************************************************
*/



$query = "SELECT count(*) as CT FROM bounceLog WHERE bounceDate BETWEEN '$today_start' AND '$today_end'";
$result = mysql_query($query);
echo mysql_error();
$today_bounce_count = 0;
while ($row = mysql_fetch_object($result)) {
	$today_bounce_count = $row->CT;
}

$query = "SELECT count(*) as CT FROM bounceLog WHERE bounceDate BETWEEN '$yesterday_start' AND '$yesterday_end'";
$result = mysql_query($query);
echo mysql_error();
$yesterday_bounce_count = 0;
while ($row = mysql_fetch_object($result)) {
	$yesterday_bounce_count = $row->CT;
}


/*
**************************************************************************************************************
*/





/*
**************************************************************************************************************
*/



// for bounce out and feedback loop
$query = "SELECT count(*) as CT FROM bounceOut WHERE bounceDate = '$previous_day'";
$result = mysql_query($query);
echo mysql_error();
$today_bounceout_count = 0;
while ($row = mysql_fetch_object($result)) {
	$today_bounceout_count = $row->CT;
}

$query = "SELECT count(*) as CT FROM bounceOut WHERE bounceDate = '$previous_previous_day'";
$result = mysql_query($query);
echo mysql_error();
$yesterday_bounceout_count = 0;
while ($row = mysql_fetch_object($result)) {
	$yesterday_bounceout_count = $row->CT;
}

/*
**************************************************************************************************************
*/



// for bounce out and feedback loop
$query = "SELECT count(*) as CT FROM feedBackLoop WHERE feedBackDate = '$previous_day'";
$result = mysql_query($query);
echo mysql_error();
$today_feedback_count = 0;
while ($row = mysql_fetch_object($result)) {
	$today_feedback_count = $row->CT;
}

$query = "SELECT count(*) as CT FROM feedBackLoop WHERE feedBackDate = '$previous_previous_day'";
$result = mysql_query($query);
echo mysql_error();
$yesterday_feedback_count = 0;
while ($row = mysql_fetch_object($result)) {
	$yesterday_feedback_count = $row->CT;
}



/*
**************************************************************************************************************
*/



$query = "SELECT listid, count(*) AS count FROM joinEmailActive GROUP BY listid";
$result = mysql_query($query);
echo mysql_error();
$current_count_by_list = "";
$total_list = 0;
while ($row = mysql_fetch_object($result)) {
	$query_list = "SELECT title FROM joinLists WHERE listid='$row->listid'";
	$result_list = mysql_query($query_list);
	echo mysql_error();
	$list_title = '';
	while ($row_list = mysql_fetch_object($result_list)) {
		$list_title = $row_list->title;
	}
	
	$current_count_by_list .= "<tr><td>".$row->listid." ($list_title)</td><td>".$row->count."<br><br></td></tr>";
	$total_list += $row->count;
}






/*
**************************************************************************************************************
*/


$query = "SELECT count(*) as CT FROM signin WHERE dateTimeAdded BETWEEN '$today_start' AND '$today_end'";
$result = mysql_query($query);
echo mysql_error();
$today_signin_count = 0;
while ($row = mysql_fetch_object($result)) {
	$today_signin_count = $row->CT;
}



$query = "SELECT count(*) as CT FROM signin WHERE dateTimeAdded BETWEEN '$yesterday_start' AND '$yesterday_end'";
$result = mysql_query($query);
echo mysql_error();
$yesterday_signin_count = 0;
while ($row = mysql_fetch_object($result)) {
	$yesterday_signin_count = $row->CT;
}


/*
**************************************************************************************************************
*/


$query = "SELECT count(*) as CT FROM signin WHERE dateTimeAdded BETWEEN '$today_start' AND '$today_end' AND site='r4l'";
$result = mysql_query($query);
echo mysql_error();
$today_signin_count_r4l = 0;
while ($row = mysql_fetch_object($result)) {
	$today_signin_count_r4l = $row->CT;
}



$query = "SELECT count(*) as CT FROM signin WHERE dateTimeAdded BETWEEN '$yesterday_start' AND '$yesterday_end' AND site='r4l'";
$result = mysql_query($query);
echo mysql_error();
$yesterday_signin_count_r4l = 0;
while ($row = mysql_fetch_object($result)) {
	$yesterday_signin_count_r4l = $row->CT;
}


/*
**************************************************************************************************************
*/


$query = "SELECT count(*) as CT FROM signin WHERE dateTimeAdded BETWEEN '$today_start' AND '$today_end' AND site='fitfab'";
$result = mysql_query($query);
echo mysql_error();
$today_signin_count_ff = 0;
while ($row = mysql_fetch_object($result)) {
	$today_signin_count_ff = $row->CT;
}

$query = "SELECT count(*) as CT FROM signin WHERE dateTimeAdded BETWEEN '$yesterday_start' AND '$yesterday_end' AND site='fitfab'";
$result = mysql_query($query);
echo mysql_error();
$yesterday_signin_count_ff = 0;
while ($row = mysql_fetch_object($result)) {
	$yesterday_signin_count_ff = $row->CT;
}

?>
<html>
<head>
<title>Quick Stats</title>
<style>
table {
	font-family: verdana;
	font-size:75%;
}
</style>
<script type="text/javascript">
function display(action, id) {
	if (action == 'show') {
		document.getElementById("explanation"+id).style.display = "block";
		document.getElementById("link"+id).href= "javascript:display('hide', "+id+")";
		document.getElementById("link"+id).innerHTML = "Close";
	}
	
	if (action == 'hide') {
		document.getElementById("explanation"+id).style.display = "none";
		document.getElementById("link"+id).href= "javascript:display('show', "+id+")";
		document.getElementById("link"+id).innerHTML = "Explain";
	}
}
</script>
</head>
<body>

<table border="0" align="center" width="400px" cellpadding="5" cellspacing="5">
	<tr>
		<td colspan="2" align="center">
		<a href="http://admin.popularliving.com/admin/index.php">Return to Nibbles Main Menu</a>
		</td>
	</tr>
	<tr><td colspan="2">&nbsp;</td></tr>
	
	
	<tr><td colspan="2">Unique Subscribers (Unique Emails): <?php echo $unique_emails; ?></td></tr>
	
	
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr><td colspan="2"><b>Current Active Subscribers (<?php echo $total_list; ?>)</b>
	<br><font size="1">(this stats changes as user subscribe/unsubscribe)</font><br><br>
	</td></tr>
	
	
	<?php echo $current_count_by_list; ?>
	
	
	
	<tr><td colspan="2">&nbsp;</td></tr>
	

	<tr><td colspan="2"><b>Today</b></td></tr>
	
	<tr>
		<td>Net New Subscription: <a id='link1' href="javascript:display('show', 1)">Explain</a></td>
		<td><?php echo $today_active_count; ?></td>
	</tr>
	<tr><td colspan="2" align="center"><?php echo $today_joinEmailActive_detailed_stats; ?></td></tr>
	
	<tr>
		<td>Gross New Subscription: <a id='link2' href="javascript:display('show', 2)">Explain</a></td>
		<td><?php echo $today_signup_count; ?></td>
	</tr>
	<tr><td colspan="2"><?php echo $today_joinEmailSub_detailed_stats; ?></td></tr>
	
	<tr>
		<td>Unsub Count: <a id='link3' href="javascript:display('show', 3)">Explain</a></td>
		<td><?php echo $today_unsub_count; ?></td>
	</tr>
	<tr><td colspan="2"><?php echo $today_joinEmailUnsub_detailed_stats; ?></td></tr>
	
	<tr>
		<td>Flow Signup/API (# of leads): </td>
		<td><?php echo $today_api_count; ?></td>
	</tr>
	<tr>
		<td>Brite Verify Count: </td>
		<td><?php echo $today_bv_count; ?></td>
	</tr>
	<tr>
		<td>SubCenter Signin R4L: </td>
		<td><?php echo $today_signin_count_r4l; ?></td>
	</tr>
	<tr>
		<td>SubCenter Signin FF: </td>
		<td><?php echo $today_signin_count_ff; ?></td>
	</tr>
	<tr>
		<td>Email Change Count: </td>
		<td><?php echo $today_email_change_count; ?></td>
	</tr>
	<tr>
		<td>Bounce Log Count: </td>
		<td><?php echo $today_bounce_count; ?></td>
	</tr>
	<tr>
		<td>Bounce Out Count: </td>
		<td><?php echo $today_bounceout_count; ?></td>
	</tr>
	<tr>
		<td>Feed Back Loop Count: </td>
		<td><?php echo $today_feedback_count; ?></td>
	</tr>
	
	
	
	
	
	
	
	
	
	
	<tr><td colspan="2">&nbsp;</td></tr>
	
	
	
	
	<tr><td colspan="2"><b>Yesterday</b></td></tr>
	
	
	<tr>
		<td>Current List Size: <a id='link4' href="javascript:display('show', 4)">Explain</a></td>
		<td><?php echo $yesterday_active_count; ?></td>
	</tr>
	<tr><td colspan="2"><?php echo $yesterday_joinEmailActive_detailed_stats; ?></td></tr>
	
	
	<tr>
		<td>Signup Count: <a id='link5' href="javascript:display('show', 5)">Explain</a></td>
		<td><?php echo $yesterday_signup_count; ?></td>
	</tr>
	<tr><td colspan="2"><?php echo $yesterday_joinEmailSub_detailed_stats; ?></td></tr>
	
	
	<tr>
		<td>Unsub Count: <a id='link6' href="javascript:display('show', 6)">Explain</a></td>
		<td><?php echo $yesterday_unsub_count; ?></td>
	</tr>
	<tr><td colspan="2"><?php echo $yesterday_joinEmailUnsub_detailed_stats; ?></td></tr>
	
	
	<tr>
		<td>Flow Signup/API (# of leads): </td>
		<td><?php echo $yesterday_api_count; ?></td>
	</tr>
	<tr>
		<td>Brite Verify Count: </td>
		<td><?php echo $yesterday_bv_count; ?></td>
	</tr>
	<tr>
		<td>SubCenter Signin R4L: </td>
		<td><?php echo $yesterday_signin_count_r4l; ?></td>
	</tr>
	<tr>
		<td>SubCenter Signin FF: </td>
		<td><?php echo $yesterday_signin_count_ff; ?></td>
	</tr>
	<tr>
		<td>Email Change Count: </td>
		<td><?php echo $yesterday_email_change_count; ?></td>
	</tr>
	<tr>
		<td>Bounce Log Count: </td>
		<td><?php echo $yesterday_bounce_count; ?></td>
	</tr>
	<tr>
		<td>Bounce Out Count: </td>
		<td><?php echo $yesterday_bounceout_count; ?></td>
	</tr>
	<tr>
		<td>Feed Back Loop Count: </td>
		<td><?php echo $yesterday_feedback_count; ?></td>
	</tr>
	
	
	
</table>
</body>
</html>

<?php

include_once("include.php");

if ($linkid == '') { echo 'bad page...email support@junemedia.com and provide screenshot and URL of this page';exit; }



$name_result = mysql_query("SELECT name FROM links WHERE linkid = \"$linkid\"");
while ($name_row = mysql_fetch_object($name_result)) {
	$link_name = $name_row->name;
}


$get_data_result = mysql_query("SELECT * FROM links_subcampid WHERE linkid = \"$linkid\"");
while ($data_row = mysql_fetch_object($get_data_result)) {
	$month_year = $data_row->month_year;
	$$month_year = $data_row->subcampid;
}

?>
<html>
<head>
<title>Add/Edit SubcampID: <?php echo $linkid; ?> </title>
<style>
table{font:12px Arial,Helvetica,sans-serif;line-height:1.25em;color:#4e4e4e;background:#e2ded5;}
</style>


<SCRIPT LANGUAGE=JavaScript SRC="ajax.js" TYPE=text/javascript></script>
<script language="JavaScript">
function update (month_year,subcampid) {
	div = getObject(month_year);
	response=jm.send('set_subcampid.php?linkid=<?php echo $linkid; ?>&month_year='+month_year+'&subcampid='+subcampid,'');
	var n=response.split("|");
	if (n[0] == 'success') {
		div.innerHTML = "<font size='1' color='Green'><b>&#10003; "+n[1]+"</b></font>";
	} else {
		div.innerHTML = "<font size='1' color='Red'><b>&#120;</b></font>";
	}
}
function isNumberKey(evt) {
	var charCode = (evt.which) ? evt.which : event.keyCode
	if (charCode > 31 && (charCode < 48 || charCode > 57)) {
		return false;
	} else {
		return true;
	}
}
</script>
</head>
<body>
<center>
<a href="/admin/flow2.0/">Back to Links Management</a>
<br><br>
<b>Add/Edit SubcampID: <?php echo $link_name; ?></b>
<br><br>
</center>


<?php
$result2 = mysql_query('SELECT * FROM subcampids ORDER BY subcampid DESC');
if(mysql_num_rows($result2)) {
	echo '<table>';
	echo '<tr><th>SubcampID</th><th>Name</th></tr>';
	while($row2 = mysql_fetch_row($result2)) {
		echo '<tr>';
		foreach($row2 as $key=>$value) {
			echo '<td>',$value,'</td>';
		}
		echo '</tr>';
	}
	echo '</table><br />';
}
?>


<table cellpadding="5" cellspacing="5" align="center" border="1" width="90%">
<tr>
	<td>
		<table cellpadding="5" cellspacing="5" align="center" width="90%">
			<tr>
				<td colspan="4"><b>Year 2013</b></td>
			</tr>
			<tr>
				<td colspan="4">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="2">&nbsp;</td>
				<td>Jul 2013: <input type="text" maxlength="4" size="4" name="jul_2013" value="<?php echo $jul_2013; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='jul_2013'></div></td>
				<td>Aug 2013: <input type="text" maxlength="4" size="4" name="aug_2013" value="<?php echo $aug_2013; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='aug_2013'></div></td>
			</tr>
			<tr>
				<td>Sep 2013: <input type="text" maxlength="4" size="4" name="sep_2013" value="<?php echo $sep_2013; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='sep_2013'></div></td>
				<td>Oct 2013: <input type="text" maxlength="4" size="4" name="oct_2013" value="<?php echo $oct_2013; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='oct_2013'></div></td>
				<td>Nov 2013: <input type="text" maxlength="4" size="4" name="nov_2013" value="<?php echo $nov_2013; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='nov_2013'></div></td>
				<td>Dec 2013: <input type="text" maxlength="4" size="4" name="dec_2013" value="<?php echo $dec_2013; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='dec_2013'></div></td>
			</tr>
		</table>
	</td>
</tr>
</table>
<br>
<table cellpadding="5" cellspacing="5" align="center" border="1" width="90%">
<tr>
	<td>
		<table cellpadding="5" cellspacing="5" align="center" width="90%">
			<tr>
				<td colspan="4"><b>Year 2014</b></td>
			</tr>
			<tr>
				<td>Jan 2014: <input type="text" maxlength="4" size="4" name="jan_2014" value="<?php echo $jan_2014; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='jan_2014'></div></td>
				<td>Feb 2014: <input type="text" maxlength="4" size="4" name="feb_2014" value="<?php echo $feb_2014; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='feb_2014'></div></td>
				<td>Mar 2014: <input type="text" maxlength="4" size="4" name="mar_2014" value="<?php echo $mar_2014; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='mar_2014'></div></td>
				<td>Apr 2014: <input type="text" maxlength="4" size="4" name="apr_2014" value="<?php echo $apr_2014; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='apr_2014'></div></td>
			</tr>
			<tr>
				<td>May 2014: <input type="text" maxlength="4" size="4" name="may_2014" value="<?php echo $may_2014; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='may_2014'></div></td>
				<td>Jun 2014: <input type="text" maxlength="4" size="4" name="jun_2014" value="<?php echo $jun_2014; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='jun_2014'></div></td>
				<td>Jul 2014: <input type="text" maxlength="4" size="4" name="jul_2014" value="<?php echo $jul_2014; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='jul_2014'></div></td>
				<td>Aug 2014: <input type="text" maxlength="4" size="4" name="aug_2014" value="<?php echo $aug_2014; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='aug_2014'></div></td>
			</tr>
			<tr>
				<td>Sep 2014: <input type="text" maxlength="4" size="4" name="sep_2014" value="<?php echo $sep_2014; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='sep_2014'></div></td>
				<td>Oct 2014: <input type="text" maxlength="4" size="4" name="oct_2014" value="<?php echo $oct_2014; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='oct_2014'></div></td>
				<td>Nov 2014: <input type="text" maxlength="4" size="4" name="nov_2014" value="<?php echo $nov_2014; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='nov_2014'></div></td>
				<td>Dec 2014: <input type="text" maxlength="4" size="4" name="dec_2014" value="<?php echo $dec_2014; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='dec_2014'></div></td>
			</tr>
		</table>
	</td>
</tr>
</table>
<br>
<table cellpadding="5" cellspacing="5" align="center" border="1" width="90%">
<tr>
	<td>
		<table cellpadding="5" cellspacing="5" align="center" width="90%">
			<tr>
				<td colspan="4"><b>Year 2015</b></td>
			</tr>
			<tr>
				<td>Jan 2015: <input type="text" maxlength="4" size="4" name="jan_2015" value="<?php echo $jan_2015; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='jan_2015'></div></td>
				<td>Feb 2015: <input type="text" maxlength="4" size="4" name="feb_2015" value="<?php echo $feb_2015; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='feb_2015'></div></td>
				<td>Mar 2015: <input type="text" maxlength="4" size="4" name="mar_2015" value="<?php echo $mar_2015; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='mar_2015'></div></td>
				<td>Apr 2015: <input type="text" maxlength="4" size="4" name="apr_2015" value="<?php echo $apr_2015; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='apr_2015'></div></td>
			</tr>
			<tr>
				<td>May 2015: <input type="text" maxlength="4" size="4" name="may_2015" value="<?php echo $may_2015; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='may_2015'></div></td>
				<td>Jun 2015: <input type="text" maxlength="4" size="4" name="jun_2015" value="<?php echo $jun_2015; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='jun_2015'></div></td>
				<td>Jul 2015: <input type="text" maxlength="4" size="4" name="jul_2015" value="<?php echo $jul_2015; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='jul_2015'></div></td>
				<td>Aug 2015: <input type="text" maxlength="4" size="4" name="aug_2015" value="<?php echo $aug_2015; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='aug_2015'></div></td>
			</tr>
			<tr>
				<td>Sep 2015: <input type="text" maxlength="4" size="4" name="sep_2015" value="<?php echo $sep_2015; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='sep_2015'></div></td>
				<td>Oct 2015: <input type="text" maxlength="4" size="4" name="oct_2015" value="<?php echo $oct_2015; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='oct_2015'></div></td>
				<td>Nov 2015: <input type="text" maxlength="4" size="4" name="nov_2015" value="<?php echo $nov_2015; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='nov_2015'></div></td>
				<td>Dec 2015: <input type="text" maxlength="4" size="4" name="dec_2015" value="<?php echo $dec_2015; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='dec_2015'></div></td>
			</tr>
		</table>
	</td>
</tr>
</table>
<br>
<table cellpadding="5" cellspacing="5" align="center" border="1" width="90%">
<tr>
	<td>
		<table cellpadding="5" cellspacing="5" align="center" width="90%">
			<tr>
				<td colspan="4"><b>Year 2016</b></td>
			</tr>
			<tr>
				<td>Jan 2016: <input type="text" maxlength="4" size="4" name="jan_2016" value="<?php echo $jan_2016; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='jan_2016'></div></td>
				<td>Feb 2016: <input type="text" maxlength="4" size="4" name="feb_2016" value="<?php echo $feb_2016; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='feb_2016'></div></td>
				<td>Mar 2016: <input type="text" maxlength="4" size="4" name="mar_2016" value="<?php echo $mar_2016; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='mar_2016'></div></td>
				<td>Apr 2016: <input type="text" maxlength="4" size="4" name="apr_2016" value="<?php echo $apr_2016; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='apr_2016'></div></td>
			</tr>
			<tr>
				<td>May 2016: <input type="text" maxlength="4" size="4" name="may_2016" value="<?php echo $may_2016; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='may_2016'></div></td>
				<td>Jun 2016: <input type="text" maxlength="4" size="4" name="jun_2016" value="<?php echo $jun_2016; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='jun_2016'></div></td>
				<td>Jul 2016: <input type="text" maxlength="4" size="4" name="jul_2016" value="<?php echo $jul_2016; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='jul_2016'></div></td>
				<td>Aug 2016: <input type="text" maxlength="4" size="4" name="aug_2016" value="<?php echo $aug_2016; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='aug_2016'></div></td>
			</tr>
			<tr>
				<td>Sep 2016: <input type="text" maxlength="4" size="4" name="sep_2016" value="<?php echo $sep_2016; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='sep_2016'></div></td>
				<td>Oct 2016: <input type="text" maxlength="4" size="4" name="oct_2016" value="<?php echo $oct_2016; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='oct_2016'></div></td>
				<td>Nov 2016: <input type="text" maxlength="4" size="4" name="nov_2016" value="<?php echo $nov_2016; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='nov_2016'></div></td>
				<td>Dec 2016: <input type="text" maxlength="4" size="4" name="dec_2016" value="<?php echo $dec_2016; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='dec_2016'></div></td>
			</tr>
		</table>
	</td>
</tr>
</table>
<br>
<table cellpadding="5" cellspacing="5" align="center" border="1" width="90%">
<tr>
	<td>
		<table cellpadding="5" cellspacing="5" align="center" width="90%">
			<tr>
				<td colspan="4"><b>Year 2017</b></td>
			</tr>
			<tr>
				<td>Jan 2017: <input type="text" maxlength="4" size="4" name="jan_2017" value="<?php echo $jan_2017; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='jan_2017'></div></td>
				<td>Feb 2017: <input type="text" maxlength="4" size="4" name="feb_2017" value="<?php echo $feb_2017; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='feb_2017'></div></td>
				<td>Mar 2017: <input type="text" maxlength="4" size="4" name="mar_2017" value="<?php echo $mar_2017; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='mar_2017'></div></td>
				<td>Apr 2017: <input type="text" maxlength="4" size="4" name="apr_2017" value="<?php echo $apr_2017; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='apr_2017'></div></td>
			</tr>
			<tr>
				<td>May 2017: <input type="text" maxlength="4" size="4" name="may_2017" value="<?php echo $may_2017; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='may_2017'></div></td>
				<td>Jun 2017: <input type="text" maxlength="4" size="4" name="jun_2017" value="<?php echo $jun_2017; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='jun_2017'></div></td>
				<td>Jul 2017: <input type="text" maxlength="4" size="4" name="jul_2017" value="<?php echo $jul_2017; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='jul_2017'></div></td>
				<td>Aug 2017: <input type="text" maxlength="4" size="4" name="aug_2017" value="<?php echo $aug_2017; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='aug_2017'></div></td>
			</tr>
			<tr>
				<td>Sep 2017: <input type="text" maxlength="4" size="4" name="sep_2017" value="<?php echo $sep_2017; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='sep_2017'></div></td>
				<td>Oct 2017: <input type="text" maxlength="4" size="4" name="oct_2017" value="<?php echo $oct_2017; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='oct_2017'></div></td>
				<td>Nov 2017: <input type="text" maxlength="4" size="4" name="nov_2017" value="<?php echo $nov_2017; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='nov_2017'></div></td>
				<td>Dec 2017: <input type="text" maxlength="4" size="4" name="dec_2017" value="<?php echo $dec_2017; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='dec_2017'></div></td>
			</tr>
		</table>
	</td>
</tr>
</table>
<br>
<table cellpadding="5" cellspacing="5" align="center" border="1" width="90%">
<tr>
	<td>
		<table cellpadding="5" cellspacing="5" align="center" width="90%">
			<tr>
				<td colspan="4"><b>Year 2018</b></td>
			</tr>
			<tr>
				<td>Jan 2018: <input type="text" maxlength="4" size="4" name="jan_2018" value="<?php echo $jan_2018; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='jan_2018'></div></td>
				<td>Feb 2018: <input type="text" maxlength="4" size="4" name="feb_2018" value="<?php echo $feb_2018; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='feb_2018'></div></td>
				<td>Mar 2018: <input type="text" maxlength="4" size="4" name="mar_2018" value="<?php echo $mar_2018; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='mar_2018'></div></td>
				<td>Apr 2018: <input type="text" maxlength="4" size="4" name="apr_2018" value="<?php echo $apr_2018; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='apr_2018'></div></td>
			</tr>
			<tr>
				<td>May 2018: <input type="text" maxlength="4" size="4" name="may_2018" value="<?php echo $may_2018; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='may_2018'></div></td>
				<td>Jun 2018: <input type="text" maxlength="4" size="4" name="jun_2018" value="<?php echo $jun_2018; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='jun_2018'></div></td>
				<td>Jul 2018: <input type="text" maxlength="4" size="4" name="jul_2018" value="<?php echo $jul_2018; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='jul_2018'></div></td>
				<td>Aug 2018: <input type="text" maxlength="4" size="4" name="aug_2018" value="<?php echo $aug_2018; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='aug_2018'></div></td>
			</tr>
			<tr>
				<td>Sep 2018: <input type="text" maxlength="4" size="4" name="sep_2018" value="<?php echo $sep_2018; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='sep_2018'></div></td>
				<td>Oct 2018: <input type="text" maxlength="4" size="4" name="oct_2018" value="<?php echo $oct_2018; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='oct_2018'></div></td>
				<td>Nov 2018: <input type="text" maxlength="4" size="4" name="nov_2018" value="<?php echo $nov_2018; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='nov_2018'></div></td>
				<td>Dec 2018: <input type="text" maxlength="4" size="4" name="dec_2018" value="<?php echo $dec_2018; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='dec_2018'></div></td>
			</tr>
		</table>
	</td>
</tr>
</table>
<br>
<table cellpadding="5" cellspacing="5" align="center" border="1" width="90%">
<tr>
	<td>
		<table cellpadding="5" cellspacing="5" align="center" width="90%">
			<tr>
				<td colspan="4"><b>Year 2019</b></td>
			</tr>
			<tr>
				<td>Jan 2019: <input type="text" maxlength="4" size="4" name="jan_2019" value="<?php echo $jan_2019; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='jan_2019'></div></td>
				<td>Feb 2019: <input type="text" maxlength="4" size="4" name="feb_2019" value="<?php echo $feb_2019; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='feb_2019'></div></td>
				<td>Mar 2019: <input type="text" maxlength="4" size="4" name="mar_2019" value="<?php echo $mar_2019; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='mar_2019'></div></td>
				<td>Apr 2019: <input type="text" maxlength="4" size="4" name="apr_2019" value="<?php echo $apr_2019; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='apr_2019'></div></td>
			</tr>
			<tr>
				<td>May 2019: <input type="text" maxlength="4" size="4" name="may_2019" value="<?php echo $may_2019; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='may_2019'></div></td>
				<td>Jun 2019: <input type="text" maxlength="4" size="4" name="jun_2019" value="<?php echo $jun_2019; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='jun_2019'></div></td>
				<td>Jul 2019: <input type="text" maxlength="4" size="4" name="jul_2019" value="<?php echo $jul_2019; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='jul_2019'></div></td>
				<td>Aug 2019: <input type="text" maxlength="4" size="4" name="aug_2019" value="<?php echo $aug_2019; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='aug_2019'></div></td>
			</tr>
			<tr>
				<td>Sep 2019: <input type="text" maxlength="4" size="4" name="sep_2019" value="<?php echo $sep_2019; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='sep_2019'></div></td>
				<td>Oct 2019: <input type="text" maxlength="4" size="4" name="oct_2019" value="<?php echo $oct_2019; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='oct_2019'></div></td>
				<td>Nov 2019: <input type="text" maxlength="4" size="4" name="nov_2019" value="<?php echo $nov_2019; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='nov_2019'></div></td>
				<td>Dec 2019: <input type="text" maxlength="4" size="4" name="dec_2019" value="<?php echo $dec_2019; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='dec_2019'></div></td>
			</tr>
		</table>
	</td>
</tr>
</table>
<br>
<table cellpadding="5" cellspacing="5" align="center" border="1" width="90%">
<tr>
	<td>
		<table cellpadding="5" cellspacing="5" align="center" width="90%">
			<tr>
				<td colspan="4"><b>Year 2020</b></td>
			</tr>
			<tr>
				<td>Jan 2020: <input type="text" maxlength="4" size="4" name="jan_2020" value="<?php echo $jan_2020; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='jan_2020'></div></td>
				<td>Feb 2020: <input type="text" maxlength="4" size="4" name="feb_2020" value="<?php echo $feb_2020; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='feb_2020'></div></td>
				<td>Mar 2020: <input type="text" maxlength="4" size="4" name="mar_2020" value="<?php echo $mar_2020; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='mar_2020'></div></td>
				<td>Apr 2020: <input type="text" maxlength="4" size="4" name="apr_2020" value="<?php echo $apr_2020; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='apr_2020'></div></td>
			</tr>
			<tr>
				<td>May 2020: <input type="text" maxlength="4" size="4" name="may_2020" value="<?php echo $may_2020; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='may_2020'></div></td>
				<td>Jun 2020: <input type="text" maxlength="4" size="4" name="jun_2020" value="<?php echo $jun_2020; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='jun_2020'></div></td>
				<td>Jul 2020: <input type="text" maxlength="4" size="4" name="jul_2020" value="<?php echo $jul_2020; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='jul_2020'></div></td>
				<td>Aug 2020: <input type="text" maxlength="4" size="4" name="aug_2020" value="<?php echo $aug_2020; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='aug_2020'></div></td>
			</tr>
			<tr>
				<td>Sep 2020: <input type="text" maxlength="4" size="4" name="sep_2020" value="<?php echo $sep_2020; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='sep_2020'></div></td>
				<td>Oct 2020: <input type="text" maxlength="4" size="4" name="oct_2020" value="<?php echo $oct_2020; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='oct_2020'></div></td>
				<td>Nov 2020: <input type="text" maxlength="4" size="4" name="nov_2020" value="<?php echo $nov_2020; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='nov_2020'></div></td>
				<td>Dec 2020: <input type="text" maxlength="4" size="4" name="dec_2020" value="<?php echo $dec_2020; ?>" onkeypress="return isNumberKey(event)" onblur="update(this.name,this.value);"> <div id='dec_2020'></div></td>
			</tr>
		</table>
	</td>
</tr>
</table>
</body>
</html>

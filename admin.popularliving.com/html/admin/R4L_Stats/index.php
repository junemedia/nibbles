<?php

while (list($key,$val) = each($_GET)) {
	$$key = $val;
}
while (list($key,$val) = each($_POST)) {
	$$key = $val;
}

mysql_pconnect ("mydb01.amperemedia.com", "nibbles", "#a!!yu5");
mysql_select_db ("nibbles_temp");

if ($sFromDate == '') {
	$sFromDate = date('Y-m-d', mktime(0,0,0,date("m"),date("d")-1,date("Y")));
	$campbells1 = 'yes';
}
if ($sToDate == '') {
	$sToDate = date('Y-m-d', mktime(0,0,0,date("m"),date("d")-1,date("Y")));
	$campbells1 = 'yes';
}

if ($sort_by == '') {
	$sort_by = 'ORDER BY count DESC';
}

$campbells1_filter = '';
if ($campbells1 == 'yes') {
	$campbells1_filter = " AND title IN ('Pennsylvania Dutch Ham & Noodle Casserole','Hearty Chicken & Noodle Casserole','Turkey and Stuffing Casserole',
				'Broccoli & Cheese Casserole','Swiss Vegetable Casserole','Green Bean Casserole','Cheesy Chicken & Rice Casserole','Fiesta Chicken Casserole',
				'Country Turkey Casserole','Country Chicken Casserole','Crowd-Pleasing Vegetable Casserole','Tuna Noodle Casserole','Super Chicken Casserole',
				'Chicken Pasta & Vegetable Casserole','Hearty Sausage & Rice Casserole','Cheddar Potato Casserole','Jill\'s Hash Brown Casserole','Gumbo Casserole',
				'Southwestern Chicken & Rice Casserole','Tempting Tetrazzini Casserole','Beefy Cornbread Casserole','Squash Casserole','Easy Chicken Pot Pie',
				'Easy Chicken & Cheese Enchiladas','Beef Taco Skillet','Beef Stroganoff','Pennsylvania Dutch Ham & Noodle Casserole','Best Ever Meatloaf','French Onion Burgers',
				'Hearty Chicken & Noodle Casserole','Slow-Cooked Pulled Pork Sandwiches','Picante Chicken Quesadillas','Souper Sloppy Joes','Melt-In-Your-Mouth Short Ribs',
				'Chicken & Broccoli Alfredo','Beef & Mushroom Dijon','One Dish Chicken & Stuffing Bake','Chicken Quesadillas & Fiesta Rice','Buffalo Burgers',
				'Quick Barbecued Beef Sandwiches','Cheesy Chicken & Rice Casserole','Tuna Noodle Casserole','One Dish Chicken & Rice Bake','Slow Cooker Savory Pot Roast',
				'15-Minute Chicken & Rice Dinner','Quick & Easy Chicken, Broccoli & Brown Rice','Slow-Cooker Chicken & Dumplings','Paprika Chicken with Sour Cream Gravy',
				'Beef Stroganoff','Country Scalloped Potatoes','Beef Taco Bake','Chicken & Stuffing Skillet','Chicken & Cheese Enchiladas','Chicken in Creamy Sun-Dried Tomato Sauce',
				'Turkey Broccoli Divan','Chicken & Broccoli Alfredo for a Crowd','Oven-Baked Risotto','Turkey & Broccoli Alfredo','Easy Turkey Pot Pie','Beef & Mushroom Lasagna',
				'Chicken with Sun-Dried Tomatoes','Golden Mushroom Pork & Apples') ";
}



$campbells2_filter = '';
if ($campbells2 == 'yes') {
	$campbells2_filter = " AND title IN ('Creamy Dijon Chicken with Rice','Chicken Crunch','White Chicken Chili','Sweet & Tangy Grilled Chicken Salad','Polynesian Pork Chops',
				'One-Dish Chicken & Stuffing Bake','Crowd-Pleasing Tuna Casserole','Buffalo Chicken Dip','Baked Macaroni and Cheese','Skillet Mac & Beef',
				'Slow Cooker Hearty Beef & Bean Chili','Slow Cooker Tuscan Beef Stew','Quick Creamy Chicken & Noodles','Southwestern Chicken & White Bean Soup',
				'Broccoli and Pasta Bianco','Chicken Mozzarella','Herbed Pork Chops in Mushroom Sauce','Lemon Broccoli Chicken','Creamy Mustard Pork Chops',
				'Slow-Cooker Chicken Cacciatore','Zesty Slow-Cooker Italian Pot Roast','Creamy Ranch Pork Chops & Rice','Stroganoff-Style Chicken','Skillet Garlic Chicken',
				'Tasty 2-Step Chicken','Polynesian Pork Chops','Classic Tuna Noodle Casserole','Pork Chops & Stuffing Bake','Mushroom-Garlic Pork Chops',
				'Savory Pot Roast','Speedy Chicken Enchiladas','Ham Asparagus Gratin','Campbell\'s Skillet Chicken & Broccoli','Honey-Barbecued Ribs',
				'Creamy Pesto Chicken & Bow Ties','2-Step Skillet Chicken Broccoli Divan','Chicken Caesar Pasta Bowl','Quick & Easy Dinner Nachos Supreme',
				'Spicy Grilled Quesadillas','Shortcut Stroganoff','Slow Cooker Beef & Mushroom Stew','Tomato-Basil Chicken','Simply Delicious Meat Loaf & Gravy',
				'3-Cheese Pasta Bake','Apricot Glazed Pork Roast','Chicken in Savory Lemon Sauce','Cheddar Broccoli Frittata','Smothered Pork Chops',
				'Campbell\'s Beef Bourguignonne','Herb Roasted Chicken & Vegetables','Crunchy No-Fry Chicken') ";
}




$title_filter = '';
if ($temp_title != '') {
	$title_filter = " AND title LIKE \"%$temp_title%\" ";
}


$sReportQuery = "SELECT * FROM RecipesDisplayHistory WHERE dateAdded BETWEEN '$sFromDate' AND '$sToDate' 
				$title_filter
				$campbells1_filter
				$campbells2_filter
				$sort_by ";
echo "<!-- ".$sReportQuery." -->";


$rReportResult = mysql_query($sReportQuery);
$sReportContent =  "<tr>
							<td><b>Date</b></td>
							<td><b>Title</b></td>
							<td><b>Print Count</b></td>
							<td><b>URL</b></td>
						</tr>";
echo mysql_error();
$iCount = 0;
while ($oReportRow = mysql_fetch_object($rReportResult)) {
	if ($sBgcolorClass=="#E6E6FA") {
		$sBgcolorClass="#FFFACD";
	} else {
		$sBgcolorClass="#E6E6FA";
	}
	$sReportContent .=  "<tr bgcolor=$sBgcolorClass><!-- $oReportRow->id -->
							<td>$oReportRow->dateAdded</td>
							<td>$oReportRow->title</td>
							<td>$oReportRow->count</td>
							<td>$oReportRow->url</td>
						</tr>";
	$iCount += $oReportRow->count;
}



?>

<html>
<head>
<title>Recipe4Living Recipe View Stats</title>
<LINK rel="stylesheet" href="http://admin.popularliving.com/admin/styles.css" type="text/css" >
</head>
<body>
<center>
<table width="85%">
<tr>
<td align ="center">
<img src = "http://admin.popularliving.com/admin/nibbles_header.gif">
</td>
</tr>
</table>
</center>
<br>
<center><a href='http://admin.popularliving.com/admin/index.php?SID' class=menulink>Return to Nibbles Main Menu</a><BR><BR></center>
<table align=center width=85%><tr><td align=center class=header></td></tr></table>
<table width=85% align=center><tr><td align=left><a href=JavaScript:history.go(-1);>Back</a></td><Td align=right>
&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;Logged In : spatel</td></tr>
<tr><Td class=message align=center colspan=2></td></tr>
</table>
<form name=form1 action='<?php echo $_SERVER['PHP_SELF'];?>'>
<table cellpadding=5 cellspacing=0 bgcolor=	#FFE4B5 width=95% align=center>
	<tr><td colspan=4><b><a href="today.php">Display Today's Stats</a></b>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="checkbox" name='campbells1' value="yes" <?php if ($campbells1 == 'yes') { echo 'checked'; }?>> Campbells Only (1)
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="checkbox" name='campbells2' value="yes" <?php if ($campbells2 == 'yes') { echo 'checked'; }?>> Campbells Only (2)
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<b>Title Search: </b> 
			<input type="text" name="temp_title" value="<?php echo $temp_title; ?>">
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			
			Sort Report By <select name="sort_by">
					<option value='ORDER BY count DESC' <?php if ($sort_by == '' || $sort_by == 'ORDER BY count DESC') { echo 'selected'; } ?>>Count DESC</option>
					<option value='ORDER BY count ASC' <?php if ($sort_by == 'ORDER BY count ASC') { echo 'selected'; } ?>>Count ASC</option>
					<option value='ORDER BY title DESC' <?php if ($sort_by == 'ORDER BY title DESC') { echo 'selected'; } ?>>Title DESC</option>
					<option value='ORDER BY title ASC' <?php if ($sort_by == 'ORDER BY title ASC') { echo 'selected'; } ?>>Title ASC</option>
					</select>
		</td>
	</tr>
	<tr id='date_range'>
			<td><b>Date From</b></td>
			<td><input type="text" maxlength="10" name='sFromDate' value="<?php echo $sFromDate; ?>" size="10"> (yyyy-mm-dd)</td>
			<td><b>Date To</b></td>
			<td><input type="text" maxlength="10" name='sToDate' value="<?php echo $sToDate; ?>" size="10"> (yyyy-mm-dd)</td>
	</tr>
	<tr><td colspan=2><input type="submit" name=sSubmit value='View Report'> (Total: <?php echo $iCount; ?>)</td>
		<td colspan=2></td></tr>
</table>
</form>
<table cellpadding=5 cellspacing=0 width=85% align=center bgcolor=#FFFFFF>
<?php echo $sReportContent; ?>
<tr><td colspan="2">Total</td><td colspan="2"><?php echo $iCount; ?></td></tr>
</table>
</body>
</html>

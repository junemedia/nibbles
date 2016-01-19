<?php

include("../../../includes/paths.php");

session_start();
$sList = '';

mysql_select_db('newsletter_templates');

$sSelectQuery = "SELECT * FROM slots ORDER BY id DESC LIMIT 300";
$rSelectResult = dbQuery($sSelectQuery);
while ($oRow = dbFetchObject($rSelectResult)) {
	// For alternate background color
	if ($sBgcolorClass == "ODD") {
		$sBgcolorClass = "EVEN";
	} else {
		$sBgcolorClass = "ODD";
	}
	
	$subject = '';
	if ($oRow->subject != '') {
		$subject = "&nbsp;&nbsp;&nbsp;<b>".$oRow->subject."</b>";
	}
	$mailing_date = '';
	if ($oRow->mailing_date != '0000-00-00') {
		$mailing_date = "&nbsp;&nbsp;&nbsp;<b>[".$oRow->mailing_date."]</b>";
	}
	$enable = '';
	if ($oRow->enable == 'Y') {
		$enable = " <b><font color='Red'>LIVE</font></b> ";
	}
	
	$sList .= "<tr class=$sBgcolorClass><TD>$oRow->name ($oRow->title){$subject}{$mailing_date}</td>
					<TD>{$enable}<a href='slots.php?iMenuId=$iMenuId&iId=$oRow->id'>Edit</a>
					</td></tr>";
}

if (dbNumRows($rSelectResult) == 0) {
	$sMessage = "No Records Exist...";
}
	
// Hidden fields to be passed with form submission
$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
		<input type=hidden name=iId value='$iId'>";

$sAddButton = "<table border='0' align='center' cellspacing='5' cellpadding='5'>
<tr>
	<td><b>Recipe4Living:</b></td>
	<td><a href='slots.php?iMenuId=$iMenuId&prefill=Y&title=R4L_Recipe4Living'><font color='blue'><b>Recipe4Living</b></font></a></td>
	<td><a href='slots.php?iMenuId=$iMenuId&prefill=Y&title=R4L_BudgetCooking'><font color='blue'><b>Budget Cooking</b></font></a></td>
	<td><a href='slots.php?iMenuId=$iMenuId&prefill=Y&title=R4L_QuickEasy'><font color='blue'><b>Quick Easy</b></font></a></td>
	<td><a href='slots.php?iMenuId=$iMenuId&prefill=Y&title=R4L_PartyRecipesTip'><font color='blue'><b>Party Recipes Tip</b></font></a></td>
	<td><a href='slots.php?iMenuId=$iMenuId&prefill=Y&title=R4L_Crockpot'><font color='blue'><b>Crockpot</b></font></a></td>
	<td><a href='slots.php?iMenuId=$iMenuId&prefill=Y&title=R4L_Casserole'><font color='blue'><b>Casserole</b></font></a></td>
	<td><a href='slots.php?iMenuId=$iMenuId&prefill=Y&title=R4L_Copycat'><font color='blue'><b>Copycat</b></font></a></td>
	<td><a href='slots.php?iMenuId=$iMenuId&prefill=Y&title=R4L_Diabetic'><font color='blue'><b>Diabetic</b></font></a></td>
	<td><a href='slots.php?iMenuId=$iMenuId&prefill=Y&title=R4L_Solo'><font color='green'><b>Solo</b></font></a></td>
</tr>
<tr>
	<td><b>FitAndFabLiving:</b></td>
	<td><a href='slots.php?iMenuId=$iMenuId&prefill=Y&title=FF_DailyInsider'><font color='blue'><b>Daily Insider</b></font></a></td>
	<td><a href='slots.php?iMenuId=$iMenuId&prefill=Y&title=FF_DietInsider'><font color='blue'><b>Diet Insider</b></font></a></td>
	<!--
	<td><a href='slots.php?iMenuId=$iMenuId&prefill=Y&title=FF_FitFabLiving'><font color='blue'><b>FitFabLiving</b></font></a></td>
	<td><a href='slots.php?iMenuId=$iMenuId&prefill=Y&title=FF_FitnessInsider'><font color='blue'><b>Fitness Insider</b></font></a></td>
	<td><a href='slots.php?iMenuId=$iMenuId&prefill=Y&title=FF_BeautyInsider'><font color='blue'><b>Beauty Insider</b></font></a></td>
	-->
	<td><a href='slots.php?iMenuId=$iMenuId&prefill=Y&title=FF_Solo'><font color='green'><b>Solo</b></font></a></td>
	<td colspan='6'>&nbsp;</td>
</tr>
<tr>
	<td><b>WorkItMom:</b></td>
	<td><a href='slots.php?iMenuId=$iMenuId&prefill=Y&title=WIM_MakingItWork'><font color='blue'><b>Making It Work</b></font></a></td>
	<td><a href='slots.php?iMenuId=$iMenuId&prefill=Y&title=WIM_Solo'><font color='green'><b>Solo</b></font></a></td>
	<td colspan='6'>&nbsp;</td>
</tr>
<tr>
	<td><b>Recipe4Living (v2)[slot #1 to #5]:</b></td>
	<td><a href='slots.php?iMenuId=$iMenuId&prefill=Y&title=R4L_DailyRecipes'><font color='blue'><b>Daily Recipes</b></font></a></td>
	<td><a href='slots.php?iMenuId=$iMenuId&prefill=Y&title=R4L_BudgetCooking2'><font color='blue'><b>Budget Cooking</b></font></a></td>
	<td><a href='slots.php?iMenuId=$iMenuId&prefill=Y&title=R4L_Casserole2'><font color='blue'><b>Casserole</b></font></a></td>
	<td><a href='slots.php?iMenuId=$iMenuId&prefill=Y&title=R4L_Copycat2'><font color='blue'><b>Copycat</b></font></a></td>
	<td><a href='slots.php?iMenuId=$iMenuId&prefill=Y&title=R4L_Crockpot2'><font color='blue'><b>Crockpot</b></font></a></td>
	<td><a href='slots.php?iMenuId=$iMenuId&prefill=Y&title=R4L_QuickEasy2'><font color='blue'><b>Quick & Easy</b></font></a></td>
	<td><a href='slots.php?iMenuId=$iMenuId&prefill=Y&title=R4L_Diabetic2'><font color='blue'><b>Diabetic</b></font></a></td>
	<td>&nbsp;</td>
</tr>
<tr>
	<td><b>FitAndFabLiving (v2):</b></td>
	<td><a href='slots.php?iMenuId=$iMenuId&prefill=Y&title=FF_DailyInsider2'><font color='blue'><b>Daily Insider</b></font></a></td>
	<td><a href='slots.php?iMenuId=$iMenuId&prefill=Y&title=FF_DietInsider2'><font color='blue'><b>Diet Insider</b></font></a></td>
	<td colspan='6'>&nbsp;</td>
</tr>
<tr>
	<td><b>WorkItMom (v2):</b></td>
	<td><a href='slots.php?iMenuId=$iMenuId&prefill=Y&title=WIM_MakingItWork2'><font color='blue'><b>Making It Work</b></font></a></td>
	<td colspan='7'>&nbsp;</td>
</tr>
<tr>
	<td><b>SavvyFork:</b></td>
	<td><a href='slots.php?iMenuId=$iMenuId&prefill=Y&title=SF_Feed'><font color='blue'><b>SF, The Feed</b></font></a></td>
	<td colspan='7'>&nbsp;</td>
</tr>

<tr>
	<td><b>JuneExchange:</b></td>
	<td><a href='slots.php?iMenuId=$iMenuId&prefill=Y&title=JE_JuneExchange'><font color='blue'><b>June Exchange</b></font></a></td>
	<td colspan='7'>&nbsp;</td>
</tr>



</table><br><br>";

include("../../../includes/adminHeader.php");	

?>

<form name=form1 action='<?php echo $_SERVER['PHP_SELF'];?>'>
<?php echo $sHidden;?>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=75% align=center border="0">
<tr><td colspan=2 align="right"><?php echo $sAddButton;?></td></tr>
<tr><td class='header' colspan="2" align="center">Slots Management</td>
</tr>
<?php echo $sList;?>
</table>

</form>

<table cellpadding=5 cellspacing=0 width=75% align=center border="0">
<tr>
	<td><b>Note:</b>  This page will ONLY display last 300 issues.  If you need access to older issues, please contact Samir.</td>
</tr>
</table>
	
<?php
	include("../../../includes/adminFooter.php");
?>


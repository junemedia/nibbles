<?php

include("../includes/paths.php");

session_start();

$sStatsInsertQuery = "INSERT INTO popupDisplayStats(offerId, displayDate)
					  VALUES('AMP9992', CURRENT_DATE)";
$rStatsInsertResult = dbQuery($sStatsInsertQuery);
echo dbError();


?>

<html>

<head>

<script language="JavaScript">

<!--
function MM_openBrWindow(theURL,winName,features) { //v2.0
  window.open(theURL,winName,features);
}
//-->

</script>


<title></title>

<!-- hide this from tired old Browsers  if (window.location != top.location)   
{top.location.href=window.location} // -->


</head>


<body>
<table width=720 align=top border=0>

<TR>
    <TD width="50%" height=3>
      <P align=center><A href="http://bd.myfree.com/r/r.php?src=amptb050304035"><IMG 
      height=400
      src="<?php echo $sGblSiteRoot;?>/pops/images/500x400yw_countdown2.gif" 
      width=500 border=0 NOSEND="1"></A> 
</td></tr></table>

</body>

</html>



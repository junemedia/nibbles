<!--#include virtual="site_funcs.asp"-->

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


<%
dim conn
'INSERT INTO CLICK TRACKING
OpenDb()

sql = "insert into RedirectsTracking (offerid) values('AMP8888')"
conn.execute(sql)

CloseDb()
%>

<body>

<meta http-equiv="refresh" content="1;URL=http://www.popularliving.com/c/youWon/index.php?src=ampywredpop">

</body>

</html>



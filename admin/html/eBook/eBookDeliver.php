<?php

include("../includes/paths.php");

if ($src) {
	$s = $src;
}
// entry in stats table
// check if entry for same data and sourcecode
$checkQuery = "SELECT *
				FROM   ebook
				WHERE  displayDate = CURRENT_DATE
				AND    sourceCode = '$s'";
$checkResult = mysql_query($checkQuery);
echo mysql_error();
if ( mysql_num_rows($checkResult) == 0 ) {
	$insertQuery = "INSERT INTO ebook(displayDate, sourceCode, counts)
				VALUES(CURRENT_DATE, '$s', 1)";
	$insertResult = mysql_query($insertQuery);
	echo mysql_error();
} else {
	// update the count
	$updateQuery = "UPDATE ebook
					SET    counts = counts+1
					WHERE  displayDate = CURRENT_DATE
					AND   sourceCode = '$s'";
	$updateResult = mysql_query($updateQuery) ;
}


echo "<script language=JavaScript>	
	var popup = window.open(\"http://www.popularliving.com/p/b2b.php?src=$s\",\"popup\",\"width=550, height=350, scrollbars=yes, resizable=yes, location=yes, menubar=yes, toolbar=yes, status=yes\");
	window.location.href='eBookDownload.php?t=getpaid&s=$s';
	window.popoup.focus();
	</script>";
?>

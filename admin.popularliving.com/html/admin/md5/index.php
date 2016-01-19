<?php

ini_set('max_execution_time', 5000);

include("../../includes/paths.php");
mysql_select_db ('utility');


function getTime() {
	$a = explode (' ',microtime());
	return(double) $a[0] + $a[1];
}
$Start = getTime();


$target = "upload/";
$target = $target . basename( $_FILES['uploaded']['name']);
$sFileName = 'MD5_'.basename( $_FILES['uploaded']['name']);
$delete = unlink("$target");
$ok=1;

if (!($_FILES['uploaded']['type']=="text/plain")) {
	echo "<br><br>*** You may only upload .txt files. ***<br><br>";
	$ok=0;
}
$records = 0;
if ($ok == 1) {
	if(move_uploaded_file($_FILES['uploaded']['tmp_name'], $target)) {
		$truncate = mysql_query("TRUNCATE TABLE emails2convert;");
			
		$rFileGuy = @fopen($target,'r');
		if ($rFileGuy) {
			$sInsertQuery = 'INSERT INTO emails2convert (email) VALUES ';
			$x = 0;
			while (!feof($rFileGuy)) {
				$aEmails = array();
				for($j=0;(($j<500)&&(!feof($rFileGuy)));$j++) {
					$sLine = fgets($rFileGuy, 1024);
					$sLine = addslashes(trim($sLine));
					array_push($aEmails,'(\''.rtrim($sLine).'\')');
					$records++;
				}
				$sQuery = $sInsertQuery.join(',',$aEmails);
				$rTempInsertResult = mysql_query($sQuery);
				if ($x % 500 == 0) {
					echo ' ';@ob_flush();@flush();@ob_end_flush();@ob_start();
				}
				$x++;
			}
			fclose($rFileGuy);
		}
		$delete = unlink("$target");

		$get_emails = "SELECT * FROM emails2convert";
		$result_email = mysql_query($get_emails);
		echo mysql_error();
		$x = 0;
		while ($row = mysql_fetch_object($result_email)) {
			$md5 = md5(strtolower($row->email));
			$update = "UPDATE emails2convert SET md5='$md5' WHERE id='$row->id';";
			$update_result = mysql_query($update);
			echo mysql_error();
			if ($x % 1000 == 0) {
				echo ' ';@ob_flush();@flush();@ob_end_flush();@ob_start();
			}
			$x++;
		}
		echo "<br><br><b>Records Processed</b>: ".number_format($records)."-->  <a href='export.php?sFile=$sFileName' target=_blank><b>Export MD5</b></a><br><br>";
	} else {
		echo "Sorry, there was a problem uploading your file.";
	}
}

?>
<font size="3">
<b>Note:</b>  100MB max file size.  Split file if you have larger file. Upload text file only - one email per line.<br>
It may take time depending on file size and Internet upload speed.<br>
If input file (upload) is 55MB, the output file will be ~80MB (download)<br>
Script time estimate: It takes approx 25 mins to process 55MB file.<br>
Email addresses are automatically converted to lowercase before converting them to md5.<br><br><br>

</font>

<br><br><br>


<form enctype="multipart/form-data" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
Please choose a file: <input name="uploaded" type="file" /> (100MB max file size)<br />
<input type="submit" value="Convert To MD5" />
</form>
<br><br><br>
Step 1. Click on "<b>Browse</b>" and select text file you like to convert to md5 (must be text file - one email per line)<br>
Step 2. Click on "<b>Convert to MD5</b>" button and allow script some time to run<br>
Step 3. Click on "<b>Export MD5</b>" link once the file is processed to export data<br><br><br><br>


<font color="Red">Only <b>one</b> person can use this tool at a time.  If multiple people use this tool at the same time, it will mix up all the results.</font>

<br><br>
<?php

$End = getTime();
echo "<br><br><br>Time taken = ".number_format(($End - $Start),2)." secs"; 

?>
</font>

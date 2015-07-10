<?php

// script to download the file created  under temp dir through exporting report
include("includes/paths.php");
if ($sFile) {
	if ($sDownload) {
		$sExt = substr($sFile,strlen($sFile)-3,3);
		switch($sExt) {
			case "pdf":
				$rFpFile = fopen("$sGblWebRoot/temp/$sFile", "rb");
			break;
			case "xls":
				$rFpFile = fopen("$sGblWebRoot/temp/$sFile", "r");
			break;
			case "txt":
			default: 
				$rFpFile = fopen("$sGblWebRoot/temp/$sFile", "r");
			break;
		}

		if ($rFpFile) {
			if ($sExt == 'xls') {
				while (!feof($rFpFile)) {
					$sFileData .= fread($rFpFile, 1024);
				}
				fclose($rFpFile);
				header("Content-type: application/vnd.ms-excel");
				header("Content-Disposition: attachment; filename=$sFile");
				header("Content-Description: Excel output");
				header("Connection: close");
				echo $sFileData;
				// if didn't exit, all the html page content will be saved as excel file.
				exit();
			} else if ($sExt == 'pdf') {
				$sFile = "$sGblWebRoot/temp/$sFile";
				$fsize = filesize($sFile);
				$fname = basename ($sFile);
				header("Pragma: ");
				header("Cache-Control: ");
				header("Content-type: application/octet-stream");
				header("Content-Disposition: attachment; filename=\"".$fname."\"");
				header("Content-length: $fsize");
				header("Connection: close");
				fpassthru($rFpFile);
			} else if ($sExt == 'txt') {
				while (!feof($rFpFile)) {
					$sFileData .= fread($rFpFile, 1024);
				}
				fclose($rFpFile);
				header("Content-type: text/html");
				header("Content-Disposition: attachment; filename=$sFile");
				header("Content-Description: Text");
				header("Connection: close");
				echo $sFileData;
				// if didn't exit, all the html page content will be saved as excel file.
				exit();
			}
		}
	} else {
		?>
		<html>
		<head>
		<LINK rel="stylesheet" href="styles.css" type="text/css" >
		<title>Nibbles - Download File</title>
		</head>
		<body>
		<table width=100% border=0>
		<tr><td align=center><BR><a href='<?php echo "http://admin.popularliving.com/download.php?sDownload=download&sFile=$sFile";?>'><b><font size="2">Get File</font></b></a></td></tr>
		<tr><td><BR><BR></td></tr>
		<tr><td align=center><input type=button name=close value="Close Window" onClick="self.close();"></td></tr>
		</table>
		<?php
	}
}

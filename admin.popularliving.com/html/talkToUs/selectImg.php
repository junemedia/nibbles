<?php

$sOpenedDirectory = opendir("/home/sites/www.recipe4living.com/html/images/stockPhotos/");
$sListImages = "<table border=1 BORDERCOLOR='#0000FF' BORDERCOLORLIGHT='#33CCFF' BORDERCOLORDARK='#0000CC'><tr>";
$i = 0;
while ($sFile = readdir($sOpenedDirectory)) {
	if (stristr($sFile,'.jpg') || stristr($sFile,'.gif') || stristr($sFile,'.png')) {
		if ($i % 4 == 0) {
			$sListImages .= "</tr><tr>";
		}
		$sListImages .= "<td align=center>
			<img src='http://www.recipe4living.com/images/stockPhotos/$sFile'><br>
			<input type='radio' name='selectedPicture' value='$sFile' onclick=\"populateParent(this.value);\"> $sFile
			<td>";
		$i++;
	}
}

?>
<html><head><title>Select Image - Recipe4Living</title>
<script language='javascript'>
function populateParent(sFile) {
	window.opener.document.form1.selectedImage.value = sFile;
	alert("Your selected image has been attached.");
	window.close();
}
</script></head><body>
<b><font face="verdana">Simply select one image that goes best with your recipe.</font></b>
<?php echo $sListImages; ?>
</tr></table></body></html>

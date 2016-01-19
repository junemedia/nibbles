<html>
<head>
<title>Copy image to cloud</title>
<style>
* {
	font-family: verdana;
	font-size:12px;
}
</style>
<SCRIPT LANGUAGE=JavaScript SRC="http://r4l.popularliving.com/subctr/js/ajax.js" TYPE=text/javascript></script>
<script language="JavaScript">
function move_image_to_cloud(key) {
	if (document.getElementById(key).value != '') {
		if (document.getElementById(key).value.indexOf("media.campaigner.com") != -1) {
			return true;
		} else {
			if (document.getElementById(key).value.toUpperCase().indexOf(".JPG") != -1 || document.getElementById(key).value.toUpperCase().indexOf(".JPEG") != -1 || 
				document.getElementById(key).value.toUpperCase().indexOf(".GIF") != -1 || document.getElementById(key).value.toUpperCase().indexOf(".PNG") != -1) {
				response=coRegPopup.send('move_image_to_cloud.php?image='+document.getElementById(key).value,'');
				if (response.indexOf("media.campaigner.com") != -1) {
					document.getElementById(key).value = response.trim();
				}
			} else {
				return true;
			}
		}
	}
	return true;
}
</script>
</head>
<body>
<table align="center">
<tr><td><p>Supported file formats are: JPEG, JPG, GIF, and PNG</p>
<p>Enter image URL and hit "tab" button.  It will automatically upload image to Campaigner/Akamai/Cloud and return new image URL which is hosted on cloud.</p>
<p>Results are not saved, so make sure you copy new image URL before closing this window.</p>
<br><br>
Image 1: <input type="text" id="image1" name="image1" value="" onblur="move_image_to_cloud('image1');" size="75">
<br><br>
Image 2: <input type="text" id="image2" name="image2" value="" onblur="move_image_to_cloud('image2');" size="75">
<br><br>
Image 3: <input type="text" id="image3" name="image3" value="" onblur="move_image_to_cloud('image3');" size="75">
<br><br>
Image 4: <input type="text" id="image4" name="image4" value="" onblur="move_image_to_cloud('image4');" size="75">
<br><br>
Image 5: <input type="text" id="image5" name="image5" value="" onblur="move_image_to_cloud('image5');" size="75">
<br><br>
Image 6: <input type="text" id="image6" name="image6" value="" onblur="move_image_to_cloud('image6');" size="75">
<br><br>
Image 7: <input type="text" id="image7" name="image7" value="" onblur="move_image_to_cloud('image7');" size="75">
<br><br>
Image 8: <input type="text" id="image8" name="image8" value="" onblur="move_image_to_cloud('image8');" size="75">
<br><br>
Image 9: <input type="text" id="image9" name="image9" value="" onblur="move_image_to_cloud('image9');" size="75">
<br><br>
Image 10: <input type="text" id="image10" name="image10" value="" onblur="move_image_to_cloud('image10');" size="75">
<br><br>
</td></tr>
</table>
</body>
</html>
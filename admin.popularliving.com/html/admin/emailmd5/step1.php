<?php require_once("settings.php");?>

<STYLE type="text/css"> 
p,input {
    font-family: Verdana, sans-serif;
    font-size: 12px;
}
</STYLE>

<form action="step2.php" name="emailFile" method="post" enctype="multipart/form-data">
<div>
    <p>Select the file to upload: <input type="file" name="email" /></p>
    <p><input type="submit" value="Upload the file" /></p>
</div>
</form>
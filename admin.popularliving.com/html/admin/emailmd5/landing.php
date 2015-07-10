<?php require_once("settings.php");?>

<STYLE type="text/css"> 
p,input {
    font-family: Verdana, sans-serif;
    font-size: 12px;
}
</STYLE>

<form action="step2.php" name="files" method="post" enctype="multipart/form-data">
<div>
    <p>Email plain text file: <input type="file" name="email" /></p>
    <p>Email MD5 hashed file:<input type="file" name="emailHash" /></p>
    <p>Email domain file:<input type="file" name="emailDomain" /></p>
    <p><input type="submit" value="Upload the file" /></p>
</div>
</form>
      
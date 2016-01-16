<?php

// Set the upload size limitation to 64M
ini_set('post_max_size', '64M');
ini_set('upload_max_filesize', '64M');



?>
<style></style>
<form action="export.php" name="files" method="post" enctype="multipart/form-data">
<p>Email plain text file: <input type="file" name="email" /></p>
<p>Email MD5 hashed file:<input type="file" name="emailHash" /></p>
<p>Email domain file:<input type="file" name="emailDomain" /></p>
<p><input type="submit" value="Get the email address existing in the campaign contact list" /></p>
</form>
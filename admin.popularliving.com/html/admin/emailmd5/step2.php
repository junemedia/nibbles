<?php require_once("settings.php");?>

<?php

$uploaddir = (dirname(__FILE__)) . '/source/';
$time = date("YmdHms");
//$uploadfile = $uploaddir . $time . "_" . md5(basename($_FILES['email']['name']));
$uploadfile = $uploaddir .  "source.txt";

$perMinutes = 1328196;
$filesize = filesize($uploadfile);
$estime = ceil($filesize/$perMinutes);
    
if (isset ($_FILES['email']['tmp_name']) && (move_uploaded_file($_FILES['email']['tmp_name'], $uploadfile))) {

    
    echo "<li>Successfully uploaded!</li>";
    //echo "<li>Filename : $uploadfile</li>";
    echo "<li>Filetype: " . $_POST['filetype'] . "</li>";

} else {
    echo "File upload Error!\n Trying to read from local source";
}

/**
* Here is some more debugging info:Array
(
    [email] => Array
        (
            [name] => Borland Delphi 7 Studio Enterprise.rar
            [type] => application/x-rar-compressed
            [tmp_name] => E:\programs\wamp\tmp\phpB3AC.tmp
            [error] => 0
            [size] => 151030520
        )

)
*/

?>

<STYLE type="text/css"> 
p, ul, li, input, body {
    font-family: Verdana, sans-serif;
    font-size: 12px;
}
</STYLE>

<form action="step3.php" method="post">
<p>Please enter your email for notification when finished, using (,) to seperate emails if there are a few:</p>
<p>Select file type:
    <select name="filetype">
        <option value="email">Email Text</option>
        <option value="emailhash">Email MD5 Hashed</option>
        <option value="emaildomain">Email Domain</option>
    </select>
</p>
<p><input type="text" name="notify_email" value="leonz@junemedia.com" size="100"></p>
<p>
    <?php
        echo "<li>FileSize : $filesize (" . formatBytes($filesize) . ")</li>";
        //echo "<li>Estimate Time: $estime minutes</li>"; 
    ?>
</p>
<p><button type="submit">Process the file</button></p>
</form>


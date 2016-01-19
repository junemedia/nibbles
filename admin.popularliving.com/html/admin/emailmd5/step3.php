<?php require_once("settings.php");?>

<?php

//exec("php md5convertscript.php");

//exec("php testemail.php");

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

<img src="step_backprocess.php?notify_email=<?php echo $_POST['notify_email'];?>&type=<?php echo $_POST['filetype']?>" alt="">

<STYLE type="text/css"> 
p, ul, li, input, body {
    font-family: Verdana, sans-serif;
    font-size: 12px;
}
</STYLE>
Processing start. A notification email will be sent to <?php echo $_POST['notify_email'];?>

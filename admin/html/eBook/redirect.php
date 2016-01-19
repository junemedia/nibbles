<html>
<head>
<title>Thank You!</title>
</head>

<?php

//$link = "eBook.php?t=$t&s=$s";
if (isset($_POST['g'])){
	$g = $_POST['g'];
}
if (isset($_GET['g'])) {
        $g = $_GET['g'];
}
?>


<frameset cols="70%,30%">

  <frame src="<?php echo $g; ?>">
  <frame src="http://test.popularliving.com/eBook/eBookDownload.php?t=freestuff">


</frameset>


</html>
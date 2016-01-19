<?php
if (isset($_POST['t'])){
	$t = $_POST['t'];
}
if (isset($_GET['t'])) {
        $t = $_GET['t'];
}


switch ($t) {

	

	case 'getpaid':

	$fullPath = "getpaid.pdf";	

	break;



	case 'freestuff':

        $fullPath = "freestuff.pdf";

	break;



	case 'workathome':

	$fullPath = "workathome.pdf";

	break;

	
	
	case 'freestuffforkids':

	$fullPath = "freestuffforkids.pdf";

	break;
	
	
	
	case 'freestuffcraftbook':

	$fullPath = "freestuffcraftbook.pdf";

	break;

	

}



if ($fd = fopen ($fullPath, "rb")) {

$fsize =filesize($fullPath);

$fname = basename ($fullPath);



header("Pragma: ");

header("Cache-Control: ");

header("Content-type: application/octet-stream");

header("Content-Disposition: attachment; filename=\"".$fname."\"");

header("Content-length: $fsize");

header("Connection: close");



fpassthru($fd);

}

?>


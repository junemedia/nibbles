<?php
require_once(dirname(__FILE__) . '/../config.inc.php');
require_once(CAMPAIGNER_LEON_ROOT . 'lib/content.class.php');
define("DEBUG_PRINT", true);

date_default_timezone_set('America/Chicago');

$fileSource = file_get_contents("imageIds.csv");
$ids = explode("\n", $fileSource);

$content = new Content();

echo "Starting to delete images by Ids\n";

foreach($ids as $id){
    $content->deleteImageById($id);
    break;
}

//echo $content->getResponseStacks();


//print_r($ids);



?>

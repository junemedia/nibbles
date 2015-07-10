<?php
//takes a zip, and returns the proper options in a select box. 
include("../includes/paths_1220.php");

mysql_select_db('nibbles');

$sql = "select first as out from userData where email like '%@amperemedia.com'";
$res = dbQuery($sql);
while ($oZip = dbFetchObject($res)){
	
}

echo '<pre>'.$oZip->out.'</pre>';

//'<br>'.$res.'<br>'.$oZip.'<br>'.$sZip.'  <br>  '.$Program

?>
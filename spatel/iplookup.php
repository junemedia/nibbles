<?php

function getAddrByHost($host, $timeout = 3) {
   $query = `nslookup -timeout=$timeout -retry=1 $host`;
   if(preg_match('/\nAddress: (.*)\n/', $query, $matches))
      return trim($matches[1]);
   return $host;
}


mysql_pconnect ("localhost", "root", "8tre938G");

$query = "SELECT * FROM nibbles_samir.domains";
$result = mysql_query($query);
echo mysql_error();
while ($row = mysql_fetch_object($result)) {
	$ipaddr = getAddrByHost($row->domain);
	
	if ($ipaddr != $row->domain) {
		echo "$ipaddr --> $row->domain\n\n";
		
		//$update = "UPDATE nibbles_samir.domains SET ipaddr =\"$ipaddr\" WHERE id=\"$row->id\"";
		//$update_result = mysql_query($update);
		//echo mysql_error();
	}
}



?>

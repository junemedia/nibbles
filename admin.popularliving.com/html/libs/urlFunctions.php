<?php

// This script contains url related functions


function remoteFileExists ($url) {
	$return = true;
   $head = "";
   $url_p = parse_url ($url);

   if (isset ($url_p["host"])) { 
   		$host = $url_p["host"]; 
   } else { 
   		return false; 
   }
   
   if (isset ($url_p["path"])) { 
   		$path = $url_p["path"]; 
   } else { 
   		$path = ""; 
   }
   
   $fp = fsockopen ($host, 80, $errno, $errstr, 20);
   
   if (!$fp) 
   { 
   		return false; 
   } else {
       fputs($fp, "HEAD ".$url." HTTP/1.1\r\n");
       fputs($fp, "HOST: dummy\r\n");
       fputs($fp, "Connection: close\r\n\r\n");
       $headers = "";
       while (!feof ($fp)) { 
       		$headers .= fgets ($fp, 128); 
       }
   }
   
   fclose ($fp);
   
   $arr_headers = explode("\n", $headers);
  // $return = false;
   
   if (isset ($arr_headers[0])) {    
   		if (strstr($arr_headers[0], "404")) {   			
   			$return = false;
   		}
   		//$return = strpos($arr_headers[0], "404") !== false; 
   }
   
   return $return;
}


?>
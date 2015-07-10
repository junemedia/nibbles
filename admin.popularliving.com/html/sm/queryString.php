<?php
/*
http://www.mysite.com/myscript.php/first/John/last/Coggeshall

...to create the variables $first and $last and assign the values "John" and "Coggeshall" respectively. However, when using the $PATH_INFO method, you have more flexibility than with a GET method. The same URL could be written in the following fashion...



http://www.mysite.com/myscript.php/John/Coggeshall

...and then the script could use the following to retrieve the data:

list($dummy, $first, $last) = explode('/', $PATH_INFO);
*/


if(isset($PATH_INFO)) {
$vardata = explode('/', $PATH_INFO);
$num_param = count($vardata); 
         
        if($num_param % 2 == 0) { 
         
            $vardata[] = ''; 
            $num_param++; 
        } 
     
        for( $i = 1; $i < $num_param; $i += 2) { 
         
            $$vardata[$i] = $vardata[$i+1]; 
           // echo " <BR>".$vardata[$i]." value ".$$vardata[$i];
        } 

}
?>

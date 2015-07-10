<?php 

/***********

Script to Manage Site Contents of HandCraftersVillage site

*************/

include("../../../includes/paths.php");


$sPageTitle = "Handcrafters Village Referrer Messages";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
	
	$logfile = "$sGblHcvWebRoot/refer/referrer.txt";	
	
	
if ($delete) {		
	$fp = fopen($logfile, "w");	
	if ($fp) {
		//$fw = fwrite($fp, "");
		fclose($fp);
	}
}
	

$perPage = 10;

if (empty($number)) {
	$number = $perPage;
}

$file_line = file($logfile);
$total_lines = count($file_line);

if ($number > $total_lines) {
	$numberTo = $total_lines;
} else {
	$numberTo = $number;
}

$referrerLog = "<center>total: ".($total_lines)."<br>";
$referrerLog .= "Now showing: ".($number-($perPage-1))." to ".($numberTo)."</center>";
$referrerLog .= "<hr>";

for($i=($total_lines-1); 0 <= $i; $i--) { $line[] = $file_line[$i]; }

for($i = 0; $i < $total_lines; $i++){
	if ($i >= ($number-$perPage) && $i < $number){
		$line_array = explode("|::|",$line[$i]);
		
		$line_array[0] = stripslashes($line_array[0]);
		$line_array[1] = stripslashes($line_array[1]);
		$line_array[2] = stripslashes($line_array[2]);
		$line_array[3] = stripslashes($line_array[3]);
		$line_array[4] = stripslashes($line_array[4]);
		$line_array[5] = stripslashes($line_array[5]);
		
$referrerLog .= "
<b>Sent on:</b> $line_array[0] <b>by</b> $line_array[2] ($line_array[3])<br>
<b>URL:</b> <a href=\"$line_array[1]\" target=_blank>$line_array[1]</a><br>
<b>Sent to:</b> $line_array[4]<br>
<b>Message</b>: $line_array[5]
<hr>";
		
	}
}


$referrerLog .= "<center>";
$y = 1;
$num = $perPage;

while ($total_lines > ($num-($perPage))) {
	
	if($perPage < $total_lines){
		if(($number-($perPage-1)) == $y && $number == $num){
			
			$pageLinks .= "[$y-$num] ";
			
		} else { $pageLinks .= "[<a href=\"?password=$password&number=$num&iMenuId=$iMenuId&iParentMenuId=$iParentMenuId&sParentMenuFolder=$sParentMenuFolder\">$y-$num</a>]\n";
		}
	}
	$num += $perPage;
	$y += $perPage;
}
$referrerLog .= "</center>";


			
	// Hidden variable to be passed with form submit
	$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>			
			<input type=hidden name=iParentMenuId value='$iParentMenuId'>
			<input type=hidden name=sParentMenuFolder value='$sParentMenuFolder'>";
	
	include("$sGblIncludePath/adminHeader.php");	

	?>
	

<script language=JavaScript>
				function confirmDelete()
				{
					if(confirm('Are you sure you want to clear the log ?'))
					{							
						document.form1.elements['delete'].value='delete';
						document.form1.submit();								
					}
				}						
</script>
<form name=form1 action='<?php echo $PHP_SELF;?>'>

<input type=hidden name=delete>

<?php echo $hidden;?>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>

<tr>
<td><?php echo $clearButton;?></td><td align=right><?php echo $pageLinks;?></td>
</tr>
<tr>
<td colspan=2><?php echo $referrerLog;?></td>
</tr>


</table>

</form>


<?php
// include footer
include("$sGblIncludePath/adminFooter.php");
				
} else {
	echo "You are not authorized to access this page...";
}				
?>	


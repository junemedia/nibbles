<?php
//takes a zip, and returns the proper options in a select box. 
include("../includes/paths.php");

mysql_select_db('targetData');

//my var is going to be called sZip
//so, something like http://www.popularliving.com/images/offers/WPCM_WIN.php?sZip=60625
if(!$sZip || !$sOfferCode){
	echo '';
	exit();
}

$sZip = substr($sZip,0,5);

$sql = "select region from RCUN_NAU where zip='$sZip'";
$res = dbQuery($sql);
$oZip = dbFetchObject($res);

switch ($oZip->out) {
	case 1:
		echo '<select name="RCUN_NAU_campus"><option value="0">Choose a Campus</option><option value=>online</option></select> ' ;
		break;
	case 2:
		echo '<select name="RCUN_NAU_campus"><option value="0">Choose a Campus</option><option value=>online</option></select> ' ;
		break;
	case 3:
		echo '<select name="RCUN_NAU_campus"><option value="0">Choose a Campus</option><option value=>online</option></select> ' ;
		break;
	case 4:
		echo '<select name="RCUN_NAU_campus"><option value="0">Choose a Campus</option><option value=>online</option></select> ' ;
		break;
	case 5:
		echo '<select name="RCUN_NAU_campus"><option value="0">Choose a Campus</option><option value=>online</option></select> ' ;
		break;
	case 6:
		echo '<select name="RCUN_NAU_campus"><option value="0">Choose a Campus</option><option value=>online</option></select> ' ;
		break;
	case 7:
		echo '<select name="RCUN_NAU_campus"><option value="0">Choose a Campus</option><option value=>online</option></select> ' ;
		break;
	case 7:
		echo '<select name="RCUN_NAU_campus"><option value="0">Choose a Campus</option><option value=>online</option></select> ' ;
		break;
	case 8:
		echo '<select name="RCUN_NAU_campus"><option value="0">Choose a Campus</option><option value=>online</option></select> ' ;
		break;
	case 9:
		echo '<select name="RCUN_NAU_campus"><option value="0">Choose a Campus</option><option value=>online</option></select> ' ;
		break;
	case 10:
		echo '<select name="RCUN_NAU_campus"><option value="0">Choose a Campus</option><option value=>online</option></select> ' ;
		break;
	case 11:
		echo '<select name="RCUN_NAU_campus"><option value="0">Choose a Campus</option><option value=>online</option></select> ' ;
		break;
 
	default:
		echo '<select name="RCUN_NAU_campus"><option value="0">Choose a Campus</option><option value=>online</option></select> ' ;
		break;
}

?>





<SELECT name="RCUN_NAU_collegeID" class="standardField" onChange=""> 
							<option value="0" selected Choose a Campus</option>
							<option value="2" >Albuquerque, NM	</option>							
							<option value="3" >Brooklyn Center, MN	</option>							
							<option value="4" >Colorado Springs, CO	</option>	
							<option value="5" >Denver, CO	</option>			
							<option value="7" >Independence, MO	</option>		
							<option value="8" >Mall of America, MN	</option>				
							<option value="1">Online	</option>
							<option value="9" >Overland Park, KS	</option>		
							<option value="11">Rio Rancho, NM	</option>					
							<option value="12">Roseville, MN	</option>				
							<option value="13">Sioux Falls, SD	</option>					
							<option value="14">Zona Rosa, MO	</option>								
					</SELECT>




























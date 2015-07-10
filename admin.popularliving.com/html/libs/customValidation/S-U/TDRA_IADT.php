<?php

$iId = trim($_GET['iId']);
$ProgramDropDown = '';
	if ($iId == '15') {
	$sProgramDropDown = "<option value='' selected='selected'>Please Select</option>
                             <option value='363670'>Fashion Design - Associate Degree</option>
                            <option value='363761'>Fashion Design - Bachelor Degree</option>
                            <option value='363668'>Information Technology - Associate Degree</option>
                            <option value='363669'>Information Technology - Bachelor Degree</option>
                            <option value='363672'>Interior Design - Bachelor Degree</option>
                            <option value='363658'>Merchandising Management:  Fashion Merchandising - Associate Degree</option>
                            <option value='363659'>Merchandising Management:  Fashion Merchandising - Bachelor Degree</option>
                            <option value='363656'>Merchandising Management:  Retail Merchandising - Associate Degree</option>
                            <option value='363657'>Merchandising Management:  Retail Merchandising - Bachelor Degree</option>
                            <option value='363660'>Visual Communications:  Advertising Communications - Associate Degree</option>
                            <option value='363662'>Visual Communications:  Advertising Communications - Bachelor Degree</option>
                            <option value='363661'>Visual Communications:  Advertising Design - Associate Degree</option>
                            <option value='363663'>Visual Communications:  Advertising Design - Bachelor Degree</option>
                            <option value='363996'>Visual Communications:  Game Design - Bachelor Degree</option>
                            <option value='363665'>Visual Communications:  Graphic Design - Associate Degree</option>
                            <option value='363666'>Visual Communications:  Graphic Design - Bachelor Degree</option>
                            <option value='363716'>Visual Communications:  Multimedia and Web Design - Associate Degree</option>
                            <option value='363768'>Visual Communications:  Multimedia and Web Design - Bachelor Degree</option>
                            <option value='363717'>Visual Communications:  Video and Animation Production - Associate Degree</option>
                            <option value='363770'>Visual Communications:  Video and Animation Production - Bachelor Degree</option>";
                            }

if ($iId == '87') {
	$sProgramDropDown = "<option value='' selected='selected'>Please Select</option>
                             <option value='363943'>Fashion Design - Associate Degree</option>
                            <option value='353129'>Fashion Design - Bachelor Degree</option>
                            <option value='364025'>Game Design and Development - Bachelor Degree</option>
                            <option value='363941'>Interior Design - Associate Degree</option>
                            <option value='353132'>Interior Design - Bachelor Degree</option>
                            <option value='363942'>Visual Communication - Associate Degree</option>
                            <option value='353136'>Visual Communication - Bachelor Degree</option>";
}


if ($iId == '21') {
	$sProgramDropDown = "<option value='' selected='selected'>Please Select</option>
	                     <option value='30098'>Computer Graphics - Associate Degree</option>
                            <option value='301135'>Computer Graphics - Bachelor Degree</option>
                            <option value='363213'>Fashion Design and Merchandising - Bachelor Degree</option>
                            <option value='363496'>Game Design and Development - Bachelor Degree</option>
                            <option value='301134'>Interior Design - Bachelor Degree</option>
                            <option value='111058'>Marketing &amp; Advertising - Associate Degree</option>
                            <option value='363457'>Marketing and Advertising - Bachelor Degree</option>
                            <option value='301128'>Multimedia Design - Bachelor Degree</option>
                            <option value='363416'>Network Design and Administration - Bachelor Degree</option>";
}

if ($iId == '9') {
	$sProgramDropDown = "<option value='' selected='selected'>Please Select</option>
	                     <option value='363606'>Business Administration - Associate Degree</option>
                            <option value='271002'>Business Administration - Diploma Degree</option>
                            <option value='363584'>Fashion Merchandising - Diploma Degree</option>
                            <option value='364065'>Justice Technology - Associate Degree</option>
                            <option value='271009'>Visual Communication - Associate Degree</option>";
}

if ($iId == '16') {
	$sProgramDropDown = "<option value='' selected='selected'>Please Select</option>
	                     <option value='363187'>Computer Animation - Bachelor Degree</option>
                            <option value='363358'>Digital Movie Production - Bachelor Degree</option>
                            <option value='291100'>Digital Photography - Associate Degree</option>
                            <option value='301087'>Digital Production - Associate Degree</option>
                            <option value='3'>Fashion Design and Marketing - Associate Degree</option>
                            <option value='9'>Fashion Design and Marketing - Bachelor Degree</option>
                            <option value='8'>Graphic Design - Associate Degree</option>
                            <option value='161005'>Graphic Design - Bachelor Degree</option>
                            <option value='5'>Interactive Media - Associate Degree</option>
                            <option value='12'>Interactive Media - Bachelor Degree</option>
                            <option value='4'>Interior Design - Associate Degree</option>
                            <option value='10'>Interior Design - Bachelor Degree</option>
                            <option value='353186'>Marketing and Design - Bachelor Degree</option>
                            <option value='363959'>Media Design Management - Masters Degree</option>
                            <option value='363667'>Merchandising - Bachelor Degree</option>
                            <option value='363357'>Recording Arts - Bachelor Degree</option>
                            <option value='364039'>Visual Journalism - Bachelor Degree</option>";
}

echo $sProgramDropDown;

?>
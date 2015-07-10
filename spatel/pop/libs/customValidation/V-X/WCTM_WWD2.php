<?php

//$iCourseId = trim($_GET['iCourseId']);
//$iCampusId = trim($_GET['iCampusId']);
//$sContent = '';
$sContent = '';

$sType = trim($_GET['sType']);
$iId = trim($_GET['iId']);

if ($sType != 'course') {
	if ($iId == '30') {
		$sContent = "<OPTION VALUE='9'>Denver Aviation (Broomfield CO)</OPTION>
							 <OPTION VALUE='6'>Los Angeles Aviation (Inglewood CA)</OPTION>";
	}
	
	if ($iId == '19') {
		$sContent = "<OPTION VALUE='18'>Houston Aviation (Houston TX)</OPTION>
					 <OPTION VALUE='6'>Los Angeles Aviation (Inglewood CA)</OPTION>";	
	}
	
	
	if ($iId == '23') {
		$sContent = "
		<OPTION VALUE='7'>Denver North (Denver CO)</OPTION>";	
	}
	
	if ($iId == '11') {
		$sContent = "<OPTION VALUE='2'>Anaheim (Anaheim CA)</OPTION>
							 <OPTION VALUE='11'>Chicago DuPage (Woodridge IL)</OPTION>
							 <OPTION VALUE='15'>Dallas (Dallas TX)</OPTION>
							 <OPTION VALUE='7'>Denver North (Denver CO)</OPTION>
							 <OPTION VALUE='8'>Denver South (Denver CO)</OPTION>
							 <OPTION VALUE='16'>Fort Worth (Fort Worth TX)</OPTION><br>
							 <OPTION VALUE='17'>Houston Technology (Houston TX)</OPTION>
							 <OPTION VALUE='3'>Inland Empire (Upland CA)</OPTION>
							 <OPTION VALUE='4'>Long Beach (Torrance CA)</OPTION>
							 <OPTION VALUE='28'>Virginia Annandale (Annandale VA)</OPTION>
							 <OPTION VALUE='27'>Virginia Arlington Ballston (Arlington VA)</OPTION>";	
	}
	
	if ($iId == '17') {
		$sContent = "<OPTION VALUE='22'>Atlanta Northlake (Atlanta GA)</OPTION>
							 <OPTION VALUE='11'>Chicago DuPage (Woodridge IL)</OPTION>
							 <OPTION VALUE='12'>Chicago Loop (Chicago IL)</OPTION>
							 <OPTION VALUE='13'>Chicago OHare Airport (Chicago IL)</OPTION>
							 <OPTION VALUE='14'>Chicago River Oaks (Calumet City IL)</OPTION>
							 <OPTION VALUE='15'>Dallas (Dallas TX)</OPTION><br>
							 <OPTION VALUE='7'>Denver North (Denver CO)</OPTION>
							 <OPTION VALUE='8'>Denver South (Denver CO)</OPTION>
							 <OPTION VALUE='16'>Fort Worth (Fort Worth TX)</OPTION>
							 <OPTION VALUE='17'>Houston Technology (Houston TX)</OPTION>
							 <OPTION VALUE='3'>Inland Empire (Upland CA)</OPTION>
							 <OPTION VALUE='4'>Long Beach (Torrance CA)</OPTION>
							 <OPTION VALUE='5'>Los Angeles (Los Angeles CA)</OPTION>";	
	}
	
	if ($iId == '38') {
		$sContent = "<OPTION VALUE='9'>Denver Aviation (Broomfield CO)</OPTION>
							 <OPTION VALUE='6'>Los Angeles Aviation (Inglewood CA)</OPTION>";	
	}
	
	if ($iId == '10') {
		$sContent = "<OPTION VALUE='2'>Anaheim (Anaheim CA)</OPTION>
							 <OPTION VALUE='10'>Atlanta Midtown (Atlanta GA)</OPTION>
							 <OPTION VALUE='22'>Atlanta Northlake (Atlanta GA)</OPTION>
							 <OPTION VALUE='11'>Chicago DuPage (Woodridge IL)</OPTION>
							 <OPTION VALUE='12'>Chicago Loop (Chicago IL)</OPTION>
							 <OPTION VALUE='13'>Chicago OHare Airport (Chicago IL)</OPTION>
							 <OPTION VALUE='14'>Chicago River Oaks (Calumet City IL)</OPTION>
							 <OPTION VALUE='15'>Dallas (Dallas TX)</OPTION>
							 <OPTION VALUE='7'>Denver North (Denver CO)</OPTION>
							 <OPTION VALUE='16'>Fort Worth (Fort Worth TX)</OPTION>
							 <OPTION VALUE='17'>Houston Technology (Houston TX)</OPTION>
							 <OPTION VALUE='3'>Inland Empire (Upland CA)</OPTION>
							 <OPTION VALUE='4'>Long Beach (Torrance CA)</OPTION>
							 <OPTION VALUE='5'>Los Angeles (Los Angeles CA)</OPTION>
							 <OPTION VALUE='28'>Virginia Annandale (Annandale VA)</OPTION>
							 <OPTION VALUE='27'>Virginia Arlington Ballston (Arlington VA)</OPTION>";	
	}


	if ($iId == '4') {
		$sContent = "<OPTION VALUE='7'>Denver North (Denver CO)</OPTION>";	
	}
	
	if ($iId == '24') {
		$sContent = "<OPTION VALUE='9'>Denver Aviation (Broomfield CO)</OPTION>";	
	}
	
	if ($iId == '26') {
		$sContent = "<OPTION VALUE='9'>Denver Aviation (Broomfield CO)</OPTION>";	
	}
	
	
	if ($iId == '25') {
		$sContent = "<OPTION VALUE='7'>Denver North (Denver CO)</OPTION>";	
	}
	
	if ($iId == '5') {
		$sContent = "<OPTION VALUE='2'>Anaheim (Anaheim CA)</OPTION>
							 <OPTION VALUE='11'>Chicago DuPage (Woodridge IL)</OPTION>
							 <OPTION VALUE='12'>Chicago Loop (Chicago IL)</OPTION>
							 <OPTION VALUE='13'>Chicago OHare Airport (Chicago IL)</OPTION>
							 <OPTION VALUE='14'>Chicago River Oaks (Calumet City IL)</OPTION>
							 <OPTION VALUE='3'>Inland Empire (Upland CA)</OPTION>
							 <OPTION VALUE='5'>Los Angeles (Los Angeles CA)</OPTION>
							 <OPTION VALUE='28'>Virginia Annandale (Annandale VA)</OPTION>
							 <OPTION VALUE='27'>Virginia Arlington Ballston (Arlington VA)</OPTION>";	
	}
	
	if ($iId == '1') {
		$sContent = "<OPTION VALUE='3'>Inland Empire (Upland CA)</OPTION>";	
	}
	
	if ($iId == '29') {
		$sContent = "<OPTION VALUE='2'>Anaheim (Anaheim CA)</OPTION>
							 <OPTION VALUE='12'Chicago Loop (Chicago IL)></OPTION>
							 <OPTION VALUE='3'>Inland Empire (Upland CA)</OPTION>
							 <OPTION VALUE='5'>Los Angeles (Los Angeles CA)</OPTION>";	
	}
	
	if ($iId == '14') {
		$sContent = "<OPTION VALUE='2'>Anaheim (Anaheim CA)</OPTION>
							 <OPTION VALUE='10'>Atlanta Midtown (Atlanta GA)</OPTION>
							 <OPTION VALUE='12'>Chicago Loop (Chicago IL)</OPTION>
							 <OPTION VALUE='14'>Chicago River Oaks (Calumet City IL)</OPTION>
							 <OPTION VALUE='7'>Denver North (Denver CO)</OPTION>
							 <OPTION VALUE='3'>Inland Empire (Upland CA)</OPTION>
							 <OPTION VALUE='5'>Los Angeles (Los Angeles CA)</OPTION>";	
	}
	
	if ($iId == '2') {
		$sContent = "<OPTION VALUE='2'>Anaheim (Anaheim CA)</OPTION>
							 <OPTION VALUE='10'>Atlanta Midtown (Atlanta GA)</OPTION>
							 <OPTION VALUE='22'>Atlanta Northlake (Atlanta GA)</OPTION>
							 <OPTION VALUE='11'>Chicago DuPage (Woodridge IL)</OPTION>
							 <OPTION VALUE='12'>Chicago Loop (Chicago IL)</OPTION>
							 <OPTION VALUE='13'>Chicago OHare Airport (Chicago IL)</OPTION>
							 <OPTION VALUE='14'>Chicago River Oaks (Calumet City IL)</OPTION>
							 <OPTION VALUE='7'>Denver North (Denver CO)</OPTION>
							 <OPTION VALUE='8'>Denver South (Denver CO)</OPTION>
							 <OPTION VALUE='3'>Inland Empire (Upland CA)</OPTION>
							 <OPTION VALUE='4'>Long Beach (Torrance CA)</OPTION>
							 <OPTION VALUE='5'>Los Angeles (Los Angeles CA)</OPTION>
							 <OPTION VALUE='28'>Virginia Annandale (Annandale VA)</OPTION>
							 <OPTION VALUE='27'>Virginia Arlington Ballston (Arlington VA)</OPTION>";	
	}
	
	if ($iId == '3') {
		$sContent = "<OPTION VALUE='2'>Anaheim (Anaheim CA)</OPTION>
							 <OPTION VALUE='14'>Chicago River Oaks (Calumet City IL)</OPTION>
							 <OPTION VALUE='8'>Denver South (Denver CO)</OPTION>";	
	}
	
	if ($iId == '34') {
		$sContent = "<OPTION VALUE='7'>Denver North (Denver CO)</OPTION>
							 <OPTION VALUE='8'>Denver South (Denver CO)</OPTION>";	
	}
	
	if ($iId == '6') {
		$sContent = "<OPTION VALUE='2'>Anaheim (Anaheim CA)</OPTION>
							 <OPTION VALUE='10'>Atlanta Midtown (Atlanta GA)</OPTION>
							 <OPTION VALUE='11'>Chicago DuPage (Woodridge IL)</OPTION>
							 <OPTION VALUE='7'>Denver North (Denver CO)</OPTION>
							 <OPTION VALUE='8'>Denver South (Denver CO)</OPTION>
							 <OPTION VALUE='3'>Inland Empire (Upland CA)</OPTION>
							 <OPTION VALUE='5'>Los Angeles (Los Angeles CA)</OPTION>";	
	}
	
	if ($iId == '15') {
		$sContent = "<OPTION VALUE='2'>Anaheim (Anaheim CA)</OPTION>
							 <OPTION VALUE='13'>Chicago OHare Airport (Chicago IL)</OPTION>
							 <OPTION VALUE='14'>Chicago River Oaks (Calumet City IL)</OPTION>
							 <OPTION VALUE='3'>Inland Empire (Upland CA)</OPTION>
							 <OPTION VALUE='5'>Los Angeles (Los Angeles CA)</OPTION>";	
	}
	
	if ($iId == '36') {
		$sContent = "<OPTION VALUE='7'>Denver North (Denver CO)</OPTION>";	
	}
	
	if ($iId == '16') {
		$sContent = "<OPTION VALUE='22'>Atlanta Northlake (Atlanta GA)</OPTION>
							 <OPTION VALUE='11'>Chicago DuPage (Woodridge IL)</OPTION>
							 <OPTION VALUE='12'>Chicago Loop (Chicago IL)</OPTION>
							 <OPTION VALUE='13'>Chicago OHare Airport (Chicago IL)</OPTION>
							 <OPTION VALUE='14'>Chicago River Oaks (Calumet City IL)</OPTION>
							 <OPTION VALUE='7'>Denver North (Denver CO)</OPTION>
							 <OPTION VALUE='8'>Denver South (Denver CO)</OPTION>
							 <OPTION VALUE='3'>Inland Empire (Upland CA)</OPTION>
							 <OPTION VALUE='4'>Long Beach (Torrance CA)</OPTION>";	
	}
	
	if ($iId == '7') {
		$sContent = "<OPTION VALUE='2'>Anaheim (Anaheim CA)</OPTION>
							 <OPTION VALUE='11'>Chicago DuPage (Woodridge IL)</OPTION>
							 <OPTION VALUE='12'>Chicago Loop (Chicago IL)</OPTION>
							 <OPTION VALUE='13'>Chicago OHare Airport (Chicago IL)</OPTION>
							 <OPTION VALUE='7'>Denver North (Denver CO)</OPTION>
							 <OPTION VALUE='8'>Denver South (Denver CO)</OPTION>
							 <OPTION VALUE='3'>Inland Empire (Upland CA)</OPTION>
							 <OPTION VALUE='4'>Long Beach (Torrance CA)</OPTION>
							 <OPTION VALUE='5'>Los Angeles (Los Angeles CA)</OPTION>
							 <OPTION VALUE='28'>Virginia Annandale (Annandale VA)</OPTION>
							 <OPTION VALUE='27'>Virginia Arlington Ballston (Arlington VA)</OPTION>";	
	}
	
	if ($iId == '27') {
		$sContent = "<OPTION VALUE='7'>Denver North (Denver CO)</OPTION>";	
	}
	
	if ($iId == '8') {
		$sContent = "<OPTION VALUE='2'>Anaheim (Anaheim CA)</OPTION>
							 <OPTION VALUE='22'>Atlanta Northlake (Atlanta GA)</OPTION>
							 <OPTION VALUE='11'>Chicago DuPage (Woodridge IL)</OPTION>
							 <OPTION VALUE='12'>Chicago Loop (Chicago IL)</OPTION>
							 <OPTION VALUE='13'>Chicago OHare Airport (Chicago IL)</OPTION>
							 <OPTION VALUE='14'>Chicago River Oaks (Calumet City IL)</OPTION>
							 <OPTION VALUE='7'>Denver North (Denver CO)</OPTION>
							 <OPTION VALUE='8'>Denver South (Denver CO)</OPTION>
							 <OPTION VALUE='3'>Inland Empire (Upland CA)</OPTION>
							 <OPTION VALUE='4'>Long Beach (Torrance CA)</OPTION>
							 <OPTION VALUE='5'>Los Angeles (Los Angeles CA)</OPTION>";	
	}
	if ($iId == '9') {
		$sContent = "<OPTION VALUE='3'>Inland Empire (Upland CA)</OPTION>
							 <OPTION VALUE='5'>Los Angeles (Los Angeles CA)</OPTION>";	
	}
	
	if ($iId == '20') {
		$sContent = "<OPTION VALUE='9'>Denver Aviation (Broomfield CO)</OPTION>";	
	}
	
	if ($iId == '22') {
		$sContent = "<OPTION VALUE='10'>Atlanta Midtown (Atlanta GA)</OPTION>
							 <OPTION VALUE='22'>Atlanta Northlake (Atlanta GA)</OPTION>
							 <OPTION VALUE='13'>Chicago OHare Airport (Chicago IL)</OPTION>
							 <OPTION VALUE='14'>Chicago River Oaks (Calumet City IL)</OPTION>
							 <OPTION VALUE='15'>Dallas (Dallas TX)</OPTION>
							 <OPTION VALUE='8'>Denver South (Denver CO)</OPTION>
							 <OPTION VALUE='16'>Fort Worth (Fort Worth TX)</OPTION>
							 <OPTION VALUE='17'>Houston Technology (Houston TX)</OPTION>";	
	}
	
	if ($iId == '33') {
		$sContent = "<OPTION VALUE='15'>Dallas (Dallas TX)</OPTION>
							 <OPTION VALUE='7'>Denver North (Denver CO)</OPTION>
							 <OPTION VALUE='16'>Fort Worth (Fort Worth TX)</OPTION>
							 <OPTION VALUE='17'>Houston Technology (Houston TX)</OPTION>";	
	}
	
} else {
	//do the campus id stuff
	if ($iId == '2') {
	$sContent = "<option value='11'>Associate Degree - CAD - Architectural Drafting</option>
						<option value='10'>Associate Degree - Graphic Design and Multimedia</option>
						<option value='5'>Bachelor Degree - Animation</option>
						<option value='29'>Bachelor Degree - Business Administration - Marketing/Sales</option>
						<option value='14'>Bachelor Degree - Computer Network Management</option>
						<option value='2'>Bachelor Degree - Criminal Justice</option>
						<option value='3'>Bachelor Degree - E-Business Management</option>
						<option value='6'>Bachelor Degree - Game Art and Design</option>
						<option value='15'>Bachelor Degree - Game Software Development</option>
						<option value='7'>Bachelor Degree - Interior Design</option>
						<option value='8'>Bachelor Degree - Visual Communications</option>";
											}

if ($iId == '10') {
	$sContent = "<option value='14'>Bachelor Degree - Computer Network Management</option>
						 <option value='10'>Associate Degree - Graphic Design and Multimedia</option>
						 <option value='2'>Bachelor Degree - Criminal Justice</option>
						 <option value='6'>Bachelor Degree - Game Art &amp; Design</option>
						 <option value='22'>Diploma - Medical Assisting</option>";
						 
}


if ($iId == '22') {
	$sContent = "<OPTION VALUE='17'>Associate Degree - Computer Network Engineering</OPTION>
						<OPTION VALUE='10'>Associate Degree - Graphic Design and Multimedia</OPTION>
						<OPTION VALUE='2'>Bachelor Degree - Criminal Justice</OPTION>
						<OPTION VALUE='16'>Bachelor Degree - Information Systems Security</OPTION>
						<OPTION VALUE='8'>Bachelor Degree - Visual Communications</OPTION>
						<OPTION VALUE='22'>Diploma - Medical Assisting</OPTION>";	
}

if ($iId == '11') {
	$sContent = "<OPTION VALUE='11'>Associate Degree - CAD - Architectural Drafting</OPTION>
						<OPTION VALUE='17'>Associate Degree - Computer Network Engineering</OPTION>
						<OPTION VALUE='10'>Associate Degree - Graphic Design and Multimedia</OPTION>
						<OPTION VALUE='5'>Bachelor Degree - Animation</OPTION>
						<OPTION VALUE='2'>Bachelor Degree - Criminal Justice</OPTION>
						<OPTION VALUE='6'>Bachelor Degree - Game Art and Design</OPTION>
						<OPTION VALUE='16'>Bachelor Degree - Information Systems Security</OPTION>
						<OPTION VALUE='7'>Bachelor Degree - Interior Design</OPTION>
						<OPTION VALUE='8'>Bachelor Degree - Visual Communications</OPTION>";	
}
if ($iId == '12') {
	$sContent = "<OPTION VALUE='17'>Associate Degree - Computer Network Engineering</OPTION>
						<OPTION VALUE='10'>Associate Degree - Graphic Design and Multimedia</OPTION>
						<OPTION VALUE='5'>Bachelor Degree - Animation</OPTION>
						<OPTION VALUE='29'>Bachelor Degree - Business Administration - Marketing/Sales</OPTION>
						<OPTION VALUE='14'>Bachelor Degree - Computer Network Management</OPTION>
						<OPTION VALUE='2'>Bachelor Degree - Criminal Justice</OPTION>
						<OPTION VALUE='16'>Bachelor Degree - Information Systems Security</OPTION>
						<OPTION VALUE='7'>Bachelor Degree - Interior Design</OPTION>
						<OPTION VALUE='8'>Bachelor Degree - Visual Communications</OPTION>";	
}
if ($iId == '13') {
	$sContent = "<OPTION VALUE='17'>Associate Degree - Computer Network Engineering</OPTION>
						<OPTION VALUE='10'>Associate Degree - Graphic Design and Multimedia</OPTION>
						<OPTION VALUE='5'>Bachelor Degree - Animation</OPTION>
						<OPTION VALUE='2'>Bachelor Degree - Criminal Justice</OPTION>
						<OPTION VALUE='15'>Bachelor Degree - Game Software Development</OPTION>
						<OPTION VALUE='16'>Bachelor Degree - Information Systems Security</OPTION>
						<OPTION VALUE='7'>Bachelor Degree - Interior Design</OPTION>
						<OPTION VALUE='8'>Bachelor Degree - Visual Communications</OPTION>
						<OPTION VALUE='22'>Diploma - Medical Assisting</OPTION>";	
}
if ($iId == '14') {
	$sContent = "<OPTION VALUE='17'>Associate Degree - Computer Network Engineering</OPTION>
						<OPTION VALUE='10'>Associate Degree - Graphic Design and Multimedia</OPTION>
						<OPTION VALUE='5'>Bachelor Degree - Animation</OPTION>
						<OPTION VALUE='14'>Bachelor Degree - Computer Network Management</OPTION>
						<OPTION VALUE='2'>Bachelor Degree - Criminal Justice</OPTION>
						<OPTION VALUE='3'>Bachelor Degree - E-Business Management</OPTION>
						<OPTION VALUE='15'>Bachelor Degree - Game Software Development</OPTION>
						<OPTION VALUE='16'>Bachelor Degree - Information Systems Security</OPTION>
						<OPTION VALUE='8'>Bachelor Degree - Visual Communications</OPTION>
						<OPTION VALUE='22'>Diploma - Medical Assisting</OPTION>";	
}
if ($iId == '15') {
	$sContent = "<OPTION VALUE='11'>Associate Degree - CAD - Architectural Drafting</OPTION>
						<OPTION VALUE='17'>Associate Degree - Computer Network Engineering</OPTION>
						<OPTION VALUE='10'>Associate Degree - Graphic Design and Multimedia</OPTION>
						<OPTION VALUE='22'>Diploma - Medical Assisting</OPTION>
						<OPTION VALUE='33'>Diploma - Medical Insurance Coding and Billing</OPTION>";	
}
if ($iId == '9') {
	$sContent = "<OPTION VALUE='30'>Associate Degree - Airframe and Powerplant</OPTION>
						<option value='38'>Associate - Construction Management</option>
						<OPTION VALUE='24'>Associate Degree - HVAC/R</OPTION>
						<OPTION VALUE='20'>Diploma - Advanced Electronics Technology (Avionics)</OPTION>
						<OPTION VALUE='26'>Diploma - HVAC/R</OPTION>";	
}
if ($iId == '7') {
	$sContent = "<OPTION VALUE='14'>Bachelor Degree - Computer Network Management</OPTION>
						<OPTION VALUE='2'>Bachelor Degree - Criminal Justice</OPTION>
						<OPTION VALUE='4'>Associate Degree - Hotel and Restaurant Management</OPTION>
						<OPTION VALUE='6'>Bachelor Degree - Game Art and Design</OPTION>
						<option value='36'>Bachelor - Game Software Development</option>
						<OPTION VALUE='16'>Bachelor Degree - Information Systems Security</OPTION>
						<OPTION VALUE='7'>Bachelor Degree - Interior Design</OPTION>
						<OPTION VALUE='27'>Bachelor Degree - Technical Management</OPTION>
						<OPTION VALUE='8'>Bachelor Degree - Visual Communications</OPTION>
						<OPTION VALUE='23'>Associate Degree - Automotive Technology</OPTION>
						<OPTION VALUE='11'>Associate Degree - CAD - Architectural Drafting</OPTION>
						<OPTION VALUE='17'>Associate Degree - Computer Network Engineering</OPTION>
						<OPTION VALUE='10'>Associate Degree - Graphic Design and Multimedia</OPTION>
					    <OPTION VALUE='34'>Bachelor Degree - Fashion Merchandising</OPTION>
						<OPTION VALUE='25'>Associate Degree - Surveying</OPTION>
						<OPTION VALUE='33'>Diploma - Medical Insurance Coding and Billing</OPTION>";	
}
if ($iId == '8') {
	$sContent = "<OPTION VALUE='2'>Bachelor Degree - Criminal Justice</OPTION>
						<OPTION VALUE='3'>Bachelor Degree - E-Business Management</OPTION>
						<OPTION VALUE='34'>Bachelor Degree - Fashion Merchandising</OPTION>
						<OPTION VALUE='6'>Bachelor Degree - Game Art and Design</OPTION>
						<OPTION VALUE='16'>Bachelor Degree - Information Systems Security</OPTION>
						<OPTION VALUE='7'>Bachelor Degree - Interior Design</OPTION>
						<OPTION VALUE='8'>Bachelor Degree - Visual Communications</OPTION>
						<OPTION VALUE='22'>Diploma - Medical Assisting</OPTION>
						<OPTION VALUE='11'>Associate Degree - CAD - Architectural Drafting</OPTION>
						<OPTION VALUE='17'>Associate Degree - Computer Network Engineering</OPTION>";	
}
if ($iId == '16') {
	$sContent = "<OPTION VALUE='11'>Associate Degree - CAD - Architectural Drafting</OPTION>
						<OPTION VALUE='17'>Associate Degree - Computer Network Engineering</OPTION>
						<OPTION VALUE='10'>Associate Degree - Graphic Design and Multimedia</OPTION>
						<OPTION VALUE='22'>Diploma - Medical Assisting</OPTION>
						<OPTION VALUE='33'>Diploma - Medical Insurance Coding and Billing</OPTION>";	
}
if ($iId == '18') {
	$sContent = "<OPTION VALUE='19'>Diploma - Airframe and Powerplant</OPTION>";	
}
if ($iId == '17') {
	$sContent = "<OPTION VALUE='11'>Associate Degree - CAD - Architectural Drafting</OPTION>
						<OPTION VALUE='17'>Associate Degree - Computer Network Engineering</OPTION>
						<OPTION VALUE='10'>Associate Degree - Graphic Design and Multimedia</OPTION>
						<OPTION VALUE='22'>Diploma - Medical Assisting</OPTION>
						<OPTION VALUE='33'>Diploma - Medical Insurance Coding and Billing</OPTION>";	
}
if ($iId == '3') {
	$sContent = "<OPTION VALUE='5'>Bachelor Degree - Animation</OPTION>
						<OPTION VALUE='1'>Bachelor Degree - Business Administration - Accounting</OPTION>
						<OPTION VALUE='29'>Bachelor Degree - Business Administration - Marketing/Sales</OPTION>
						<OPTION VALUE='14'>Bachelor Degree - Computer Network Management</OPTION>
						<OPTION VALUE='2'>Bachelor Degree - Criminal Justice</OPTION>
						<OPTION VALUE='6'>Bachelor Degree - Game Art and Design</OPTION>
						<OPTION VALUE='15'>Bachelor Degree - Game Software Development</OPTION>
						<OPTION VALUE='16'>Bachelor Degree - Information Systems Security</OPTION>
						<OPTION VALUE='7'>Bachelor Degree - Interior Design</OPTION>
						<OPTION VALUE='8'>Bachelor Degree - Visual Communications</OPTION>
						<OPTION VALUE='9'>Bachelor Degree - Web Design and Multimedia</OPTION>
						<OPTION VALUE='11'>Associate Degree - CAD - Architectural Drafting</OPTION>
						<OPTION VALUE='17'>Associate Degree - Computer Network Engineering</OPTION>
						<OPTION VALUE='10'>Associate Degree - Graphic Design and Multimedia</OPTION>";	
}
if ($iId == '4') {
	$sContent = "<OPTION VALUE='11'>Associate Degree - CAD - Architectural Drafting</OPTION>
						<OPTION VALUE='17'>Associate Degree - Computer Network Engineering</OPTION>
						<OPTION VALUE='10'>Associate Degree - Graphic Design and Multimedia</OPTION>
						<OPTION VALUE='2'>Bachelor Degree - Criminal Justice</OPTION>
						<OPTION VALUE='16'>Bachelor Degree - Information Systems Security</OPTION>
						<OPTION VALUE='7'>Bachelor Degree - Interior Design</OPTION>
						<OPTION VALUE='8'>Bachelor Degree - Visual Communications</OPTION>";	
}
if ($iId == '5') {
	$sContent = "<OPTION VALUE='5'>Bachelor Degree - Animation</OPTION>
						<OPTION VALUE='29'>Bachelor Degree - Business Administration - Marketing/Sales</OPTION>
						<OPTION VALUE='14'>Bachelor Degree - Computer Network Management</OPTION>
						<OPTION VALUE='2'>Bachelor Degree - Criminal Justice</OPTION>
						<OPTION VALUE='6'>Bachelor Degree - Game Art and Design</OPTION>
						<OPTION VALUE='15'>Bachelor Degree - Game Software Development</OPTION>
						<OPTION VALUE='7'>Bachelor Degree - Interior Design</OPTION>
						<OPTION VALUE='8'>Bachelor Degree - Visual Communications</OPTION>
						<OPTION VALUE='9'>Bachelor Degree - Web Design and Multimedia</OPTION>
						<OPTION VALUE='17'>Associate Degree - Computer Network Engineering</OPTION>
						<OPTION VALUE='10'>Associate Degree - Graphic Design and Multimedia</OPTION>";	
}
if ($iId == '6') {
	$sContent = "<OPTION VALUE='30'>Associate Degree - Airframe and Powerplant</OPTION>
						 <option value='38'>Associate - Construction Management</option>
						<OPTION VALUE='19'>Diploma - Airframe and Powerplant</OPTION>";	
}
if ($iId == '28') {
	$sContent = "<OPTION VALUE='5'>Bachelor Degree - Animation</OPTION>
						<OPTION VALUE='2'>Bachelor Degree - Criminal Justice</OPTION>
						<OPTION VALUE='7'>Bachelor Degree - Interior Design</OPTION>
						<OPTION VALUE='10'>Associate Degree - Graphic Design and Multimedia</OPTION>
						<OPTION VALUE='11'>Associate Degree - CAD - Architectural Drafting</OPTION>";	
}
if ($iId == '27') {
	$sContent = "<option value='5'>Bachelor - Animation</option>
						<option value='2'>Bachelor - Criminal Justice</option>
						<option value='7'>Bachelor - Interior Design</option>
						<option value='11'>Associate - CAD - Architectural Drafting</option>
						<option value='10'>Associate - Graphic Design and Multimedia</option>";	
}
if ($iId == '19') {
	$sContent = "<option value='5'>Bachelor - Animation</option>
						<option value='1'>Bachelor - Business Administration - Accounting</option>
						<option value='29'>Bachelor - Business Administration - Marketing/Sales</option>
						<option value='14'>Bachelor - Computer Network Management</option>
						<option value='2'>Bachelor - Criminal Justice</option>
						<option value='3'>Bachelor - E-Business Management</option>
						<option value='34'>Bachelor - Fashion Merchandising</option>
						<option value='6'>Bachelor - Game Art and Design</option>
						<option value='15'>Bachelor - Game Software Development</option>
						<option value='37'>Bachelor - Healthcare Management</option>
						<option value='16'>Bachelor - Information Systems Security</option>
						<option value='7'>Bachelor - Interior Design</option>
						<option value='8'>Bachelor - Visual Communications</option>
						<option value='9'>Bachelor - Web Design and Multimedia</option>
						<option value='17'>Associate - Computer Network Engineering</option>
						<option value='10'>Associate - Graphic Design and Multimedia</option>
						<option value='18'>Associate - Software Engineering</option>";	
}

}
echo $sContent;	
?>
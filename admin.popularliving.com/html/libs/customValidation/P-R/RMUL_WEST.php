<?php

$iProgramId = trim($_GET['iProgramId']);
$sCollegeDropDown = '';

if ($iProgramId == '30') {
	$sCollegeDropDown = "<OPTION VALUE=''>Please Select</OPTION>
						<OPTION VALUE='9'>Denver Aviation (Broomfield CO)</OPTION>
						<OPTION VALUE='6'>Los Angeles Aviation (Inglewood CA)</OPTION>";
}

if ($iProgramId == '19') {
	$sCollegeDropDown = "<OPTION VALUE=''>Please Select</OPTION>
						 <OPTION VALUE='18'>Houston Aviation (Houston TX)</OPTION>
						 <OPTION VALUE='6'>Los Angeles Aviation (Inglewood CA)</OPTION>";	
}


if ($iProgramId == '23') {
	$sCollegeDropDown = "<OPTION VALUE=''>Please Select</OPTION>
						 <OPTION VALUE='7'>Denver North (Denver CO)</OPTION>";	
}

if ($iProgramId == '11') {
	$sCollegeDropDown = "<OPTION VALUE=''>Please Select</OPTION>
						 <OPTION VALUE='2'>Anaheim (Anaheim CA)</OPTION>
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

if ($iProgramId == '17') {
	$sCollegeDropDown = "<OPTION VALUE=''>Please Select</OPTION>
						 <OPTION VALUE='22'>Atlanta Northlake (Atlanta GA)</OPTION>
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
						 <OPTION VALUE='5'>Los Angeles (Los Angeles CA)</OPTION>
						 <OPTION VALUE='19'>Westwood ONLINE</OPTION>";	
}

if ($iProgramId == '38') {
	$sCollegeDropDown = "<OPTION VALUE=''>Please Select</OPTION>
						 <OPTION VALUE='9'>Denver Aviation (Broomfield CO)</OPTION>
						 <OPTION VALUE='6'>Los Angeles Aviation (Inglewood CA)</OPTION>";	
}

if ($iProgramId == '10') {
	$sCollegeDropDown = "<OPTION VALUE=''>Please Select</OPTION>
						 <OPTION VALUE='2'>Anaheim (Anaheim CA)</OPTION>
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
						 <OPTION VALUE='27'>Virginia Arlington Ballston (Arlington VA)</OPTION>
						 <OPTION VALUE='19'>Westwood ONLINE</OPTION>";	
}


if ($iProgramId == '4') {
	$sCollegeDropDown = "<OPTION VALUE=''>Please Select</OPTION>
						 <OPTION VALUE='7'>Denver North (Denver CO)</OPTION>";	
}

if ($iProgramId == '24') {
	$sCollegeDropDown = "<OPTION VALUE=''>Please Select</OPTION>
						 <OPTION VALUE='9'>Denver Aviation (Broomfield CO)</OPTION>";	
}

if ($iProgramId == '26') {
	$sCollegeDropDown = "<OPTION VALUE=''>Please Select</OPTION>
						 <OPTION VALUE='9'>Denver Aviation (Broomfield CO)</OPTION>";	
}

if ($iProgramId == '18') {
	$sCollegeDropDown = "<OPTION VALUE=''>Please Select</OPTION>
						 <OPTION VALUE='19'>Westwood ONLINE</OPTION>";	
}


if ($iProgramId == '25') {
	$sCollegeDropDown = "<OPTION VALUE=''>Please Select</OPTION>
						 <OPTION VALUE='7'>Denver North (Denver CO)</OPTION>";	
}

if ($iProgramId == '5') {
	$sCollegeDropDown = "<OPTION VALUE=''>Please Select</OPTION>
						 <OPTION VALUE='2'>Anaheim (Anaheim CA)</OPTION>
						 <OPTION VALUE='11'>Chicago DuPage (Woodridge IL)</OPTION>
						 <OPTION VALUE='12'>Chicago Loop (Chicago IL)</OPTION>
						 <OPTION VALUE='13'>Chicago OHare Airport (Chicago IL)</OPTION>
						 <OPTION VALUE='14'>Chicago River Oaks (Calumet City IL)</OPTION>
						 <OPTION VALUE='3'>Inland Empire (Upland CA)</OPTION>
						 <OPTION VALUE='5'>Los Angeles (Los Angeles CA)</OPTION>
						 <OPTION VALUE='28'>Virginia Annandale (Annandale VA)</OPTION>
						 <OPTION VALUE='27'>Virginia Arlington Ballston (Arlington VA)</OPTION>
						 <OPTION VALUE='19'>Westwood ONLINE</OPTION>";	
}

if ($iProgramId == '1') {
	$sCollegeDropDown = "<OPTION VALUE=''>Please Select</OPTION>
						 <OPTION VALUE='3'>Inland Empire (Upland CA)</OPTION>
						 <OPTION VALUE='19'>Westwood ONLINE</OPTION>";	
}

if ($iProgramId == '29') {
	$sCollegeDropDown = "<OPTION VALUE='2'>Anaheim (Anaheim CA)</OPTION>
						 <OPTION VALUE='12'Chicago Loop (Chicago IL)></OPTION>
						 <OPTION VALUE='3'>Inland Empire (Upland CA)</OPTION>
						 <OPTION VALUE='5'>Los Angeles (Los Angeles CA)</OPTION>
						 <OPTION VALUE='19'>Westwood ONLINE</OPTION>";	
}

if ($iProgramId == '14') {
	$sCollegeDropDown = "<OPTION VALUE=''>Please Select</OPTION>
						 <OPTION VALUE='2'>Anaheim (Anaheim CA)</OPTION>
						 <OPTION VALUE='10'>Atlanta Midtown (Atlanta GA)</OPTION>
						 <OPTION VALUE='12'>Chicago Loop (Chicago IL)</OPTION>
						 <OPTION VALUE='14'>Chicago River Oaks (Calumet City IL)</OPTION>
						 <OPTION VALUE='7'>Denver North (Denver CO)</OPTION>
						 <OPTION VALUE='3'>Inland Empire (Upland CA)</OPTION>
						 <OPTION VALUE='5'>Los Angeles (Los Angeles CA)</OPTION>
						 <OPTION VALUE='19'>Westwood ONLINE</OPTION>";	
}

if ($iProgramId == '2') {
	$sCollegeDropDown = "<OPTION VALUE=''>Please Select</OPTION>
						 <OPTION VALUE='2'>Anaheim (Anaheim CA)</OPTION>
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
						 <OPTION VALUE='27'>Virginia Arlington Ballston (Arlington VA)</OPTION>
						 <OPTION VALUE='19'>Westwood ONLINE</OPTION>";	
}

if ($iProgramId == '3') {
	$sCollegeDropDown = "<OPTION VALUE=''>Please Select</OPTION>
						 <OPTION VALUE='2'>Anaheim (Anaheim CA)</OPTION>
						 <OPTION VALUE='14'>Chicago River Oaks (Calumet City IL)</OPTION>
						 <OPTION VALUE='8'>Denver South (Denver CO)</OPTION>
						 <OPTION VALUE='19'>Westwood ONLINE</OPTION>";	
}

if ($iProgramId == '34') {
	$sCollegeDropDown = "<OPTION VALUE=''>Please Select</OPTION>
						 <OPTION VALUE='7'>Denver North (Denver CO)</OPTION>
						 <OPTION VALUE='8'>Denver South (Denver CO)</OPTION>
						 <OPTION VALUE='19'>Westwood ONLINE</OPTION>";	
}

if ($iProgramId == '6') {
	$sCollegeDropDown = "<OPTION VALUE=''>Please Select</OPTION>
						 <OPTION VALUE='2'>Anaheim (Anaheim CA)</OPTION>
						 <OPTION VALUE='10'>Atlanta Midtown (Atlanta GA)</OPTION>
						 <OPTION VALUE='11'>Chicago DuPage (Woodridge IL)</OPTION>
						 <OPTION VALUE='7'>Denver North (Denver CO)</OPTION>
						 <OPTION VALUE='8'>Denver South (Denver CO)</OPTION>
						 <OPTION VALUE='3'>Inland Empire (Upland CA)</OPTION>
						 <OPTION VALUE='5'>Los Angeles (Los Angeles CA)</OPTION>
						 <OPTION VALUE='19'>Westwood ONLINE</OPTION>";	
}

if ($iProgramId == '15') {
	$sCollegeDropDown = "<OPTION VALUE=''>Please Select</OPTION>
						 <OPTION VALUE='2'>Anaheim (Anaheim CA)</OPTION>
						 <OPTION VALUE='13'>Chicago OHare Airport (Chicago IL)</OPTION>
						 <OPTION VALUE='14'>Chicago River Oaks (Calumet City IL)</OPTION>
						 <OPTION VALUE='3'>Inland Empire (Upland CA)</OPTION>
						 <OPTION VALUE='5'>Los Angeles (Los Angeles CA)</OPTION>
						 <OPTION VALUE='19'>Westwood ONLINE</OPTION>";	
}

if ($iProgramId == '36') {
	$sCollegeDropDown = "<OPTION VALUE=''>Please Select</OPTION>
						 <OPTION VALUE='7'>Denver North (Denver CO)</OPTION>";	
}

if ($iProgramId == '37') {
	$sCollegeDropDown = "<OPTION VALUE=''>Please Select</OPTION>
						 <OPTION VALUE='19'>Westwood ONLINE</OPTION>";	
}

if ($iProgramId == '16') {
	$sCollegeDropDown = "<OPTION VALUE=''>Please Select</OPTION>
						 <OPTION VALUE='22'>Atlanta Northlake (Atlanta GA)</OPTION>
						 <OPTION VALUE='11'>Chicago DuPage (Woodridge IL)</OPTION>
						 <OPTION VALUE='12'>Chicago Loop (Chicago IL)</OPTION>
						 <OPTION VALUE='13'>Chicago OHare Airport (Chicago IL)</OPTION>
						 <OPTION VALUE='14'>Chicago River Oaks (Calumet City IL)</OPTION>
						 <OPTION VALUE='7'>Denver North (Denver CO)</OPTION>
						 <OPTION VALUE='8'>Denver South (Denver CO)</OPTION>
						 <OPTION VALUE='3'>Inland Empire (Upland CA)</OPTION>
						 <OPTION VALUE='4'>Long Beach (Torrance CA)</OPTION>
						 <OPTION VALUE='19'>Westwood ONLINE</OPTION>";	
}

if ($iProgramId == '7') {
	$sCollegeDropDown = "<OPTION VALUE=''>Please Select</OPTION>
						 <OPTION VALUE='2'>Anaheim (Anaheim CA)</OPTION>
						 <OPTION VALUE='11'>Chicago DuPage (Woodridge IL)</OPTION>
						 <OPTION VALUE='12'>Chicago Loop (Chicago IL)</OPTION>
						 <OPTION VALUE='13'>Chicago OHare Airport (Chicago IL)</OPTION>
						 <OPTION VALUE='7'>Denver North (Denver CO)</OPTION>
						 <OPTION VALUE='8'>Denver South (Denver CO)</OPTION>
						 <OPTION VALUE='3'>Inland Empire (Upland CA)</OPTION>
						 <OPTION VALUE='4'>Long Beach (Torrance CA)</OPTION>
						 <OPTION VALUE='5'>Los Angeles (Los Angeles CA)</OPTION>
						 <OPTION VALUE='28'>Virginia Annandale (Annandale VA)</OPTION>
						 <OPTION VALUE='27'>Virginia Arlington Ballston (Arlington VA)</OPTION>
						 <OPTION VALUE='19'>Westwood ONLINE</OPTION>";	
}

if ($iProgramId == '27') {
	$sCollegeDropDown = "<OPTION VALUE=''>Please Select</OPTION>
						 <OPTION VALUE='7'>Denver North (Denver CO)</OPTION>";	
}

if ($iProgramId == '8') {
	$sCollegeDropDown = "<OPTION VALUE=''>Please Select</OPTION>
						 <OPTION VALUE='2'>Anaheim (Anaheim CA)</OPTION>
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
						 <OPTION VALUE='19'>Westwood ONLINE</OPTION>";	
}
if ($iProgramId == '9') {
	$sCollegeDropDown = "<OPTION VALUE=''>Please Select</OPTION>
						 <OPTION VALUE='3'>Inland Empire (Upland CA)</OPTION>
						 <OPTION VALUE='5'>Los Angeles (Los Angeles CA)</OPTION>
						 <OPTION VALUE='19'>Westwood ONLINE</OPTION>";	
}

if ($iProgramId == '20') {
	$sCollegeDropDown = "<OPTION VALUE=''>Please Select</OPTION>
						 <OPTION VALUE='9'>Denver Aviation (Broomfield CO)</OPTION>";	
}

if ($iProgramId == '22') {
	$sCollegeDropDown = "<OPTION VALUE=''>Please Select</OPTION>
						 <OPTION VALUE='10'>Atlanta Midtown (Atlanta GA)</OPTION>
						 <OPTION VALUE='22'>Atlanta Northlake (Atlanta GA)</OPTION>
						 <OPTION VALUE='13'>Chicago OHare Airport (Chicago IL)</OPTION>
						 <OPTION VALUE='14'>Chicago River Oaks (Calumet City IL)</OPTION>
						 <OPTION VALUE='15'>Dallas (Dallas TX)</OPTION>
						 <OPTION VALUE='8'>Denver South (Denver CO)</OPTION>
						 <OPTION VALUE='16'>Fort Worth (Fort Worth TX)</OPTION>
						 <OPTION VALUE='17'>Houston Technology (Houston TX)</OPTION>";	
}

if ($iProgramId == '33') {
	$sCollegeDropDown = "<OPTION VALUE=''>Please Select</OPTION>
						 <OPTION VALUE='15'>Dallas (Dallas TX)</OPTION>
						 <OPTION VALUE='7'>Denver North (Denver CO)</OPTION>
						 <OPTION VALUE='16'>Fort Worth (Fort Worth TX)</OPTION>
						 <OPTION VALUE='17'>Houston Technology (Houston TX)</OPTION>";	
}

if ($iProgramId == '31') {
	$sCollegeDropDown = "<OPTION VALUE=''>Please Select</OPTION>
						 <OPTION VALUE='19'>Westwood ONLINE</OPTION>";	
}

echo $sCollegeDropDown;

?>
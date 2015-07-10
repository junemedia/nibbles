<?php

// tables moved from nibbles database to nibbles_reference database
// -- spatel 7/24/06 at 4:40pm

//takes a zip, and returns a formatted select box of all of the courses for that given zip
include("includes/paths.php");

//my var is going to be called sZip
if(!$sZip){
	echo '';
	exit();
}

$sql = "SELECT campusId, campusName FROM nibbles_reference.TNVM_UOPC_campusData WHERE zip = '$sZip'";
$res = dbQuery($sql);
$oCampus = dbFetchObject($res);

if($sId){
	echo "$oCampus->campusId";
	exit();
}

if($sName){
	echo "$oCampus->campusName";
	exit();
}

$sql = "SELECT * FROM nibbles_reference.TNVM_UOPC_courseData WHERE campusId = $oCampus->campusId ORDER BY id ASC";
$res = dbQuery($sql);
$courses = array();
$weird_values = array();
while($oCourses = dbFetchObject($res)){
	array_push($courses, $oCourses->courseName);
	array_push($weird_values, $oCourses->value);
}

//so, now we format this, and print it.
echo "<select name='TNVM_UOPC_X_ae_area_of_interest'>";
echo "<option value=''> -- Select one -- </option>";
echo "<option value='X|NONE'>None - do not contact me</option>";
for($i=0;$i<count($courses);$i++){
	echo "<option value='".$weird_values[$i]."'>".$courses[$i]."</option>";
}
echo "</select>";

?>
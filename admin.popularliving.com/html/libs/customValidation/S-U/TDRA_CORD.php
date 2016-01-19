<?php

$iId = trim($_GET['iId']);
$ProgramDropDown = '';
	if ($iId == '7') {
                 $sProgramDropDown = "<option value='' selected='selected'>Please Select</option>
                                      <option value='141002'>Baking and Pastry Arts - Certificate Degree</option>
                                      <option value='301166'>Le Cordon Bleu Culinary Arts - Associate Degree</option>
                                      <option value='211016'>Le Cordon Bleu Hospitality and Restaurant Management - Associate Degree</option>";
}

if ($iId == '55') {
	$sProgramDropDown = "<option value='' selected='selected'>Please Select</option>
                             <option value='301051'>Le Cordon Bleu Culinary Arts - Associate Degree</option>";
}


if ($iId == '99') {
	$sProgramDropDown = "<option value='' selected='selected'>Please Select</option>
                             <option value='363501'>Le Cordon Bleu Culinary Arts - Associate Degree</option>
                             <option value='364086'>Le Cordon Bleu Patisserie and Baking - Associate Degree</option>";
}

if ($iId == '48') {
	$sProgramDropDown = "<option value='' selected='selected'>Please Select</option>
                             <option value='363928'>Le Cordon Bleu Culinary Arts - Associate Degree</option>
                             <option value='363930'>Le Cordon Bleu Hospitality and Restaurant Management - Associate Degree</option>
                             <option value='363931'>Le Cordon Bleu Patisserie and Baking - Associate Degree</option>";
}

if ($iId == '5') {
	$sProgramDropDown = "<option value='' selected='selected'>Please Select</option>
                             <option value='363319'>Le Cordon Bleu Culinary Arts - Associate Degree</option>
                             <option value='363567'>Le Cordon Bleu Culinary Management - Bachelor Degree</option>
                             <option value='363140'>Le Cordon Bleu Hospitality and Restaurant Management - Associate Degree</option>
                             <option value='363822'>Le Cordon Bleu Hospitality and Restaurant Management - Bachelor Degree</option>
                             <option value='363356'>Le Cordon Bleu Patisserie and Baking - Associate Degree</option>
                             <option value='363418'>Le Cordon Bleu Patisserie and Baking - Certificate Degree</option>";
}

if ($iId == '6') {
	$sProgramDropDown = "<option value='' selected='selected'>Please Select</option>
                             <option value='364037'>Le Cordon Bleu Culinary Arts - Associate Degree</option>
                             <option value='364081'>Le Cordon Bleu Culinary Arts - Diploma Degree</option>
                             <option value='364038'>Le Cordon Bleu Hospitality and Restaurant Management - Associate Degree</option>
                             <option value='364076'>Le Cordon Bleu Patisserie and Baking - Diploma Degree</option>
                             <option value='364075'>Le Cordon Blue Patisserie and Baking - Associate Degree</option>";
}

echo $sProgramDropDown;

?>
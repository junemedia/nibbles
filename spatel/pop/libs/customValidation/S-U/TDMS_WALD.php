<?php

$iId = trim($_GET['iId']);
$ProgramDropDown = '';
	if ($iId == 'M.S. in Education') {
	$sProgramDropDown = "<option value='' selected='selected'>Please Select A Program Of Interest</option>
                            <OPTION VALUE='M.E.GN'>M.S. in Education - General</OPTION>
                            <OPTION VALUE='MS.W1EDU.EDU.W1CA'>Curriculum, Instruction, and Assessment</OPTION>
                            <OPTION VALUE='MS.W1EDU.EDU.W1EL'>Educational Leadership</OPTION>
                            <OPTION VALUE='MS.W1EDU.EDU.W1ER'>Elementary Reading and Literacy</OPTION>
                            <OPTION VALUE='MS.W1EDU.EDU.W1ERM'>Elementary Reading and Mathematics</OPTION>
                            <OPTION VALUE='MS.W1EDU.EDU.W1IC'>Integrating Technology in the Classroom</OPTION>
                            <OPTION VALUE='MS.W1EDU.EDU.W1LL'>Literacy and Learning in the Content Areas</OPTION>
                            <OPTION VALUE='MS.W1EDU.EDU.W1MA'>Mathematics (Grades 6-8)</OPTION>
                            <OPTION VALUE='MS.W1EDU.EDU.W1MAK'>Mathematics (Grades K-5)</OPTION>
                            OPTION VALUE='MS.W1EDU.EDU.W1ML'>Middle Level Education</OPTION>
                            <OPTION VALUE='MS.W1EDU.EDU.W1SCI'>Science (K-8)</OPTION>";
	}

if ($iId == 'Doctor of Education (Ed.D.)') {
	$sProgramDropDown = "<option value='' selected='selected'>Please Select A Program Of Interest</option>
                            <OPTION VALUE='EDD.W1EDU.EDU.W1TL'>Doctor of Education (Ed.D.) - General</OPTION>
                            <OPTION VALUE='EDD.W1EDU.EDU.W1TL'>Teacher Leadership</OPTION>
                            <OPTION VALUE='EDD.W1EDU.EDU.W1AL'>Administrator Leadership for Teaching and Learning</OPTION>";
	}
if ($iId == 'Ph.D. in Education') {
	$sProgramDropDown = "<option value='' selected='selected'>Please Select A Program Of Interest</option>
                            <OPTION VALUE='PHD.W1EDU.EDU.W1GN'>Ph.D. in Education - General</OPTION>
                            <OPTION VALUE='PHD.W1EDU.EDU.W1AE'>Adult Education Leadership</OPTION>
                            <OPTION VALUE='PHD.W1EDU.EDU.W1CC'>Community College Leadership</OPTION>
                            <OPTION VALUE='PHD.W1EDU.EDU.W1EC'>Early Childhood Education</OPTION>
                            <OPTION VALUE='PHD.W1EDU.EDU.W1ET'>Educational Technology</OPTION>
                            <OPTION VALUE='PHD.W1EDU.EDU.W1HE'>Higher Education</OPTION>
                            <OPTION VALUE='PHD.W1EDU.EDU.W1KL'>K-12 Education Leadership</OPTION>
                            <OPTION VALUE='PHD.W1EDU.EDU.W1SE'>Special Education</OPTION>";
	}
if ($iId == 'M.S. in Computer Engineering') {
	$sProgramDropDown = "<option value='' selected='selected'>Please Select A Program Of Interest</option>
                             <OPTION VALUE='MS.W1EGR.CE'>M.S. in Computer Engineering</OPTION>";
	}
if ($iId == 'M.S. in Computer Science') {
	$sProgramDropDown = "<option value='' selected='selected'>Please Select A Program Of Interest</option>
                             <OPTION VALUE='MS.W1EGR.CS'>M.S. in Computer Science</OPTION>";
	}
if ($iId == 'M.S. in Electrical Engineering') {
	$sProgramDropDown = "<option value='' selected='selected'>Please Select A Program Of Interest</option>
                             <OPTION VALUE='MS.W1EGR.EE.W1EE'>M.S. in Electrical Engineering - General</OPTION>
                            <OPTION VALUE='MS.W1EGR.EE.W1MES'>Microelectronic and Semiconductor Engineering</OPTION>
                            <OPTION VALUE='MS.W1EGR.EE.W1CC'>Communications Engineering</OPTION>
                            <OPTION VALUE='MS.W1EGR.EE.W1IC'>Integrated Circuits Engineering</OPTION>";
	}
if ($iId == 'M.S. in Engineering Management') {
	$sProgramDropDown = "<option value='' selected='selected'>Please Select A Program Of Interest</option>
                             <OPTION VALUE='MS.W1EGR.EM'>M.S. in Engineering Management</OPTION>";
	}
if ($iId == 'M.S. in Software Engineering') {
	$sProgramDropDown = "<option value='' selected='selected'>Please Select A Program Of Interest</option>
        <OPTION VALUE='MS.W1EGR.SE'>M.S. in Software Engineering</OPTION>";
	}
if ($iId == 'M.S. in Systems Engineering') {
	$sProgramDropDown = "<option value='' selected='selected'>Please Select A Program Of Interest</option>
                             <OPTION VALUE='MS.W1EGR.SY'>M.S. in Systems Engineering</OPTION>";
	}
if ($iId == 'M.S. in Nursing (BSN to MSN)') {
	$sProgramDropDown = "<option value='' selected='selected'>Please Select A Program Of Interest</option>
                              <OPTION VALUE='NURSING.GEN.INTEREST'>Master's in Nursing (BSN - MSN) - General</OPTION>
                              <OPTION VALUE='MS.W1HHS.NUR.W1ED'>Education</OPTION>
                              <OPTION VALUE='MS.W1HHS.NUR.W1LMS'>Leadership and Management</OPTION>";
	}
if ($iId == 'M.S. in Nursing (RN to MSN)') {
	$sProgramDropDown = "<option value='' selected='selected'>Please Select A Program Of Interest</option>
                             <OPTION VALUE='NURSING.GEN.INTERES2'>Masters in Nursing (RN - MSN) - General</OPTION'>
                            <OPTION VALUE='MS.W1HHS.NUR.W1ED2'>Education</OPTION>
                            <OPTION VALUE='MS.W1HHS.NUR.W1LMS2'>Leadership and Management</OPTION>";
	}
if ($iId == 'M.S. in Public Health') {
	$sProgramDropDown = "<option value='' selected='selected'>Please Select A Program Of Interest</option>
                             <OPTION VALUE='MS.PUBH.GN'>M.S. in Public Health - General</OPTION>
                             <OPTION VALUE='MS.W1HHS.PBH.W1CH'>Community Health</OPTION>";
	}
if ($iId == 'Master of Public Health') {
	$sProgramDropDown = "<option value='' selected='selected'>Please Select A Program Of Interest</option>
                            <OPTION VALUE='MPH.W1HHS.PBH.W1CH'>Master of Public Health - General</OPTION>
                            <OPTION VALUE='MPH.W1HHS.PBH.W1CH'>Community Health</OPTION>
";
	}
if ($iId == 'Ph.D. in Health Services') {
	$sProgramDropDown = "<option value='' selected='selected'>Please Select A Program Of Interest</option>
                            <OPTION VALUE='PHD.W1HHS.HTH.W1GN'>Ph.D. in Health Services - General</OPTION>
                            <OPTION VALUE='PHD.W1HHS.HTH.W1CH'>Community Health</OPTION>
                            <OPTION VALUE='PHD.W1HHS.HTH.W1HB'>Health and Human Behavior</OPTION>
                            <OPTION VALUE='PHD.W1HHS.HTH.W1HMP'>Health Management and Policy</OPTION>
                            <OPTION VALUE='PHD.W1HHS.HTH.W1HP'>Health Promotion and Education</OPTION>";
	}
if ($iId == 'Ph.D. in Human Services') {
	$sProgramDropDown = "<option value='' selected='selected'>Please Select A Program Of Interest</option>
                            <OPTION VALUE='PHD.W1HHS.HMN.W1GN'>Ph.D. in Human Services - General</OPTION>
                            <OPTION VALUE='PHD.W1HHS.HMN.W1CSW'>Clinical Social Work</OPTION>
                            <OPTION VALUE='PHD.W1HHS.HMN.W1CJ'>Criminal Justice</OPTION>
                            <OPTION VALUE='PHD.W1HHS.HMN.W1PC'>Counseling</OPTION>
                            <OPTION VALUE='PHD.W1HHS.HMN.W1FS'>Family Studies and Intervention Strategies</OPTION>
                            <OPTION VALUE='PHD.W1HHS.HMN.W1HSA'>Human Services Administration</OPTION>
                            <OPTION VALUE='PHD.W1HHS.HMN.W1SO'>Social Policy Analysis and Planning</OPTION>";
	}
if ($iId == 'Ph.D. in Public Health') {
	$sProgramDropDown = "<option value='' selected='selected'>Please Select A Program Of Interest</option>
                            <OPTION VALUE='PHD.PUBH.GN'>Ph.D. in Public Health - General</OPTION>
                            <OPTION VALUE='PHD.W1HHS.PBH.W1CH'>Community Health Education and Promotion</OPTION>
                            <OPTION VALUE='PHD.W1HHS.PBH.W1EP'>Epidemiology</OPTION>";
	}
if ($iId == 'M.S. in Mental Health Counseling') {
	$sProgramDropDown = "<option value='' selected='selected'>Please Select A Program Of Interest</option>
                             <OPTION VALUE='MS.W1PSY.MHC.W1GN'>M.S. in Mental Health Counseling</OPTION>";
	}
if ($iId == 'M.S. in Psychology') {
	$sProgramDropDown = "<option value='' selected='selected'>Please Select A Program Of Interest</option>
                             <OPTION VALUE='MS.W1PSY.PSY.W1GN'>M.S. in Psychology - General</OPTION>
                             <OPTION VALUE='MS.W1PSY.PSY.W1IO'>Industrial/Organizational Psychology</OPTION>";
	}
if ($iId == 'Ph.D. in Psychology') {
	$sProgramDropDown = "<option value='' selected='selected'>Please Select A Program Of Interest</option>
                            <OPTION VALUE='PHD.PSYC.GN'>Ph.D. in Psychology</OPTION>
                            <OPTION VALUE='PHD.W1PSY.PSY.W1CN'>Clinical Psychology</OPTION>
                            <OPTION VALUE='PHD.W1PSY.PSY.W1CO'>Counseling Psychology</OPTION>
                            <OPTION VALUE='PHD.W1PSY.PSY.W1EDP'>General Educational Psychology</OPTION>
                            <OPTION VALUE='PHD.W1PSY.PSY.W1RE'>General Research Evaluation</OPTION>
                            <OPTION VALUE='PHD.W1PSY.PSY.W1HLP'>Health Psychology</OPTION>
                            <OPTION VALUE='PHD.W1PSY.PSY.W1OG'>Organizational Psychology</OPTION>
                            <OPTION VALUE='PHD.W1PSY.PSY.W1SCL'>School Psychology</OPTION>";
	}
if ($iId == 'Master of Public Administration (M.P.A.)') {
	$sProgramDropDown = "<option value='' selected='selected'>Please Select A Program Of Interest</option>
                            <OPTION VALUE='MPA.W1MGT.PA.W1GN'>Master of Public Administration (M.P.A.) - General</OPTION>
                            <OPTION VALUE='MPA.W1MGT.PA.W1EG'>E-Government</OPTION>
                            <OPTION VALUE='MPA.W1MGT.PA.W1ERP'>Emergency Response Policy and Coordination</OPTION>
                            <OPTION VALUE='MPA.W1MGT.PA.W1HS'>Health Services</OPTION>
                            <OPTION VALUE='MPA.W1MGT.PA.W1NGO'>International Non Governmental Organizations</OPTION>
                            <OPTION VALUE='MPA.W1MGT.PA.W1KM'>Knowledge Management</OPTION>
                            <OPTION VALUE='MPA.W1MGT.PA.W1NPL'>Nonprofit Management and Leadership</OPTION>
                            <OPTION VALUE='MPA.W1MGT.PA.W1PML'>Public Management and Leadership</OPTION>
                            <OPTION VALUE='MPA.W1MGT.PA.W1PP'>Public Policy</OPTION>
                            <OPTION VALUE='MPA.W1MGT.PA.W1PSM'>Public Safety Management</OPTION>";
	}
if ($iId == 'Ph.D. in Public Policy and Administration') {
	$sProgramDropDown = "<option value='' selected='selected'>Please Select A Program Of Interest</option>
                            <OPTION VALUE='PHD.W1MGT.PPA.W1GN'>Ph.D. in Public Policy and Administration</OPTION>
                            <OPTION VALUE='PHD.W1MGT.PPA.W1EG'>E-Government</OPTION>
                            <OPTION VALUE='PHD.W1MGT.PPA.W1ERP'>Emergency Response Policy and Coordination</OPTION>
                            <OPTION VALUE='PHD.W1MGT.PPA.W1HS'>Health Services</OPTION>
                            <OPTION VALUE='PHD.W1MGT.PPA.W1NGO'>International Non Governmental Organizations</OPTION>
                            <OPTION VALUE='PHD.W1MGT.PPA.W1KM'>Knowledge Management</OPTION>
                            <OPTION VALUE='PHD.W1MGT.PPA.W1NPL'>Nonprofit Management and Leadership</OPTION>
                            <OPTION VALUE='PHD.W1MGT.PPA.W1PML'>Public Management and Leadership</OPTION>
                            <OPTION VALUE='PHD.W1MGT.PPA.W1PP'>Public Policy</OPTION>
                            <OPTION VALUE='PHD.W1MGT.PPA.W1PSM'>Public Safety Management</OPTION>";
	}
if ($iId == 'Ph.D. in Applied Management and Decision Sciences') {
	$sProgramDropDown = "<option value='' selected='selected'>Please Select A Program Of Interest</option>
                            <OPTION VALUE='PHD.W1MGT.AMD.W1GN'>Ph.D. in Applied Management and Decision Sciences - General</OPTION>
                            <OPTION VALUE='PHD.W1MGT.AMD.W1EM'>Engineering Management</OPTION>
                            <OPTION VALUE='PHD.W1MGT.AMD.W1FN'>Finance</OPTION>
                            <OPTION VALUE='PHD.W1MGT.AMD.W1IS'>Information Systems Management</OPTION>
                            <OPTION VALUE='PHD.W1MGT.AMD.W1KM'>Knowledge Management</OPTION>
                            <OPTION VALUE='PHD.W1MGT.AMD.W1LO'>Leadership and Organizational Change</OPTION>
                            <OPTION VALUE='PHD.W1MGT.AMD.W1LM'>Learning Management</OPTION>
                            <OPTION VALUE='PHD.W1MGT.AMD.W1OR'>Operations Research</OPTION>";
	}
echo $sProgramDropDown;

?>

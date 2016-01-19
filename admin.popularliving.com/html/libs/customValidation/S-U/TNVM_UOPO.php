<?php

$iId = trim($_GET['iId']);
$ProgramDropDown = '';
	if ($iId == '2') {
	$sProgramDropDown = "
	<option value='BSB/ACC'>Bachelor of Science in Business/Accounting</option>
						<option value='BSB/A'>Bachelor of Science in Business/Administration</option>
						<option value='BSB/M'>Bachelor of Science in Business/Management</option>
						<option value='BSB/MKT'>Bachelor of Science in Business/Marketing</option>
						<option value='BSCJA'>Bachelor of Science in Criminal Justice Administration</option>
						<option value='BSED/E'>Bachelor of Science in Education/Elementary Teacher Education</option>
						<option value='BSHCS'>Bachelor of Science in Health Administration</option>
						<option value='BSHS'>Bachelor of Science in Human Services</option>
						<option value='BSIT'>Bachelor of Science in Information Technology</option>
						<option value='BSN'>Bachelor of Science in Nursing</option>
						<option value='MAED/ADM'>Master of Arts in Education/Administration & Supervision</option>
						<option value='MAED/CI/CE'>Master of Arts in Education/Computer Education</option>
						<option value='MAED/CI'>Master of Arts in Education/Curriculum & Instruction</option>
						<option value='MAED/CI/AE'>Master of Arts in Education/Curriculum & Instruction/Adult Education</option>
						<option value='MAED/SPE'>Master of Arts in Education/Special Education</option>
						<option value='MAED/TED-E'>Master of Arts in Education/Teacher Education for Elementary Licensure</option>
						<option value='MAED/TED-S'>Master of Arts in Education/Teacher Education for Secondary Licensure</option>
						<option value='MBA'>Master of Business Administration</option>
						<option value='MHA'>Master of Health in Administration</option>
						<option value='MIS/M'>Master of Information Systems/Management</option>
						<option value='MM'>Master of Management</option>
						<option value='MSC/CC'>Master of Science in Counseling/Community Counseling</option>
						<option value='MSC/MFCT'>Master of Science in Counseling/Marriage, Family and Child Therapy</option>
						<option value='MSN'>Master of Science in Nursing</option>
						<option value='MSN/FNP'>Master of Science in Nursing/Family Nurse Practitioner</option>
						<option value='CONT-ED'>Continuing Teacher Education</option>
						<option value='CERT/FNP'>Family Nurse Practicioner Certificate</option>
						<option value='CFP'>Financial Planning Professional Certificate</option>
						<option value='CERT/HRM'>Human Resources Management Certificate</option>
						<option value='MED'>Mediation Certificate</option>
						<option value='CERT/MSN/N/HCE'>Nursing/Health Care Education Certificate</option>
						<option value='CERT/PM'>Project Management Certificate</option>
						<option value='SHRM'>Society for Human Resources Management Prep Course</option>";
											}

if ($iId == '3') {
	$sProgramDropDown = "
	<option value='BSB/ACC'>Bachelor of Science in Business/Accounting</option>
						<option value='BSB/F'>Bachelor of Science in Business/Finance</option>
						<option value='BSB/M'>Bachelor of Science in Business/Management</option>
						<option value='BSB/MKT'>Bachelor of Science in Business/Marketing</option>
						<option value='BSCJA'>Bachelor of Science in Criminal Justice Administration</option>
						<option value='BSED/E'>Bachelor of Science in Education/Elementary Teacher Education</option>
						<option value='BSHA'>Bachelor of Science in Health Administration</option>
						<option value='BSHS/M'>Bachelor of Science in Human Services</option>
						<option value='BSIT'>Bachelor of Science in Information Technology</option>
						<option value='BSM'>Bachelor of Science in Management</option>
						<option value='BSN'>Bachelor of Science in Nursing</option>
						<option value='MAED/CI'>Master of Arts in Education/Curriculum & Instruction</option>
						<option value='MAED/TED-E	'>Master of Arts in Education/Teacher Education for Elementary Licensure</option>
						<option value='MAED/TED-S'>Master of Arts in Education/Teacher Education for Secondary Licensure</option>
						<option value='MBA'>Master of Business Administration</option>
						<option value='MBA/ACC'>Master of Business Administration/Accounting</option>
						<option value='MBA/EB'>Master of Business Administration/e-Business</option>
						<option value='MBA/GM'>Master of Business Administration/Global Management</option>
						<option value='MBA/HCM'>Master of Business Administration/Health Care Management</option>
						<option value='MBA/HRM'>Master of Business Administration/Human Resources Management</option>
						<option value='MBA/MKT'>Master of Business Administration/Marketing</option>
						<option value='MBA/TM'>Master of Business Administration/Technology Management</option>
						<option value='MIS/M'>Master of Information Systems/Management</option>
						<option value='MM'>Master of Management</option>
						<option value='MSC/MHC'>Master of Science in Counseling/Mental Health Counseling</option>
						<option value='MSC/SC'>Master of Science in Counseling/School Counseling</option>
						<option value='MSN'>Master of Science in Nursing</option>";
						 
}


if ($iId == '4') {
	$sProgramDropDown = "
	<OPTION VALUE='BSB/ACC'>Bachelor of Science in Business/Accounting</option>
						<OPTION VALUE='BSB/A'>Bachelor of Science in Business/Administration</option>
						<OPTION VALUE='BSB/IS'>Bachelor of Science in Business/Information Systems</option>
						<OPTION VALUE='BSB/M'>Bachelor of Science in Business/Management</option>
						<OPTION VALUE='BSB/MKT'>Bachelor of Science in Business/Marketing</option>
						<OPTION VALUE='BSB/PA'>Bachelor of Science in Business/Public Administration</option>
						<OPTION VALUE='BSCJA'>Bachelor of Science in Criminal Justice Administration</option>
						<OPTION VALUE='BSHA'>Bachelor of Science in Health Administration</option>
						<OPTION VALUE='BSHS'>Bachelor of Science in Human Services</option>
						<OPTION VALUE='BSHS/M'>Bachelor of Science in Human Services</option>
						<OPTION VALUE='BSIT'>Bachelor of Science in Information Technology</option>
						<OPTION VALUE='BSM'>Bachelor of Science in Management</option>
						<OPTION VALUE='BSN'>Bachelor of Science in Nursing</option>
						<OPTION VALUE='MAED/CI'>Master of Arts in Education/Curriculum & Instruction</option>
						<OPTION VALUE='MAED/TED-E'>Master of Arts in Education/Teacher Education for Elementary Licensure</option>
						<OPTION VALUE='MAED/TED-S'>Master of Arts in Education/Teacher Education for Secondary Licensure</option>
						<OPTION VALUE='MBA'>Master of Business Administration</option>
						<OPTION VALUE='MBA/ACC'>Master of Business Administration/Accounting</option>
						<OPTION VALUE='MBA/GM'>	Master of Business Administration/Global Management</option>
						<OPTION VALUE='MBA/HCM	'>Master of Business Administration/Health Care Management</option>
						<OPTION VALUE='MBA/HRM	'>Master of Business Administration/Human Resources Management</option>
						<OPTION VALUE='MBA/MKT'>Master of Business Administration/Marketing</option>
						<OPTION VALUE='MBA/TM'>Master of Business Administration/Technology Management</option>
						<OPTION VALUE='MHA'>Master of Health in Administration</option>
						<OPTION VALUE='MIS'>Master of Information Systems</option>
						<OPTION VALUE='MIS/M'>Master of Information Systems/Management</option>
						<OPTION VALUE='MM'>Master of Management</option>
						<OPTION VALUE='MM/HRM'>Master of Management/Human Resources</option>
						<OPTION VALUE='MSC/MFCT'>Master of Science in Counseling/Marriage, Family and Child Therapy</option>
						<OPTION VALUE='MSN'>Master of Science in Nursing</option>
						<OPTION VALUE='MSN/FNP'>Master of Science in Nursing/Family Nurse Practitioner</option>
						<OPTION VALUE='MSN/N/HCE'>Master of Science in Nursing/Nursing/Health Care Education</option>";	
}

if ($iId == '5') {
	$sProgramDropDown = "
	<OPTION VALUE='BSB/ACC'>Bachelor of Science in Business/Accounting</option>
						<OPTION VALUE='BSB/A'>Bachelor of Science in Business/Administration</option>
						<OPTION VALUE='BSB/M'>Bachelor of Science in Business/Management</option>
						<OPTION VALUE='BSB/PA'>Bachelor of Science in Business/Public Administration</option>
						<OPTION VALUE='BSCJA'>Bachelor of Science in Criminal Justice Administration</option>
						<OPTION VALUE='BSHA'>Bachelor of Science in Health Administration</option>
						<OPTION VALUE='BSHS'>Bachelor of Science in Human Services</option>
						<OPTION VALUE='BSIT'>Bachelor of Science in Information Technology</option>
						<OPTION VALUE='BSN'>Bachelor of Science in Nursing</option>
						<OPTION VALUE='MAED/CI'>Master of Arts in Education/Curriculum & Instruction</option>
						<OPTION VALUE='MAED/TED-E'>Master of Arts in Education/Teacher Education for Elementary Licensure</option>
						<OPTION VALUE='MAED/TED-S'>Master of Arts in Education/Teacher Education for Secondary Licensure</option>
						<OPTION VALUE='MBA'>Master of Business Administration</option>
						<OPTION VALUE='MBA/ACC'>Master of Business Administration/Accounting</option>
						<OPTION VALUE='MBA/HCM'>Master of Business Administration/Health Care Management</option>
						<OPTION VALUE='MBA/HRM'>Master of Business Administration/Human Resources Management</option>
						<OPTION VALUE='MBA/MKT'>Master of Business Administration/Marketing</option>
						<OPTION VALUE='MBA/TM'>Master of Business Administration/Technology Management</option>
						<OPTION VALUE='MHA'>Master of Health in Administration</option>
						<OPTION VALUE='MM'>Master of Management</option>
						<OPTION VALUE='MSCIS'>Master of Science in Computer Information Systems</option>
						<OPTION VALUE='MSN'>Master of Science in Nursing</option>
						<OPTION VALUE='MSN/FNP'>Master of Science in Nursing/Family Nurse Practitioner</option>
						<OPTION VALUE='MSN/N/HCE'>Master of Science in Nursing/Nursing/Health Care Education</option>
						<OPTION VALUE='RN/MSN/MSN/FNP'>RN to MSN or MSN/FNP Bridge, BSN Equivalency Requirement</option>";	
}
if ($iId == '6') {
	$sProgramDropDown = "
	<OPTION VALUE='BSB/ACC'>Bachelor of Science in Business/Accounting</option>
						<OPTION VALUE='BSB/A'>Bachelor of Science in Business/Administration</option>
						<OPTION VALUE='BSB/M'>Bachelor of Science in Business/Management</option>
						<OPTION VALUE='BSB/MKT'>Bachelor of Science in Business/Marketing</option>
						<OPTION VALUE='BSCJA'>Bachelor of Science in Criminal Justice Administration</option>
						<OPTION VALUE='BSED/E'>Bachelor of Science in Education/Elementary Teacher Education</option>
						<OPTION VALUE='BSHS'>Bachelor of Science in Human Services</option>
						<OPTION VALUE='BSIT'>Bachelor of Science in Information Technology</option>
						<OPTION VALUE='BSIT/VC'>	Bachelor of Science in Information Technology/Visual Communication</option>
						<OPTION VALUE='BSN'>Bachelor of Science in Nursing</option>
						<OPTION VALUE='LPN/BSN'>Licensed Practical Nurse to Bachelor of Science in Nursing</option>
						<OPTION VALUE='MAED/ADM'>Master of Arts in Education/Administration & Supervision</option>
						<OPTION VALUE='MAED/CI'>Master of Arts in Education/Curriculum & Instruction</option>
						<OPTION VALUE='MAED/CI/AE'>Master of Arts in Education/Curriculum & Instruction/Adult Education</option>
						<OPTION VALUE='MAED/SPE	'>Master of Arts in Education/Special Education</option>
						<OPTION VALUE='MAED/TED-E'>Master of Arts in Education/Teacher Education for Elementary Licensure</option>
						<OPTION VALUE='MAED/TED-S'>Master of Arts in Education/Teacher Education for Secondary Licensure</option>
						<OPTION VALUE='MBA'>Master of Business Administration</option>
						<OPTION VALUE='MBA/GM'>Master of Business Administration/Global Management</option>
						<OPTION VALUE='MBA/TM'>Master of Business Administration/Technology Management</option>
						<OPTION VALUE='MSC/MFCT'>Master of Counseling/Marriage, Family & Child Therapy</option>
						<OPTION VALUE='MIS'>Master of Information Systems</option>
						<OPTION VALUE='MIS/M'>Master of Information Systems/Management</option>
						<OPTION VALUE='MM'>Master of Management</option>
						<OPTION VALUE='MSC/SC'>Master of Science in Counseling/School Counseling</option>
						<OPTION VALUE='MSN'>Master of Science in Nursing</option>
						<OPTION VALUE='MSN/FNP'>Master of Science in Nursing/Family Nurse Practitioner</option>";	
}
if ($iId == '7') {
	$sProgramDropDown = "
	<OPTION VALUE='BSB/ACC'>Bachelor of Science in Business/Accounting</OPTION>
						<OPTION VALUE='BSB/A'>Bachelor of Science in Business/Administration</OPTION>
						<OPTION VALUE='BSB/M'>Bachelor of Science in Business/Management</OPTION>
						<OPTION VALUE='BSCJA'>Bachelor of Science in Criminal Justice Administration</OPTION>
						<OPTION VALUE='BSHS/M'>Bachelor of Science in Human Services</OPTION>
						<OPTION VALUE='BSIT/SE'>Bachelor of Science in Information Technology/Software Engineering</OPTION>
						<OPTION VALUE='BSIT/VC'>Bachelor of Science in Information Technology/Visual Communication</OPTION>
						<OPTION VALUE='BSN'>Bachelor of Science in Nursing</OPTION>
						<OPTION VALUE='LPN/BSN'>Licensed Practical Nurse to Bachelor of Science in Nursing</OPTION>
						<OPTION VALUE='MAED/ADM'>Master of Arts in Education/Administration & Supervision</OPTION>
						<OPTION VALUE='MAED/CI'>Master of Arts in Education/Curriculum & Instruction</OPTION>
						<OPTION VALUE='MAED/TED-E'>Master of Arts in Education/Teacher Education for Elementary Licensure</OPTION>
						<OPTION VALUE='MAED/TED-S'>Master of Arts in Education/Teacher Education for Secondary Licensure</OPTION>
						<OPTION VALUE='MBA'>Master of Business Administration</OPTION>
						<OPTION VALUE='MBA/ACC'>Master of Business Administration/Accounting</OPTION>
						<OPTION VALUE='MBA/HCM'>Master of Business Administration/Health Care Management</OPTION>
						<OPTION VALUE='MBA/TM'>Master of Business Administration/Technology Management</OPTION>
						<OPTION VALUE='MM'>Master of Management</OPTION>
						<OPTION VALUE='MSC/CC'>Master of Science in Counseling/Community Counseling</OPTION>
						<OPTION VALUE='MSC/SC'>Master of Science in Counseling/School Counseling</OPTION>
						<OPTION VALUE='MSN'>Master of Science in Nursing</OPTION>
						<OPTION VALUE='HRM'>Professional Certificate Program/Human Resource Management</OPTION>
						<OPTION VALUE='PM'>Professional Certificate Program/Project Management</OPTION>
						<OPTION VALUE='CERT/PL'>Professional Education Certificate Program/Practical Licensure</OPTION>";	
}
if ($iId == '8') {
	$sProgramDropDown = "
	<option value='BSB/ACC'>Bachelor of Science in Business/Accounting</option>
						<option value='BSB/A'>Bachelor of Science in Business/Administration</option>
						<option value='BSB/M'>Bachelor of Science in Business/Management</option>
						<option value='BSCJA'>Bachelor of Science in Criminal Justice Administration</option>
						<option value='BSHS'>Bachelor of Science in Human Services</option>
						<option value='BSIT'>Bachelor of Science in Information Technology</option>
						<option value='BSM'>Bachelor of Science in Management</option>
						<option value='BSN'>Bachelor of Science in Nursing</option>
						<option value='MAED/ADM'>Master of Arts in Education/Administration & Supervision</option>
						<option value='MAED/CI'>Master of Arts in Education/Curriculum & Instruction</option>
						<option value='MAED/TED-E'>Master of Arts in Education/Teacher Education for Elementary Licensure</option>
						<option value='MAED/TED-S'>Master of Arts in Education/Teacher Education for Secondary Licensure</option>
						<option value='MBA'>Master of Business Administration</option>
						<option value='MBA/ACC'>Master of Business Administration/Accounting</option>
						<option value='MBA/EB'>Master of Business Administration/e-Business</option>
						<option value='MBA/GM'>Master of Business Administration/Global Management</option>
						<option value='MBA/HCM'>Master of Business Administration/Health Care Management</option>
						<option value='MBA/HRM'>Master of Business Administration/Human Resources Management</option>
						<option value='MBA/MKT'>Master of Business Administration/Marketing</option>
						<option value='MBA/TM'>Master of Business Administration/Technology Management</option>
						<option value='MS/AJS'>Master of Science in Administration of Justice and Security</option>
						<option value='MSC/MFCT'>Master of Science in Counseling/Marriage, Family and Child Therapy</option>
						<option value='MSN'>Master of Science in Nursing</option>";	
}
if ($iId == '10') {
	$sProgramDropDown = "
	<option value='BSB/ACC'>Bachelor of Science in Business/Accounting</option>
<option value='BSB/A'>Bachelor of Science in Business/Administration</option>
<option value='BSB/M'>Bachelor of Science in Business/Management</option>
<option value='BSB/MKT'>Bachelor of Science in Business/Marketing</option>
<option value='BSCJA'>Bachelor of Science in Criminal Justice Administration</option>
<option value='BSHA'>Bachelor of Science in Health Administration</option>
<option value='BSHS/M'>Bachelor of Science in Human Services</option>
<option value='BSIT'>Bachelor of Science in Information Technology</option>
<option value='BSIT/VC'>Bachelor of Science in Information Technology/Visual Communication</option>
<option value='BSN'>Bachelor of Science in Nursing</option>
<option value='MAED/CI/CE'>aster of Arts in Education/Computer Education</option>
<option value='MAED/CI'>Master of Arts in Education/Curriculum & Instruction</option>
<option value='MAED/CI/AE'>Master of Arts in Education/Curriculum & Instruction/Adult Education</option>
<option value='MAED/TED-E'>Master of Arts in Education/Teacher Education for Elementary Licensure</option>
<option value='MAED/TED-S'>Master of Arts in Education/Teacher Education for Secondary Licensure</option>
<option value='MBA'>Master of Business Administration</option>
<option value='MBA/GM'>Master of Business Administration/Global Management</option>
<option value='MBA/HCM'>Master of Business Administration/Health Care Management</option>
<option value='MBA/TM'>Master of Business Administration/Technology Management</option>
<option value='MIS'>Master of Information Systems</option>
<option value='MIS/M'>Master of Information Systems/Management</option>
<option value='MM'>Master of Management</option>
<option value='MS/AJS'>Master of Science in Administration of Justice and Security</option>
<option value='MSC/MFCT'>Master of Science in Counseling/Marriage, Family and Child Therapy</option>
<option value='MSN'>Master of Science in Nursing</option>";	
}
if ($iId == '15') {
	$sProgramDropDown = "
	<option value='BSB/ACC'>Bachelor of Science in Business/Accounting</option>
<option value='BSB/A'>Bachelor of Science in Business/Administration</option>
<option value='BSB/EB'>Bachelor of Science in Business/e-Business</option>
<option value='BSB/M'>Bachelor of Science in Business/Management</option>
<option value='BSB/MKT'>Bachelor of Science in Business/Marketing</option>
<option value='BSCJA'>Bachelor of Science in Criminal Justice Administration</option>
<option value='BSHA'>Bachelor of Science in Health Administration</option>
<option value='BSIT'>Bachelor of Science in Information Technology</option>
<option value='BSN'>Bachelor of Science in Nursing</option>
<option value='MAED/ADM'>Master of Arts in Education/Administration & Supervision</option>
<option value='MAED/CI'>Master of Arts in Education/Curriculum & Instruction</option>
<option value='MBA'>Master of Business Administration</option>
<option value='MBA/ACC'>Master of Business Administration/Accounting</option>
<option value='MBA/EB'>Master of Business Administration/e-Business</option>
<option value='MBA/GM'>Master of Business Administration/Global Management</option>
<option value='MBA/HCM'>Master of Business Administration/Health Care Management</option>
<option value='MBA/HRM'>Master of Business Administration/Human Resources Management</option>
<option value='MBA/MKT'>Master of Business Administration/Marketing</option>
<option value='MBA/TM'>Master of Business Administration/Technology Management</option>
<option value='MIS/M'>Master of Information Systems/Management</option>
<option value='MM'>Master of Management</option>
<option value='MSN'>Master of Science in Nursing</option>";	
}
if ($iId == '17') {
	$sProgramDropDown = "
	<option value='BSB/ACC'>Bachelor of Science in Business/Accounting</option>
<option value='BSB/A'>Bachelor of Science in Business/Administration</option>
<option value='BSB/M'>Bachelor of Science in Business/Management</option>
<option value='BSCJA'>Bachelor of Science in Criminal Justice Administration</option>
<option value='BSIT'>Bachelor of Science in Information Technology</option>
<option value='BSM'>Bachelor of Science in Management</option>
<option value='BSN'>Bachelor of Science in Nursing</option>
<option value='MBA'>Master of Business Administration</option>
<option value='MBA/HCM'>Master of Business Administration/Health Care Management</option>
<option value='MBA/TM'>Master of Business Administration/Technology Management</option>
<option value='MIS/M'>Master of Information Systems/Management</option>
<option value='MM/HRM'>Master of Management/Human Resources</option>
<option value='MSN'>Master of Science in Nursing</option>";	
}
if ($iId == '18') {
	$sProgramDropDown = "
	<option value='BSB/ACC'>Bachelor of Science in Business/Accounting</option>
<option value='BSB/A'>Bachelor of Science in Business/Administration</option>
<option value='BSB/EB'>Bachelor of Science in Business/e-Business</option>
<option value='BSB/IS'>Bachelor of Science in Business/Information Systems</option>
<option value='BSB/M'>Bachelor of Science in Business/Management</option>
<option value='BSB/MKT'>Bachelor of Science in Business/Marketing</option>
<option value='BSCJA'>Bachelor of Science in Criminal Justice Administration</option>
<option value='BSED/E'>Bachelor of Science in Education/Elementary Teacher Education</option>
<option value='BSHA'>Bachelor of Science in Health Administration</option>
<option value='BSIT'>Bachelor of Science in Information Technology</option>
<option value='BSIT/VC'>Bachelor of Science in Information Technology/Visual Communication</option>
<option value='BSM'>Bachelor of Science in Management</option>
<option value='BSN'>Bachelor of Science in Nursing</option>
<option value='XMBA'>Executive Master of Business Administration</option>
<option value='MAED/ADM'>Master of Arts in Education/Administration & Supervision</option>
<option value='MAED/CI/CE'>Master of Arts in Education/Computer Education</option>
<option value='MAED/CI'>Master of Arts in Education/Curriculum & Instruction</option>
<option value='MAED/TED-E'>Master of Arts in Education/Teacher Education for Elementary Licensure</option>
<option value='MAED/TED-S'>Master of Arts in Education/Teacher Education for Secondary Licensure</option>
<option value='MBA'>Master of Business Administration</option>
<option value='MBA/ACC'>Master of Business Administration/Accounting</option>
<option value='MBA/GM'>Master of Business Administration/Global Management</option>
<option value='MBA/HCM'>Master of Business Administration/Health Care Management</option>
<option value='MBA/HRM'>Master of Business Administration/Human Resources Management</option>
<option value='MBA/MKT'>Master of Business Administration/Marketing</option>
<option value='MBA/PA'>Master of Business Administration/Public Administration</option>
<option value='MBA/TM'>Master of Business Administration/Technology Management</option>
<option value='MHA'>Master of Health in Administration</option>
<option value='MIS'>Master of Information Systems</option>
<option value='MIS/M'>Master of Information Systems/Management</option>
<option value='MM'>Master of Management</option>
<option value='MM/HRM'>Master of Management/Human Resources</option>
<option value='MSN'>Master of Science in Nursing</option>
<option value='MSN/MBA/HCM'>Master of Science in Nursing/MBA/Health Care Management</option>
<option value='MSN/N/HCE'>Master of Science in Nursing/Nursing/Health Care Education</option>";	
}
if ($iId == '19') {
	$sProgramDropDown = "
	<option value='BSB/ACC'>Bachelor of Science in Business/Accounting</option>
<option value='BSB/M'>Bachelor of Science in Business/Management</option>
<option value='BSB/MKT'>Bachelor of Science in Business/Marketing</option>
<option value='BSCJA'>Bachelor of Science in Criminal Justice Administration</option>
<option value='BSHS'>Bachelor of Science in Human Services</option>
<option value='BSIT'>Bachelor of Science in Information Technology</option>
<option value='MBA'>Master of Business Administration</option>
<option value='MBA/TM'>Master of Business Administration/Technology Management</option>
<option value='MM/HRM'>Master of Management/Human Resources</option>";	
}
if ($iId == '22') {
	$sProgramDropDown = "
	<option value='BSB/A'>Bachelor of Science in Business/Administration</option>
<option value='BSB/EB'>Bachelor of Science in Business/e-Business</option>
<option value='BSB/M'>Bachelor of Science in Business/Management</option>
<option value='BSB/MKT'>Bachelor of Science in Business/Marketing</option>
<option value='MAED/ADM'>Master of Arts in Education/Administration & Supervision</option>
<option value='MAED/ECH'>Master of Arts in Education/Early Childhood</option>
<option value='MBA'>aster of Business Administration</option>
<option value='MBA/ACC'>Master of Business Administration/Accounting</option>
<option value='MBA/EB'>aster of Business Administration/e-Business</option>
<option value='MBA/GM'>Master of Business Administration/Global Management</option>
<option value='MBA/HRM'>Master of Business Administration/Human Resources Management</option>
<option value='MBA/MKT'>Master of Business Administration/Marketing</option>
<option value='MBA/TM'>Master of Business Administration/Technology Management</option>
<option value='MSC/EC'>Master of Science in Counseling/Educational Counseling</option>
<option value='MSC/MFC'>Master of Science in Counseling/Marriage and Family Counseling</option>
<option value='MSC/MHC'>Master of Science in Counseling/Mental Health Counseling</option>";	
}
if ($iId == '25') {
	$sProgramDropDown = "
	<option value='BSB/ACC'>Bachelor of Science in Business/Accounting</option>
<option value='BSB/A'>Bachelor of Science in Business/Administration</option>
<option value='BSB/IS'>Bachelor of Science in Business/Information Systems</option>
<option value='BSB/M'>Bachelor of Science in Business/Management</option>
<option value='BSCJA'>Bachelor of Science in Criminal Justice Administration</option>
<option value='BSHA'>Bachelor of Science in Health Administration</option>
<option value='BSHS'>Bachelor of Science in Human Services</option>
<option value='BSIT'>Bachelor of Science in Information Technology</option>
<option value='BSM'>Bachelor of Science in Management</option>
<option value='BSN'>Bachelor of Science in Nursing</option>
<option value='MAED/CI'>Master of Arts in Education/Curriculum & Instruction</option>
<option value='MAED/TED-E'>Master of Arts in Education/Teacher Education for Elementary Licensure</option>
<option value='MAED/TED-S'>Master of Arts in Education/Teacher Education for Secondary Licensure</option>
<option value='MBA'>Master of Business Administration</option>
<option value='MBA/PA'>Master of Business Administration/Public Administration</option>
<option value='MIS/M'>Master of Information Systems/Management</option>
<option value='MM'>Master of Management</option>
<option value='MS/AJS'>Master of Science in Administration of Justice and Security</option>
<option value='MSN'>Master of Science in Nursing</option>
<option value='MSN/FNP'>Master of Science in Nursing/Family Nurse Practitioner</option>";	
}
if ($iId == '26') {
	$sProgramDropDown = "
	<option value='BSB/ACC'>Bachelor of Science in Business/Accounting</option>
<option value='BSB/A'>Bachelor of Science in Business/Administration</option>
<option value='BSB/EB'>Bachelor of Science in Business/e-Business</option>
<option value='BSB/F'>Bachelor of Science in Business/Finance</option>
<option value='BSB/GBM'>Bachelor of Science in Business/Global Business Management</option>
<option value='BSB/M'>Bachelor of Science in Business/Management</option>
<option value='BSB/PA'>Bachelor of Science in Business/Public Administration</option>
<option value='BSB/RM'>Bachelor of Science in Business/Retail Management</option>
<option value='BSCJA'>Bachelor of Science in Criminal Justice Administration</option>
<option value='BSHA'>Bachelor of Science in Health Administration</option>
<option value='BSHCS'>Bachelor of Science in Health Administration</option>
<option value='BSHS'>Bachelor of Science in Human Services</option>
<option value='BSHS/M'>Bachelor of Science in Human Services</option>
<option value='BSIT'>Bachelor of Science in Information Technology</option>
<option value='BSIT/VC'>Bachelor of Science in Information Technology/Visual Communication</option>
<option value='BSN'>Bachelor of Science in Nursing</option>
<option value='LPN/BSN'>Licensed Practical Nurse to Bachelor of Science in Nursing</option>
<option value='MAED/ADM'>Master of Arts in Education/Administration & Supervision</option>
<option value='MAED/CI'>Master of Arts in Education/Curriculum & Instruction</option>
<option value='MAED/SPE'>Master of Arts in Education/Special Education</option>
<option value='MAED/TED-E'>Master of Arts in Education/Teacher Education for Elementary Licensure</option>
<option value='MAED/TED-S'>Master of Arts in Education/Teacher Education for Secondary Licensure</option>
<option value='MBA'>Master of Business Administration</option>
<option value='MBA/ACC'>Master of Business Administration/Accounting</option>
<option value='MBA/GM'>Master of Business Administration/Global Management</option>
<option value='MBA/HCM'>Master of Business Administration/Health Care Management</option>
<option value='MBA/HRM'>Master of Business Administration/Human Resources Management</option>
<option value='MBA/MKT'>Master of Business Administration/Marketing</option>
<option value='MBA/PA'>Master of Business Administration/Public Administration</option>
<option value='MBA/TM'>Master of Business Administration/Technology Management</option>
<option value='MIS'>Master of Information Systems</option>
<option value='MIS/M'>Master of Information Systems/Management</option>
<option value='MM'>Master of Management</option>
<option value='MS/AJS'>Master of Science in Administration of Justice and Security</option>
<option value='MSN'>Master of Science in Nursing</option>";	
}
if ($iId == '27') {
	$sProgramDropDown = "
	<option value='BSB/A'>Bachelor of Science in Business/Administration</option>
<option value='BSB/M'>Bachelor of Science in Business/Management</option>
<option value='BSCJA'>Bachelor of Science in Criminal Justice Administration</option>
<option value='BSHA'>Bachelor of Science in Health Administration</option>
<option value='BSHS'>Bachelor of Science in Human Services</option>
<option value='BSIT'>Bachelor of Science in Information Technology</option>
<option value='MAED/ADM'>Master of Arts in Education/Administration & Supervision</option>
<option value='MAED/CI'>Master of Arts in Education/Curriculum & Instruction</option>
<option value='MAED/TED-E'>Master of Arts in Education/Teacher Education for Elementary Licensure</option>
<option value='MBA'>Master of Business Administration</option>
<option value='MIS/M'>Master of Information Systems/Management</option>
<option value='MM'>Master of Management</option>
<option value='MSC/MFCT'>Master of Science in Counseling/Marriage, Family and Child Therapy</option>
<option value='MSC/SC'>Master of Science in Counseling/School Counseling</option>
<option value='E-BUS'>Professional Certificate Program/e-Business</option>
<option value='CERT/GMGT'>Professional Certificate Program/Global Management</option>
<option value='HRM'>rofessional Certificate Program/Human Resource Management</option>
<option value='PM'>Professional Certificate Program/Project Management</option>
<option value='CERT/TMGT'>Professional Certificate Program/Technology Management</option>";	
}
if ($iId == '28') {
	$sProgramDropDown = "
	<option value='BSB/ACC'>Bachelor of Science in Business/Accounting</option>
<option value='BSB/A'>Bachelor of Science in Business/Administration</option>
<option value='BSB/M'>Bachelor of Science in Business/Management</option>
<option value='BSCJA'>Bachelor of Science in Criminal Justice Administration</option>
<option value='BSHS/M'>Bachelor of Science in Human Services</option>
<option value='BSIT'>Bachelor of Science in Information Technology</option>
<option value='MBA'>Master of Business Administration</option>
<option value='MBA/ACC'>Master of Business Administration/Accounting</option>
<option value='MBA/EB'>Master of Business Administration/e-Business</option>
<option value='MBA/GM'>Master of Business Administration/Global Management</option>
<option value='MBA/HCM'>Master of Business Administration/Health Care Management</option>
<option value='MBA/HRM'>Master of Business Administration/Human Resources Management</option>
<option value='MBA/MKT'>Master of Business Administration/Marketing</option>
<option value='MBA/TM'>Master of Business Administration/Technology Management</option>
<option value='MM'>Master of Management</option>";	
}
if ($iId == '29') {
	$sProgramDropDown = "
	<option value='BSB/ACC'>Bachelor of Science in Business/Accounting</option>
<option value='BSB/A'>Bachelor of Science in Business/Administration</option>
<option value='BSB/EB'>Bachelor of Science in Business/e-Business</option>
<option value='BSB/IS'>Bachelor of Science in Business/Information Systems</option>
<option value='BSB/M'>Bachelor of Science in Business/Management</option>
<option value='BSB/MKT'>Bachelor of Science in Business/Marketing</option>
<option value='BSCJA'>Bachelor of Science in Criminal Justice Administration</option>
<option value='BSED/E'>Bachelor of Science in Education/Elementary Teacher Education</option>
<option value='BSHA'>Bachelor of Science in Health Administration</option>
<option value='BSIT'>Bachelor of Science in Information Technology</option>
<option value='BSIT/VC'>Bachelor of Science in Information Technology/Visual Communication</option>
<option value='BSM'>Bachelor of Science in Management</option>
<option value='BSN'>Bachelor of Science in Nursing</option>
<option value='XMBA'>Executive Master of Business Administration</option>
<option value='MAED/ADM'>Master of Arts in Education/Administration & Supervision</option>
<option value='MAED/CI/CE'>Master of Arts in Education/Computer Education</option>
<option value='MAED/CI'>Master of Arts in Education/Curriculum & Instruction</option>
<option value='MAED/TED-E'>Master of Arts in Education/Teacher Education for Elementary Licensure</option>
<option value='MAED/TED-S'>Master of Arts in Education/Teacher Education for Secondary Licensure</option>
<option value='MBA'>Master of Business Administration</option>
<option value='MBA/ACC'>Master of Business Administration/Accounting</option>
<option value='MBA/GM'>Master of Business Administration/Global Management</option>
<option value='MBA/HCM'>Master of Business Administration/Health Care Management</option>
<option value='MBA/HRM'>Master of Business Administration/Human Resources Management</option>
<option value='MBA/MKT'>Master of Business Administration/Marketing</option>
<option value='MBA/PA'>Master of Business Administration/Public Administration</option>
<option value='MBA/TM'>Master of Business Administration/Technology Management</option>
<option value='MHA'>Master of Health in Administration</option>
<option value='MIS'>Master of Information Systems</option>
<option value='MIS/M'>Master of Information Systems/Management</option>
<option value='MM'>Master of Management</option>
<option value='MM/HRM'>Master of Management/Human Resources</option>
<option value='MSN'>Master of Science in Nursing</option>
<option value='MSN/MBA/HCM'>Master of Science in Nursing/MBA/Health Care Management</option>
<option value='MSN/N/HCE'>Master of Science in Nursing/Nursing/Health Care Education</option>";	
}
if ($iId == '30') {
	$sProgramDropDown = "
	<option value='BSB/ACC'>Bachelor of Science in Business/Accounting</option>
<option value='BSB/A'>Bachelor of Science in Business/Administration</option>
<option value='BSB/EB'>Bachelor of Science in Business/e-Business</option>
<option value='BSB/IS'>Bachelor of Science in Business/Information Systems</option>
<option value='BSB/M'>Bachelor of Science in Business/Management</option>
<option value='BSB/MKT'>Bachelor of Science in Business/Marketing</option>
<option value='BSCJA'>Bachelor of Science in Criminal Justice Administration</option>
<option value='BSED/E'>Bachelor of Science in Education/Elementary Teacher Education</option>
<option value='BSHA'>Bachelor of Science in Health Administration</option>
<option value='BSIT'>Bachelor of Science in Information Technology</option>
<option value='BSIT/VC'>Bachelor of Science in Information Technology/Visual Communication</option>
<option value='BSM'>Bachelor of Science in Management</option>
<option value='BSN'>Bachelor of Science in Nursing</option>
<option value='XMBA'>Executive Master of Business Administration</option>
<option value='MAED/ADM'>Master of Arts in Education/Administration & Supervision</option>
<option value='MAED/CI/CE'>Master of Arts in Education/Computer Education</option>
<option value='MAED/CI'>Master of Arts in Education/Curriculum & Instruction</option>
<option value='MAED/TED-E'>Master of Arts in Education/Teacher Education for Elementary Licensure</option>
<option value='MAED/TED-S'>Master of Arts in Education/Teacher Education for Secondary Licensure</option>
<option value='MBA'>Master of Business Administration</option>
<option value='MBA/ACC'>Master of Business Administration/Accounting</option>
<option value='MBA/GM'>Master of Business Administration/Global Management</option>
<option value='MBA/HCM'>Master of Business Administration/Health Care Management</option>
<option value='MBA/HRM'>Master of Business Administration/Human Resources Management</option>
<option value='MBA/MKT'>Master of Business Administration/Marketing</option>
<option value='MBA/PA'>Master of Business Administration/Public Administration</option>
<option value='MBA/TM'>Master of Business Administration/Technology Management</option>
<option value='MHA'>Master of Health in Administration</option>
<option value='MIS'>Master of Information Systems</option>
<option value='MIS/M'>Master of Information Systems/Management</option>
<option value='MM'>Master of Management</option>
<option value='MM/HRM'>Master of Management/Human Resources</option>
<option value='MSN'>Master of Science in Nursing</option>
<option value='MSN/MBA/HCM'>Master of Science in Nursing/MBA/Health Care Management</option>
<option value='MSN/N/HCE'>Master of Science in Nursing/Nursing/Health Care Education</option>";	
}

if ($iId == '32') {
	$sProgramDropDown = "
	<option value='BSB/ACC'>Bachelor of Science in Business/Accounting</option>
<option value='BSB/A'>Bachelor of Science in Business/Administration</option>
<option value='BSB/EB'>Bachelor of Science in Business/e-Business</option>
<option value='BSB/IS'>Bachelor of Science in Business/Information Systems</option>
<option value='BSB/M'>achelor of Science in Business/Management</option>
<option value='BSB/MKT'>Bachelor of Science in Business/Marketing</option>
<option value='BSCJA'>Bachelor of Science in Criminal Justice Administration</option>
<option value='BSED/E'>Bachelor of Science in Education/Elementary Teacher Education</option>
<option value='BSHA'>Bachelor of Science in Health Administration</option>
<option value='BSIT'>Bachelor of Science in Information Technology</option>
<option value='BSIT/VC'>Bachelor of Science in Information Technology/Visual Communication</option>
<option value='BSM'>Bachelor of Science in Management</option>
<option value='BSN'>Bachelor of Science in Nursing</option>
<option value='XMBA'>Executive Master of Business Administration</option>
<option value='MAED/ADM'>Master of Arts in Education/Administration & Supervision</option>
<option value='MAED/CI/CE'>Master of Arts in Education/Computer Education</option>
<option value='MAED/CI'>Master of Arts in Education/Curriculum & Instruction</option>
<option value='MAED/TED-E'>Master of Arts in Education/Teacher Education for Elementary Licensure</option>
<option value='MAED/TED-S'>Master of Arts in Education/Teacher Education for Secondary Licensure</option>
<option value='MBA'>Master of Business Administration</option>
<option value='MBA/ACC'>Master of Business Administration/Accounting</option>
<option value='MBA/GM'>Master of Business Administration/Global Management</option>
<option value='MBA/HCM'>Master of Business Administration/Health Care Management</option>
<option value='MBA/HRM'>Master of Business Administration/Human Resources Management</option>
<option value='MBA/MKT'>Master of Business Administration/Marketing</option>
<option value='MBA/PA'>Master of Business Administration/Public Administration</option>
<option value='MBA/TM'>Master of Business Administration/Technology Management</option>
<option value='MHA'>Master of Health in Administration</option>
<option value='MIS'>Master of Information Systems</option>
<option value='MIS/M'>Master of Information Systems/Management</option>
<option value='MM'>Master of Management</option>
<option value='MM/HRM'>Master of Management/Human Resources</option>
<option value='MSN'>Master of Science in Nursing</option>
<option value='MSN/MBA/HCM'>Master of Science in Nursing/MBA/Health Care Management</option>
<option value='MSN/N/HCE'>Master of Science in Nursing/Nursing/Health Care Education</option>";	
}

if ($iId == '34') {
	$sProgramDropDown = "
	<option value='BSB/ACC'>Bachelor of Science in Business/Accounting</option>
<option value='BSB/A'>Bachelor of Science in Business/Administration</option>
<option value='BSB/M'>Bachelor of Science in Business/Management</option>
<option value='BSCJA'>Bachelor of Science in Criminal Justice Administration</option>
<option value='BSHA'>Bachelor of Science in Health Administration</option>
<option value='BSHS/M'>Bachelor of Science in Human Services</option>
<option value='BSIT'>Bachelor of Science in Information Technology</option>
<option value='BSM'>Bachelor of Science in Management</option>
<option value='BSN'>Bachelor of Science in Nursing</option>
<option value='MBA'>Master of Business Administration</option>
<option value='MBA/HCM'>Master of Business Administration/Health Care Management</option>
<option value='MBA/HRM'>Master of Business Administration/Human Resources Management</option>
<option value='MIS/M'>Master of Information Systems/Management</option>
<option value='MS/AJS'>Master of Science in Administration of Justice and Security</option>
<option value='MSN'>Master of Science in Nursing</option>";	
}
if ($iId == '35') {
	$sProgramDropDown = "
	<option value='BSB/ACC'>Bachelor of Science in Business/Accounting</option>
<option value='BSB/A'>Bachelor of Science in Business/Administration</option>
<option value='BSB/M'>Bachelor of Science in Business/Management</option>
<option value='BSCJA'>Bachelor of Science in Criminal Justice Administration</option>
<option value='BSHA'>Bachelor of Science in Health Administration</option>
<option value='BSHS/M'>Bachelor of Science in Human Services</option>
<option value='BSIT'>Bachelor of Science in Information Technology</option>
<option value='BSM'>Bachelor of Science in Management</option>
<option value='MBA'>Master of Business Administration</option>
<option value='MBA/HCM'>Master of Business Administration/Health Care Management</option>
<option value='MBA/HRM'>Master of Business Administration/Human Resources Management</option>
<option value='MS/AJS'>Master of Science in Administration of Justice and Security</option>";	
}
if ($iId == '37') {
	$sProgramDropDown = "
	<option value='BSB/ACC'>Bachelor of Science in Business/Accounting</option>
<option value='BSB/A'>Bachelor of Science in Business/Administration</option>
<option value='BSB/EB'>Bachelor of Science in Business/e-Business</option>
<option value='BSB/M'>Bachelor of Science in Business/Management</option>
<option value='BSIT'>Bachelor of Science in Information Technology</option>
<option value='MBA'>Master of Business Administration</option>
<option value='MBA/EB'>Master of Business Administration/e-Business</option>
<option value='MBA/GM'>Master of Business Administration/Global Management</option>
<option value='MBA/TM'>Master of Business Administration/Technology Management</option>
<option value='MM'>Master of Management</option>
<option value='MSCIS'>Master of Science in Computer Information Systems</option>";	
}
if ($iId == '39') {
	$sProgramDropDown = "
	<option value='BSB/A'>Bachelor of Science in Business/Administration</option>
<option value='BSB/EB'>Bachelor of Science in Business/e-Business</option>
<option value='BSB/M'>Bachelor of Science in Business/Management</option>
<option value='BSB/MKT'>Bachelor of Science in Business/Marketing</option>
<option value='BSCJA'>Bachelor of Science in Criminal Justice Administration</option>
<option value='BSHA'>Bachelor of Science in Health Administration</option>
<option value='BSHS'>Bachelor of Science in Human Services</option>
<option value='BSIT'>Bachelor of Science in Information Technology</option>
<option value='BSM'>Bachelor of Science in Management</option>
<option value='BSN'>Bachelor of Science in Nursing</option>
<option value='MAED/ADM'>Master of Arts in Education/Administration & Supervision</option>
<option value='MAED/CI'>Master of Arts in Education/Curriculum & Instruction</option>
<option value='MBA'>Master of Business Administration</option>
<option value='MBA/EB'>Master of Business Administration/e-Business</option>
<option value='MBA/GM'>Master of Business Administration/Global Management</option>
<option value='MBA/HCM'>Master of Business Administration/Health Care Management</option>
<option value='MBA/HRM'>Master of Business Administration/Human Resources Management</option>
<option value='MBA/TM'>Master of Business Administration/Technology Management</option>
<option value='MSN'>Master of Science in Nursing</option>";	
}
if ($iId == '40') {
	$sProgramDropDown = "
	<option value='BSB/ACC'>Bachelor of Science in Business/Accounting</option>
<option value='BSB/A'>Bachelor of Science in Business/Administration</option>
<option value='BSB/M'>Bachelor of Science in Business/Management</option>
<option value='BSCJA'>Bachelor of Science in Criminal Justice Administration</option>
<option value='BSHS/M'>Bachelor of Science in Human Services</option>
<option value='BSIT/SE'>Bachelor of Science in Information Technology/Software Engineering</option>
<option value='BSIT/VC'>Bachelor of Science in Information Technology/Visual Communication</option>
<option value='BSN'>Bachelor of Science in Nursing</option>
<option value='LPN/BSN'>Licensed Practical Nurse to Bachelor of Science in Nursing</option>
<option value='MAED/ADM'>Master of Arts in Education/Administration & Supervision</option>
<option value='MAED/CI'>Master of Arts in Education/Curriculum & Instruction</option>
<option value='MAED/TED-E'>Master of Arts in Education/Teacher Education for Elementary Licensure</option>
<option value='MAED/TED-S'>Master of Arts in Education/Teacher Education for Secondary Licensure</option>
<option value='MBA'>Master of Business Administration</option>
<option value='MBA/ACC'>Master of Business Administration/Accounting</option>
<option value='MBA/HCM'>Master of Business Administration/Health Care Management</option>
<option value='MBA/TM'>Master of Business Administration/Technology Management</option>
<option value='MSC/CC'>Master of Science in Counseling/Community Counseling</option>
<option value='MSC/SC'>Master of Science in Counseling/School Counseling</option>
<option value='MSN'>Master of Science in Nursing</option>
<option value='CERT/PL'>Professional Education Certificate Program/Practical Licensure</option>";	
}
if ($iId == '41') {
	$sProgramDropDown = "
	<option value='BSB/A'>Bachelor of Science in Business/Administration</option>
<option value='BSB/M'>Bachelor of Science in Business/Management</option>
<option value='BSCJA'>Bachelor of Science in Criminal Justice Administration</option>
<option value='BSIT'>Bachelor of Science in Information Technology</option>
<option value='MBA'>Master of Business Administration</option>
<option value='MBA/GM'>Master of Business Administration/Global Management</option>
<option value='MBA/HCM'>Master of Business Administration/Health Care Management</option>
<option value='MBA/TM'>Master of Business Administration/Technology Management</option>
<option value='MM'>Master of Management</option>";	
}
if ($iId == '43') {
	$sProgramDropDown = "
	<option value='BSB/A'>Bachelor of Science in Business/Administration</option>
<option value='BSB/M'>Bachelor of Science in Business/Management</option>
<option value='BSIT'>Bachelor of Science in Information Technology</option>
<option value='MBA'>Master of Business Administration</option>
<option value='MBA/GM'>Master of Business Administration/Global Management</option>
<option value='MBA/TM'>Master of Business Administration/Technology Management</option>
<option value='MM'>Master of Management</option>";	
}
if ($iId == '44') {
	$sProgramDropDown = "
	<option value='BSB/A'>Bachelor of Science in Business/Administration</option>
<option value='BSB/M'>Bachelor of Science in Business/Management</option>
<option value='BSHA'>Bachelor of Science in Health Administration</option>
<option value='BSIT'>Bachelor of Science in Information Technology</option>
<option value='BSN'>Bachelor of Science in Nursing</option>
<option value='MBA'>Master of Business Administration</option>
<option value='MM'>Master of Management</option>
<option value='MSN'>Master of Science in Nursing</option>";	
}
if ($iId == '45') {
	$sProgramDropDown = "
	<option value='BSB/A'>Bachelor of Science in Business/Administration</option>
<option value='BSB/M'>Bachelor of Science in Business/Management</option>
<option value='BSCJA'>Bachelor of Science in Criminal Justice Administration</option>
<option value='BSHA'>Bachelor of Science in Health Administration</option>
<option value='BSIT'>Bachelor of Science in Information Technology</option>
<option value='MBA'>Master of Business Administration</option>
<option value='MBA/HCM'>Master of Business Administration/Health Care Management</option>
<option value='MBA/TM'>Master of Business Administration/Technology Management</option>
<option value='MM'>Master of Management</option>
<option value='MM/HRM'>Master of Management/Human Resources</option>";	
}
if ($iId == '46') {
	$sProgramDropDown = "
	<option value='BSB/M'>Bachelor of Science in Business/Management</option>
<option value='BSIT'>Bachelor of Science in Information Technology</option>
<option value='MBA'>Master of Business Administration</option>
<option value='MBA/GM'>Master of Business Administration/Global Management</option>
<option value='MBA/TM'>Master of Business Administration/Technology Management</option>";	
}
if ($iId == '48') {
	$sProgramDropDown = "
	<option value='BSB/A'>Bachelor of Science in Business/Administration</option>
<option value='BSB/IS'>Bachelor of Science in Business/Information Systems</option>
<option value='BSB/M'>Bachelor of Science in Business/Management</option>
<option value='BSM'>Bachelor of Science in Management</option>
<option value='MBA'>Master of Business Administration</option>
<option value='MBA/GM'>Master of Business Administration/Global Management</option>
<option value='MBA/HCM'>Master of Business Administration/Health Care Management</option>
<option value='MBA/HRM'>Master of Business Administration/Human Resources Management</option>
<option value='MBA/PA'>Master of Business Administration/Public Administration</option>
<option value='MBA/TM'>Master of Business Administration/Technology Management</option>";	
}

if ($iId == '49') {
	$sProgramDropDown = "
	<option value=>BSB/ACC'>Bachelor of Science in Business/Accounting</option>
<option value=>BSB/A'>Bachelor of Science in Business/Administration</option>
<option value=>BSB/IS'>Bachelor of Science in Business/Information Systems</option>
<option value=>BSB/M'>Bachelor of Science in Business/Management</option>
<option value=>BSB/MKT'>Bachelor of Science in Business/Marketing</option>
<option value=>BSM'>Bachelor of Science in Management</option>
<option value=>MBA'>Master of Business Administration</option>
<option value=>MBA/ACC'>Master of Business Administration/Accounting</option>
<option value=>MBA/GM'>Master of Business Administration/Global Management</option>
<option value=>MBA/HCM'>Master of Business Administration/Health Care Management</option>
<option value=>MBA/MKT'>Master of Business Administration/Marketing</option>
<option value=>MM'>Master of Management</option>";	
}
if ($iId == '50') {
	$sProgramDropDown = "
	<option value='BSB/A'>Bachelor of Science in Business/Administration</option>
<option value='BSB/M'>Bachelor of Science in Business/Management</option>
<option value='BSIT'>Bachelor of Science in Information Technology</option>
<option value='BSM'>Bachelor of Science in Management</option>
<option value='MBA'>Master of Business Administration</option>
<option value='MM'>Master of Management</option>
<option value='MSCIS'>Master of Science in Computer Information Systems</option>";	
}
if ($iId == '51') {
	$sProgramDropDown = "
	<option value='BSB/M'>Bachelor of Science in Business/Management</option>
<option value='BSCJA'>Bachelor of Science in Criminal Justice Administration</option>
<option value='BSHA'>Bachelor of Science in Health Administration</option>
<option value='BSIT'>Bachelor of Science in Information Technology</option>
<option value='MAED/CI'>Master of Arts in Education/Curriculum & Instruction</option>
<option value='MBA'>Master of Business Administration</option>
<option value='MBA/ACC'>Master of Business Administration/Accounting</option>
<option value='MBA/GM'>Master of Business Administration/Global Management</option>
<option value='MBA/HCM'>Master of Business Administration/Health Care Management</option>
<option value='MBA/HRM'>Master of Business Administration/Human Resources Management</option>
<option value='MBA/MKT'>Master of Business Administration/Marketing</option>
<option value='MBA/TM'>Master of Business Administration/Technology Management</option>";	
}
if ($iId == '52') {
	$sProgramDropDown = "
	<option value='BSB/A'>Bachelor of Science in Business/Administration</option>
<option value='BSB/M'>Bachelor of Science in Business/Management</option>
<option value='BSCJA'>Bachelor of Science in Criminal Justice Administration</option>
<option value='BSHA'>Bachelor of Science in Health Administration</option>
<option value='BSIT'>Bachelor of Science in Information Technology</option>
<option value='MBA'>Master of Business Administration</option>
<option value='MBA/HCM'>Master of Business Administration/Health Care Management</option>
<option value='MBA/TM'>Master of Business Administration/Technology Management</option>
<option value='MIS'>Master of Information Systems</option>
<option value='MIS/M'>Master of Information Systems/Management</option>
<option value='MM'>Master of Management</option>";	
}
if ($iId == '53') {
	$sProgramDropDown = "
	<option value='BSB/ACC'>Bachelor of Science in Business/Accounting</option>
<option value='BSB/A'>Bachelor of Science in Business/Administration</option>
<option value='BSB/IS'>Bachelor of Science in Business/Information Systems</option>
<option value='BSB/M'>Bachelor of Science in Business/Management</option>
<option value='BSB/MKT'>Bachelor of Science in Business/Marketing</option>
<option value='BSCJA'>Bachelor of Science in Criminal Justice Administration</option>
<option value='BSHA'>Bachelor of Science in Health Administration</option>
<option value='BSIT'>Bachelor of Science in Information Technology</option>
<option value='BSM'>Bachelor of Science in Management</option>
<option value='BSN'>Bachelor of Science in Nursing</option>
<option value='XMBA'>Executive Master of Business Administration</option>
<option value='MBA'>Master of Business Administration</option>
<option value='MBA/GM'>Master of Business Administration/Global Management</option>
<option value='MBA/HCM'>Master of Business Administration/Health Care Management</option>
<option value='MBA/HRM'>Master of Business Administration/Human Resources Management</option>
<option value='MBA/TM'>Master of Business Administration/Technology Management</option>
<option value='MM'>Master of Management</option>
<option value='MSCIS'>Master of Science in Computer Information Systems</option>
<option value='MSN'>Master of Science in Nursing</option>";	
}
if ($iId == '54') {
	$sProgramDropDown = "
	<option value='BSB/A'>Bachelor of Science in Business/Administration</option>
<option value='BSB/IS'>Bachelor of Science in Business/Information Systems</option>
<option value='BSB/M'>Bachelor of Science in Business/Management</option>
<option value='MBA'>Master of Business Administration</option>
<option value='MBA/EB'>Master of Business Administration/e-Business</option>
<option value='MBA/GM'>Master of Business Administration/Global Management</option>
<option value='MBA/HRM'>Master of Business Administration/Human Resources Management</option>
<option value='MBA/TM'>Master of Business Administration/Technology Management</option>
<option value='MIS/M'>Master of Information Systems/Management</option>
<option value='MM'>Master of Management</option>";	
}
if ($iId == '55') {
	$sProgramDropDown = "
	<option value='BSB/ACC'>Bachelor of Science in Business/Accounting</option>
<option value='BSB/A'>Bachelor of Science in Business/Administration</option>
<option value='BSB/M'>Bachelor of Science in Business/Management</option>
<option value='BSB/MKT'>Bachelor of Science in Business/Marketing</option>
<option value='BSHA'>Bachelor of Science in Health Administration</option>
<option value='BSIT'>Bachelor of Science in Information Technology</option>
<option value='BSN'>Bachelor of Science in Nursing</option>
<option value='MBA'>Master of Business Administration</option>
<option value='MBA/EB'>Master of Business Administration/e-Business</option>
<option value='MBA/HCM'>Master of Business Administration/Health Care Management</option>
<option value='MBA/HRM'>Master of Business Administration/Human Resources Management</option>
<option value='MBA/TM'>Master of Business Administration/Technology Management</option>";	
}
if ($iId == '56') {
	$sProgramDropDown = "
	<option value='BSB/A'>Bachelor of Science in Business/Administration</option>
<option value='BSB/IS'>Bachelor of Science in Business/Information Systems</option>
<option value='BSB/M'>Bachelor of Science in Business/Management</option>
<option value='BSIT'>Bachelor of Science in Information Technology</option>
<option value='MBA'>Master of Business Administration</option>
<option value='MBA/ACC'>Master of Business Administration/Accounting</option>
<option value='MBA/EB'>Master of Business Administration/e-Business</option>
<option value='MBA/GM'>Master of Business Administration/Global Management</option>
<option value='MBA/HCM'>Master of Business Administration/Health Care Management</option>
<option value='MBA/HRM'>Master of Business Administration/Human Resources Management</option>
<option value='MBA/MKT'>Master of Business Administration/Marketing</option>
<option value='MBA/TM'>Master of Business Administration/Technology Management</option>
<option value='MIS'>Master of Information Systems</option>
<option value='MIS/M'>Master of Information Systems/Management</option>";	
}
if ($iId == '57') {
	$sProgramDropDown = "
	<option value='BSB/M'>Bachelor of Science in Business/Management</option>
<option value='BSHA'>Bachelor of Science in Health Administration</option>
<option value='BSIT'>Bachelor of Science in Information Technology</option>
<option value='MBA'>Master of Business Administration</option>";	
}
if ($iId == '58') {
	$sProgramDropDown = "
	<option value='BSIT'>Bachelor of Science in Information Technology</option>
<option value='BSM'>Bachelor of Science in Management</option>
<option value='MBA'>Master of Business Administration</option>
<option value='MBA/ACC'>Master of Business Administration/Accounting</option>
<option value='MBA/EB'>Master of Business Administration/e-Business</option>
<option value='MBA/GM'>Master of Business Administration/Global Management</option>
<option value='MBA/HCM'>Master of Business Administration/Health Care Management</option>
<option value='MBA/TM'>Master of Business Administration/Technology Management</option>";	
}
if ($iId == '59') {
	$sProgramDropDown = "
	<option value='BSB/A'>Bachelor of Science in Business/Administration</option>
<option value='BSB/EB'>Bachelor of Science in Business/e-Business</option>
<option value='BSB/M'>Bachelor of Science in Business/Management</option>
<option value='BSB/MKT'>Bachelor of Science in Business/Marketing</option>
<option value='BSIT'>Bachelor of Science in Information Technology</option>
<option value='BSM'>Bachelor of Science in Management</option>
<option value='MBA'>Master of Business Administration</option>
<option value='MBA/ACC'>Master of Business Administration/Accounting</option>
<option value='MBA/EB'>Master of Business Administration/e-Business</option>
<option value='MBA/GM'>Master of Business Administration/Global Management</option>
<option value='MBA/HCM'>Master of Business Administration/Health Care Management</option>
<option value='MBA/TM'>Master of Business Administration/Technology Management</option>
<option value='MSCIS'>Master of Science in Computer Information Systems</option>
";	
}
if ($iId == '60') {
	$sProgramDropDown = "
	<option value='BSB/A'>Bachelor of Science in Business/Administration</option>
<option value='BSB/EB'>Bachelor of Science in Business/e-Business</option>
<option value='BSB/M'>Bachelor of Science in Business/Management</option>
<option value='BSB/MKT'>Bachelor of Science in Business/Marketing</option>
<option value='BSCJA'>Bachelor of Science in Criminal Justice Administration</option>
<option value='BSHA'>Bachelor of Science in Health Administration</option>
<option value='BSIT'>Bachelor of Science in Information Technology</option>
<option value='MBA'>Master of Business Administration</option>
<option value='MBA/EB'>Master of Business Administration/e-Business</option>
<option value='MBA/HCM'>Master of Business Administration/Health Care Management</option>
<option value='MBA/HRM'>Master of Business Administration/Human Resources Management</option>
<option value='MBA/TM'>Master of Business Administration/Technology Management</option>
<option value='MM'>Master of Management</option>";	
}
if ($iId == '61') {
	$sProgramDropDown = "
	<option value='BSB/IS'>Bachelor of Science in Business/Information Systems</option>
<option value='BSB/M'>Bachelor of Science in Business/Management</option>
<option value='BSCJA'>Bachelor of Science in Criminal Justice Administration</option>
<option value='BSHA'>Bachelor of Science in Health Administration</option>
<option value='BSN'>Bachelor of Science in Nursing</option>
<option value='MBA'>Master of Business Administration</option>
<option value='MBA/GM'>Master of Business Administration/Global Management</option>
<option value='MBA/HCM'>Master of Business Administration/Health Care Management</option>
<option value='MBA/HRM'>Master of Business Administration/Human Resources Management</option>
<option value='MBA/MKT'>Master of Business Administration/Marketing</option>
<option value='MBA/TM'>Master of Business Administration/Technology Management</option>
<option value='MIS'>Master of Information Systems</option>
<option value='MIS/M'>Master of Information Systems/Management</option>
<option value='MSN'>Master of Science in Nursing</option>";	
}
if ($iId == '62') {
	$sProgramDropDown = "
	<option value='BSB/M'>Bachelor of Science in Business/Management</option>
<option value='MBA'>Master of Business Administration</option>
<option value='MBA/ACC'>Master of Business Administration/Accounting</option>
<option value='MBA/GM'>Master of Business Administration/Global Management</option>
<option value='MBA/HCM'>Master of Business Administration/Health Care Management</option>
<option value='MBA/HRM'>Master of Business Administration/Human Resources Management</option>
<option value='MBA/MKT'>Master of Business Administration/Marketing</option>
<option value='MBA/PA'>Master of Business Administration/Public Administration</option>
<option value='MBA/TM'>Master of Business Administration/Technology Management</option>";	
}
if ($iId == '63') {
	$sProgramDropDown = "
	<option value='BSB/M'>Bachelor of Science in Business/Management</option>
<option value='BSCJA'>Bachelor of Science in Criminal Justice Administration</option>
<option value='BSIT'>Bachelor of Science in Information Technology</option>
<option value='MBA'>Master of Business Administration</option>
<option value='MBA/ACC'>Master of Business Administration/Accounting</option>
<option value='MBA/GM'>Master of Business Administration/Global Management</option>
<option value='MBA/HCM'>Master of Business Administration/Health Care Management</option>
<option value='MBA/HRM'>Master of Business Administration/Human Resources Management</option>
<option value='MBA/MKT'>Master of Business Administration/Marketing</option>
<option value='MBA/PA'>Master of Business Administration/Public Administration</option>
<option value='MBA/TM'>Master of Business Administration/Technology Management</option>";	
}
if ($iId == '64') {
	$sProgramDropDown = "
	<option value='BSB/A'>Bachelor of Science in Business/Administration</option>
<option value='BSB/EB'>Bachelor of Science in Business/e-Business</option>
<option value='BSB/M'>Bachelor of Science in Business/Management</option>
<option value='BSIT'>Bachelor of Science in Information Technology</option>";	
}
if ($iId == '65') {
	$sProgramDropDown = "
	<option value='BSB/ACC'>Bachelor of Science in Business/Accounting</option>
<option value='BSB/A'>Bachelor of Science in Business/Administration</option>
<option value='BSB/EB'>Bachelor of Science in Business/e-Business</option>
<option value='BSB/IS'>Bachelor of Science in Business/Information Systems</option>
<option value='BSB/M'>Bachelor of Science in Business/Management</option>
<option value='BSB/MKT'>Bachelor of Science in Business/Marketing</option>
<option value='BSCJA'>Bachelor of Science in Criminal Justice Administration</option>
<option value='BSHCS'>Bachelor of Science in Health Administration</option>
<option value='BSM'>Bachelor of Science in Management</option>
<option value='BSN'>Bachelor of Science in Nursing</option>
<option value='MBA'>Master of Business Administration</option>
<option value='MBA/ACC'>Master of Business Administration/Accounting</option>
<option value='MBA/EB'>Master of Business Administration/e-Business</option>
<option value='MBA/GM'>Master of Business Administration/Global Management</option>
<option value='MBA/HCM'>Master of Business Administration/Health Care Management</option>
<option value='MBA/HRM'>Master of Business Administration/Human Resources Management</option>
<option value='MBA/MKT'>Master of Business Administration/Marketing</option>
<option value='MBA/TM'>Master of Business Administration/Technology Management</option>
<option value='MSN'>Master of Science in Nursing</option>";	
}
if ($iId == '66') {
	$sProgramDropDown = "
	<option value='BSB/M'>Bachelor of Science in Business/Management</option>
<option value='BSIT'>Bachelor of Science in Information Technology</option>
<option value='MBA'>Master of Business Administration</option>
<option value='MM'>Master of Management</option>
";	
}
if ($iId == '67') {
	$sProgramDropDown = "
	<option value='BSB/M'>Bachelor of Science in Business/Management</option>
<option value='BSIT'>Bachelor of Science in Information Technology</option>";	
}
if ($iId == '69') {
	$sProgramDropDown = "
	<option value='BSB/A'>Bachelor of Science in Business/Administration</option>
<option value='BSB/M'>Bachelor of Science in Business/Management</option>
<option value='BSHS'>Bachelor of Science in Human Services</option>
<option value='BSIT'>Bachelor of Science in Information Technology</option>
<option value='MBA'>Master of Business Administration</option>
<option value='MBA/ACC'>Master of Business Administration/Accounting</option>
<option value='MBA/GM'>Master of Business Administration/Global Management</option>
<option value='MBA/HCM'>Master of Business Administration/Health Care Management</option>
<option value='MBA/HRM'>Master of Business Administration/Human Resources Management</option>
<option value='MBA/MKT'>Master of Business Administration/Marketing</option>
<option value='MBA/TM'>Master of Business Administration/Technology Management</option>
<option value='MIS/M'>Master of Information Systems/Management</option>";	
}

if ($iId == '70') {
	$sProgramDropDown = "
	<option value='BSB/M'>Bachelor of Science in Business/Management</option>
<option value='BSHA'>Bachelor of Science in Health Administration</option>
<option value='BSIT'>Bachelor of Science in Information Technology</option>
<option value='BSM'>Bachelor of Science in Management</option>
<option value='MBA'>Master of Business Administration</option>
<option value='MBA/ACC'>Master of Business Administration/Accounting</option>
<option value='MBA/GM'>Master of Business Administration/Global Management</option>
<option value='MBA/HCM'>Master of Business Administration/Health Care Management</option>
<option value='MBA/HRM'>Master of Business Administration/Human Resources Management</option>
<option value='MBA/MKT'>Master of Business Administration/Marketing</option>
<option value='MBA/TM'>Master of Business Administration/Technology Management</option>
<option value='MIS/M'>Master of Information Systems/Management</option>
<option value='MM'>Master of Management</option>";	
}

if ($iId == '71') {
	$sProgramDropDown = "
	<option value='BSB/M'>Bachelor of Science in Business/Management</option>
<option value='MAED/ADM'>Master of Arts in Education/Administration & Supervision</option>
<option value='MAED/CI'>Master of Arts in Education/Curriculum & Instruction</option>
<option value='MBA'>Master of Business Administration</option>";	
}

if ($iId == '72') {
	$sProgramDropDown = "
	<option value='BSB/M'>Bachelor of Science in Business/Management</option>
<option value='BSCJA'>Bachelor of Science in Criminal Justice Administration</option>
<option value='BSHA'>Bachelor of Science in Health Administration</option>
<option value='BSIT'>Bachelor of Science in Information Technology</option>
<option value='MBA'>Master of Business Administration</option>
<option value='MBA/HCM'>Master of Business Administration/Health Care Management</option>
<option value='MBA/PA'>Master of Business Administration/Public Administration</option>
<option value='MBA/TM'>Master of Business Administration/Technology Management</option>
<option value='MM'>Master of Management</option>";	
}

if ($iId == '73') {
	$sProgramDropDown = "
	<option value='BSB/M'>Bachelor of Science in Business/Management</option>
<option value='MBA'>Master of Business Administration</option>";	
}
if ($iId == '76') {
	$sProgramDropDown = "
	<option value='BSB/ACC'>Bachelor of Science in Business/Accounting</option>
<option value='BSB/A'>Bachelor of Science in Business/Administration</option>
<option value='BSB/IS'>Bachelor of Science in Business/Information Systems</option>
<option value='BSB/M'>Bachelor of Science in Business/Management</option>
<option value='BSB/PA'>Bachelor of Science in Business/Public Administration</option>
<option value='BSCJA'>Bachelor of Science in Criminal Justice Administration</option>
<option value='BSHA'>Bachelor of Science in Health Administration</option>
<option value='BSHS'>Bachelor of Science in Human Services</option>
<option value='BSIT'>Bachelor of Science in Information Technology</option>
<option value='BSN'>Bachelor of Science in Nursing</option>
<option value='MAED/CI'>Master of Arts in Education/Curriculum & Instruction</option>
<option value='MAED/TED-E'>Master of Arts in Education/Teacher Education for Elementary Licensure</option>
<option value='MAED/TED-S'>Master of Arts in Education/Teacher Education for Secondary Licensure</option>
<option value='MBA'>Master of Business Administration</option>
<option value='MBA/ACC'>Master of Business Administration/Accounting</option>
<option value='MBA/EB'>Master of Business Administration/e-Business</option>
<option value='MBA/GM'>Master of Business Administration/Global Management</option>
<option value='MBA/HCM'>Master of Business Administration/Health Care Management</option>
<option value='MBA/HRM'>Master of Business Administration/Human Resources Management</option>
<option value='MBA/MKT'>Master of Business Administration/Marketing</option>
<option value='MBA/TM'>Master of Business Administration/Technology Management</option>
<option value='MM'>Master of Management</option>
<option value='MM/HRM'>Master of Management/Human Resources</option>
<option value='MSC/MFCT'>Master of Science in Counseling/Marriage, Family and Child Therapy</option>";	
}

if ($iId == '77') {
	$sProgramDropDown = "
	<option value='BSB/ACC'>Bachelor of Science in Business/Accounting</option>
<option value='BSB/A'>Bachelor of Science in Business/Administration</option>
<option value='BSB/EB'>Bachelor of Science in Business/e-Business</option>
<option value='BSB/IS'>Bachelor of Science in Business/Information Systems</option>
<option value='BSB/M'>Bachelor of Science in Business/Management</option>
<option value='BSB/MKT'>Bachelor of Science in Business/Marketing</option>
<option value='BSB/PA'>Bachelor of Science in Business/Public Administration</option>
<option value='BSM'>Bachelor of Science in Management</option>
<option value='MBA'>Master of Business Administration</option>
<option value='MBA/ACC'>Master of Business Administration/Accounting</option>
<option value='MBA/GM'>Master of Business Administration/Global Management</option>
<option value='MBA/HCM'>Master of Business Administration/Health Care Management</option>
<option value='MBA/HRM'>Master of Business Administration/Human Resources Management</option>
<option value='MBA/MKT'>Master of Business Administration/Marketing</option>
<option value='MBA/PA'>Master of Business Administration/Public Administration</option>
<option value='MBA/TM'>Master of Business Administration/Technology Management</option>
<option value='MM'>Master of Management</option>
<option value='MM/HRM'>Master of Management/Human Resources</option>";	
}

if ($iId == '78') {
	$sProgramDropDown = "
	<option value='BSB/ACC'>Bachelor of Science in Business/Accounting</option>
<option value='BSB/A'>Bachelor of Science in Business/Administration</option>
<option value='BSB/IS'>Bachelor of Science in Business/Information Systems</option>
<option value='BSB/M'>Bachelor of Science in Business/Management</option>
<option value='BSB/MKT'>Bachelor of Science in Business/Marketing</option>
<option value='BSM'>Bachelor of Science in Management</option>
<option value='MBA'>Master of Business Administration</option>
<option value='MBA/ACC'>Master of Business Administration/Accounting</option>
<option value='MBA/GM'>Master of Business Administration/Global Management</option>
<option value='MBA/HCM'>Master of Business Administration/Health Care Management</option>
<option value='MBA/MKT'>Master of Business Administration/Marketing</option>
<option value='MBA/PA'>Master of Business Administration/Public Administration</option>
<option value='MBA/TM'>Master of Business Administration/Technology Management</option>
<option value='MM'>Master of Management</option>
<option value='MM/HRM'>Master of Management/Human Resources</option>";	
}

if ($iId == '79') {
	$sProgramDropDown = "
	<option value='BSB/ACC'>Bachelor of Science in Business/Accounting</option>
<option value='BSB/A'>Bachelor of Science in Business/Administration</option>
<option value='BSB/M'>Bachelor of Science in Business/Management</option>
<option value='BSB/MKT'>Bachelor of Science in Business/Marketing</option>
<option value='BSCJA'>Bachelor of Science in Criminal Justice Administration</option>
<option value='BSHA'>Bachelor of Science in Health Administration</option>
<option value='BSIT'>Bachelor of Science in Information Technology</option>
<option value='BSN'>Bachelor of Science in Nursing</option>
<option value='MBA'>Master of Business Administration</option>
<option value='MBA/GM'>Master of Business Administration/Global Management</option>
<option value='MBA/HCM'>Master of Business Administration/Health Care Management</option>
<option value='MBA/HRM'>Master of Business Administration/Human Resources Management</option>
<option value='MBA/TM'>Master of Business Administration/Technology Management</option>
<option value='MSN'>Master of Science in Nursing</option>";	
}

if ($iId == '82') {
	$sProgramDropDown = "
	<option value='BSB/IS'>Bachelor of Science in Business/Information Systems</option>
<option value='BSB/M'>Bachelor of Science in Business/Management</option>
<option value='MAED/ADM'>Master of Arts in Education/Administration & Supervision</option>
<option value='MBA'>Master of Business Administration</option>
<option value='MBA/ACC'>Master of Business Administration/Accounting</option>
<option value='MBA/GM'>Master of Business Administration/Global Management</option>
<option value='MBA/HCM'>Master of Business Administration/Health Care Management</option>
<option value='MBA/HRM'>Master of Business Administration/Human Resources Management</option>
<option value='MBA/MKT'>Master of Business Administration/Marketing</option>
<option value='MBA/TM'>Master of Business Administration/Technology Management</option>";	
}

if ($iId == '84') {
	$sProgramDropDown = "
	<option value='BSB/A'>Bachelor of Science in Business/Administration</option>
<option value='BSB/EB'>Bachelor of Science in Business/e-Business</option>
<option value='BSB/M'>Bachelor of Science in Business/Management</option>
<option value='BSB/MKT'>Bachelor of Science in Business/Marketing</option>
<option value='BSIT'>Bachelor of Science in Information Technology</option>
<option value='BSM'>Bachelor of Science in Management</option>
<option value='MBA'>Master of Business Administration</option>
<option value='MBA/GM'>Master of Business Administration/Global Management</option>
<option value='MBA/HCM'>Master of Business Administration/Health Care Management</option>
<option value='MBA/TM'>Master of Business Administration/Technology Management</option>
<option value='MIS'>Master of Information Systems</option>
<option value='MIS/M'>Master of Information Systems/Management</option>
<option value='MSCIS'>Master of Science in Computer Information Systems</option>";	
}

if ($iId == '85') {
	$sProgramDropDown = "
	<option value='BSB/A'>Bachelor of Science in Business/Administration</option>
<option value='BSB/M'>Bachelor of Science in Business/Management</option>
<option value='BSCJA'>Bachelor of Science in Criminal Justice Administration</option>
<option value='BSHA'>Bachelor of Science in Health Administration</option>
<option value='BSHS'>Bachelor of Science in Human Services</option>
<option value='BSIT'>Bachelor of Science in Information Technology</option>
<option value='MAED/ADM'>Master of Arts in Education/Administration & Supervision</option>
<option value='MAED/CI'>Master of Arts in Education/Curriculum & Instruction</option>
<option value='MAED/TED-E'>Master of Arts in Education/Teacher Education for Elementary Licensure</option>
<option value='MBA'>Master of Business Administration</option>
<option value='MIS/M'>Master of Information Systems/Management</option>
<option value='MM'>Master of Management</option>
<option value='MSC/MFCT'>Master of Science in Counseling/Marriage, Family and Child Therapy</option>
<option value='MSC/SC'>Master of Science in Counseling/School Counseling</option>
<option value='E-BUS'>Professional Certificate Program/e-Business</option>
<option value='CERT/GMGT'>Professional Certificate Program/Global Management</option>
<option value='HRM'>Professional Certificate Program/Human Resource Management</option>
<option value='PM'>Professional Certificate Program/Project Management</option>
<option value='CERT/TMGT'>Professional Certificate Program/Technology Management</option>";	
}

if ($iId == '86') {
	$sProgramDropDown = "
	<option value='BSB/A'>Bachelor of Science in Business/Administration</option>
<option value='BSB/M'>Bachelor of Science in Business/Management</option>
<option value='BSIT'>Bachelor of Science in Information Technology</option>
<option value='MBA'>Master of Business Administration</option>
<option value='MBA/TM'>Master of Business Administration/Technology Management</option>";	
}

if ($iId == '87') {
	$sProgramDropDown = "
	<option value='BSB/M'>Bachelor of Science in Business/Management</option>
<option value='BSIT'>Bachelor of Science in Information Technology</option>
<option value='MBA'>Master of Business Administration</option>";	
}
if ($iId == '88') {
	$sProgramDropDown = "
	<option value='BSB/IS'>Bachelor of Science in Business/Information Systems</option>
<option value='BSB/M'>Bachelor of Science in Business/Management</option>
<option value='BSCJA'>Bachelor of Science in Criminal Justice Administration</option>
<option value='BSHA'>Bachelor of Science in Health Administration</option>
<option value='MBA'>Master of Business Administration</option>
<option value='MBA/GM'>Master of Business Administration/Global Management</option>
<option value='MBA/HCM'>Master of Business Administration/Health Care Management</option>
<option value='MBA/HRM'>Master of Business Administration/Human Resources Management</option>
<option value='MBA/MKT'>Master of Business Administration/Marketing</option>
<option value='MBA/TM'>Master of Business Administration/Technology Management</option>
<option value='MIS'>Master of Information Systems</option>
<option value='MIS/M'>Master of Information Systems/Management</option>";	
}

if ($iId == '91') {
	$sProgramDropDown = "
	<option value='BSB/A'>Bachelor of Science in Business/Administration</option>
<option value='BSB/M'>Bachelor of Science in Business/Management</option>
<option value='BSCJA'>Bachelor of Science in Criminal Justice Administration</option>
<option value='BSIT'>Bachelor of Science in Information Technology</option>
<option value='BSIT/VC'>Bachelor of Science in Information Technology/Visual Communication</option>
<option value='MBA'>Master of Business Administration</option>
<option value='MIS/M'>Master of Information Systems/Management</option>
<option value='MM'>Master of Management</option>";	
}

if ($iId == '92') {
	$sProgramDropDown = "
	<option value='BSB/M'>Bachelor of Science in Business/Management</option>
<option value='MBA'>Master of Business Administration</option>";	
}
if ($iId == '93') {
	$sProgramDropDown = "
	<option value='BSB/A'>Bachelor of Science in Business/Administration</option>
<option value='BSB/M'>Bachelor of Science in Business/Management</option>
<option value='BSCJA'>Bachelor of Science in Criminal Justice Administration</option>
<option value='BSIT'>Bachelor of Science in Information Technology</option>
<option value='MBA'>Master of Business Administration</option>
<option value='MBA/GM'>Master of Business Administration/Global Management</option>
<option value='MBA/HCM'>Master of Business Administration/Health Care Management</option>
<option value='MBA/TM'>Master of Business Administration/Technology Management</option>
<option value='MM'>Master of Management</option>";	
}
if ($iId == '94') {
	$sProgramDropDown = "
	<option value='BSB/M'>Bachelor of Science in Business/Management</option>
<option value='MBA'>Master of Business Administration</option>";	
}

echo $sProgramDropDown;

?>
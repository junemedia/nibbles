<?php

//field
class Field {
	var $out = "<input type='[TYPE]' name='[NAME]' value='[VALUE]' [ONBLUR] [EXTRA]>";
	var $name = '';
	var $type = 'text';
	var $value = '';
	var $onBlur = '';
	var $extra = '';
	function html($name = '', $type = '',$value = '', $onBlur = '', $extra = ''){
		$a = $this->out;
		
		if($name != '') $this->name = $name;		
		if($type != '') $this->type = $type;
		if($value != '') $this->value = $value;
		if($onBlur != '') $this->onBlur = $onBlur;
		if($extra != '') $this->extra = $extra;

		$a = str_replace('[TYPE]', $this->type, $a);
		$a = str_replace('[NAME]', $this->name, $a);
		$a = str_replace('[VALUE]', $this->value, $a);
		$a = str_replace('[ONBLUR]', ($this->onBlur ? "onBlur=\"".$this->onBlur."\"" : ''), $a);
		$a = str_replace('[EXTRA]', $this->extra, $a);
		return $a;
	}
}

class Select extends Field{
	var $out = "<select name='[NAME]' [ONBLUR]>[VALUE]</select>";
}

//email field
class EmailField extends Field {
	var $name = 'sEmail';
	var $onBlur = "fieldError('sEmail',coRegPopup.send('/libs/valid.php?field=email&value='+this.value,''));";
}

//name field
class FNameField extends Field {
	var $name = 'sFirst';
	var $onBlur = "fieldError('sFirst',coRegPopup.send('/libs/valid.php?field=first&value='+this.value,''));";
}

class LNameField extends Field {
	var $name = 'sLast';
	var $onBlur = "fieldError('sLast',coRegPopup.send('/libs/valid.php?field=last&value='+this.value,''));";
}

//address field
class AddressField extends Field {
	var $name = 'sAddress';
	var $onBlur = "fieldError('sAddress',coRegPopup.send('/libs/valid.php?field=address&value='+this.value,''));";
}

//city
class CityField extends Field {
	var $name = 'sCity';
	var $onBlur = "fieldError('sCity',coRegPopup.send('/libs/valid.php?field=city&value='+this.value,''));";
}

//state
class StateSelect extends Select {
	var $name = 'sState';
	//var $onBlur = "if(coreg.send('/nibbles2/lib/validate.php?field=state&value='+this.value,'')=='0') fieldError('sState');";
	var $value = "<option value=''>
	<option value=AL >Alabama
	<option value=AK >Alaska
	<option value=AS >American Samoa
	<option value=AZ >Arizona
	<option value=AR >Arkansas
	<option value=CA >California
	<option value=CO >Colorado
	<option value=CT >Connecticut
	<option value=DE >Delaware
	<option value=DC >District of Columbia
	<option value=FL >Florida
	<option value=GA >Georgia
	<option value=GU >Guam
	<option value=HI >Hawaii
	<option value=ID >Idaho
	<option value=IL >Illinois
	<option value=IN >Indiana
	<option value=IA >Iowa
	<option value=KS >Kansas
	<option value=KY >Kentucky
	<option value=LA >Louisiana
	<option value=ME >Maine
	<option value=MH >Marshall Islands
	<option value=MD >Maryland
	<option value=MA >Massachusetts
	<option value=MI >Michigan
	<option value=MN >Minnesota
	<option value=MS >Mississippi
	<option value=MO >Missouri
	<option value=MT >Montana
	<option value=NE >Nebraska
	<option value=NV >Nevada
	<option value=NH >New Hampshire
	<option value=NJ >New Jersey
	<option value=NM >New Mexico
	<option value=NY >New York
	<option value=NC >North Carolina
	<option value=ND >North Dakota
	<option value=OH >Ohio
	<option value=OK >Oklahoma
	<option value=OR >Oregon
	<option value=PW >Palau
	<option value=PA >Pennsylvania
	<option value=PR >Puerto Rico
	<option value=RI >Rhode Island
	<option value=SC >South Carolina
	<option value=SD >South Dakota
	<option value=TN >Tennessee
	<option value=TX >Texas
	<option value=TT >Trust Territories
	<option value=UT >Utah
	<option value=VT >Vermont
	<option value=VI >Virgin Islands
	<option value=VA >Virginia
	<option value=WL >Wake Island
	<option value=WA >Washington
	<option value=WV >West Virginia
	<option value=WI >Wisconsin
	<option value=WY >Wyoming";
}

//stateText
class StateTextField extends Field {
	var $name = 'sState';
	var $onBlur = "fieldError('sState',coRegPopup.send('/libs/valid.php?field=state&value='+this.value,''));";
}

//zip
class ZipField extends Field {
	var $name = 'sZip';
	var $extra = "size=5 length=5 ";
	var $onBlur = "fieldError('sZip',coRegPopup.send('/libs/valid.php?field=zip&value='+this.value,''));";
}

class AddressGroup {
	
	var $script = "<script language='javascript'>
			function AddressValidate(){
				
				//alert('Phone Validate');
			
				var val = document.form1.elements['sAddress'].value;
				val += '-'+document.form1.elements['sAddress2'].value;
				val += '-'+document.form1.elements['sCity'].value;
				val += '-'+document.form1.elements['sState'].value;
				val += '-'+document.form1.elements['sZip'].value;
				
				var response = coRegPopup.send('/libs/valid.php?field=address&value='+val,'');
				
				if(response=='0'){
					fieldError('sAddress','0');
					fieldError('sAddress2','0');
					fieldError('sCity','0');
					fieldError('sState','0');
					fieldError('sZip','0');
				} else {
					fieldError('sAddress','1');
					fieldError('sAddress2','1');
					fieldError('sCity','1');
					fieldError('sState','1');
					fieldError('sZip','1');		
				}
			}
		
		</script>";
	
	function html(){
		$address = new AddressField();
		$address2 = new AddressField();
		$city = new CityField();
		$state = new StateSelect();
		$zip = new ZipField();
		return "<tr>
				<td>Address:</td><td> ".$address->html()."</td>
			</tr>
			<tr>
				<td>Address 2:</td><td> ".$address2->html('sAddress2')."</td>
			</tr>
			<tr>
				<td>City :</td><td>".$city->html()."</td><td>State:</td><td> ".$state->html()."</td><td>Zip:</td><td> ".$zip->html()."</td><td>
			</tr>".$this->script;
	}
	
	function register(){
		return "AddressValidate();";
	}
}

//phoneAreaCode
class PhoneAreaCodeField extends Field {
	var $name = 'sPhoneAreaCode';
	var $extra = "size=3 length=3 ";
	//var $onBlur = "PhoneValidate();";
}

//phoneExchange
class PhoneExchangeField extends Field {
	var $name = 'sPhoneExchange';
	var $extra = "size=3 length=3 ";
	//var $onBlur = "PhoneValidate();";
}

//phoneLast4
class PhoneLast4Field extends Field {
	var $name = 'sPhoneLast4';
	var $extra = "size=4 length=4 ";
	//var $onBlur = "PhoneValidate();";
}

//phoneExtension
class PhoneExtensionField extends Field {
	var $name = 'sPhoneExtension';
	var $extra = "size=4 length=4 ";
	//var $onBlur = "PhoneValidate();";
}

//phone
class PhoneField {
	
	function html(){
		$area = new PhoneAreaCodeField();
		$exch = new PhoneExchangeField();
		$last4 = new PhoneLast4Field();
		$ext = new PhoneExtensionField();
		$script = "<script language='javascript'>
			function PhoneValidate(){
				
				//alert('Phone Validate');
			
				var val = document.form1.elements['sPhoneAreaCode'].value;
				val += '-'+document.form1.elements['sPhoneExchange'].value;
				val += '-'+document.form1.elements['sPhoneLast4'].value;
				val += '-'+document.form1.elements['sPhoneExtension'].value;
				if(coRegPopup.send('/libs/valid.php?field=phone&value='+val,'')=='0'){
					fieldError('sPhoneAreaCode','0');
					fieldError('sPhoneExchange','0');
					fieldError('sPhoneLast4','0');
					fieldError('sPhoneExtension','0');
				} else {
					fieldError('sPhoneAreaCode','1');
					fieldError('sPhoneExchange','1');
					fieldError('sPhoneLast4','1');	
					fieldError('sPhoneExtension','1');				
				}
			}
		
		</script>";
		return $area->html().$exch->html().$last4->html().$ext->html().$script;
	}
	
	function register(){
		return "PhoneValidate();";
	}
}

//salutation
class SalutationSelect extends Select {
	var $name = 'sSalutation';
	var $value = "<option value='Mr.' >Mr.
		<option value='Mrs.' >Mrs.
		<option value='Ms.' >Ms.
		<option value='Dr.' >Dr.
		<option value='Other' >Other";
}

//dobYear
class DOBYearSelect extends Select {
	var $name = 'sDOBYear';
	var $value = "<option value='' >";
	//var $onBlur = "DOBValidate();";
	function html($name = '', $type = '',$years = 88, $onBlur = '', $extra = ''){
		for($i=0;$i<$years;$i++){
			$value .= "\n<option value='".(strftime('%Y',strtotime('today'))-$i)."'>".(strftime('%Y',strtotime('today'))-$i);
		}	
		$a = $this->out;
	
		if($name != '') $this->name = $name;		
		if($type != '') $this->type = $type;
		if($value != '') $this->value = $value;
		if($onBlur != '') $this->onBlur = $onBlur;
		if($extra != '') $this->extra = $extra;

		$a = str_replace('[TYPE]', $this->type, $a);
		$a = str_replace('[NAME]', $this->name, $a);
		$a = str_replace('[VALUE]', $this->value, $a);
		$a = str_replace('[ONBLUR]', ($this->onBlur ? "onBlur=\"".$this->onBlur."\"" : ''), $a);
		$a = str_replace('[EXTRA]', $this->extra, $a);
		
		return $a;
	}
		
}

//dobMonth
class DOBMonthSelect extends Select {
	var $name = 'sDOBMonth';
	//var $onBlur = "DOBValidate();";
	var $value = "<option value='' >
		<option value='01' >Jan.
		<option value='02' >Feb.
		<option value='03' >Mar.
		<option value='04' >Apr.
		<option value='05' >May
		<option value='06' >Jun.
		<option value='07' >Jul.
		<option value='08' >Aug.
		<option value='09' >Sep.
		<option value='10' >Oct.
		<option value='11' >Nov.
		<option value='12' >Dec.";
}

//dobDay
class DOBDaySelect extends Select {
	var $name = 'sDOBDay';
	//var $onBlur = "DOBValidate();";
	var $value = "<option value='' >
		<option value='01' >01
		<option value='02' >02
		<option value='03' >03
		<option value='04' >04
		<option value='05' >05
		<option value='06' >06
		<option value='07' >07
		<option value='08' >08
		<option value='09' >09
		<option value='10' >10
		<option value='11' >11
		<option value='12' >12
		<option value='13' >13
		<option value='14' >14
		<option value='15' >15
		<option value='16' >16
		<option value='17' >17
		<option value='18' >18
		<option value='19' >19
		<option value='20' >20
		<option value='21' >21
		<option value='22' >22
		<option value='23' >23
		<option value='24' >24
		<option value='25' >25
		<option value='26' >26
		<option value='27' >27
		<option value='28' >28
		<option value='29' >29
		<option value='30' >30
		<option value='31' >31
		";
}

//dob
class DOBField {
	
	function html(){
		$year = new DOBYearSelect();
		$month = new DOBMonthSelect();
		$day = new DOBDaySelect();
		
		$script = "<script language='javascript'>
			function DOBValidate(){		
			
				//alert('DOB Validate');
			
				var val = document.form1.elements['sDOBMonth'].value;
				val += '/'+document.form1.elements['sDOBDay'].value;
				val += '/'+document.form1.elements['sDOBYear'].value;
				
				//alert(coRegPopup.send('/libs/valid.php?field=dob&value='+val,''));
				
				if(coRegPopup.send('/libs/valid.php?field=dob&value='+val,'')=='0'){
					fieldError('sDOBDay','0');
					fieldError('sDOBMonth','0');
					fieldError('sDOBYear','0');
				} else {
					fieldError('sDOBDay','1');
					fieldError('sDOBMonth','1');
					fieldError('sDOBYear','1');			
				}
			}
		
		</script>";
		return $month->html().$day->html().$year->html().$script;
	}
	
	function register(){
		return " DOBValidate();";
	}
	
}
//gender
class GenderSelect extends Select {
	var $name = 'sGender';
	var $value = "<option value='M' >Male
		<option value='F' >Female";
}

class Button extends Field {
	var $out = "<input type='[TYPE]' name='[NAME]' value='[VALUE]' [ONBLUR] [EXTRA]>";
	var $type = 'button';
	var $name = 'sSubmit';
	var $value = 'submit';
	var $onBlur = '';
	var $extra = '';
	
	function html($name = '', $type = '',$value = '', $onBlur = '', $extra = ''){
		$a = $this->out;
		
		if($name != '') $this->name = $name;		
		if($type != '') $this->type = $type;
		if($value != '') $this->value = $value;
		if($onBlur != '') $this->onBlur = $onBlur;
		if($extra != '') $this->extra = $extra;

		$a = str_replace('[TYPE]', $this->type, $a);
		$a = str_replace('[NAME]', $this->name, $a);
		$a = str_replace('[VALUE]', $this->value, $a);
		$a = str_replace('[ONBLUR]', ($this->onBlur ? "onBlur=\"".$this->onBlur."\"" : ''), $a);
		$a = str_replace('[EXTRA]', ($this->extra ? "onClick=\"".$this->extra."validation();\"" : ''), $a);
		return $a;
	}
	
	
}

?>
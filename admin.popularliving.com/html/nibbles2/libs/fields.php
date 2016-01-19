<?php

//field
class Field {
	var $out = "<input type='[TYPE]' name='[NAME]' id='[NAME]' value='[VALUE]' [ONBLUR] [EXTRA] [STYLE]>";
	var $name = '';
	var $type = 'text';
	var $value = '';
	var $onBlur = '';
	var $extra = '';
	var $style = '';
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
		$a = str_replace('[STYLE]', ($this->style ? "style=\"".$this->style."\"" : ''), $a);
		return $a;
	}
	
	function required(){
		return "if(document.form1.$this->name.value == ''){fieldError('$this->name','0');} else {fieldError('$this->name','1');}".$this->onBlur;
	}
}

class Select extends Field{
	var $out = "<select name='[NAME]' [ONBLUR] id='[NAME]' [EXTRA] [STYLE]>[VALUE]</select>";
}

//email field
class EmailField extends Field {
	var $name = 'sEmail';
	var $onBlur = "fieldError('sEmail',coRegPopup.send('/nibbles2/libs/valid.php?field=email&value='+document.form1.sEmail.value,''));";
}

//name field
class FNameField extends Field {
	var $name = 'sFirst';
	var $onBlur = "fieldError('sFirst',coRegPopup.send('/nibbles2/libs/valid.php?field=first&value='+document.form1.sFirst.value,''));";
	var $extra = "tabindex=1";
}

class LNameField extends Field {
	var $name = 'sLast';
	var $onBlur = "fieldError('sLast',coRegPopup.send('/nibbles2/libs/valid.php?field=last&value='+document.form1.sLast.value,''));";
	var $extra = "tabindex=2";
}

//address field
class AddressField extends Field {
	var $name = 'sAddress';
	//var $onBlur = "fieldError('sAddress',coRegPopup.send('/nibbles2/valid.php?field=address&value='+this.value,''));";
	
	var $extra = "tabindex=3";
}

//city
class CityField extends Field {
	var $name = 'sCity';
	//var $onBlur = "fieldError('sCity',coRegPopup.send('/nibbles2/valid.php?field=city&value='+this.value,''));";
	var $extra = "tabindex=4";
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
	var $onBlur = "fieldError('sState',coRegPopup.send('/nibbles2/libs/valid.php?field=state&value='+this.value,''));";
}


class StateField extends Field {
	var $name = 'sState';
	var $type = 'hidden';
	
}

//zip
class ZipField extends Field {
	var $name = 'sZip';
	var $extra = "size=10 maxlength=5 tabindex=5";
	var $onBlur = "fieldError('sZip',coRegPopup.send('/nibbles2/libs/valid.php?field=zip&value='+document.form1.sZip.value,''));asdf = coRegPopup.send('/nibbles2/libs/valid.php?field=zip2State&value='+document.form1.sZip.value,''); if(asdf != '0'){document.form1.sState.value = asdf; fieldError('sZip', '1');} else {fieldError('sZip', '0');}";
}

class AddressGroup {
			
	function html(){
		$address = new AddressField();
		//$address2 = new AddressField();
		$city = new CityField();
		$state = new StateSelect();
		$zip = new ZipField();
		return "<tr>
				<td>Address:</td><td> ".$address->html()."</td>
			</tr>
			<!--
			-->
			<tr>
				<td>City :</td><td>".$city->html()."</td><td>State:</td><td> ".$state->html()."</td><td>Zip:</td><td> ".$zip->html()."</td><td>
			</tr>";//.$this->script;
	}
	
	function register(){
		return "AddressValidate();";
	}
	
	function required(){
		$address = new AddressField();
		//$address2 = new AddressField();
		$city = new CityField();
		$state = new StateSelect();
		$zip = new ZipField();
		
		return $address->required().$city->required().$state->required().$zip->required();
		
	}
}

//phoneAreaCode
class PhoneAreaCodeField extends Field {
	var $name = 'sPhone_areaCode';
	var $extra = "size=3 maxlength=3 tabindex=6 ";
	//var $onBlur = "PhoneValidate();";
}

//phoneExchange
class PhoneExchangeField extends Field {
	var $name = 'sPhone_exchange';
	var $extra = "size=3 maxlength=3 tabindex=7 ";
	//var $onBlur = "PhoneValidate();";
}

//phoneLast4
class PhoneLast4Field extends Field {
	var $name = 'sPhone_number';
	var $extra = "size=4 maxlength=4 tabindex=8 ";
	//var $onBlur = "PhoneValidate();";
}

//phoneExtension
class PhoneExtensionField extends Field {
	var $name = 'sPhoneExtension';
	var $extra = "size=4 maxlength=4 tabindex=9 ";
	//var $onBlur = "PhoneValidate();";
}

//phone
class PhoneField {
	
	var $extra = '';
	
	function html(){
		if($this->value != ''){
			list($areaN, $exchN, $last4N) = explode('-',$this->value);
		}
		//echo $this->value;
		$area = new PhoneAreaCodeField();
		$exch = new PhoneExchangeField();
		$last4 = new PhoneLast4Field();
		//$ext = new PhoneExtensionField();
		
		if($this->extra != ''){
			$area->extra .= $this->extra;
			$exch->extra .= $this->extra;
			$last4->extra .= $this->extra;
		}
		
		return $area->html('sPhone_areaCode', 'text',$areaN).' - '.$exch->html('sPhone_exchange','text',$exchN).' - '.$last4->html('sPhone_number', 'text',$last4N);//.$script;//$ext->html()
	}
	
	function register(){
		return "PhoneValidate();";
	}
	
	function required(){
		$area = new PhoneAreaCodeField();
		$exch = new PhoneExchangeField();
		$last4 = new PhoneLast4Field();
		
		return $area->required().$exch->required().$last4->required();
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
	var $name = 'iBirthYear';
	var $value = "<option value='' >Year";
	var $selected = '';
	var $extra = "tabindex=12 ";
	var $style = 'font: bold 13px Arial, Helvetica, sans-serif;width:58px;';
	//var $onBlur = "DOBValidate();";
	function html($name = '', $type = '',$value = '', $onBlur = '', $extra = ''){
		$years = strftime('%Y',strtotime('today')) - '1909';
		for($i='16';$i<$years;$i++){
			$selected = ((strftime('%Y',strtotime('today'))-$i) == $this->selected ? 'selected' : '');
			$this->value .= "\n<option value='".(strftime('%Y',strtotime('today'))-$i)."' $selected>".(strftime('%Y',strtotime('today'))-$i);
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
		$a = str_replace('[STYLE]', ($this->style ? "style=\"".$this->style."\"" : ''), $a);
		$a = str_replace('[EXTRA]', $this->extra, $a);
		
		return $a;
	}
		
}

//dobMonth
class DOBMonthSelect extends Select {
	var $name = 'iBirthMonth';
	//var $onBlur = "DOBValidate();";
	var $value = "<option value='' >Month";
	var $selected = '';
	var $extra = "tabindex=10 ";
	var $style = 'font: bold 13px Arial, Helvetica, sans-serif;width:60px;';
	
	function html($name = '', $type = '',$value = '', $onBlur = '', $extra = ''){
		
		$this->value .= "\n<option value='01' ".('01' == $this->selected ? 'selected' : '').">Jan";
		$this->value .= "\n<option value='02' ".('02' == $this->selected ? 'selected' : '').">Feb";
		$this->value .= "\n<option value='03' ".('03' == $this->selected ? 'selected' : '').">Mar";
		$this->value .= "\n<option value='04' ".('04' == $this->selected ? 'selected' : '').">Apr";
		$this->value .= "\n<option value='05' ".('05' == $this->selected ? 'selected' : '').">May";
		$this->value .= "\n<option value='06' ".('06' == $this->selected ? 'selected' : '').">Jun";
		$this->value .= "\n<option value='07' ".('07' == $this->selected ? 'selected' : '').">Jul";
		$this->value .= "\n<option value='08' ".('08' == $this->selected ? 'selected' : '').">Aug";
		$this->value .= "\n<option value='09' ".('09' == $this->selected ? 'selected' : '').">Sep";
		$this->value .= "\n<option value='10' ".('10' == $this->selected ? 'selected' : '').">Oct";
		$this->value .= "\n<option value='11' ".('11' == $this->selected ? 'selected' : '').">Nov";
		$this->value .= "\n<option value='12' ".('12' == $this->selected ? 'selected' : '').">Dec";
			
			
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
		$a = str_replace('[STYLE]', ($this->style ? "style=\"".$this->style."\"" : ''), $a);
		$a = str_replace('[EXTRA]', $this->extra, $a);
		
		return $a;
	}
}

//dobDay
class DOBDaySelect extends Select {
	var $name = 'iBirthDay';
	//var $onBlur = "DOBValidate();";
	var $value = "<option value='' >Day";
	var $selected = '';
	var $extra = "tabindex=11 ";
	var $style = 'font: bold 13px Arial, Helvetica, sans-serif;width:50px;';
	
	function html($name = '', $type = '',$value = '', $onBlur = '', $extra = ''){
		for($i=1;$i<32;$i++){
			$selected = ($i == $this->selected ? 'selected' : '');
			$this->value .= "\n<option value='$i' $selected>$i";
		}	
		$a = $this->out;
	
		if($name != '') $this->name = $name;		
		if($type != '') $this->type = $type;
		//if($value != '') $this->value = $value;
		if($onBlur != '') $this->onBlur = $onBlur;
		if($extra != '') $this->extra = $extra;

		$a = str_replace('[TYPE]', $this->type, $a);
		$a = str_replace('[NAME]', $this->name, $a);
		$a = str_replace('[VALUE]', $this->value, $a);
		$a = str_replace('[ONBLUR]', ($this->onBlur ? "onBlur=\"".$this->onBlur."\"" : ''), $a);
		$a = str_replace('[STYLE]', ($this->style ? "style=\"".$this->style."\"" : ''), $a);
		$a = str_replace('[EXTRA]', $this->extra, $a);
		
		return $a;
	}
}

//dob
class DOBField {
	var $extra = '';
	
	function html($value = ''){
		$year = new DOBYearSelect();
		$month = new DOBMonthSelect();
		$day = new DOBDaySelect();
		if($value != ''){
			//echo $value;
			list($m,$d,$y) = explode('/',$value);
			$year->selected = $y;
			$month->selected = $m;
			$day->selected = $d;
		}
		
		if($this->extra != ''){
			$year->extra .= $this->extra;
			$month->extra .= $this->extra;
			$day->extra .= $this->extra;
		}
		
		return $month->html()."\n                    ".$day->html()."\n                    ".$year->html();//.$script;
	}
	
	function register(){
		return " DOBValidate();";
	}
	
	function required(){
		
		$year = new DOBYearSelect();
		$month = new DOBMonthSelect();
		$day = new DOBDaySelect();
		
		return $year->required().$month->required().$day->required();
	}
	
}
//gender
class GenderSelect extends Select {
	var $name = 'sGender';
	var $value = "<option value='' >";
	var $selected = '';
	var $extra = "tabindex=13 ";
	
	function html($name = '', $type = '',$value = '', $onBlur = '', $extra = ''){
		
		$value .= "\n<option value='M' ".('M' == $this->selected ? 'selected' : '').">Male";
		$value .= "\n<option value='F' ".('F' == $this->selected ? 'selected' : '').">Female";
			
			
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

class GenderRadio extends Field {
	
	var $out = "Male <input type='radio' name='sGender' value='M' [M_SELECTED] [ONBLUR] [M_EXTRA]> Female  <input type='radio' name='sGender' value='F' [F_SELECTED] [ONBLUR] [F_EXTRA]>";
	var $type = 'radio';
	var $name = 'sGender';
	var $value = '';
	//var $selected = '';
	var $extra = "tabindex=14 ";
	
	function html($name = '', $type = '',$value = '', $onBlur = '', $extra = ''){
		$a = $this->out;
		
		if($name != '') $this->name = $name;		
		if($type != '') $this->type = $type;
		if($value != '') $this->value = $value;
		if($onBlur != '') $this->onBlur = $onBlur;
		if($extra != '') $this->extra = $extra;

		$a = str_replace('[TYPE]', $this->type, $a);
		$a = str_replace('[NAME]', $this->name, $a);
		//$a = str_replace('[VALUE]', $this->value, $a);
		$a = str_replace('[ONBLUR]', ($this->onBlur ? "onBlur=\"".$this->onBlur."\"" : ''), $a);
		$a = str_replace('[EXTRA]', ($this->extra ? $this->extra : ""), $a);
		$a = str_replace('[M_SELECTED]', (strtoupper($this->value) == 'M' ? 'checked' : ''), $a);
		$a = str_replace('[F_SELECTED]', (strtoupper($this->value) == 'F' ? 'checked' : ''), $a);
		$a = str_replace('[M_EXTRA]', ($this->extra ? $this->extra : ''), $a);
		$a = str_replace('[F_EXTRA]', ($this->extra ? $this->extra : ''), $a);
		
		
		return $a;
	}
		
	function required(){
		return "if((document.form1.sGender[0].checked != true)&&(document.form1.sGender[1].checked != true)){ if(!in_array(FieldErrors, 'sGender')){FieldErrors.push('sGender');document.form1.sGender[0].style.backgroundColor = '#FFFF00';document.form1.sGender[1].style.backgroundColor = '#FFFF00';}} else {if(in_array(FieldErrors, 'sGender')){FieldErrors = array_remove(FieldErrors,'sGender');document.form1.sGender[0].style.backgroundColor = '#FFFFFF';document.form1.sGender[1].style.backgroundColor = '#FFFFFF';}}";
	}
}

class Button extends Field {
	var $out = "<input type='[TYPE]' name='[NAME]' value='[VALUE]' [ONBLUR] [EXTRA] [STYLE] >";
	var $type = 'button';
	var $name = 'sSubmit';
	var $value = "submit";
	var $onBlur = '';
	var $extra = '';
	var $style = "border-style:Double;text-transform:capitalize;";
	
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
		$a = str_replace('[EXTRA]', ($this->extra ? "onClick=\"".$this->extra."\"" : ""), $a);
		$a = str_replace('[STYLE]', ($this->style ? "style=\"".$this->style."\"" : ''), $a);
		return $a;
	}
	
}

class LetsGoButton extends Button {
	var $type = 'submit';
	var $value = "Let&#39;s Go!&gt;&gt;&gt;";
	var $style = "border-style:Double;width:150px;text-transform:capitalize;";
		
}

class SubmitButton extends Button {
	var $type = 'submit';
	var $extra = 'UserFormErrors();';//submit1Clicked();
	
}

class SkipButton extends Button {
	var $name = 'skip';
	var $value = 'Skip';
	
}

class SubmitImage extends Button {
	var $out = "<input type='[TYPE]' name='[NAME]' value='[VALUE]' [ONBLUR] [EXTRA] [STYLE] src='[SRC]' tabindex=15>";
	var $type = 'image';
	var $name = 'submit';
	var $value = "Let&#39;s Go &gt;&gt;&gt;";
	var $onBlur = '';
	var $extra = '';
	var $style = "border-width:0px;height:40px;width:300px; cursor: pointer;";
	var $src = "http://images.popularliving.com/p/scf_dove1/images/E1/spacer.gif";
	
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
		$a = str_replace('[SRC]', $this->src, $a);
		$a = str_replace('[ONBLUR]', ($this->onBlur ? "onBlur=\"".$this->onBlur."\"" : ''), $a);
		$a = str_replace('[EXTRA]', ($this->extra ? "onClick=\"".$this->extra."\"" : ""), $a);
		$a = str_replace('[STYLE]', ($this->style ? "style=\"".$this->style."\"": "" ), $a);
		
		return $a;
	}
	
}

class SubmitImage2 extends Button {
	var $head = "<input type='[TYPE]' name='[NAME]' value='[VALUE]' [ONBLUR] [EXTRA] [STYLE] ";
	var $tail = " tabindex=15>";
	var $type = 'image';
	var $name = 'submit';
	var $value = "Let&#39;s Go &gt;&gt;&gt;";
	var $onBlur = '';
	var $extra = '';
	var $style = "";
		
	function head($name = '', $type = '',$value = '', $onBlur = '', $extra = ''){
		$a = $this->head;
		
		if($name != '') $this->name = $name;		
		if($type != '') $this->type = $type;
		if($value != '') $this->value = $value;
		if($onBlur != '') $this->onBlur = $onBlur;
		if($extra != '') $this->extra = $extra;

		$a = str_replace('[TYPE]', $this->type, $a);
		$a = str_replace('[NAME]', $this->name, $a);
		$a = str_replace('[VALUE]', $this->value, $a);
		$a = str_replace('[ONBLUR]', ($this->onBlur ? "onBlur=\"".$this->onBlur."\"" : ''), $a);
		$a = str_replace('[EXTRA]', ($this->extra ? "onClick=\"".$this->extra."\"" : ""), $a);
		$a = str_replace('[STYLE]', ($this->style ? "style=\"".$this->style."\"": "" ), $a);
		
		return $a;
	}
	
	function tail(){
		return $this->tail;
	}
	
}

?>
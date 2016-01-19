function AmpereMedia() {
};

AmpereMedia.prototype.init = function () {
	var xmlHttp=false;
/*@cc_on @*/
/*@if (@_jscript_version >= 5)
 try {
  xmlHttp = new ActiveXObject("Msxml2.XMLHTTP");
 } catch (e) {
  try {
   xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
  } catch (E) {
   xmlHttp = false;
  }
 }
@end @*/
	if (!xmlHttp && typeof XMLHttpRequest!='undefined') {
	  xmlHttp = new XMLHttpRequest();
	}

	try {
		// Mozilla / Safari
		this._xh = new XMLHttpRequest();
	} catch (e) {
		// Explorer
		this._xh = new ActiveXObject("Microsoft.XMLHTTP");
	}
}

AmpereMedia.prototype.busy = function () {
	return (this._xh.readyState && (this._xh.readyState > 4))
}

AmpereMedia.prototype.send = function (url,data) {
	if (!this._xh) {
		this.init();
	}
	if (!this.busy()) {
		this._xh.open("GET",url,false);
		this._xh.send(data);
		if (this._xh.readyState == 4 && this._xh.status == 200) {
			return this._xh.responseText;
		}
	}
	return false;
}

var coRegPopup = new AmpereMedia();

var FieldErrors = new Array();
function fieldError(name, value){
	if(in_array(FieldErrors,name)){
		if(value == '1'){
			//alert('passed');
			document.form1.elements[name].style.backgroundColor = '#FFFFFF';
			FieldErrors = array_remove(FieldErrors,name);
		}
	} else {
		if(value == '0'){
			//alert('failed');
			FieldErrors.push(name);
			document.form1.elements[name].style.backgroundColor = '#FFFF00';
		}
	}
}

function in_array(arr, val){
	for(i=0;i<arr.length;i++){
		if(arr[i] == val)
			return true;
	}
	return false;
}

function array_remove(arr, val){
	//alert('in: '+arr);
	if(arr.length == 0) return;
	for(i=0;i<arr.length;i++){
		if(arr[i] == val){
			killit = i;
		}
	}
	if(killit == (arr.length-1)){
		arr = arr.slice(0,(arr.length-1));
		return arr;
	} else if(killit == 0){
		arr = arr.slice(1,(arr.length));
		return arr;
	} else {
		part1 = arr.slice(0,(killit));
		part2 = arr.slice((killit+1),(arr.length));
			
		arr = part1.concat(part2);
		return arr;
	}
}

function AddressValidate(){
				
				//alert('Address Validate');
						
	var val = document.form1.elements['sAddress'].value;
	//val += '-'+document.form1.elements['sAddress2'].value;
	val += '-'+document.form1.elements['sCity'].value;
	//val += '-'+document.form1.elements['sState'].value;
	val += '-'+document.form1.elements['sZip'].value;
				
	var response = coRegPopup.send('/nibbles2/libs/valid.php?field=address&value='+val,'');
	if(response=='0'){
		fieldError('sAddress','0');
		//fieldError('sAddress2','0');
		fieldError('sCity','0');
		//fieldError('sState','0');
		if(!((document.form1.elements['sZip'].value != '') && (document.form1.elements['sCity'].value == '') && (document.form1.elements['sAddress'].value == ''))) {
			fieldError('sZip','0');
		} else {
			fieldError('sZip','1');
		}
	} else {
		//alert(response);
		if(response== 'AM'){
				//$sMessage .= "<li>$sAoErrorText Contains Invalid Characters or Are Blank";
				if(document.form1.elements['sAddress'].value == '') {fieldError('sAddress','0');} else {fieldError('sAddress','1');}
				if(document.form1.elements['sCity'].value == '') {fieldError('sCity','0');} else {fieldError('sCity','1');}
				if(document.form1.elements['sZip'].value == '') {fieldError('sZip','0');} else {fieldError('sZip','1');}
		}
		else if(response == 'R'){
				//$sMessage .= "<li>Please Enter a Valid Address for your Street";
				fieldError('sAddress','0');
		}
		else if(response == 'U'){
				//$sMessage .= "<li>Please Enter a Valid Street for your City, State, and ZipCode";
				fieldError('sAddress','0');
		}
		else if(response == 'X'){
				//$sMessage .= "<li>Please Enter a Deliverable Address";
				fieldError('sAddress','0');
				fieldError('sCity','0');
				fieldError('sZip','0');
		}
		else if(response == 'T'){
				//$sMessage .= "<li>Please Check the Format of Your Address";
				fieldError('sAddress','0');
				fieldError('sCity','0');
				fieldError('sZip','0');
		}
		else if(response == 'Z'){
				//$sMessage .= "<li>Please Enter a Valid Zip Code";
				fieldError('sZip','0');
		}
		else if(response == 'W'){
				//$sMessage .= "<li>Address Failure: Early Waring Address.  Please correct.";
				fieldError('sAddress','0');
				fieldError('sCity','0');
				fieldError('sZip','0');
		} else if(response.match('^update')){
			AddressResponse = response.split('||');
			AddressParts = AddressResponse[1].split('-');
			document.form1.sAddress.value = AddressParts[0];
			document.form1.sCity.value = AddressParts[1];
			document.form1.sZip.value = AddressParts[2];
			fieldError('sAddress','1');
			fieldError('sCity','1');
			fieldError('sZip','1');		
		} else {
			
			fieldError('sAddress','1');
			//fieldError('sAddress2','1');
			fieldError('sCity','1');
			//fieldError('sState','1');
			fieldError('sZip','1');		
		}
	}
}

function ErrorMessage(fieldName, value, src) {
	var out = coRegPopup.send('/nibbles2/libs/ErrorMessage.php?field='+fieldName+'&value='+value+'&src='+src,'');
	return out;
	/*
	if(fieldName == 'sFirst') {
		out += "\n* Please enter your First Name.";
	} else if(fieldName == 'sLast') {
		out += "\n* Please enter your Last Name.";
	} else if(fieldName == 'sEmail') {
		if(value == '') {
			out += "\n* Please enter your Email.";
		} else {
			out += "\n* Please enter a valid Email address.";
		}
	} else if(fieldName == 'sAddress') {
		if(value == '') {
			out += "\n* Please enter your Address.";
		}
	} else if(fieldName == 'sCity') {
		if(value == '') {
			out += "\n* Please enter your city.";
		}
	} else if(fieldName == 'sState') {
		out += "\n* Please enter your state.";
	} else if(fieldName == 'sZip') {
		if(value == '') {
			out += "\n* Please enter your zip code.";
		} else {
			out += "\n* Please enter a valid zip code.";
		}
	} else if(fieldName == 'sPhone_areaCode') {
		if(value == '') {
			out += "\n* Please enter area code.";
		}
	} else if(fieldName == 'sPhone_exchange') {
		if(value == '') {
			out += "\n* Please enter your exchange.";
		}
	} else if(fieldName == 'sPhone_number') {
		if(value == '') {
			out += "\n* Please enter your number.";
		}
	} else if(fieldName == 'iBirthYear') {
		if(value == '') {
			out += "\n* Please select birth year.";
		}
	} else if(fieldName == 'iBirthMonth') {
		if(value == '') {
			out += "\n* Please select birth month.";
		}
	} else if(fieldName == 'iBirthDay') {
		if(value == '') {
			out += "\n* Please select birth day.";
		} 
	} else if(fieldName == 'sGender') {
		out += "\n* Please select your gender.";
	}
	
	if((document.getElementById('sPhone_areaCode') && document.form1.sPhone_areaCode)&&
	(document.getElementById('sPhone_exchange') && document.form1.sPhone_exchange)&&
	(document.getElementById('sPhone_number') && document.form1.sPhone_number)){
		if (document.form1.sPhone_areaCode.value !='' && document.form1.sPhone_exchange.value !='' && document.form1.sPhone_number.value !='') {
			if (fieldName == 'sPhone_areaCode') {
				if(value != '') {
					out += "\n* Please enter a correct area code.";
				}
			} else if(fieldName == 'sPhone_exchange') {
				if(value != '') {
					out += "\n* Please enter a correct exchange.";
				}
			} else if(fieldName == 'sPhone_number') {
				if(value != '') {
					out += "\n* Please enter a correct number.";
				}
			}
		}
	}
	
	if (out == '') {
		if(fieldName == 'sPhoneDistance') {
			out = "\n*Phone number is out of range with address supplied.  Please correct and resubmit.";
		}
	}*/
	//alert('asdfasdf');
	//return out;
}

function DOBValidate(){				
	//alert('DOB Validate');
			
	var val = document.form1.elements['iBirthMonth'].value;
		val += '/'+document.form1.elements['iBirthDay'].value;
		val += '/'+document.form1.elements['iBirthYear'].value;
				
	//alert(coRegPopup.send('/nibbles2/valid.php?field=dob&value='+val,''));
				
	if(coRegPopup.send('/nibbles2/libs/valid.php?field=dob&value='+val,'')=='0'){
		fieldError('iBirthDay','0');
		fieldError('iBirthMonth','0');
		fieldError('iBirthYear','0');
	} else {
		fieldError('iBirthDay','1');
		fieldError('iBirthMonth','1');
		fieldError('iBirthYear','1');
	}
}

function PhoneValidate(){
				
	//alert('Phone Validate');
			
	var val = document.form1.elements['sPhone_areaCode'].value;
		val += '-'+document.form1.elements['sPhone_exchange'].value;
		val += '-'+document.form1.elements['sPhone_number'].value;

	var src = document.form1.hiddenSrc.value;

	if (coRegPopup.send('/nibbles2/libs/valid.php?field=phone&value='+val+'&src='+src,'')=='0') {
		fieldError('sPhone_areaCode','0');
		fieldError('sPhone_exchange','0');
		fieldError('sPhone_number','0');
		//fieldError('sPhoneExtension','0');
	} else { // valid
		fieldError('sPhone_areaCode','1');
		fieldError('sPhone_exchange','1');
		fieldError('sPhone_number','1');
		//fieldError('sPhoneExtension','1');
	}
}

function UserFormErrors(){
	if(document.getElementById('sFirst') && document.form1.sFirst){
		if(document.form1.sFirst.value == ''){
			fieldError('sFirst','0');
		} else {
			fieldError('sFirst',coRegPopup.send('/nibbles2/libs/valid.php?field=first&value='+document.form1.sFirst.value,''));
		}
	}
	
	
	if(document.getElementById('sLast') && document.form1.sLast){
		if(document.form1.sLast.value == ''){
			fieldError('sLast','0');
		} else {
			fieldError('sLast',coRegPopup.send('/nibbles2/libs/valid.php?field=last&value='+document.form1.sLast.value,''));
		}
	}
	
	if(document.getElementById('sEmail') && document.form1.sEmail){
		if(document.form1.sEmail.value == ''){
			fieldError('sEmail','0');
		} else {
			fieldError('sEmail',coRegPopup.send('/nibbles2/libs/valid.php?field=email&value='+document.form1.sEmail.value,''));
		}
	}
	
	if(document.getElementById('sZip') && document.form1.sZip){
		if(document.form1.sZip.value == ''){
			fieldError('sZip','0');
		} else {
			fieldError('sZip',coRegPopup.send('/nibbles2/libs/valid.php?field=zip&value='+document.form1.sZip.value,''));
			if(document.getElementById('sState') && document.form1.sState){
				asdf = coRegPopup.send('/nibbles2/libs/valid.php?field=zip2State&value='+document.form1.sZip.value,''); 
				if(asdf != '0'){
					document.form1.sState.value = asdf; fieldError('sZip', '1');
				} else {
					fieldError('sZip', '0');
				}
			}
		}
	}
	
	if((document.getElementById('sAddress') && document.form1.sAddress)&&
	(document.getElementById('sCity') && document.form1.sCity)&&
	(document.getElementById('sState') && document.form1.sState)&&
	(document.getElementById('sZip') && document.form1.sZip)){
		//alert('address validate');
		AddressValidate();		
	}
	
	
	if((document.getElementById('sPhone_areaCode') && document.form1.sPhone_areaCode)&&
	(document.getElementById('sPhone_exchange') && document.form1.sPhone_exchange)&&
	(document.getElementById('sPhone_number') && document.form1.sPhone_number)){
		//alert('phone validate');
		PhoneValidate();
	}
	

	if((document.getElementById('iBirthMonth') && document.form1.iBirthMonth)&&
	(document.getElementById('iBirthDay') && document.form1.iBirthDay)&&
	(document.getElementById('iBirthYear') && document.form1.iBirthYear)){
		//alert('dob validate');
		DOBValidate();
	}
	
	if(document.getElementById('sGender') && document.form1.sGender){
		if((!document.form1.sGender[0].checked)&&(!document.form1.sGender[1].checked)){
			if(!in_array(FieldErrors, 'sGender')){
				FieldErrors.push('sGender');
				document.form1.sGender[0].style.backgroundColor = '#FFFF00';
				document.form1.sGender[1].style.backgroundColor = '#FFFF00';
			}
		} else {
			if(in_array(FieldErrors, 'sGender')){
				FieldErrors = array_remove(FieldErrors,'sGender');
				document.form1.sGender[0].style.backgroundColor = '#FFFFFF';
				document.form1.sGender[1].style.backgroundColor = '#FFFFFF';
			}
		}
	}
	
	if (in_array(FieldErrors, 'sPhoneDistance')) {
		FieldErrors = array_remove(FieldErrors,'sPhoneDistance');
		document.form1.sPhone_areaCode.style.backgroundColor = '#FFFFFF';
		document.form1.sPhone_exchange.style.backgroundColor = '#FFFFFF';
		document.form1.sPhone_number.style.backgroundColor = '#FFFFFF';
	}
	if (FieldErrors.length == 0) {
		if (document.getElementById('sZip') && document.form1.sZip && document.getElementById('sPhone_areaCode') && document.form1.sPhone_areaCode && document.getElementById('sPhone_exchange') && document.form1.sPhone_exchange && document.getElementById('sPhone_number') && document.form1.sPhone_number) {
			var temp = document.form1.sZip.value+'@'+document.form1.sPhone_areaCode.value;
				temp += '-'+document.form1.sPhone_exchange.value;
				temp += '-'+document.form1.sPhone_number.value;
				
			var src = document.form1.hiddenSrc.value;

			if(coRegPopup.send('/nibbles2/libs/valid.php?field=phoneDistance&value='+temp+'&src='+src,'')=='0') {
				// invalid
				if(!in_array(FieldErrors, 'sPhoneDistance')) {
					FieldErrors.push('sPhoneDistance');
					document.form1.sPhone_areaCode.style.backgroundColor = '#FFFF00';
					document.form1.sPhone_exchange.style.backgroundColor = '#FFFF00';
					document.form1.sPhone_number.style.backgroundColor = '#FFFF00';
				}
			}
		}
	}
}

function Set_Cookie( name, value, expires, path, domain, secure ) 
{
	// set time, it's in milliseconds
	var today = new Date();
	today.setTime( today.getTime() );
	
	/*
	if the expires variable is set, make the correct 
	expires time, the current script below will set 
	it for x number of days, to make it for hours, 
	delete * 24, for minutes, delete * 60 * 24
	*/
	if ( expires ){
		expires = expires * 1000 * 60 * 60 * 24;
	}
	var expires_date = new Date( today.getTime() + (expires) );
	
	document.cookie = name + "=" +escape( value ) +
	( ( expires ) ? ";expires=" + expires_date.toGMTString() : "" ) + 
	( ( path ) ? ";path=" + path : "" ) + 
	( ( domain ) ? ";domain=" + domain : "" ) +
	( ( secure ) ? ";secure" : "" );
}

function Get_Cookie( name ) {
		
	var start = document.cookie.indexOf( name + "=" );
	var len = start + name.length + 1;
	if ( ( !start ) &&	( name != document.cookie.substring( 0, name.length ) ) )	{
		return null;
	}
	if ( start == -1 ) return null;
	var end = document.cookie.indexOf( ";", len );
	if ( end == -1 ) end = document.cookie.length;
	return unescape( document.cookie.substring( len, end ) );
}
	

function Delete_Cookie( name, path, domain ) {
	if ( Get_Cookie( name ) ) 
		document.cookie = name + "=" +
		( ( path ) ? ";path=" + path : "") +
		( ( domain ) ? ";domain=" + domain : "" ) +
		";expires=Thu, 01-Jan-1970 00:00:01 GMT";
}

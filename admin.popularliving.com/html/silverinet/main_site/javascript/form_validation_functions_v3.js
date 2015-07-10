/*
quirksmode.org based library 

version 1.0
created 2005-12-18 by gc
last modified 2006-02-08 by gc
*/

//------------------------------------------------------------------------------------------------------------------------------------------------

// VARS FOR YOU TO SET
// messages
var required_field_error_message = 'This field is required.';
var invalid_numeric_error_message = 'The value of this field must be a number.';
var invalid_numeric_range_message = 'The value of this field must be';
var invalid_email_error_message = 'This is not a valid email address.';
var invalid_form_message = 'This form cannot be submitted.  Please check the page for error messages.';

// form element names
var input_error_td = 'error_td';					// this is the name of the hidden element that indicates if errors should change the appearance of a <td>. The value is 1
var input_required_fields = 'required_fields';		// this is the name of the hidden element that indicates if the form contains required fields. The value of the input is a comma separtaed list with no spaces (value="item_1,item_2,item_3")
var input_numeric_fields = 'numeric_fields';		// this is the name of the hidden element that indicates if the form contains fields where the value must be number. The value of the input is a comma separtaed list with no spaces (value="item_1,item_2,item_3")
var input_email_fields = 'email_fields';			// this is the name of the hidden element that indicates if the form contains fields with email adresses that need to be validated. The value of the input is a comma separtaed list with no spaces (value="item_1,item_2,item_3")
var input_numeric_range = 'numeric_range';		// this is the name of the hidden element used to set a min and max value for a given numeric field. Numeric_range is an array, one element for each field to be tested. The value of each element id a comma separated list: element name, min value, max value (ie "field_name,0,10" ). That would test the input named field_name for a value equal to or greater than zero. And less than or equal to 10. Use the string 'none' to indicate that there is no min or max value

// css vars
var css_text_error = 'formError';		// required
var css_td_error = 'formErrorTdBgColor';	// optional: use if you want the <td> to change appearance. Use in conjunction with 'input_error_td'



//==================================================
// DO NOT EDIT BELOW THIS LINE
//==================================================

// this var tests the capabilitities of the browsers
var W3CDOM_validate_forms = (document.getElementsByTagName && document.createElement);		// the name of the var is a bit long due to the fact that 'W3CDOM' name is used in other scripts

// static vars that get values in the functions
var validForm;
var firstError;
var errorstring;
var change_td_color = false;		// should the color of the table cell change

//------------------------------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------------------------------

function validateForm(the_form) {
	validForm = true;		// assume the form is valid
	firstError = null;		// this will be the first element in the form that in not valid, if any. This var will allow the script to set the focus on that element
	errorstring = '';		// a message used for non-W3C DOM browsers 
	
	// should errors trigger a change in td color?
	// if the input_error_td form element is present 
	if (the_form[input_error_td]) {
		change_td_color = true;	
	}

	
	// validate required fields
	// if there are required fields
	if (the_form[input_required_fields]) {
		validateFields_Required(the_form);
	}

	// validate numeric fields
	// if there are numeric fields
	if (the_form[input_numeric_fields]) {
		validateFields_Numeric(the_form)
	}
	
	// validate numeric range values
	var numeric_range_name = input_numeric_range + '[0]';	// the format of the first numeric range input name is the value of input_numeric_range plus [0], "numeric_range[0]". Remeber, this is a string, not really an array
	if (the_form[numeric_range_name]) {
		validateFields_IsInNumericRange(the_form);
	}
	
	
	// validate email fields
	// if there are email fields
	var email_fields = new Array();
	if (the_form[input_email_fields]) {
		// the form passed can have a hidden field with a comma separated list of email fields to validate
		// split the value on the commas and pass that array
		email_fields  = the_form[input_email_fields].value.split(',');
		validateFields_Email( the_form, email_fields );
	} else if (the_form.email) {
		// most forms just have one email field to validate. Look for an element named 'email'
		email_fields[0] = the_form.email.name;
		validateFields_Email( the_form, email_fields );
	}
	
	
	
	// if the browser is old, just alert a string
	if (!W3CDOM_validate_forms) {
		alert(errorstring);
	}
	/*
	// this sets the focus of a form element, causing the cursor to move to the element. It is a bit buggy
	if (firstError) {
		//firstError.focus();
	}
	*/
	
	// alert a general message to indicate the form cannot be submitted
	if (!validForm) {
		alert(invalid_form_message);
	}

	// return true or false
	return validForm;
}

//------------------------------------------------------------------------------------------------------------------------------------------------

function validateFields_Required(the_form) {
	var required_fields = new Array();
	// the form passed needs to have a hidden field with a comma separated list of fields to validate e.g. 'name,address,email'
	required_fields = the_form[input_required_fields].value.split(',');
	
	var limit = required_fields.length;
	// loop through the required fields
	for (var x=0; x<limit; x++) {
		var element = the_form[required_fields[x]];		// get an easy var name for the form element
		var element_valid = true;						// assume the element will validate
		//alert('the element is ' + element + ' the type is ' + element.type + ' name = ' +element.name);
		//alert('the type is ' + element.type);
		switch (element.type) {
			case 'text':
			case 'textarea':
			case 'hidden':
			case 'password':
				element_valid = hasValue_Text(element);
				break;
			case 'select-one':
				element_valid = hasValue_Select(element);
				break;
			case 'checkbox':
				element_valid = hasValue_Checkbox(element);
				break;
			case 'radio':
				// some function
				break;	
		}
		if (!element_valid) {
			showErrors(element, required_field_error_message);
		}
	}
}

//------------------------------------------------------------------------------------------------------------------------------------------------

function validateFields_Numeric(the_form) {
	// the form passed can have a hidden field with a comma separated list of numeric fields to validate
	// this function does not assume the numeric field is required. If it is required, the form element name should be in the required hidden field
	var numeric_fields = new Array();
	numeric_fields = the_form.numeric_fields.value.split(',');
	var limit = numeric_fields.length;
	// loop through the numeric fields
	for (x=0; x<limit; x++) {
		var element = the_form[numeric_fields[x]];		// get an easy var name for the form element
		// if the user entered a value (remeber, this test does not assume the field is required)
		if (hasValue_Text(element)) {
			if (!isValidNumeric(element) ) {
				showErrors(element, invalid_numeric_error_message);
			}
		}
	}
}

//------------------------------------------------------------------------------------------------------------------------------------------------

function validateFields_IsInNumericRange(the_form) {
	// the form passed can have hidden fields each with a comma separated list of numeric fields to validate if the value is within a given range
	var numbers = new Array();
	var loop = true;
	var x=0;
	while (loop == true) {
		// this loop assumes all the numeric range elements are numbered in order starting with 0
		var numeric_range_field = input_numeric_range + '[' + x + ']';	// the format of the numeric range input name is the value of input_numeric_range plus [0], "numeric_range[0]". Remeber, this is a string, not really an array
		// if the form element exists
		if (the_form[numeric_range_field]) {
			x++;
			var range_element = the_form[numeric_range_field];
			//alert(range_element);
			
			var params = range_element.value.split(',');	// split the values of the numeric range element
			//alert('param length = ' + params.length);
			
			var numeric_field = the_form[ params[0] ];
			// check to see if the value of the field is numeric
			if (isValidNumeric(numeric_field)) {
				var valid = true;
				var minimim_error = false;
				// set the message for this element
				var message = invalid_numeric_range_message;
				
				// see if there is a minimum value
				if (params[1] != 'none') {
					minimim_error = true;
					message = message + ' greater or equal to ' + params[1];
					//alert('numeric_field.value = ' + numeric_field.value);
					//alert(' params 1 = ' + params[1]);
					if (parseFloat(numeric_field.value) < parseFloat(params[1])) {
						//alert(numeric_field.value + ' is less than ' + params[1]);
						valid = false;
					} 
				}
				
				// see if there is a maximum value
				if (params[2] != 'none') {
					if (minimim_error == true) {
						message = message + ' and';
					}
					message = message + ' less than or equal to ' + params[2];
					//alert('numeric_field.value = ' + numeric_field.value);
					//alert(' params 2 = ' + params[2]);
					if (parseFloat(numeric_field.value) > parseFloat(params[2])) {
						//alert(numeric_field.value + ' is greater than ' + params[2]);
						valid = false;
					}
				}
				
				if (valid == false) {
					showErrors(numeric_field, message);
				}
			}
		} else {
			// the numeric range element does not exist. kill the loop
			loop = false;
		}
	}
	
}

//------------------------------------------------------------------------------------------------------------------------------------------------

function validateFields_Email( the_form, email_fields ) {
	var limit = email_fields.length;
	// loop through the email fields
	for (x=0; x<limit; x++) {
		var element = the_form[email_fields[x]];		// get an easy var name for the form element
		// if the user entered a value 
		// (this does not assume the email field is required. If it was, the form element name should be in the required hidden field)
		if (hasValue_Text(element)) {
			if (!isValidEmail(element) ) {
				showErrors(element, invalid_email_error_message);
			}
		}
	}
}

//------------------------------------------------------------------------------------------------------------------------------------------------

function showErrors(obj, message) {
	writeError(obj, message);	// this is the default. We always write the message, for w3cdom enabled browsers
	if (change_td_color) {
		changeBgColor(obj, true);	
	}
}

//------------------------------------------------------------------------------------------------------------------------------------------------

function writeError(obj, message) {
	validForm = false;	// this form is not valid.Do not submit
	
	if (obj.hasError) {
		return;		// if this is already shown as an error, skip the rest
	}
	
	// if this is a good browser
	if (W3CDOM_validate_forms) {
		// set the css class of the form element	I SEE NO NEED FOR THIS
		//obj.className += ' formError';
		// set the onchange handler of the form element
		obj.onchange = removeError;
		// create a span node that will hold the error message that gets displayed next to each form element
		var sp = document.createElement('span');
		// set the css class of the form element
		sp.className = css_text_error;
		// append the message text to the text node
		sp.appendChild(document.createTextNode(message));
		// add the span node as the last child of the parent of the form element
		obj.parentNode.appendChild(sp);
		//obj.parentNode.insertBefore(sp);
		// set a property that lets us test if the form element already has an error showing.
		// Prevents multiple errors from being appended.
		// Needs to be the span node that was added so that node can be removed later if the user fixes the error.
		obj.hasError = sp;
	} else {
		// if this is not a good browser, add to the error string that will be alerted to the user. It might not be too pretty depending on the naming used, but, come on. Get a real browser :)
		errorstring += obj.name + ': ' + message + '\n';
		obj.hasError = true;
	}

	if (!firstError) {
		firstError = obj;
	}
}

//------------------------------------------------------------------------------------------------------------------------------------------------

function changeBgColor(obj, state) {
	// not sure if this will cause problems for table cells that already have a class value
	// the state param tracks if we are showing an error or turning it off
	if (state == true) {
		obj.parentNode.className = css_td_error;
	} else {
		obj.parentNode.className = '';
	}
}

//------------------------------------------------------------------------------------------------------------------------------------------------

function removeError() {
	//this.className = this.className.substring(0,this.className.lastIndexOf(' '));
	this.parentNode.removeChild(this.hasError);		// default. Removes the error text node we added
	if (change_td_color) {
		changeBgColor(this, false);	
	}
	
	this.hasError = null;
	this.onchange = null;
}

//------------------------------------------------------------------------------------------------------------------------------------------------

function hasValue_Text(element) {
	// test input types text, textarea, hidden for a value
	if (element.value) {
		return true;	
	}
	return false;
}

//------------------------------------------------------------------------------------------------------------------------------------------------

function hasValue_Checkbox(element) {
	// test input type checkbox for a value
	if (element.checked) {
		return true;	
	}
	return false;
}

//------------------------------------------------------------------------------------------------------------------------------------------------

function hasValue_Select(element) {
	//alert(element.options[element.selectedIndex]);
	var valid = true;
	if (element.selectedIndex == 0) {
		valid = false;
	}
	
	return valid;
}

//------------------------------------------------------------------------------------------------------------------------------------------------

function isValidEmail(element) {
	var str = element.value;
	var at_sign = "@";
	var dot = ".";
	var at_sign_index=str.indexOf(at_sign);
	var str_len=str.length;
	var dot_index=str.indexOf(dot);

	// check for presence of '@' symbol
	if ((at_sign_index == -1) || (at_sign_index==0) || (at_sign_index==str_len)) {
		return false;
	}

	// check for presence of '.' symbol
	if ((dot_index==-1) || (dot_index==0) || (dot_index==str_len)) {
		return false;
	}

	if (str.indexOf(at_sign,(at_sign_index+1)) != -1 ) {
		return false;
	}

	// make sure there is at least one character between the @ and the .??
	if (str.substring(at_sign_index-1,at_sign_index)==dot || str.substring(at_sign_index+1,at_sign_index+2)==dot) {
		return false;
	}

	if (str.indexOf(dot,(at_sign_index+2)) == -1 ) {
		return false;
	}
	
	// make sure there are no whitespaces
	if (str.indexOf(" ") != -1) {
		return false;
	}
	return true;			
}

//------------------------------------------------------------------------------------------------------------------------------------------------

function isValidNumeric(element) {
	var str = element.value;
	var validChars = "0123456789,.";
	for (var y=0; y<str.length; y++) {
		if (validChars.indexOf(str.charAt(y),0) == -1) {
			return false;
		}
	}
	return true;
}

//------------------------------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------------------------------

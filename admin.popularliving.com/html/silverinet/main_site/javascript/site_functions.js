function validateForm_ContactUs(the_form) {
	
	var is_valid = true;	// we assume it is a valid form
	// custom code can be added to this fucntion if form specific actions are required
	
	// call the standard form validation function
	is_valid = validateForm(the_form);
	
	return is_valid;

}

//======================================================

function openWin(url)
{
	aWindow = window.open(url,"thewindow",'toolbar=0,location=0,directories=0,status=0,menubar=0, width=400, height=400, scrollbars, resizable');
	aWindow.focus();
}




//======================================================
//	START AFFILIATE FORM
//======================================================

function validateForm_AffiliateSignUp(the_form) {
	
	var is_valid = true;	// we assume it is a valid form
	// custom code can be added to this fucntion if form specific actions are required
	
	// call the standard form validation function
	is_valid = validateForm(the_form);
	
	return is_valid;

}

//======================================================

function init_AffiliateSignUp() {
	if (document.getElementById) {
		//refer_other_row_node = document.getElementById(refer_other_row_id);
		//refer_other_row_node.style.display = 'none';
		
		refer_other_label_node = document.getElementById(refer_other_label_id);
		refer_other_label_node.style.display = 'none';
		
		refer_other_input_node = document.getElementById(refer_other_input_id);
		refer_other_input_node.style.display = 'none';
	}
}

//======================================================

function showOtherOption(element) {
	//alert(element);
	if (element.options[element.selectedIndex].value == 'other') {
		//alert('table-row');
		//refer_other_row_node.style.display = 'table-row';
		refer_other_label_node.style.display = 'inline';
		refer_other_input_node.style.display = 'inline';
		
		refer_other_input_node.name = alt_name;
		refer_other_input_node.focus();
		refer_other_input_node.select();
	} else {
		//alert('none');
		//refer_other_row_node.style.display = 'none';
		refer_other_label_node.style.display = 'none';
		refer_other_input_node.style.display = 'none';
		
		refer_other_input_node.name = orig_name;
	}
}

//======================================================

//======================================================
//	END AFFILIATE FORM
//======================================================

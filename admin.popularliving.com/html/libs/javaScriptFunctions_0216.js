var sa = new Array('222','333','444', '555', '666', '777', '888', '999');
	
function getSelectedRadio(buttonGroup) {
   // returns the array number of the selected radio button or -1 if no button is selected
   if (buttonGroup[0]) { // if the button group is an array (one button is not an array)
      for (var i=0; i<buttonGroup.length; i++) {
         if (buttonGroup[i].checked) {
            return i;
         }
      }
   } else {
      if (buttonGroup.checked) { return 0; } // if the one button is checked, return zero
   }
   // if we get to this point, no radio button is selected
   return -1;
} // Ends the "getSelectedRadio" function

function getSelectedRadioValue(buttonGroup) {
   // returns the value of the selected radio button or "" if no button is selected
   var i = getSelectedRadio(buttonGroup);
   if (i == -1) {
      return "";
   } else {
      if (buttonGroup[i]) { // Make sure the button group is an array (not just one button)
         return buttonGroup[i].value;
      } else { // The button group is just the one button, and it is checked
         return buttonGroup.value;
      }
   }
} // Ends the "getSelectedRadioValue" function




function isNumber(num, digits) {
	var reg = "^[0-9]{" + digits + "}$";
	if (digits !=0 ) {
		if (!num.match(reg)) {
			return false;
		}
	} else if ( !num.match("^[0-9]*$")) {
		return false;
	}
	return true;
}

function isNumberExtraChars(num, extraChars) {
	var reg = "^[0-9" + extraChars + "]+$";
	
	if (!num.match(reg)) {
		return false;
	}
	
	return true;
}


function validateAreaCode(areaCode) {
	
	if ( areaCode.match("^[01]{1}") || !areaCode.match("^[0-9]{3}$")) {
		return false;
	}
	
	//Test 3 digit codes against areacode
	for (i=0; i<sa.length; i++) {
		
		if (areaCode == sa[i] ) {
			return false;
		}
	}
	return true;

}

function validateExt(ext) {
	
	if ( ext.match("^[01]{1}") || !ext.match("^[0-9]{3}$")) {
		return false;
	}
	
	//Test 3 digit codes against areacode
	/*for (i=0; i<sa.length; i++) {
		
		if (ext == sa[i] ) {
			return false;
		}
	}*/
	
	return true;
	

}

function validatePhone(phoneNo) {

	if ( phoneNo.length != 10 && phoneNo.length != 12) {
	
		return false;
	} else {
		if (phoneNo.length == 10 && phoneNo.match("^[0-9]{10}$")) {
			
			var areaCode = phoneNo.substring(0,2);
			var ext = phoneNo.substring(3,5);
			if (!(validateAreaCode(areaCode) && validateExt(ext))){
				return false;
			}
			
		} else if (phoneNo.length == 12 && phoneNo.match("^[0-9]{3}[-]{1}[0-9]{3}[-]{1}[0-9]{4}$")) {
			
			var areaCode = phoneNo.substring(0,3);
			var ext = phoneNo.substring(4,7);
			
			if (!(validateAreaCode(areaCode) && validateExt(ext))){
				return false;
			}
		} else {
			return false;
		}	
	}
	
	return true;
}



function validateZipCode(zipCode) {
	if (!zipCode.match("^[0-9]{5}$")) {
		return false;
	} 
	return true;
	
}

function validateEmail(email) {
		validRegExp = /^[^@][A-Za-z0-9\$\._-]+@[A-Za-z0-9-]+[\.]{1}[A-Za-z0-9\.-]+[A-Za-z]$/i;
 
   		// search email text for regular exp matches
    	if (email.search(validRegExp) == -1) 
   		{     
      		return false;
    	} 
    	return true;
}


function validateDate(valDate) {
		
   		// search email text for regular exp matches
    	if (!valDate.match("^[0-9]{1,2}[/-]{1}[0-9]{1,2}[/-]{1}[0-9]{4}$")) {   		
      		return false;
    	} 
    	return true;

}
/*
function calculateAge(birthDate) {

var age = 0;
if (birthDate.match("^[0-9]{1,2}[/-]{1}[0-9]{1,2}[/-]{1}[0-9]{4}$")) {
	if (birthDate.match("^[0-9]{1,2}[/]{1}[0-9]{1,2}[/]{1}[0-9]{4}$")) {
		var bdateArray = birthDate.split("/");
	} else {
		var bdateArray = birthDate.split("-");
	}

	today=new Date();
	var pastdate=new Date(bdateArray[2], bdateArray[0]-1, bdateArray[1]);

	yearspast=today.getFullYear()-yr-1;
	tail=(today.getMonth()>mon-1 || today.getMonth()==mon-1 && today.getDate()>=day)? 1 : 0;
	pastdate.setFullYear(today.getFullYear());
	pastdate2=new Date(today.getFullYear()-1, mon-1, day);
	tail=(tail==1)? tail+Math.floor((today.getTime()-pastdate.getTime())/(finalunit)*decimals)/decimals : Math.floor((today.getTime()-pastdate2.getTime())/(finalunit)*decimals)/decimals;
	age = yearspast;
	//document.write(yearspast+tail+" "+countunit)
} else {
	return false;
}

return age;

}
*/


function getMonthLength(month,year,julianFlag)
{
   var ml;
   if(month==1 || month==3 || month==5 || month==7 || month==8 || month==10||month==12)
      {ml = 31;}
   else {
       if(month==2) {
          ml = 28;
          if(!(year%4) && (julianFlag==1 || year%100 || !(year%400)))
             ml++;
       }
       else
          {ml = 30;}
   }
   return ml;    
}


function calculateAge(birthDate) {

var age = 0;
if (birthDate.match("^[0-9]{1,2}[/-]{1}[0-9]{1,2}[/-]{1}[0-9]{4}$")) {
	if (birthDate.match("^[0-9]{1,2}[/]{1}[0-9]{1,2}[/]{1}[0-9]{4}$")) {
		var bdateArray = birthDate.split("/");
	} else {
		var bdateArray = birthDate.split("-");
	}
	
	today=new Date();
	
MNames=new Array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep",
      "Oct","Nov","Dec");

   
   var yd = today.getFullYear();
   var md = today.getMonth();
   var dd = today.getDate();

   var yb = bdateArray[2];
   var mb = bdateArray[0]-1;
   var db = bdateArray[1];
   
   // Month length 0->use calendar length
 /*  var mLength =parseInt(
      form.monthLength.options[form.monthLength.selectedIndex].value);
   // 0 if Gregorian, 1 is Julian
   var isJulian =
      form.isJulian.options[form.isJulian.selectedIndex].value;*/
mLength=0;
isJulian = 0;
   
   var ma=0;
   var ya=0;

   var da = dd-db;
   
   // This is the all-important day borrowing code.
   if(da<0)
   {
      md--;
      // Borrow months from the year if necesssary.
      if(md<1)
      {
	 yd--;
	 // Determine no. of months in year
	 if(mLength)
	    {md=md+parseInt(365/mLength);}
	 else
	    {md=md+12;}
      }
      if(mLength==0) // Use real month length if no fixed
      {              // length is indicated - note that we add a leap day if necessary.
         ml=getMonthLength(md,yd,isJulian);
	 da=da+ml;
      } 
      // For this case, everything works like it did in elementary school.
      else
	 {da+=mLength;} // Use fixed month length
   }

   ma = md - mb;
  
   // Month borrowing code - borrows months from years.
   if(ma<0)
   {
      yd--;
      if(mLength!=0)
	 {ma=ma+parseInt(365/mLength);}
      else
	 {ma=ma+12;}
   }

   ya = yd - yb;

   return ya;
   //form.da.value = da;

//   form.ma.value = ma;

  // form.ya.value = ya;

	}
	 else {
		return false;
	}
	}
	
	
function validateSocialSecurity(ss) {

	if (ss.length == 9 || ss.length == 11) {
		if (ss.length == 9 && !ss.match("^[0-9]{9}$")) {
			return false;
		} else if (ss.length == 11 && !ss.match("^[0-9]{3}[-]{1}[0-9]{2}[-]{1}[0-9]{4}$")) {
			return false;
		}
	} else {
		return false;
	}
	return true;

}



function validateCreditCard(cardType, cardNumber)
{
	var cdReg="^\\d{15,16}$";
	
	if ( !cardNumber.match(cdReg) )
	{
		alert ("Error in Card number");
		return -1;
	}

	var ckCdLen = cardNumber.length;
	//var cardType = "";
	//var ckCdS = cardNumber.substr(0,1);
		
	if (cardType.toLowerCase() == "visa" && !(isVisa(cardNumber))) {
		//alert('visa invalid');
		return false;
	}
	
	if ((cardType.toLowerCase() == "master") && !(isMasterCard(cardNumber))) {
		//alert('master invalid');
		return false;
	} 
			
	if ( ( cardType.toLowerCase() == "amex" ) && !(isAmericanExpress(cardNumber))) {
		//alert('amex invalid');
		return false;
	}
	
	if ((cardType.toLowerCase() == "discover") && !(isDiscover(cardNumber))) {
		//alert('discover invalid');
		return false;
	}
	
	
	/*
	if ((ckCdLen == "15") && (ckCdS == "3"))
		{cardType = "Amex";}
	if ((ckCdLen == "16") && (ckCdS == "4"))
		{cardType = "Visa";}
	if ((ckCdLen == "16")  && (ckCdS == "5"))
		{cardType = "Master";}
	if ((ckCdLen == "16") && (ckCdS == "6"))
		{cardType = "Discover";}
	*/
	
	var cdN1 = new Number;
	var cdN2 = new Number;

	for (i = 0; i < ckCdLen; i ++)
	{
		if (i % 2 != 0 ) {
			cdN1 = eval(cardNumber.substr(i,1)); 
		}

		if (i % 2 == 0) {
			cdN1 = eval(cardNumber.substr(i,1)) * 2;
		}

		if (cdN1 > 9 ) {
			cdN2 += cdN1 - 9;
		} else {
				cdN2 += cdN1;
			
		}

	}

	mod10 = cdN2 % 10;
	
	if (mod10 == 0){ 
		return true;
	} else {
		return false;
	}

	//alert(cdN2)
	//alert('card type = ' + cardType + '\ncard number =  ' + cardNumber +    '\nmod10
//ckeck= ' + mod10 );

}





function validateCreditCardOld(cardType, cardNumber) {
	
	if ((cardType.toLowerCase() == "visa") && (!isVisa(cardNumber))) {
		return false;
	}
	if ((cardType.toLowerCase() == "mastercard") && (!isMasterCard(cardNumber))) {
		return false;
	} 
	if ( ( (cardType.toLowerCase() == "amex") || (cardType.toLowerCase() == "american express") ) && (!isAmericanExpress(cardNumber))) {
		return false;
	}
	
	if ((cardType.toLowerCase() == "discover") && (!isDiscover(cardNumber))) {
		return false;
	}
	//if ((cardType.toLowerCase() == "diner's club" || cardType.toLowerCase == "carteblanche") && (!isDinersClub(cardNumber)))
		//doesMatch = false;
	return true;
	
}




/// ***********   Following functions are called by credit card validation function ****************/

function isVisa(cc)
{
  if (((cc.length == 16) || (cc.length == 13)) &&
      (cc.substring(0,1) == 4)) {  
      return true;     
      } else {
     
  return false;
      }
}  // END FUNCTION isVisa()



function isMasterCard(cc){
  firstdig = cc.substring(0,1);
  seconddig = cc.substring(1,2);
  if ((cc.length == 16) && (firstdig == 5) &&
      ((seconddig >= 1) && (seconddig <= 5))){
      
    return true;
      } else {
      
  	return false;
      }
} // END FUNCTION isMasterCard()



function isAmericanExpress(cc)
{

  firstdig = cc.substring(0,1);
  seconddig = cc.substring(1,2);
  if ((cc.length == 15) && (firstdig == 3) && (seconddig == 4 || seconddig == 7) ) {
     
    return true;
      } else {
    
  	return false;
      }
} // END FUNCTION isAmericanExpress()

function isDinersClub(cc)
{
  firstdig = cc.substring(0,1);
  seconddig = cc.substring(1,2);
  if ((cc.length == 14) && (firstdig == 3) &&
      ((seconddig == 0) || (seconddig == 6) || (seconddig == 8)))
    return true;
  return false;
}

function isDiscover(cc)
{
  first4digs = cc.substring(0,4);
  if ((cc.length == 16) && (first4digs == "6011"))
    return true;
 return false;

} // END FUNCTION isDiscover()




////////////////////// check date validation

var minYear=1900;
var maxYear=2100;

function isInteger(s){
	var i;
    for (i = 0; i < s.length; i++){   
        // Check that current character is number.
        var c = s.charAt(i);
        if (((c < "0") || (c > "9"))) return false;
    }
    // All characters are numbers.
    return true;
}

function stripCharsInBag(s, bag){
	var i;
    var returnString = "";
    // Search through string's characters one by one.
    // If character is not in bag, append to returnString.
    for (i = 0; i < s.length; i++){   
        var c = s.charAt(i);
        if (bag.indexOf(c) == -1) returnString += c;
    }
    return returnString;
}

function daysInFebruary (year){
	// February has 29 days in any year evenly divisible by four,
    // EXCEPT for centurial years which are not also divisible by 400.
    return (((year % 4 == 0) && ( (!(year % 100 == 0)) || (year % 400 == 0))) ? 29 : 28 );
}
function DaysArray(n) {
	for (var i = 1; i <= n; i++) {
		this[i] = 31
		if (i==4 || i==6 || i==9 || i==11) {this[i] = 30}
		if (i==2) {this[i] = 29}
   } 
   return this
}

function isValidDate(dtStr){
	var daysInMonth = DaysArray(12)
	
	if (dtStr.indexOf('/')>=0) {
		var dtCh= "/";
	} else if (dtStr.indexOf('-')>=0) {
		var dtCh= "-";
	} else {
		return false;
	}
	
	var pos1=dtStr.indexOf(dtCh)
	var pos2=dtStr.indexOf(dtCh,pos1+1)
	var strMonth=dtStr.substring(0,pos1)
	var strDay=dtStr.substring(pos1+1,pos2)
	var strYear=dtStr.substring(pos2+1)
	strYr=strYear
	if (strDay.charAt(0)=="0" && strDay.length>1) strDay=strDay.substring(1)
	if (strMonth.charAt(0)=="0" && strMonth.length>1) strMonth=strMonth.substring(1)
	for (var i = 1; i <= 3; i++) {
		if (strYr.charAt(0)=="0" && strYr.length>1) strYr=strYr.substring(1)
	}
	month=parseInt(strMonth)
	day=parseInt(strDay)
	year=parseInt(strYr)
	if (pos1==-1 || pos2==-1){
		//alert("The date format should be : dd/mm/yyyy")
		return false
	}
	if (strMonth.length<1 || month<1 || month>12){
		//alert("Please enter a valid month")
		return false
	}
	if (strDay.length<1 || day<1 || day>31 || (month==2 && day>daysInFebruary(year)) || day > daysInMonth[month]){
		//alert("Please enter a valid day")
		return false
	}
	if (strYear.length != 4 || year==0 || year<minYear || year>maxYear){
		//alert("Please enter a valid 4 digit year between "+minYear+" and "+maxYear)
		return false
	}
	if (dtStr.indexOf(dtCh,pos2+1)!=-1 || isInteger(stripCharsInBag(dtStr, dtCh))==false){
		//alert("Please enter a valid date")
		return false
	}
return true
}

//////////////////// end date validation


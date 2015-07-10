//==============================================================
//showPopup() - Executes myFreePopup() based upon cookie value
//==============================================================
//f should = 1 or 0. If f=1, the script will call
//myFreePopup.  myFreePopup will show the user popup windows
//based on the value of some cookies.
//To set up this function, use the body tag, and script tags below.
//<body onunload="showPopup(f)">
//<script LANGUAGE="javascript">var f=1</script>
//You must also add the onClick="f=0" parameter to ALL links on the html
//page that is calling this script, or it will cause MyFreeExitPop to
//display interstitials instead of exit windows.
//<a href="default.htm" onClick="f=0">

function showPopup(f) {
	if ( f == 1 ) {
		myFreePopup();
	}
}

//************START OF POPUP EXIT CODE****************

function myFreePopup() {
	
	//define URL Vars
	
	//**** DO NOT CHANGE ANYTHING BETWEEN FOLLOWING COMMENTS ****/
	
	//*** START SPECIFY POPUP ARRAYS ***//

var popUpArray = new Array ("AMP9992","AMP9999","AMP9995","YFP9992","AMP9994","AMP9998","AMP9991","FSW9999");

var popUpUrlArray = new Array("http://www.popularliving.com/pops/AMP9992.php","http://www.popularliving.com/pops/AMP9999.php","http://www.popularliving.com/pops/AMP9995.php","http://www.popularliving.com/pops/YFP9992.php","http://www.popularliving.com/pops/AMP9994.php","http://www.popularliving.com/pops/AMP9998.php","http://www.popularliving.com/pops/AMP9991.php","http://www.popularliving.com/pops/FSW9999.php");

//*** END SPECIFY POPUP ARRAYS ***//


	//**** DO NOT CHANGE ANYTHING BEFORE PREVIOUS COMMENT LINE ****/
	
	var noPopUp="off"
	
	//define interval between popups
	var daysBetweenPopups=1;
	
	//define today's date
	var dateVar = new Date();
	var currentMonth = dateVar.getMonth();
	var currentDay   = dateVar.getDate();
	var currentYear  = dateVar.getFullYear();
	var currentDate = currentMonth + " " + currentDay + " " + currentYear;
	
	//------------ READ COOKIE SECTION ------------\\
	//var last_popped = GetCookie("MYFLastExitPopDate");
	var lastPopUpDate = getCookie("lastPopUpDate");
	
	if ( lastPopUpDate ) {
		//See if popup was within interval defined in "DaysBetweenPopus"
		//Extract Date From Cookie
		lastPopUpDateArray = lastPopUpDate.split(" ");
		lastPopUpMonth = lastPopUpDateArray[0];
		lastPopUpDay   = lastPopUpDateArray[1];
		lastPopUpYear  = lastPopUpDateArray[2];
		
		//Compare The Dates
		var dateDifference = compareDates (currentMonth, currentDay, lastPopUpMonth, lastPopUpDay );
		
		if (dateDifference >= daysBetweenPopups)
		{
		
			//Set Popup URL
			var ary = new Array();
			//var popUpDisplayedArray = getCookie("popUpDisplayedArray");
			var popUpDisplayedArray = getArrayCookie('popUpDisplayed', ary);
			
			var i=0;
			var j=0;
			tempPopUpArray = popUpArray;
			tempPopUpUrlArray = popUpUrlArray;
			
			for (i=0; i < tempPopUpArray.length;i++) {
				
				for (j=0; j < popUpDisplayedArray.length;j++) {
					
					// remove from popUpArray whatever popUp is already displayed
					if ( tempPopUpArray[i] == popUpDisplayedArray[j]) {
						// remove the element from the array
						tempPopUpArray.splice(i,1);
						tempPopUpUrlArray.splice(i,1);
					}
				}
			}
			
			// Use array with the popUps removed which are already displayed 
			// only if array length after removing elements is not zero
			// if it's zero, i.e. all the popUps displayed to the user, user array with all the elements
			
			if (tempPopUpArray.length > 0 ) {
				popUpArray	= tempPopUpArray;
				popUpUrlArray = tempPopUpUrlArray;											
			} else {
				// reset cookie value if all the popup already displayed to the user.
				setCookie("popupDisplayed", "", expires,"");
				popUpDisplayedArray = new Array();
			}
			
			// get no of elements remaining in the array
			var popUpArrayCount = popUpArray.length;
			var randomPopUpNo =   Math.round( popUpArrayCount * Math.random() );
			
		}
		
		if (dateDifference < daysBetweenPopups)
		{
			noPopUp="on"
		}
		
	} else {
		//if no cookie, get random popup from the array
		
		// get no of elements remaining in the array
		var popUpArrayCount = popUpArray.length;
		var randomPopUpNo =   Math.round( popUpArrayCount * Math.random() );
		
		var popUpDisplayedArray = new Array();
		
	}
	
	
	//------------ POPUP SECTION ------------\\
	//Set Expiration Date
	expires = new Date();
	expires.setTime(expires.getTime() + 3E11);
	
	
	//Popup URL if needed
	
	if (noPopUp == "off")
	{
	
		//Popup URL
		var popUpName = popUpArray[randomPopUpNo];
		var popUpUrl = popUpUrlArray[randomPopUpNo];
		
		window.open(popUpUrl, "FreeOffer","scrollbars=yes,resizeable=yes,height=490,width=600,directories=no,location=no,status=no,toolbar=no,menubar=yes,left=0,top=0,screenX=0,screenY=0)");
		
		//Record This Popup and This Visit In Cookies
		setCookie("lastPopUpDate", currentDate, expires,"");
		setCookie("lastVisit", currentDate, expires,"");
		
		var newArrayLength = popUpDisplayedArray.push(popUpName);
		setArrayCookie("popUpDisplayed", popUpDisplayedArray, expires)
		//setCookie("popUpDisplayed", popUpDisplayed, expires, "");
		
		setCookie("mfDomain", "myfree.com", expires,"");
	}
	else
	{
		//reset Date Last Visited Cookie
		setCookie("lastVisit", currentDate, expires,"");
	}
}

//************END OF POPUP EXIT CODE****************

//**************************************************************
//COOKIE FUNCTIONS from http://www.hidaho.com/cookies/cookie.txt
//**************************************************************
//
// "Internal" function to return the decoded value of a cookie
//
function getCookieVal (offset) {
	var endstr = document.cookie.indexOf (";", offset);
	if (endstr == -1)
	endstr = document.cookie.length;
	return unescape(document.cookie.substring(offset, endstr));
}

//
//  Function to return the value of the cookie specified by "name".
//    name - String object containing the cookie name.
//    returns - String object containing the cookie value, or null if
//      the cookie does not exist.
//
function getCookie (name) {
	var arg = name + "=";
	var alen = arg.length;
	var clen = document.cookie.length;
	var i = 0;
	while (i < clen) {
		var j = i + alen;
		if (document.cookie.substring(i, j) == arg)
		return getCookieVal (j);
		i = document.cookie.indexOf(" ", i) + 1;
		if (i == 0) break;
	}
	return null;
}
//
//  Function to create or update a cookie.
//    name - String object containing the cookie name.
//    value - String object containing the cookie value.  May contain
//      any valid string characters.
//    [expires] - Date object containing the expiration data of the cookie.  If
//      omitted or null, expires the cookie at the end of the current session.
//110    [path] - String object indicating the path for which the cookie is valid.
//      If omitted or null, uses the path of the calling document.
//    [domain] - String object indicating the domain for which the cookie is
//      valid.  If omitted or null, uses the domain of the calling document.
//    [secure] - Boolean (true/false) value indicating whether cookie transmission
//      requires a secure channel (HTTPS).
//
//  The first two parameters are required.  The others, if supplied, must
//  be passed in the order listed above.  To omit an unused optional field,
//  use null as a place holder.  For example, to call SetCookie using name,
//  value and path, you would code:
//
//      SetCookie ("myCookieName", "myCookieValue", null, "/");
//
//  Note that trailing omitted parameters do not require a placeholder.
//
//  To set a secure cookie for path "/myPath", that expires after the
//  current session, you might code:
//
//      SetCookie (myCookieVar, cookieValueVar, null, "/myPath", null, true);
//
function setCookie (name,value,expires,path,domain,secure) {
	document.cookie = name + "=" + escape (value) +
	((expires) ? "; expires=" + expires.toGMTString() : "") +
	((path) ? "; path=" + path : "") +
	((domain) ? "; domain=" + domain : "") +
	((secure) ? "; secure" : "");
}


//******** COMPARE DATES ********
//Finds the difference between 2 month date pairs.
//Month1 and Day1 should be the later date
//month2 and day2 should be the earlier date
function compareDates (month1, day1, month2, day2 ) {
	switch (month1 - month2){
		case 0:
		compare_value = day1 - day2
		break
		case 1:
		compare_value = day1 + (30-day2)
		break
		case -11:
		compare_value = day1 + (31-day2)
		break
		default:
		if (month2 > month1)
		{
			compare_value = day1 + (30-day2) + (11-month2) + (30*month1);
		}
		else
		{
			compare_value = day1 - day2 + (30*(month1-month2));
		}
	}
	return compare_value;
}


// functions to set array value into cookie

function getArrayCookie(name, ary) {
		
	var ent = getCookie(name); 
		
	if (ent) {
		i = 1; 
		while (ent.indexOf('^') != '-1') {
			ary[i] = ent.substring(0,ent.indexOf('^')); 
			i++;
			ent = ent.substring(ent.indexOf('^')+1, ent.length);
		}
	}
	return ary;	
} 

function setArrayCookie(name, ary, expires) {
	
	var value = ''; 
	for (var i = 0; i< ary.length; i++) {
		value += ary[i] + '^';
	} 
	
	setCookie(name, value, expires);
} 


function nextEntry(ary) {
	var j = 0; 
	for (var i = 0; i < ary.length; i++) {
		j = i
	} 
	return j + 1;
}
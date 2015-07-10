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
			FieldErrors = array_remove(FieldErrors,name);
			document.form1.elements[name].style.backgroundColor = '#FFFFFF';
		}
	} else {
		if(value == '0'){
			//alert('failed');
			FieldErrors.push(name);
			document.form1.elements[name].style.backgroundColor = '#FF9999';
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
			if(i == (arr.length-1)){
				arr = arr.slice(0,(arr.length-2));
				//alert('out: '+arr);
				return arr;
			}
			if(i == 0){
				arr = arr.slice(1,(arr.length-1));
				//alert('out: '+arr);
				return arr;
			}
			part1 = arr.slice(0,(i-1));
			part2 = arr.slice((i+1),(arr.length-1));
			
			arr = part1.concat(part2);
			//alert('out: '+arr);
			return arr;
		}
	}
}

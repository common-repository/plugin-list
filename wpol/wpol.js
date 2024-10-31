/**
 * WordPress OpenLab Library
 * 
 * @author Martin Wiso <wiso@openlab.net>
 * @copyright OpenLab Ltd.
 */
var WPOL = {};

// Ajax helpers
WPOL.ajax = (window.XMLHttpRequest) ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
WPOL.send = function(method, url, data, resultHander, wpnonce) {
	WPOL.ajax.onreadystatechange = resultHander;	
	var header='Content-Type:application/x-www-form-urlencoded;charset=UTF-8';
	
	// make call secure
	if (wpnonce && wpnonce.length > 0) {
	 var secureurl = (url.indexOf('?') >= 0) ? url + "&_wpnonce=" + wpnonce : url + "?_wpnonce=" + wpnonce; 
	 WPOL.ajax.open(method, secureurl, true);
  	} else {	
	 WPOL.ajax.open(method, url, true);
	}	
	WPOL.ajax.setRequestHeader(header.split(':')[0],header.split(':')[1]);
	WPOL.ajax.send(data);
};
WPOL.loading = function (show) {
	WPOL.q('activity').style.display = (show) ? 'block' : 'none';
};

// string helpers
WPOL.replaceAll = function (text, pattern, replacement) {
    //return text.replace(new RegExp(pattern, "g"), replacement);
	while (text.indexOf(pattern) != -1) {
        text = text.replace(pattern, replacement);
    }
	
	return text;
};

// Array helpers
WPOL.removeFromArray = function (key, data) {
	var newData = new Array();
	for (var i = 0;i < data.length;i++){
		if (data[i] != key) {
			newData.push(data[i]);
		}
	}
	
	return newData;
};  
WPOL.searchArray = function(key, data){
 	for (var i = 0;i < data.length;i++){
		if (data[i] == key) return true;
	}
	
	return false;
};

// DOM helpers
WPOL.q = function (id) {
	return document.getElementById(id);
}

// Debug helper
WPOL.debug = function (obj) {
	var message = 'WPOLDebug: ' + obj;
	if (console) {
		console.log(message)
	} else {
		alert(message);
	}
};
WPOL.ldebug = function (obj) {
	if (console) {
		console.dir(obj)
	} else {
		alert('WPOLDebug: ' + obj.length);
	}
};

// end of library

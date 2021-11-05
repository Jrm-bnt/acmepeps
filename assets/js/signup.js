"use strict";

function checkPwd() {

	if (form1.pwd.value !== form1.pwd2.value) {
		form1.pwd2.style.color = 'red';
		form1.pwd2.style.outline = '3px solid red';
		form1.submit.disabled = true;
	}
	else {
		form1.pwd2.style.color = 'green';
		form1.pwd2.style.outline = '3px solid green';
		form1.submit.disabled = false;
	}



}
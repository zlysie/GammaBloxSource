var theForm = document.forms['aspnetForm'];
if (!theForm) {
	theForm = document.aspnetForm;
}
function __doPostBack(eventTarget, eventArgument) {
	if(theForm !== "undefined") {
		theForm = document.aspnetForm;
	}
	if (!theForm.onsubmit || (theForm.onsubmit() != false)) {
		theForm.__EVENTTARGET.value = eventTarget;
		theForm.__EVENTARGUMENT.value = eventArgument;
		theForm.submit();
	}
}
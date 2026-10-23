ADD.Form.isValid = function(message, validateHidden) {
	if(!validateHidden) validateHidden=false;
	ADD.Form.validateHidden=validateHidden;
	if (has('__autosave') && get('__autosave') == 1)
		return true;
	
	var fields = ADD.fieldList();
	if (!message)
		message = "You must fix the error(s) highlighted at the top of the page before you can save the data.";

	var valid=true;
	for(var f=0;f<fields.length;f++) {
		for (var d = 0; d < fields[f].length; d++) {
			var currField = fields[f][d];
			if (currField.validate) currField.validate();
			if (currField.invalid) {
				valid = false;
			}
		}
	}
	if(!valid) {
		ADD.Notice.stopWait();
		alert(message);
	}
	return valid;
}
ADD.Form.showTab = function(tabName) {
	ADD.Form.disableDirty=true;
	//$('.content-' + tabName + ' a').click();
	$('.nav-tabs a[href=\"#' + tabName + '\"]').tab('show');
	//$("html, body").animate({ scrollTop: 0 }, "slow");
	ADD.Form.disableDirty=false;
}

ADD.Form.ignoreClearContent=false;
ADD.Form.currentFormId="content-form";
ADD.Feature.NotifyRequired = function() {
	return {
		"change": function(event) {
			fname = this.fieldName;
			var val = get(fname);
			if (val && val.length > 0) {
				content(fname).removeClass('warning');
			}
			else if (val && val instanceof Object && keys(val).length > 0) {
				content(fname).removeClass('warning');
			}
			else {
				content(fname).addClass('warning')
			}
		}
	}
};

ADD.Feature.ErrorRequired = function() {
	return {
		"validate": function() {
			this.invalid = false;
			/*
			if (this.attrs.modal) {
				var modalMessageElement = "modal-messages-" + this.attrs.modal;
				if (modalMessageElement != ADD.Notice.getMessageElement())
					return;
			}
			*/
			var req = field(this.fieldName);
			if (ADD.Form.validateHidden || req.is(":visible")) {
				var value = get(this.fieldName);
					
				if (value == null || value == "" || value.length == 0) {
					ADD.Notice.addFieldErrorWithLabel(label(this.fieldName), "is required", this.fieldName);
					//move notices into ADD.Form.validate if invalid=true
					this.invalid = true;
				}
				else {
					this.errorDisplay=false;
					this.invalid = false;
				}
			}
		},
		"change": function() {
			this.validate();
		},
		"errorMessage": true
	}
}

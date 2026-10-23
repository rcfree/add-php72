/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

ADD.NoticePanel = function() {

	return {
		"addFieldErrorWithLabel": function (label, message, name) {
			cleanName = 'no_field';
			if (name) {
				cleanName = clean(name);
			}
			else {
				cleanName = "no-field";
			}
			if (label.length > 0) {
				message = "'" + label + "' " + message;
			}

			if (this.getMessageContent('div.status').size() > 0)
				this.getMessageContent('div.status').remove()
			if (this.getMessageContent('div.alert-error ul').size() > 0) {
				this.getMessageContent('div.alert-error ul').prepend('<li class="error-' + cleanName + '">' + message + "</li>");
			}
			else {
				var errElement = ADD.Notice.getMessageElement();
				errElement.append('<div class="alert alert-error"><ul><li class="error-' + cleanName + '">' + message + '</li></ul></div>');
			}

			var fieldGroup = this.getMessageContent(".content-" + cleanName);
			if (fieldGroup.hasClass('warning'))
				fieldGroup.removeClass('warning');
			if (!fieldGroup.hasClass('control-group'))
				fieldGroup.addClass('control-group');
			if (!fieldGroup.hasClass('error'))
				fieldGroup.addClass('error');
		},
		"addFieldWarning": function (name, message) {
			if (this.getMessageContent('div.status').size() > 0)
				this.getMessageContent('div.status').remove()
			if (this.getMessageContent('div.alert-warning ul').size() > 0) {
				this.getMessageContent('div.alert-warning ul').append('<li class="warning-' + name + '">' + message + "</li>");
			}
			else {
				var errElement = this.getMessageElement();
				errElement.append('<div class="alert alert-warning"><ul><li class="warning-' + name + '">' + message + '</li></ul></div>');
			}
			var fieldGroup = this.getMessageContent(".content-" + name);
			if (fieldGroup.hasClass('error'))
				return;
			if (!fieldGroup.hasClass('control-group'))
				fieldGroup.addClass('control-group');
			if (!fieldGroup.hasClass('warning'))
				fieldGroup.addClass('warning');
		},
		"addMessage": function (message) {
			if (this.getMessageContent('div.alert-success ul').size() > 0) {
				this.getMessageContent('div.alert-success ul').append('<li class="message-' + name + '">' + message + "</li>");
			}
			else {
				var errElement = ADD.Notice.getMessageElement();
				errElement.append('<div class="alert alert-success"><ul><li class="message-' + name + '">' + message + '</li></ul></div>');
			}
		},
		"addError": function (error) {
			if (this.getMessageContent('div.status').size() > 0)
				this.getMessageContent('div.status').remove()
			if (this.getMessageContent('div.alert-error ul').size() > 0) {
				this.getMessageContent('div.alert-error ul').prepend('<li class="error">' + error + "</li>");
			}
			else {
				var errElement = ADD.Notice.getMessageElement();
				errElement.append('<div class="alert alert-error"><ul><li>' + error + '</li></ul></div>');
			}
		},
		"removeFieldErrors": function (name) {
			if (!name) {
				return;
			}
			if (!has(name)) {
				return;
			}
			cleanName = clean(name);
			this.getMessageContent('.error-' + cleanName).each(function () {
				jQuery(this).remove();
			});
			if (this.getMessageContent('div.alert-error ul li').size() == 0) {
				this.getMessageContent('div.alert-error').remove();
			}
			if (this.getMessageContent(".content-" + cleanName).hasClass('error'))
				this.getMessageContent(".content-" + cleanName).removeClass('error');
		},
		"removeAllErrors": function () {
			this.getMessageContent('.alert-error').remove();
			this.getMessageContent(".control-group").removeClass('error');
		},
		"hasErrors": function (name) {
			if (!name) {
				return this.getMessageContent('div.alert-error ul li').size() > 0 ? true : false;
			}
			cleanName = clean(name);
			return this.getMessageContent('.error-' + cleanName).size() > 0 ? true : false;
		},
		"removeAllWarnings": function () {
			jQuery('.alert-warning').remove();
			jQuery(".control-group").removeClass('warning');
		},
		"removeFieldWarnings": function (name) {
            if(!name) {
                if (this.getMessageContent('div.alert-warning ul li').size() == 0) {
                    this.getMessageContent('div.alert-warning').remove();
                }
                return;
            }
			var cleanName = clean(name);
			this.getMessageContent('.warning-' + cleanName).each(function () {
				jQuery(this).remove();
			});

			if (this.getMessageContent(".content-" + cleanName).hasClass('warning'))
				this.getMessageContent(".content-" + cleanName).removeClass('warning');
		},
		"removeAjaxWarnings": function () {
			this.getMessageContent('.warning-ajax').each(function () {
				jQuery(this).remove();
			});
			if (this.getMessageContent('div.alert-warning ul li').size() == 0) {
				this.getMessageContent('div.alert-warning').remove();
			}
		},
		"removeAllMessages": function () {
			this.getMessageContent('.alert-message').each(function () {
				jQuery(this).remove();
			});
			if (this.getMessageContent('div.alert-message ul li').size() == 0) {
				this.getMessageContent('div.alert-message').remove();
			}
		},
		"setMessageElement": function (messageElement) {
			this.messageElement = messageElement;
		},
		"getMessageElement": function () {
			return this.messageElement;
		},
		"getMessageContent":function(jQuerySel) {
			return this.messageElement.find(jQuerySel);
		}
	};
};

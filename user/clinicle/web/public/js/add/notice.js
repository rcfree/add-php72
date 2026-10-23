/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


ADD.Notice = function() {
	return {
		"hideOtherNotices":function() {
			$(".modal").each(function() {
				if(!$(this).is(":visible")) {
					return;
				}
				$(this).modal("hide");
			});
		},
		"showConfirm": function(title, text, yesHref, noHref) {
			this.hideOtherNotices();
			$("#modal-confirm-title").html(title);
			$("#modal-confirm-text").html(text);
			if (!yesHref) {
				$("#modal-confirm-yes").hide()
			}
			else {
				$("#modal-confirm-yes").show();
			}

			$("#modal-confirm-yes").attr("onclick", "CLE.Client.gotoNgPage('" + yesHref + "')");
			if (noHref)
				$("#modal-confirm-no").attr("onclick", "CLE.Client.gotoNgPage('" + noHref + "')");
			$("#modal-confirm").modal('show');
		},
		"showAlert": function(title, text, btnText, btnHref) {
			this.hideOtherNotices();
			$("#modal-confirm-title").html(title);
			$("#modal-confirm-text").html(text);
			$("#modal-confirm-yes").hide();
			$("#modal-confirm-no").show().html(btnText);

			if (btnHref)
				$("#modal-confirm-no").attr("href", btnHref);
			$("#modal-confirm").modal('show');
		},
		"addFieldErrorWithLabel": function(label, message, name) {
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

			if (jQuery('div.status').size() > 0)
				jQuery('div.status').remove()
			if (jQuery('div.alert-error ul').size() > 0) {
				jQuery('div.alert-error ul').prepend('<li class="error-' + cleanName + '">' + message + "</li>");
			}
			else {
				var errElement = ADD.Notice.getMessageElement();
				errElement.append('<div class="alert alert-error"><ul><li class="error-' + cleanName + '">' + message + '</li></ul></div>');
			}

			var fieldGroup = jQuery(".content-" + cleanName);
			if (fieldGroup.hasClass('warning'))
				fieldGroup.removeClass('warning');
			if (!fieldGroup.hasClass('control-group'))
				fieldGroup.addClass('control-group');
			if (!fieldGroup.hasClass('error'))
				fieldGroup.addClass('error');
		},
		"addFieldWarning": function(name, message) {
			if (jQuery('div.status').size() > 0)
				jQuery('div.status').remove()
			if (jQuery('div.alert-warning ul').size() > 0) {
				jQuery('div.alert-warning ul').append('<li class="warning-' + name + '">' + message + "</li>");
			}
			else {
				var errElement = ADD.Notice.getMessageElement();
				errElement.append('<div class="alert alert-warning"><ul><li class="warning-' + name + '">' + message + '</li></ul></div>');
			}
			var fieldGroup = jQuery(".content-" + name);
			if (fieldGroup.hasClass('error'))
				return;
			if (!fieldGroup.hasClass('control-group'))
				fieldGroup.addClass('control-group');
			if (!fieldGroup.hasClass('warning'))
				fieldGroup.addClass('warning');
		},
		"addMessage": function(message) {
			if (jQuery('div.alert-success ul').size() > 0) {
				jQuery('div.alert-success ul').append('<li class="message-' + name + '">' + message + "</li>");
			}
			else {
				var errElement = ADD.Notice.getMessageElement();
				errElement.append('<div class="alert alert-success"><ul><li class="message-' + name + '">' + message + '</li></ul></div>');
			}
		},
		"addError": function(error) {
			if (jQuery('div.status').size() > 0)
				jQuery('div.status').remove()
			if (jQuery('div.alert-error ul').size() > 0) {
				jQuery('div.alert-error ul').prepend('<li class="error">' + error + "</li>");
			}
			else {
				var errElement = ADD.Notice.getMessageElement();
				errElement.append('<div class="alert alert-error"><ul><li>' + error + '</li></ul></div>');
			}
		},
		"removeFieldErrors": function(name) {
			if(!name) {
				return;
			}
			if(!has(name)) {
				return;
			}
			cleanName = clean(name);
			jQuery('.error-' + cleanName).each(function() {
				jQuery(this).remove();
			});
			if (jQuery('div.alert-error ul li').size() == 0) {
				jQuery('div.alert-error').remove();
			}
			if (jQuery(".content-" + cleanName).hasClass('error'))
				jQuery(".content-" + cleanName).removeClass('error');
		},
		"removeAllErrors": function() {
			jQuery('.alert-error').remove();
			jQuery(".control-group").removeClass('error');
		},
		"hasErrors": function(name) {
			if (!name) {
				return jQuery('div.alert-error ul li').size() > 0 ? true : false;
			}
			cleanName = clean(name);
			return jQuery('.error-' + cleanName).size() > 0 ? true : false;
		},
		"removeAllWarnings": function() {
			jQuery('.alert-warning').remove();
			jQuery(".control-group").removeClass('warning');
		},
		"removeFieldWarnings": function(name) {
			var cleanName = clean(name);
			jQuery('.warning-' + cleanName).each(function() {
				jQuery(this).remove();
			});
			if (jQuery('div.alert-warning ul li').size() == 0) {
				jQuery('div.alert-warning').remove();
			}
			if (jQuery(".content-" + cleanName).hasClass('warning'))
				jQuery(".content-" + cleanName).removeClass('warning');
		},
		"removeAjaxWarnings": function() {
			jQuery('.warning-ajax').each(function() {
				jQuery(this).remove();
			});
			if (jQuery('div.alert-warning ul li').size() == 0) {
				jQuery('div.alert-warning').remove();
			}
		},
		"removeAllMessages": function() {
			jQuery('.alert-message').each(function() {
				jQuery(this).remove();
			});
			if (jQuery('div.alert-message ul li').size() == 0) {
				jQuery('div.alert-message').remove();
			}
		},
		"setMessageElement":function(messageElement) {
			ADD.Notice.messageElement = messageElement;
		},
		"getMessageElement": function() {
			if (ADD.Notice.messageElement) {
				return jQuery("#" + ADD.Notice.messageElement)
			}
			else {
				return jQuery("#messages");
			}
		},
		"startWait": function() {
			this.hideOtherNotices();
		}, 
		"stopWait": function() {
			return;
		}
	}
}();

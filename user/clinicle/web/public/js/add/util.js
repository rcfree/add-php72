ADD.Util = {}
ADD.Util.getTitleFromAssign = function(assign) {
	var title = assign.attrs.title;
	if (!title)
		title = assign.attrs.display_title;
	if (!title)
		title = "Field " + assign.fieldName;
	return title;
}
ADD.Util.getUrlVars=function(param) {
    var pageData = angular.element("body").scope().getPageData();
	
	if(!pageData.pagePath) {
		return "";
	}
	pagePath = pageData.pagePath;
	
	var url = new URI(pagePath);
	var query = url.query(true);
	return query[param];
}
ADD.Util.activateDirty = function() {
	
	$.DirtyForms.ignoreAnchorSelector = "a#modal-confirm-no,li.ui-menu-item a,a.ui-corner-all, .nav-tabs, .ui-datepicker-next";
	(function($) {
		$.DirtyForms.helpers.push({
			ignoreAnchorSelector: "a#modal-confirm-no,li.ui-menu-item a, .nav-tabs, .ui-datepicker-next, .ui-datepicker-prev"
		});
	})(jQuery);

	$(document).ready(function() {
		$('#save-confirm').on("click",function(e) {
			$('#modal-confirm-save').modal('hide');
			ADD.Form.disableDirty = true;
			var scope = angular.element("body").scope();
			scope.save().then(
				function() {
					scope.gotoPage($.DirtyForms.redirectPath);
				}
			);
		});

		$('#close-confirm').on("click",function(e) {
			$('#modal-confirm-save').modal('hide');
			$('#main-panel').removeClass("dirty");
			ADD.Form.disableDirty = true;
			angular.element("body").scope().gotoPage($.DirtyForms.redirectPath);
		});

		$('#ignore-confirm').on("click",function(e) {
			$('#modal-confirm-save').modal('hide');
		});
	});

	$.DirtyForms.dialog = {
		selector: '#modal-confirm-save',
		fire: function(message, dlgTitle) {
			if (ADD.Form.disableDirty)
				return;
			if (ADD.Notice.hasErrors())
				return;
			//$('#modal-confirm-save').modal('show');
		},
		bind: function() {

		},
		refire: function(content) {
			return false;
		},
		stash: function() {
			return false;
		}
	}
	jQuery('form').dirtyForms();
}

ADD.Util.isEnabled = function(feature) {
	var fname = feature.fieldName;
	var f = field(fname);
	if(f.prop("disabled")) return false;
	if(f.prop("readonly")) return false;
	return true;
}
ADD.Util.pairedArrayToObject = function(pairedArray) {
	var object = {};
	for(var i=0;i<pairedArray.length;i++) {
		var pairName = pairedArray[i].name;
		var pairValue = pairedArray[i].value;
		if(!object[pairName]) object[pairName]=[];
		object[pairName].push(pairValue);
	}
	return object;		
}
	
ADD.Util.isEnabled = function(feature) {
	var fname = feature.fieldName;
	var f = field(fname);
	if(f.prop("disabled")) return false;
	if(f.prop("readonly")) return false;
	return true;
}

ADD.Util.ts = function() {
	return new Date().getTime();
}

ADD.Util.gotoPage = function(path,queryParams) {
	var urlString = path;
	if(queryParams) {
		var qstring = $.param(queryParams);
		urlString = urlString + "?" + qstring;
	}
	ADD.Notice.startWait();
	window.location.href = urlString;
}	

ADD.Touch={};
ADD.Util.gotoPage = function(path,queryParams) {
	var urlString = path;
	if(queryParams) {
		var qstring = $.param(queryParams);
		urlString = urlString + "?" + qstring;
	}
	ADD.Notice.startWait();
	window.location.href = urlString;
}	

ADD.Touch.switchPushButton = function(fieldName,fieldValue,element) {
	set(fieldName,fieldValue);
	var pushButton = field(fieldName);
	$(element).parents('table').find('button').removeClass('btn-primary'); 
	$(element).addClass('btn-primary');
}
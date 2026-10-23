//unlock the record matching the login id
CLE.Client.addFunction("unlockRecord", function(id) {
	var params = {'id':id};
	var control = this.control;
	control.svc("DataAccess").manageGet("/record/unlock",params,function(data) {
		control.refresh();
	},control);
});

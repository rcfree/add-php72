//selected the selected holding and display a search patient modal dialog with search terms pre-populated
$(document).ready(function() {
CLE.Client.addFunction("showLinkModal",function(id) {
	var control = this.control

	var matchingRow = _.findWhere(control.data.grid, {"id":id});
	control.data.linkId = matchingRow.identifier.value;
	control.data.linkFirstName = matchingRow.first_name.label;
	control.data.linkLastName = matchingRow.last_name.label;
	control.data.linkDob = matchingRow.dob.label;
	
	control.data.searchId = matchingRow.identifier.label;
	control.data.searchFirstName = matchingRow.first_name.label;
	control.data.searchLastName = matchingRow.last_name.label;
	control.data.searchDob = matchingRow.dob.label;
	control.data.searchType = 1;
	control.data.patients=[];
		
	control.data.selReferralId = id;
	control.element.find('.holding-link').modal('show');
});

//nullify the selected holding and close the link patient dialog
	CLE.Client.addFunction("closeLinkModal", function() {
		var control = this.control;
	control.data.selReferralId = null;
	control.data.searchId = null;
	control.element.find('.holding-link').modal('hide');
});

//get patients from the lookup webservice for the current search params
	CLE.Client.addFunction("searchPatients", function() {
		var control = this.control;
		var fields = control.fields;
		console.log("fields",fields);
	var params = {"id":fields.searchId};
	if(fields.searchType==2) {
		params = {"fname":fields.searchFirstName, "lname":fields.searchLastName, "dob":fields.searchDob};
	}
	control.data.loadingPatients = true;
		control.svc("DataAccess").http.get('/lookup/pmi',{"params":params}).
	  success(function(data, status, headers, config) {
	    if(data.error) {
	    	control.data.patientError = $("<div />").html(data.error).text();
	    	control.data.loadingPatients = false;
	    	control.data.patients=[];
	    	return null;
	    }
	    control.data.patientError=null;
	    var patients = [];
	    for(var i=1;i<data.length;i++) {
	    	patients.push(data[i][0]);
	    }
	    control.data.patients = patients;
	    control.data.loadingPatients = false;
	    return patients;
	  }).
	  error(function(data, status, headers, config) {
	   	control.data.patientError="Network error:" + data;
		control.data.loadingPatients = false;
	  });		
});

//TODO: link the selected holding to the selected patient using a POST to /api/holding
	CLE.Client.addFunction("linkToPatient", function(patientSnumber) {
		var control = this.control;
	var params = {"identifier":patientSnumber,"encounter_id":control.data.selReferralId, "pim":control.get("pim")};


	if(control.get("updatefield")) {
		params["update_field"]=control.get("updatefield");
		params["update_value"]=control.get("updatevalue");
	}

		control.svc("DataAccess").http.get("/api/holding",{
		params: params
   }).success(function(data, status, headers, config) {
		if(data.error) {
			control.data.patientError=data.error_text;
			return;
		}
		
		control.svc("CustomFunctions").customCall("closeLinkModal",[],control);
		if(data.message) {
			control.data.gridMessage=data.message_text;
		}
		control.refresh();
		var refreshIds = control.get("refresh");
		
		if(!refreshIds) return;
		
		for(var i=0;i<refreshIds.length;i++) {
			CLE.Client.refresh(refreshIds[i]);
		}
	  }).
	  error(function(data, status, headers, config) {
	    control.data.patientError="Unexpected network error";
	  });
});
});

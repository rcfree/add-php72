var ajaxTimeout = 10000;

function showDialog(closeOnCancel, functions, content, params, title, width, height, elementID, position) {
	// if 'content' begins with a / then get using AJAX and use the params hash
	if (!height) height = 305;
	if (!width) width = 550;
	if (!elementID) elementID="patient_dialog";
	if (!position) position=position;
	var dialog = document.getElementById(elementID);
	if (!dialog) {
		dia = document.createElement("div");
		dia.setAttribute("id",elementID);
		document.body.appendChild(dia);
		dialog = document.getElementById(elementID);
	}
	if (content.substring(0, 1) == '/' || content.substr(0, 5) == 'http:') {
		// show dialog before getting content and display a loader image
		
		dialog.style.display = "block";

		showAjaxLoader(elementID);
		doShowDialog(closeOnCancel, functions, title,width,height,elementID);
		cj.ajax({
				url:content,
				data:params,
				dataType: "json",
				error: function (XMLHttpRequest, textStatus, errorThrown) {
					var dia = document.getElementById(elementID);
					dia.innerHTML="";
					var err = document.createElement("div");
					err.setAttribute("class","messages error");
					err.innerHTML = errorThrown;
					dia.appendChild(err);
				},
				success: function(data, textStatus) {
					var dia = document.getElementById(elementID);
					dia.innerHTML="<h3>Click on a patient name to use that patient:</h3>";
					var tab = document.createElement("table");
					var tbody = document.createElement("tbody");
					var tr = document.createElement("tr");
					var cols = data[0];
					for(var i=0;i<cols.length;i++) {
						var th = document.createElement("th");
						th.appendChild(document.createTextNode(cols[i]));
						tr.appendChild(th);
					}
					tbody.appendChild(tr);
					
					for(var r=1;r<data.length;r++) {
						var row = data[r];
						var tr = document.createElement("tr");
						tr.setAttribute("onmouseover","this.style.backgroundColor='lightgreen';");
						tr.setAttribute("onmouseout","this.style.backgroundColor='white';");
						tr.setAttribute("onclick","updateContactWithPatient('" + row[1] + "')");
						
						var info = row[0];
						for(var i=1;i<row.length;i++) {
							var td = document.createElement("td");
							if (row[i]!=null)  td.appendChild(document.createTextNode(row[i]));
							if (i==1) {
								for(var e in info) {
									var hi=document.createElement("input");
									hi.setAttribute("type","hidden");
									hi.setAttribute("name","patient" + row[1]);
									hi.setAttribute("id",e + "." + row[1]);
									if (info[e]!=null) hi.setAttribute("value",info[e]);
									td.appendChild(hi);
								}
							}
							tr.appendChild(td);
						}
						tbody.appendChild(tr);
					}
					
					tab.appendChild(tbody);
					dia.appendChild(tab);
					
				},
				timeout: ajaxTimeout,
				type: "get"
		});
	} else {
		dialog.innerHTML = content;
		dialog.style.display = "";
		doShowDialog(closeOnCancel, functions, title,width,height,elementID,position);
	}
}

function doShowDialog(closeOnCancel, functions, heading,width, height,elementID, position) {
	var buttonHash;

	var dialogElement = cj("#" + elementID);

	if (closeOnCancel && !functions['Cancel']) {
		functions['Cancel'] = function(){
			dialogElement.dialog("close");
		};
	}
	
	dialogElement.dialog( {
		modal :true,
		draggable :false,
		resizable :false,
		overlay : {
			opacity :0.5,
			background :"black"
		},
		buttons :functions,
		width : width + 50,
		height :height + 100,
		title: heading,
		stack: true,
		position: position
	});
	dialogElement.dialog('open');
	dialogElement.data('title.dialog',heading);
}

function patientLookup() {
		var functions = {
		};
		showDialog(true, functions, "/civicrm/patient/lookup.json",
		{
			id: getIdentifier(),
			fname: getFirstName(),
			lname: getLastName(),
			dob: getBirthDate(),
		}
		, 'Search Patients', 600, 400, 'dialog');
	}

	function showAjaxLoader(elementID,small) {
	var e = document.getElementById(elementID);
	var image="/civicrm/sites/all/modules/lrbtools/patientlookup/images/ajaxload.gif";

	
	if (small==1) {
		_ajaxElementID = elementID;
		_ajaxImage = e.src;
		e.src = image ;
		return;
	}
	else if (small==2) {
		e.innerHTML = "<center><img style='vertical-align:middle' src='"
			+ image + "></center>";
		return;
	}
		e.innerHTML = "<center><img style='vertical-align:middle' src='"
			+ image + "'></center>";
}

function updateContactWithPatient(identifier) {
	var fieldMap = getFieldMap();
	var patientFields = document.getElementsByName('patient' + identifier);
	var fieldsWithValue=0;
	
	for (var i = 0; i < patientFields.length; i++) {
		var temp = patientFields[i].id.split('.');
		var sourceFieldName = temp[0];
		var targetFieldName = fieldMap[sourceFieldName];
		if (typeof(targetFieldName)=="string") {
			var targetField = document.getElementById(targetFieldName);
			if (targetField) {
				if (targetField.value.length > 0) 
					fieldsWithValue++;
			}
		}
	}
	if (fieldsWithValue>0) {
		var ok = confirm("This contact already contains patient data.\nAre you sure you want to replace this with the patient details?");
		if (!ok) return;
	}
	
	_populatePatient(identifier);
	cj("#dialog").dialog("close");
}

function _populatePatient(identifier) {
	var fieldMap = getFieldMap();
	var patientFields = document.getElementsByName('patient' + identifier);
	for(var i=0;i<patientFields.length;i++) {
		
		var temp = patientFields[i].id.split('.');
		var sourceFieldName = temp[0];

		var targetFieldName = fieldMap[sourceFieldName];
		if (!targetFieldName) continue;
		if (typeof(targetFieldName)=="string") {
			var targetField = document.getElementById(targetFieldName);
			targetField.value = patientFields[i].value;
			//alert("field:" + targetFieldName + typeof(targetFieldName));
		}
		else {
			targetFieldName.call(null,patientFields[i].value);
		}
	}
	postPopulateCheck();
}
function guid() {
    var S4 = function() {
       return (((1+Math.random())*0x10000)|0).toString(16).substring(1);
    };
    return (S4()+S4()+"-"+S4()+"-"+S4()+"-"+S4()+"-"+S4()+S4()+S4());
}

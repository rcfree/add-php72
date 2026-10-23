var ajaxTimeout = 10000;

function showDialog(closeOnCancel, functions, content, params, title, width, height, elementID, position) {
	// if 'content' begins with a / then get using AJAX and use the params hash
	screenHeight = jQuery(window).height();
	screenWidth = jQuery(window).width()
	width = 700;
	height = 400;
	if (screenWidth<=800) width=screenWidth-200;
	
	if (screenHeight<=600) height=screenHeight-150
	
	if (!elementID) elementID="patient_dialog";
	if (!position) position=position;
	var dia = jQuery('#' + elementID);
	jQuery('.modal').css('margin-left',(width/2)*-1);
	jQuery('.modal').css('margin-top',(screenHeight-height-100)/2);
	jQuery('.modal-body').css('height',height-100);
	dia.modal('show');
	jQuery('#' + elementID + " .modal-body").html("Loading..");
		jQuery.ajax({
				url:content,
				data:params,
				dataType: "json",
				error: function (XMLHttpRequest, textStatus, errorThrown) {
					var dia = jQuery('#' + elementID + " .modal-body");
					dia.html('<div class="messages error"><ul><li class="error-patient">' + errorThrown + '</ul></li></div>')
				},
				success: function(data, textStatus) {
					var dia = jQuery('#' + elementID  + " .modal-body");
					if (data.error) {
						dia.html('<div class="messages error"><ul><li class="error-patient">' + data.error + '</ul></li></div>');
						return;
					}
					dia.html("<h4>Click on a patient name to use that patient:</h4>");
					var tab = document.createElement("table");
					tab.setAttribute("class","table table-hover");
					
					var thead = document.createElement("thead");
					
					var tr = document.createElement("tr");
					var cols = data[0];
					for(var i=0;i<cols.length;i++) {
						var th = document.createElement("th");
						th.appendChild(document.createTextNode(cols[i]));
						tr.appendChild(th);
					}
					thead.appendChild(tr);
					
					var tbody = document.createElement("tbody");
					
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
					tab.appendChild(thead);
					tab.appendChild(tbody);
					dia.append(tab);
					
				},
				timeout: ajaxTimeout,
				type: "get"
		});
}

function doShowDialog(closeOnCancel, functions, heading,width, height,elementID, position) {	
	var buttonHash;
	
	var dialogElement = jQuery("#" + elementID);
	
	if (closeOnCancel && !functions['Cancel']) {
		functions['Cancel'] = function(){
			jQuery("#" + elementID).dialog("close");
		};
	}
	jQuery("#" + elementID).dialog( {
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
	jQuery("#" + elementID).dialog('open');
	jQuery("#" + elementID).data('title.dialog',heading);
}

function patientLookup() {
		var functions = {
		};
		showDialog(true, functions, getPatientLookupPath(),
		{
			id: getIdentifier(),
			fname: getFirstName(),
			lname: getLastName(),
			dob: getBirthDate()
		}
		, 'Search Patients', 600, 400, 'patient_dialog');
	}

	function showAjaxLoader(elementID,small) {
	var e = document.getElementById(elementID);
	var image="/images/ajax-loader.gif";

	if (small) image = "/images/small-ajax-loader.gif";
	
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
	var populateFunction = function() {
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
			var ok = confirm("This patient already contains data.\nAre you sure you want to replace this with the selected patient details?");
			if (!ok) return;
		}
		
		populatePatient(identifier);
		jQuery("#patient_dialog .modal-body").html("");
		jQuery("#patient_dialog").modal("hide");
	};
	if (prePopulateCheck(identifier,populateFunction)) return;
}

function populatePatient(identifier) {
	var fieldMap = getFieldMap();
	var patientFields = document.getElementsByName('patient' + identifier);
	for(var i=0;i<patientFields.length;i++) {
		
		var temp = patientFields[i].id.split('.');
		var sourceFieldName = temp[0];

		var targetFieldName = fieldMap[sourceFieldName];
		if (!targetFieldName) continue;
		
		if (typeof(targetFieldName)=="string") {
			var targetField = document.getElementById(targetFieldName);
			if (!targetField) throw "Target field:" + targetFieldName + " was not found!";
			maxLength = targetField.getAttribute('maxlength');
			var srcValue = patientFields[i].value;
                        if(maxLength && srcValue.length>maxLength) {
                        	ADD.Notice.addFieldWarning(targetField.name,"'" + label(targetField.name) + "' is too long and has been truncated (from " + srcValue.length + " to " + maxLength + " characters)");
                            targetField.value = srcValue.substr(0,targetField.maxLength);
                            
                        }
                        else {
                            targetField.value = srcValue;
                        }
			//alert("field:" + targetFieldName + typeof(targetFieldName));
		}
		else {
			targetFieldName.call(null,patientFields[i].value);
		}
	}
	postPopulateCheck();
	jQuery('[name="patient' + identifier + '"]').remove();	
}

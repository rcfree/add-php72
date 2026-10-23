var mainMod = angular.module('clinicle', ['ngSanitize']);
mainMod.controller('EncounterGridController', ['$scope', '$element','$interpolate','DataAccess', 'ModalEntry', 'FieldLink', 'CustomFunctions',EncounterGridController])
.controller('AddItemController', ['$scope', '$element','ModalEntry', 'CustomFunctions','DataAccess',AddItemController])
.controller('RootController',['$scope','$window','$element','PageData','PageHandler','FormHandler', RootController])
.controller('MenuController',['$scope','$http','$element', '$compile','PageData','ModalEntry',MenuController])
	.service('DataAccess',['$http','CustomFunctions',DataAccess])
	.service('PageData',[PageData])
	.service('ModalEntry',['$http','DataAccess',ModalEntry])
.service('PageHandler',['$http','$compile','PageData',PageHandler])
.service('FormHandler',['$http','$window','PageData','PageHandler',FormHandler])
.service('FieldLink',[FieldLink])
	.service('CustomFunctions',[CustomFunctions])
.filter('rejoin', RejoinFilter)
.filter('timeToDate', TimeToDateFilter)
.filter('trustHtml', TrustHtmlFilter);

function FieldLink() {
    return {
        "populateMappedFields":function(mappings,cell) {
        	var date = get(mappings[0]);
        	var values = [];
        	var cellValues =[];
            for(var i=1;i<mappings.length;i++) {
                var temp = mappings[i].split("#");
                values.push(get(temp[0]));
                cellValues.push(temp.length == 2 ? cell.data[temp[1]].value : cell.value);
            }

            var forceLink = false;
            if(forceLink || (date != cell.x || _.escape(values) != cellValues)) {
                date = cell.x;
                values = cellValues;
            }
            else {
                date = "";
                values = null;
            }

            try {
                set(mappings[0],date);
                for(var i=1;i<mappings.length;i++) {
                    var temp = mappings[i].split("#");
                    if(values==null) {
                    	set(temp[0],"");
                    }
                    else {
                        set(temp[0], _.unescape(values[i-1]));
					}
                }
            }
            catch(ex) {
                ADD.Notice.showAlert("Unexpected error",ex + value,"OK");
            }
        },
        "extractCellFields" :function(linkFieldName,cell,control) {
            if(!control.get('fieldmap')) {
                var fieldName = cell.y.toLowerCase().replace("\/","_");
                return [linkFieldName + "_date_" + fieldName,linkFieldName + "_value_" + fieldName];
            }
            var fieldMap = control.get('fieldmap').split("|");
			
			for(var i=0;i<fieldMap.length;i++) {
                var mapItem = fieldMap[i].split(":");
				if(mapItem[0]==cell.y) {
                    return mapItem[1].split(";");
                }
            }
        },
        "doesDataMatch":function(cell, control) {
            var linkFieldName = control.get('linkfield');
            var mappings = this.extractCellFields(linkFieldName,cell, control);
            if(!mappings) {
                return null;
            }
            return this.dealWithDateValueMapping(cell,control,mappings);
        },
        "dealWithDateValueMapping":function(cell, control, mappings) {
        	if(!has(mappings[0])) {
                return null;
            }
            var temp = mappings[1].split("#");
            if(!has(temp[0])) {
                return null;
            }
            var date = get(mappings[0]);
            var values = [];
            var cellValues = [];
            for(var i=1;i<mappings.length;i++) {
                var temp = mappings[i].split("#");
                values.push(get(temp[0]));
                cellValues.push(temp.length == 2 ? cell.data[temp[1]].value : cell.value);
            }

            var cellDate = cell.x;
            if(control.get('map_as_date')) {
                cellDate = cellDate.substring(0,10);
            }

            if(date == cellDate && _.escape(values) == cellValues) {
                var data = {};
                data[mappings[0]]=cellDate;
                for(var i=1;i<mappings.length;i++) {
                    var temp = mappings[i].split("#");
                    data[temp[0]] = cellValues[i - 1];
                }
                return data;
            }
            return null;
        }
    };
}

CLE.Client=function() {
	var functions = {};
	return {
		"getFunctionNames": function () {
			return _.keys(functions);
		},
		"getFunction": function (actionName) {
			return functions[actionName];
		},
		"addFunction": function (actionName, funcImpl) {
			functions[actionName] = funcImpl;
		},
		"element": function (elementId) {
			var refreshElement = angular.element($("#" + elementId));
			if (!refreshElement.length) {
				return false;
			}
			return refreshElement;
		},
		"refresh": function (elementId) {
			var refreshElement = CLE.Client.element(elementId);
			if (!refreshElement) {
				return;
			}
			var angularElement = angular.element(refreshElement);
			if (!angularElement || !angularElement['scope']) {
				throw Exception("Cannot get ng-scope for element with ID '" + elementId + "'");
			}

			return angularElement.scope().refresh();
		},
		"refreshAfter": function (millisecs, elementId) {
			var refreshFunc = function () {
				CLE.Client.refresh(elementId);
			};
			setTimeout(refreshFunc, millisecs);
		},
		"removeLink": function (linkFieldName, linkedGrid) {
			set(linkFieldName, "");
			$('#' + linkFieldName + '-value').html('');

			$('#' + linkFieldName + '-linked').attr('class', 'hidden');
			$('#' + linkFieldName + '-unlinked').attr('class', '');
			var linkedGridElement = CLE.Client.element(linkedGrid);
			if (linkedGridElement && linkedGridElement.scope) {
				linkedGridElement.scope().refresh();
			}
			return;
		},
		"hideLinkRecord": function (linkFieldName, linkedGrid) {

			var linkedGridElement = CLE.Client.element(linkedGrid);
			if (!linkedGridElement && !linkedGridElement.scope && !linkedGridElement) {
				return;
			}

			if (!linkedGridElement.scope().grid) {
				return;
			}
			if (linkedGridElement.scope().grid.length != 0) {
				return;
			}
			$('#' + linkFieldName + '-linked').attr('class', 'hidden');
			$('#' + linkFieldName + '-unlinked').attr('class', 'hidden');
			$('#' + linkFieldName + '-no-records').attr('class', '');
		}
	}
}();

ADD.Form.onModalReady={};


	CLE.Client.addFunction("toggleLink",function(rowId,linkOnly, textValue, row) {
		var linkFieldName = this.control.get('linkfield');
		if(!has(linkFieldName)) {
			return;
		}
		var linkValue = get(linkFieldName);
		if(linkValue && !linkOnly) {
			CLE.Client.removeLink(linkFieldName);
			return;
		}
		set(linkFieldName,rowId);
		$('#' + linkFieldName + '-value').html(textValue);
		$('#' + linkFieldName + '-linked').attr('class','');
		$('#' + linkFieldName + '-unlinked').attr('class','hidden');
		if(this.control.has('linkcopydata')) {
			linkCopyData = this.control.get('linkcopydata');
			linkCopyFields = linkCopyData.split(";");
			for(var i=0;i<linkCopyFields.length;i++) {
				copyBits = linkCopyFields[i].split(":");
				set(copyBits[1],row[copyBits[0]]['value'])
			}
		}
	});
	
	CLE.Client.addFunction("toggleColumnLink",function(linkOnly) {
		var linkFieldName = this.control.get('linkfield');
		if(!has(linkFieldName)) {
			return;
		}
		var linkValue = get(linkFieldName);
		if(linkValue && !linkOnly) {
			set(linkFieldName,"");
			$('#' + linkFieldName + '-value').html('');
		
			$('#' + linkFieldName + '-linked').attr('class','hidden');
			$('#' + linkFieldName + '-unlinked').attr('class','');
			
			return;
		}
		set(linkFieldName,rowId);
		$('#' + linkFieldName + '-value').html(rowId);
		
		$('#' + linkFieldName + '-linked').attr('class','');
		$('#' + linkFieldName + '-unlinked').attr('class','hidden');
	});
	
	
	CLE.Client.addFunction("getLinkClass",function(rowId) {
		var linkFieldName = this.control.get('linkfield');
		if(!has(linkFieldName)) {
			return "";
		}
		var linkField = get(linkFieldName);
		
		if(linkField) {
			if(linkField != rowId) {
				return "hidden";
			}
			else {
				return "btn btn-success btn-small";
			}
		}
		
		return "btn btn-default btn-small";
	});
	
	
	CLE.Client.addFunction("getColumnLinkClass",function(rowId) {
		var linkFieldName = this.control.get('linkfield');
		if(!has(linkFieldName)) {
			return "";
		}
		var linkField = get(linkFieldName);
		
		if(linkField && linkField != rowId) {
			return "hidden";
		}
		
		return "btn btn-success btn-small";
	});
	
	
	
	CLE.Client.addFunction("toggleLinkData",function(rowId,linkOnly, textValue) {
		var cf = this.control.svc("CustomFunctions");
		return cf.customCall("toggleLink",[rowId,linkOnly,textValue],this.control);
	});
	
	CLE.Client.addFunction("getColumnLinkLabel",function(rowId,ignoreUnlinked) {
		var linkFieldName = this.control.get('linkfield');
		if(!has(linkFieldName)) {
			return "";
		}
		var linkField = get(linkFieldName);
		
		if(!linkField) {
			return ignoreUnlinked ? "" : "<i class='icon-star-empty icon-white'></i>&nbsp;Link";
		}
		if(linkField != rowId) {
			return "";
		}
		
		return "<i class='icon-star icon-white'></i>" + (ignoreUnlinked ? "" : "&nbsp;Unlink");
	});
	
	CLE.Client.addFunction("updateList",function(linkField) {
		var linkScope = CLE.Client.element(linkField).scope();
		var parentid = this.control.get("parentid");
		var scopeAdd = linkScope.add;
		scopeAdd.parentid = parentid;
		linkScope.add = scopeAdd;
		
		linkScope.refresh();
	});
	
	CLE.Client.addFunction("isSelected",function(idValue) {
		var scopeValue = this.control.get("value");

		if(scopeValue==idValue) {
			var cf = this.control.svc("CustomFunctions");
			cf.customCall("updateList",['lung_function'],this.control);
			return true;
		}
		return false;
	});

	CLE.Client.addFunction("getCellLinkClass",function(cell, readonly) {
		if(cell.link_column && !readonly) {
			return "btn btn-default btn-small";
		}
		if(!cell.y) {
			return;
		}
		var fl = this.control.svc("FieldLink");
		if(fl.doesDataMatch(cell,this.control)) {
			return readonly ? "label-success label pull-right" : "btn btn-success btn-small pull-right";
		}
		return readonly ? "pull-right" : "btn btn-default btn-small pull-right";
	});
	CLE.Client.addFunction("getCellLinkLabel",function(cell, readonly) {
		if(cell.link_column && !readonly) {
			return "Link column";
		}
        if(!cell.y) {
			return "";
		}
		var fl = this.control.svc("FieldLink");
		if(fl.doesDataMatch(cell,this.control)) {
			return "<i class='icon-star icon-white'></i>";
		}
		if(readonly) {
			return "";
		}
		return "<i class='icon-star-empty'></i>";
	});
	
	CLE.Client.addFunction("linkCellColumn",function(index) {
		var cf = this.control.svc("CustomFunctions");

		for(var y=1;y<this.control.data.grid.length;y++) {
			var cellToProcess = this.control.data.grid[y][index];
			cf.customCall("toggleCellLink",[cellToProcess,true],this.control);
		}
	});
	CLE.Client.addFunction("toggleCellLink",function(cell,forceLink) {
        if(!cell.y) {
			return;
		}
		var linkFieldName = this.control.get('linkfield');
		var fl = this.control.svc("FieldLink");

		var mappings = fl.extractCellFields(linkFieldName,cell, this.control);
        if(!mappings) {
			return;
		}

        fl.populateMappedFields(mappings,cell);
	});

	CLE.Client.addFunction("getLinkLabel", function(row,ignoreUnlinked, linkDisplayField, linkIdField) {
		if(!linkIdField) {
			linkIdField = "id";
		}
		if(!row) {
			return "";
		}
		if(!row.id) {
			row = {"id":row}
		}
		var linkFieldName = this.control.get('linkfield');
		if(!has(linkFieldName)) {
			return "";
		}
		var linkField = get(linkFieldName);

		if(linkDisplayField && !row[linkDisplayField]) {
			return "Missing " + linkDisplayField;
		}
		$("#"+ linkFieldName + "-value").html(linkDisplayField ? row[linkDisplayField].label : row[linkIdField]);

		if(!linkField) {
			return ignoreUnlinked ? "" : "<i class='icon-star-empty'></i>";
		}
		if(linkField != row[linkIdField]) {
			return "";
		}

		return "<i class='icon-star icon-white'></i>";
	});


CLE.Client.toggleDataDisplay = function(fieldName) {
	var dataField = $('#' + fieldName + "-data-toggle");
	var buttonField = $('#' + fieldName + "-toggle-button");
	if(dataField.hasClass('hidden')) {
		buttonField.html("<i class='icon-eye-close'></i>&nbsp;Hide linked data");
		dataField.removeClass('hidden');
	}
	else {
		buttonField.html("<i class='icon-eye-open'></i>&nbsp;View linked data");
		dataField.addClass('hidden');
	}
}

CLE.Client.toggleDataEdit = function(fieldName) {
	var dataField = $('#' + fieldName + "-data-toggle");

	dataField.find('input').each(function() {
		var f = $(this);
		f.attr("disable",false);
		f.attr("readonly",false);
	});
	
}

CLE.Client.initModal = function(pim) {
	if(!ADD.Form.onModalReady[pim]) return;
	ADD.Form.onModalReady[pim].call(null);
	delete ADD.Form.onModalReady[pim];
}

CLE.Client.gotoNgPage = function(path) {
	var root = angular.element("body");
	root.scope().gotoPage(path);
}

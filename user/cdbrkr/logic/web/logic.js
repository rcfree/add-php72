Codebreaker={};
function generateUUID(){
    var d = new Date().getTime();
    var uuid = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
        var r = (d + Math.random()*16)%16 | 0;
        d = Math.floor(d/16);
        return (c=='x' ? r : (r&0x3|0x8)).toString(16);
    });
    return uuid;
}

	CLE.Client.addFunction("primary",function(rowId,isPrimary) {
		var submitData = {};
		submitData['__pim']=$scope.add.pim;
		submitData['__pid']=$scope.add.pid;
		submitData['__parent']=$scope.add.parentid;
		submitData['__action']='edit';
		submitData['__id']=rowId;
		submitData['__token']=generateUUID();
		submitData['primary_diagnosis']=isPrimary==0 ? 1 : 0;
		this.control.svc("DataAccess").storeData("/client/modal-submit",submitData,this.control,false,true);
	});

	CLE.Client.togglePushButton = function(button, parentFieldName,optionValue) {
		$(button).parents('table').find('button').removeClass('btn-primary'); 
		$(button).addClass('btn-primary');
		var ele = $('#' + parentFieldName + '_matrix_set');
		var ang = CLE.Client.element(parentFieldName + '_matrix_set');
		ang.scope().control.put("group",optionValue);
		ang.scope().refresh();
	};
	
	CLE.Client.addFunction("displayItems",function() {
		var dh = this.control.svc("DiagnosisHandler");

		if (!has('select_ward_spell')) {
			return;
		}
		var sourceId = get('select_ward_spell');

		dh.displayItems(control, sourceId);
	});

function MatrixSetController($scope,$element,DataAccess, CustomFunctions, DiagnosisHandler) {
	$scope.control = new CLE.Control($element, function() {
		return DataAccess.populateGrid($scope.control);
	});
	$scope.control.svc("DiagnosisHandler", DiagnosisHandler);

	$scope.refresh = function() {
		$scope.control.refresh();
	};

	$scope.customCall = function(name, args) {
		return CustomFunctions.customCall(name, args, $scope.control);
	};

	$scope.deleteItem = function(rowId) {
		DataAccess.deleteRecord(rowId,$scope.control,$scope.control.refresh);
	};

	$scope.toggle = function(code, selectedId) {
		DiagnosisHandler.toggleDiagnosis(code,selectedId, $scope.control, $scope.control.refresh);
	}
}
function SearchController($scope,$element,DiagnosisHandler, CustomFunctions) {
	$scope.control = new CLE.Control($element);
	$scope.control.svc("DiagnosisHandler", DiagnosisHandler);

	$scope.addSelected = function() {
		var code = get('comorbidity_search');
		if(!code || code.length==0) {
			ADD.Notice.showAlert("Error adding diagnosis","You must select a diagnosis from the list","OK");
			return;
		}
		$scope.toggle(code);
	}
    $scope.toggle = function(code, selectedId) {
        DiagnosisHandler.toggleDiagnosis(code,selectedId, $scope.control, $scope.control.refresh);
    }
}

function QualController($scope, $element, DiagnosisHandler, CustomFunctions) {
	$scope.control = new CLE.Control($element);

	$scope.control.data.noQualifiers=false;
	$scope.control.data.qualifiers = [];
	$scope.control.data.code="";
	$scope.control.svc("DiagnosisHandler", DiagnosisHandler);

	$scope.showQualifiers = function(code) {
		return DiagnosisHandler.showQualifiers(code,$scope.control,$element);
	};

	$scope.addDiagnosis = function() {
		$element.modal('hide');
		DiagnosisHandler.storeDiagnosis($scope.control);
	};

	$scope.customCall = function(name, args) {
		return CustomFunctions.customCall(name, args,$scope.control);
	}

}

function DiagnosisHandler($http, DataAccess) {
	return {
		"showQualifiers":function(code, control, element) {
			return $http.get("/api/qualifieritems", {"params": {"code": code}})
				.success(function (data) {
					if (data.error) {
						ADD.Notice.showAlert("Qualifier error", data.error_text, "OK");
						control.data.qualifierError = true;
						return
					}

					if (!data.data || data.data.length === 0) {
						control.data.noQualifiers = true;
						return;
					}
					control.data.noQualifiers = false;
					control.data.qualifiers = data.data;
					element.modal('show');
					control.data.code = code;
				});
		},
		"storeDiagnosis": function(control) {

			var submitData = control.fields;
			submitData['__pim']=control.get("pim");
			submitData['__pid']=control.get("pid");
			submitData['__parent']=control.get("parentid");
			submitData['__action']='create';
			submitData['__token']=generateUUID();
			submitData['icd10_code']=control.data.code;

			DataAccess.storeData("/client/modal-submit",submitData,control,false,true).then(function() {
				set('comorbidity_search','');
				set('__comorbidity_search_ac','');
			});
		},
		"displayItems":function(control, sourceId) {
			var params = {
				"pim": control.get("pim"),
				"sourceident": control.get("patientident"),
				"parentid": control.get("parentid"),
				"sourceid": sourceId,
				"sourcepim": control.get("sourcepim")
			};
			control.data.loadingButton = true;
			$http.get('/api/transfercb', {"params": params}).then(function (data, status, headers, config) {
				if (data.error) {
					control.data.loadingButton = false;
					control.data.buttonError = $("<div />").html(data.error_text).text();
					return null;
				}
				if (data.warning) {
					control.data.loadingButton = false;
					control.data.buttonWarning = $("<div />").html(data.warning_text).text();
					return null;
				}
				control.data.loadingButton = false;

				var ele = $('#comorb_results_grid');

				var ang = angular.element(ele);
				var angScope = ang.scope();
				if (data.data) {
					var admDate = data.data.data.detail.date_of_admission;
					if (admDate) {
						set('date_of_admission', admDate.value);
					}
				}
				angScope.refresh().then(function () {
					Codebreaker.updatePrimary(angScope.grid);
				});
			});
		},
		"toggleDiagnosis":function(code, selectedId, control) {
			if(selectedId) {
				DataAccess.deleteRecord(selectedId,control,function() {
					control.refresh();
					CLE.Client.refresh("comorb_results_grid");
				},true);
				return;
			}

			var qualElement = CLE.Client.element("qualifier-modal");
			var qualModal = qualElement.scope();
			var qmControl = qualModal.control;
			var that = this;
			return this.showQualifiers(code,qmControl,qualElement).success(function() {

				if(qmControl.data.noQualifiers== false || qmControl.data.qualifierError == true) {
					return;
				}
				qmControl.data.noQualifiers=false;
				control.data.code = code;
				control.fields.uhl_qualifier="";

				that.storeDiagnosis(control);
			});
		}
	}
}
angular.module('clinicle')
	.controller('QualController', ['$scope', '$element','DiagnosisHandler','CustomFunctions',QualController])
	.controller('SearchController', ['$scope', '$element','DiagnosisHandler','CustomFunctions','DataAccess',SearchController])
	.controller('MatrixSetController', ['$scope', '$element','DataAccess','CustomFunctions','DiagnosisHandler',MatrixSetController])
	.service('DiagnosisHandler',['$http','DataAccess',DiagnosisHandler]);

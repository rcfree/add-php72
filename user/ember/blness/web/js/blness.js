/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor. blness.js
 */

$(document).ready(function() {

    CLE.Client.addFunction("emberPrimary",function(rowId,isPrimary) {
        var submitData = {};
        var control = this.control;
        submitData['__pim']=control.get("pim");
        submitData['__pid']=control.get("pid");
        submitData['__parent']=control.get("parentid");
        submitData['__action']='edit';
        submitData['__id']=rowId;
        submitData['__token']=generateUUID();
        submitData['primary_diagnosis']=isPrimary==0 ? 1 : 0;

        var that = this;

        control.svc("DataAccess").storeData("/client/modal-submit",submitData,control,false,true).then(function() {
            var grid = that.control.data.grid;
            for(var g = 0; g<grid.length;g++) {
                var item = grid[g];

                var icd10_code = item.icd10_code.value;
                var qualifier = item.uhl_qualifier.value;
                var primaryDiagnosis = isPrimary === null ? item.primary_diagnosis.value : isPrimary;

                if(rowId && item.id!=rowId) {
                    continue;
                }

                if(primaryDiagnosis==1) {
                    set('primary_condition','');
                    continue;
                }

                var data = {"icd10_code":icd10_code,"qualifier":qualifier,"is_primary":isPrimary};

                $(document).trigger("primaryCondition",data);
            }

        });
    });

});
function EmberPatientsController($scope, $element, DataAccess) {
    $scope.control = new CLE.Control($element,function() {
        return DataAccess.populateGrid($scope.control);
    });

    $scope.refresh = function(onlyIfEmpty) {
        if(onlyIfEmpty && $scope.control.data.totalRows) {
            return;
        }
        $scope.control.refresh();
    };
    $scope.refreshOnEnter = function(keyEvent) {
        if(keyEvent.which != 13) {
            return;
        }
        $scope.refresh();
    }
    $scope.control.clearMessages();

    //$scope.refresh();
}
mainMod.controller('EmberPatientsController',['$scope', '$element', 'DataAccess',EmberPatientsController]);

EMBER = {
    "Blness": {
        "refreshVisits": function(visitName) {
            var ele = angular.element('#ember-patients-' + visitName);
            if(!ele) {
                return;
            }
            if(!ele.scope()) {
                return;
            }
            ele.scope().refresh(true);
        }
    }
};
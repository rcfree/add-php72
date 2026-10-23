$(document).ready(function() {
    CLE.Client.addFunction("deleteSearch",function(id) {
        var control = this.control;
        control.svc("DataAccess").manageGet("/search/definition", {"action":"delete", "id":id}, function(data) {
            control.refresh();
        });
    });
});
function PatientSearchGridController($scope, $element, DataAccess, PageHandler) {
    $scope.control = new CLE.Control($element,function() {
        return DataAccess.populateGrid($scope.control);
    });
    $scope.control.data.scope = $scope;
    $scope.refresh = function() {
        $scope.control.refresh();
    };

    $scope.gotoPage = function(path, ignoreHistory) {
        return PageHandler.gotoPage(path, $scope, ignoreHistory);
    };

    $scope.deletePatient = function(patientId) {
        return deletePatient(patientId);
    };

    $scope.purgePatient = function(patientId) {
        return purgePatient(patientId);
    };

    $scope.control.clearMessages();

    $scope.refresh();
}

function TopSearchController($scope) {
    $scope.search = function (keyEvent) {
        if (keyEvent.which != 13) {
            return;
        }
        console.log("keyEvent",keyEvent);
        $scope.$parent.save(keyEvent)
    }
}
angular.module('clinicle').controller('PatientSearchGridController', ['$scope', '$element','DataAccess', 'PageHandler', PatientSearchGridController])
.controller('TopSearchController', ['$scope', '$element', TopSearchController])

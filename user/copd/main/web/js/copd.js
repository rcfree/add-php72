/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor. blness.js
 */

function COPDReferralsController($scope, $element, DataAccess) {
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
    $scope.control.refresh()
}

function PRReferralsController($scope, $element, DataAccess) {
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
    $scope.control.refresh()
}
mainMod.controller('COPDReferralsController',['$scope', '$element', 'DataAccess',COPDReferralsController])
    .controller('PRReferralsController',['$scope', '$element', 'DataAccess',PRReferralsController]);

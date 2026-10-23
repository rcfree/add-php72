/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

ADD.Search={};
ADD.Search.openAdvancedTab = function() {
	var loc = document.getElementById('i2b2').src; 
}
function ResearchSearchController($scope,$element, ModalEntry, DataAccess) {
	$scope.control = new CLE.Control($element,function() {
		DataAccess.populateGrid($scope.control);
	});

	$scope.refresh = function() {
        $scope.control.refresh();
	};

    $scope.search = function() {
        $scope.control.refresh();
    };

	$scope.addItem = function(pim, patientId, studyId, title) {
		$scope.control.put("pid",patientId);
		$scope.control.put("parentid",studyId);
		ModalEntry.addItem(pim,$scope.control,title);
	};

	$scope.closeItem = function() {
		ModalEntry.closeModal($scope.control);
	};
	$scope.saveItem = function() {
		ModalEntry.saveModal("/client/modal-submit",false,$scope.control);
	}

}

angular.module('clinicle')
.controller('ResearchSearchController', ['$scope', '$element', 'ModalEntry', 'DataAccess',ResearchSearchController]);

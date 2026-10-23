function EncounterGridController($scope, $element, $interpolate, DataAccess, ModalEntry, FieldLink, CustomFunctions) {
	$scope.control = new CLE.Control($element,function() {
		return DataAccess.populateGrid($scope.control);
	});

	$scope.control.svc("FieldLink",FieldLink);
	$scope.control.svc("DataAccess",DataAccess);
	$scope.control.svc("CustomFunctions",CustomFunctions);

	$scope.refresh = function() {
		$scope.control.refresh();
	};

	$scope.customCall = function(name, args) {
		return CustomFunctions.customCall(name, args, $scope.control);
	}
	$scope.range = function(min, max, step){
		step = step || 1;
		var input = [];
		for (var i = min; i <= max; i += step) input.push(i);
		return input;
	  };
	$scope.refreshOther = function(otherId) {
		CLE.Client.refresh(otherId);
	};

	$scope.editItem = function(title,rowId, pim) {
		ModalEntry.editItem(title,rowId,pim,$scope.control, $scope.gridData);
	};
	$scope.viewItem = function(title,rowId, pim) {
		ModalEntry.viewItem(title,rowId,pim,$scope.control, $scope.gridData);

	};
	$scope.deleteItem = function(rowId,confirmDelete) {
		DataAccess.deleteRecord(rowId,$scope.control,function(data) {
			$scope.control.refresh();
		},confirmDelete);
	};
	$scope.closeItem = function() {
		ModalEntry.closeModal($scope.control);
	};
	$scope.saveItem = function() {
		ModalEntry.saveModal("/client/modal-submit",false,$scope.control);
	}
	
	$scope.openPage = function(parentId) {
		$scope.gotoPage($scope.control.get("openpage") + "?pid=" + $scope.control.get("pid") + "&eid=" + parentId);
	};

	$scope.renderCell = function(cell) {
		var cellPattern = $scope.control.get('custom_pattern');
		var intOutput = $interpolate("{{" + cellPattern + "}}",false,"html");
		return intOutput({'cell':cell});
	}

	$scope.control.clearMessages();

	$scope.refresh();
}

function AddItemController($scope,$element,ModalEntry, CustomFunctions, DataAccess) {
	$scope.control = new CLE.Control($element);
    $scope.control.svc("FieldLink",FieldLink);
    $scope.control.svc("DataAccess",DataAccess);
    $scope.control.svc("CustomFunctions",CustomFunctions);

    $scope.addItem = function(pim) {
		ModalEntry.addItem(pim,$scope.control, $scope.gridData);
	};
	$scope.closeItem = function() {
		ModalEntry.closeModal($scope.control,$scope.gridData);
	};
	$scope.saveItem = function() {
		ModalEntry.saveModal("/client/modal-submit",false,$scope.control, $scope.gridData);
	}
	$scope.saveItemAndAdd = function() {
		ModalEntry.saveModal("/client/modal-submit",true,$scope.control, $scope.gridData);
	}
	$scope.customCall = function(name, args) {
		return CustomFunctions.customCall(name, args,$scope.control);
	}
}

function EmptyController($scope) {}
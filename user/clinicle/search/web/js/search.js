$(document).ready(function() {
    CLE.Client.addFunction("deleteSearch",function(id) {
        var control = this.control;
        control.svc("DataAccess").manageGet("/search/definition", {"action":"delete", "id":id}, function(data) {
            control.refresh();
        });
    });
});
function SearchGridController($scope, $element, DataAccess, ModalEntry) {
    $scope.control = new CLE.Control($element,function() {
        return DataAccess.populateGrid($scope.control);
    });
    $scope.control.data.scope = $scope;
    $scope.refresh = function() {
        $scope.control.refresh();
    };
    $scope.addSearchGroup = function() {
        ModalEntry.addItem("search:group",$scope.control);
    };
    $scope.deleteSearchGroup = function(groupId) {
        DataAccess.manageGet("/search/group", {"id":groupId,"a":"delete"}, function(data) {
            $scope.control.refresh();
        },$scope.control);
    };

    $scope.addFilter = function(groupId) {
        $scope.control.put("parentid",groupId);
        ModalEntry.addItem("search:filter",$scope.control,"Add filter");
    };
    $scope.editAttrs = function(groupId) {
        $scope.control.put("parentid",groupId);
        ModalEntry.addItem("search:attrs",$scope.control,"Edit attributes");
    };
    $scope.searchOptions = function() {
        ModalEntry.addItem("search:options",$scope.control,"Search options");
    };
    $scope.refreshOther = function(otherId) {
        CLE.Client.refresh(otherId);
    };

    $scope.editItem = function(title,rowId, pim) {
        ModalEntry.editItem(title,rowId,pim,$scope.control);
    };
    $scope.viewItem = function(title,rowId, pim) {
        ModalEntry.viewItem(title,rowId,pim,$scope.control);

    };
    $scope.exportResults = function() {
        window.location.href = "/search/export";
    };
    $scope.emailResults = function() {
        DataAccess.manageGet("/search/email",{},function(data)
        {
            if(data.error) {
                ADD.Notice.showAlert("Email Error",data.error_text,"OK");
                return;
            }
            ADD.Notice.showAlert("Email Results", "Once your results have been compiled, you will receive an email with a link to your data file.", "OK")
        });
    };

    $scope.saveSearch = function(saveNew) {
        var id =get("search_id");
        if(saveNew) {
            set("search_id",null);
            id = null;
        }
        DataAccess.manageGet("/search/save", {
            "id": id,
            "title": get("search_title")
        }, function (data) {
            sdata = data.data
            if (sdata.error) {
                ADD.Notice.showAlert("Save error",data.error_text,"OK");
                return;
            }
            set("search_id",sdata.id);
            set("search_title", sdata.title);
            ADD.Notice.showAlert("Saved", "Search successfully " + sdata.action + "d", "OK")
        });
    };

    $scope.editFilter = function(groupId, rowId) {
        $scope.control.put("parentid",groupId);
        ModalEntry.editItem("Edit filter",rowId,"search:filter",$scope.control);
    };
    $scope.changeSettings = function(group) {
        $scope.control.put("parentid",group);
        ModalEntry.addItem("search:settings",$scope.control,"Change settings");
    };
    $scope.deleteFilter = function(groupName, fieldPos ,confirmDelete) {
        DataAccess.manageGet("/search/filter", {"pim":groupName,"a":"delete", "id":fieldPos}, function(data) {
            $scope.control.refresh();
        },$scope.control);
    };
    $scope.closeItem = function() {
        ModalEntry.closeModal($scope.control);
    };
    $scope.saveItem = function() {
        ModalEntry.saveModal("/client/modal-submit",false,$scope.control);
    };

    $scope.openPage = function(parentId) {
        $scope.gotoPage($scope.control.get("openpage") + "?pid=" + $scope.control.get("pid") + "&eid=" + parentId);
    };

    $scope.renderCell = function(cell) {
        var cellPattern = $scope.control.get('custom_pattern');
        var intOutput = $interpolate("{{" + cellPattern + "}}",false,"html");
        return intOutput({'cell':cell});
    };

    $scope.control.clearMessages();

    $scope.refresh();
}
function SearchResultHandler(DataAccess,$timeout, $interval) {
    return {
        "startSearch": function(control,outputType, updateFunction, successFunction) {
            control.gridError = null
            control.gridWarning = null
            control.gridMessage = null
            var params = control.all();
            if (params['includefields']) {
                var includefields = params['includefields'].split(";");
                for (var i = 0; i < includefields.length; i++) {
                    params[includefields[i]] = get(includefields[i]);
                }
            }
            params['output_type']=outputType;
            var that = this;

            if(!successFunction) {
                successFunction = function (data) {
                    if(!data['data']) {
                        if(data['error']) {
                            control.data.gridError = data['error_text'];
                        }
                        $interval.cancel(control.data.checkCall);
                        delete control.data.checkCall;
                        control.data.searchComplete = true;
                        return;
                    }
                    var sdata = data['data'];

                    control.put("search_id", sdata['search_id']);
                    control.put("filename",sdata['filename']);
                    control.data.loadingGrid = true;

                    control.data.checkCall = $interval(function() { that.checkSearch(control,updateFunction) },6000);
                }
            }
            DataAccess.manageGet(control.get("searchurl"),params, successFunction);
        },
        "checkSearch": function(control, updateFunction) {
            var params = {"search_id":control.get("search_id")};
            DataAccess.manageGet("/search/check",{"search_id":control.get("search_id")}, function (data) {
                if(!data['data']['complete']) {
                    control.data.searchComplete = false;
                    control.data.loadingGrid = true;

                }
                else {
                    $interval.cancel(control.data.checkCall);
                    delete control.data.checkCall;
                    control.data.searchComplete = true;
                }
            },control,function() {

            }).then(updateFunction);
        }
    }
}

function ResultGridController($scope, $element, $window, DataAccess, SearchResultHandler) {
    $scope.control = new CLE.Control($element, function() {
        var control = $scope.control;
        if(control.data.loadingGrid==true) {
            return;
        }
        $scope.viewResults();
    });
    $scope.viewResults = function() {
        SearchResultHandler.startSearch($scope.control, "view", function() {
            if(!$scope.control.data.searchComplete) {
                return;
            }
            DataAccess.populateGrid($scope.control)
        });
    }
    $scope.exportResults = function() {
        SearchResultHandler.startSearch($scope.control, "export", function() {
            if(!$scope.control.data.searchComplete) {
                return;
            }
            $window.location.href="/search/download?file=" + $scope.control.get("filename");
            $scope.control.data.loadingGrid=false;
            $scope.control.data.gridMessage="Export file successfully generated";
        });
    }
    $scope.emailResults = function() {
        SearchResultHandler.startSearch($scope.control, "email", function() {

        },function(data) {
            if(data.error) {
                ADD.Notice.showAlert("Email Error",data.error_text,"OK");
                return;
            }
            ADD.Notice.showAlert("Email Results", "Once your results have been compiled, you will receive an email with a link to your data file.", "OK")
        });
    };

    $scope.control.data.scope = $scope;
    $scope.refresh = function () {
        $scope.control.refresh();
    };
}

angular.module('clinicle').controller('SearchGridController', ['$scope', '$element','DataAccess', 'ModalEntry', SearchGridController])
    .controller('ResultGridController', ['$scope', '$element', '$window', 'DataAccess','SearchResultHandler', ResultGridController])
    .service('SearchResultHandler',['DataAccess','$timeout','$interval',SearchResultHandler]);

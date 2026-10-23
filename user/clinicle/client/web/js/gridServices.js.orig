CLE = {};
CLE.Control = function(element, refreshFunction) {
    var settings = {};
    if(!element) {
        throw("'element' parameter not provided to CLE.Control");
    }
    $.each(element[0].attributes, function () {
        var attrName = this.name;
        if (attrName.substr(0, 4) == "cle-") {
            settings[attrName.substr(4)] = this.value;
        }
    });
    if (element.attr("cle-refresh")) {
        settings.refresh = element.attr("cle-refresh").split(";");
    }
    settings.modalId = element.attr("id");

    return {
        "element":element,
        "refresh":function() {
            if(!refreshFunction) {
                return;
            }
            var that = this;
            refreshFunction.call(that);
        },
        "get": function (key) {
            return this.settings[key];
        },
        "all": function () {
            return this.settings;
        },
        "put": function (key, value) {
            this.settings[key] = value;
        },
        "has":function(key) {
            if (this.settings[key]) {
                return true;
            }
            return false;
        },
        "clearMessages":function() {

        },
        "settings": settings,
        "data":{},
        "getModalElement":function() {
            return $("#" + this.getModalId());
        },
        "getModalId":function() {
            return this.get("modalId") + "-modal";
        },
        "incrementPage":function() {
            if(!this.settings['page_no']) {
                this.settings['page_no']=1;
            }
            this.settings['page_no']++;
        },
        "decrementPage":function() {
            if(!this.settings['page_no']) {
                this.settings['page_no']=1;
            }
            this.settings['page_no']--;
        },
        "fields":{},
        "services":{},
        "nextPage": function() {
            this.incrementPage();
            this.refresh();
        },
        "previousPage": function() {
            this.decrementPage();
            this.refresh();
        },
        "svc":function(svcName,svc) {
            if(!svc) {
                return this.services[svcName];
            }
            this.services[svcName]=svc;
        }
    };

}

function CustomFunctions() {
    var functions = {};
    var gridActions = CLE.Client.getFunctionNames();

    for (var i = 0; i < gridActions.length; i++) {
        var actionName = gridActions[i];
        var funcDef = CLE.Client.getFunction(actionName);//.toString();
        //var func = eval("func = " + funcDef);
        functions[actionName] = funcDef;
    }

    return {
        "functions": functions,
        "customCall": function (funcName, args, control) {
            var that = {"control": control};
            if (!this.functions[funcName]) {
                throw("Custom function '" + funcName + "' was not found");
            }
            return this.functions[funcName].apply(that, args);
        }
    };
}
function DataAccess($http, CustomFunctions) {
    return {
        "http":$http,
        "populateGrid": function(control) {
            if(control.get("linkparent")) {
                var parentid = get(control.get("linkparent"));
                if (parentid == "") {
                    return;
                }
                control.put("linkparent", parentid);
            }

            var params = control.all();
            if(control.fields) {
                var dataFields = _.pairs(control.fields);
                for(var i=0;i<dataFields.length;i++) {
                    params[dataFields[i][0]]=dataFields[i][1];
                }
            }
            if (params) {
                delete params['id'];
            }
            if (!control.get("dataurl")) {
                control.put("dataurl", "/api/encounters");
            }

            control.data.loadingGrid = true;
            control.data.gridWarning = null;
            control.data.gridError = null;
            control.data.gridMessage = null;

            if (params['includefields']) {
                var includefields = params['includefields'].split(";");
                for (var i = 0; i < includefields.length; i++) {
                    params[includefields[i]] = get(includefields[i]);
                }
            }

            var that = this;
            return this.manageGet(control.get("dataurl"), params, function(response) {
                return that.gridDataUpdate(response,control);
            });
        },
        "gridDataUpdate": function(data, control) {
            if (!data.data) {
                control.data.grid = [];
            }
            else {
                control.data.grid = data['data'];
            }
            if (data.error) {
                control.data.gridError = "Error getting data:" + data.error_text;
            }
            if (data.warning) {
                control.data.gridWarning = data.warning_text;
            }
            if (data.message) {
                control.data.gridMessage = data.message_text;
            }
            if (data.debug) {
                control.data.debug = data.debug;
            }
            control.data.loadingGrid = false;
            control.data.gridRows = control.data.grid.length;

            if (!control.data.grid) {
                return;
            }
            if (control.data.grid[0]) {
                control.data.gridColumns = control.data.grid[0].length;
            }
            control.data.totalRows = data['total'];
            control.data.firstRow = data['first'];
            control.data.lastRow = data['last'];

            control.data.rowsPerPage = data['rows_per_page']

            var pageNo = control.get("page_no");
            if ((pageNo * control.data.rowsPerPage) < control.data.totalRows) {
                control.data.moreRows = true;
            }
            else {
                control.data.moreRows = false;
            }
            if (pageNo > 1) {
                control.data.lessRows = true;
            }
            else {
                control.data.lessRows = false;
            }
        },
        "deleteRecord":function(recordId, control, successFunction, hideConfirm) {
            if(!hideConfirm) {
                if (!confirm("Are you sure you want to delete this item?")) {
                    return;
                }
            }
            if(control.data.clearMessages) {
                control.data.clearMessages();
            }
            var params = {"action": "delete", "encounter_id": recordId, "pim": control.get("pim")};
            if(!successFunction) {
                successFunction=function() { return; };
            }

            this.manageGet("/api/encounter", params, successFunction, control);
        },
        "manageGet":function(url,params,successFunction, control) {
            params['ts'] = ADD.Util.ts();
            var ctrl = control;
            return $http.get(url, {"params": params}).success(successFunction).error(function (data, status, headers, config) {
                ctrl.data.gridError = "Sorry a network error means data could not be retrieved";
                ctrl.data.loadingGrid = false;
                ctrl.data.grid = [];
                ctrl.data.gridRows = 0;
            });
        },
        "storeData" :function(url,submitData,control,showStopWait,noMessages) {
            return $http({
                url:url,
                headers:{"Content-Type":"application/x-www-form-urlencoded"},
                method:"POST",
                data:$.param(submitData)
            })
            .success(function(data, textStatus) {
                if(!noMessages && data.message) {
                    //ADD.Notice.removeAllMessages();
                    ADD.Notice.addMessage(data.message_text);
                }

                if(data.warning) {
                    ADD.Notice.addFieldWarning("ajax",data.warning_text);
                }
                if(data.debug) {
                    existingDebug = $(".debug").html();
                    $(".debug").html(existingDebug + data.debug);
                }
                if(data.error) {
                    ADD.Notice.stopWait();
                    ADD.Notice.showAlert("Error storing data",data.error_text,"OK");
                    return;
                }

                if(data.data) {
                    if(control.has("linkfield") && data.data.id) {
                        CustomFunctions.customCall("toggleLink",[data.data.id,true],control);
                    }
                    if(control.has("linkparent") && data.data.id) {
                        CLE.Client.element(control.get("linkparent")).scope().control.put("value", data.data.id);
                    }
                }
                if(control.refresh) {
                    control.refresh();
                }

                if(showStopWait) {
                    ADD.Notice.stopWait();
                }
                var refreshIds = control.get("refresh");

                if(!refreshIds) return;

                for(var i=0;i<refreshIds.length;i++) {
                    CLE.Client.refresh(refreshIds[i]);
                }
                control.data.saving=false;
            });
        }
    }
}
function ModalEntry($http, DataAccess) {
    return {
        "viewItem":function(title,rowId,pim,control) {
            var params = {"pim":pim,"pid":control.get("pid"),"a":"view","parentid":control.get("parentid"),"id":rowId};
            var url = "/client/modal-content";

            this.loadModal(url,params,title,false,control);
        },
        "editItem":function(title,rowId,pim,control) {
            var params = {"pim":pim,"pid":control.get("pid"),"a":"edit","parentid":control.get("parentid"),"id":rowId};
            var url = "/client/modal-content";

            this.loadModal(url,params,title, false, control);
        },
        "addItem":function(pim,control,title) {
            var params = {
                "pid": control.get("pid"),
                "a": "create",
                "parentid": control.get("parentid")
            };
            if (pim) {
                params['pim'] = pim;
            }
            else {
                params['pim'] = control.get("pim")
            }
            if (!params.pim || params.pim.length == 0) {
                ADD.Notice.showAlert("Item error", "Please select an option from the list", "OK");
                return;
            }
            params['ts'] = ADD.Util.ts();
            var url = "/client/modal-content";
            this.loadModal(url, params, title, false, control);
        },
        "loadModal":function(url,params,title, noSave,control) {
            var modalId = control.getModalId();
            ADD.Notice.setMessageElement(modalId + "-messages");
            ADD.Notice.removeAllWarnings();
            ADD.Notice.removeAllErrors();

            var modalElement = control.getModalElement();
            ADD.Notice.setMessageElement(modalId + "-messages");

            if(!title) {
                title = control.get("title");
            }

            ADD.Form.disableDirty = true;

            ADD.Form.currentFormId="modal-form";
            ADD.Form.isModal=true;
            var screenHeight = jQuery(window).height();
            var screenWidth = jQuery(window).width();

            var height=screenHeight-150;
            var width=screenWidth-150;

            modalElement.css('margin-left',(width/2)*-1);
            modalElement.css('width',width);
            modalElement.css('height',height);
            modalElement.css('margin-top',(screenHeight-height)/2);
            modalElement.find(' .modal-body').css('height',height-150);
            modalElement.find(' .modal-body').css('max-height',height-150);

            $('#' + modalId + ' .modal-header h3').html(title);
            if(noSave) {
                $('#' + modalId + " .save-button").hide();
            }
            else {
                $('#' + modalId + " .save-button").show();
            }

            control.data.loadingModal=true;
            modalElement.find(".modal-content").html("");
            if(modalElement.length==0) {
                throw "Modal element with ID " + modalId + " was not found";
            }
            modalElement.modal({'backdrop':'static','keyboard':false});
            modalElement.modal('show');
            modalElement.find(".modal-footer").hide();

            var that = this;
            params['ts'] = ADD.Util.ts();
            $http({url:"/client/modal-content",method:"GET",headers:{"Content-Type":"text/html"},params:params}).
            success(function(data, status, headers, config) {
                modalElement.find(".modal-content").html(data);
                that.initModal(params.pim);
                control.data.loadingModal=false;
                modalElement.find(".modal-footer").show();
            });

        },
        "initModal": function(pim) {
            if(!ADD.Form.onModalReady[pim]) return;
            ADD.Form.onModalReady[pim].call(null);
            delete ADD.Form.onModalReady[pim];
        },
        "closeModal":function(control) {
            var addModal = control.getModalElement();
            addModal.modal("hide");
            $("form#" + ADD.Form.currentFormId).remove();
            ADD.clearFeatures(ADD.Form.currentFormId);

            ADD.Form.currentFormId="content-form";
            ADD.Notice.messageElement=null;
            ADD.Form.disableDirty=true;
            addModal.children(".modal-content").html("");
        },
        "saveModal": function(url,addAnother, control) {
            control.data.saving=true;
            var message = "Unable to save data, please correct the field error(s).";
            var modalId = control.getModalId();
            var modalElement = control.getModalElement();

            var valid = ADD.Form.isValid();
            if(!valid) {
                modalElement.show();
                control.data.saving=false;
                return;
            }
            var submitData = $("#" + modalId + " form").serializeArray();
            if(!addAnother) {
                ADD.Form.isModal=false;
                modalElement.modal("hide");

                ADD.Form.disableDirty=false;
                this.closeModal(control);

                ADD.Notice.startWait();
            }
            DataAccess.storeData(url,submitData,control,true,true);
            if(addAnother) {
                this.addItem(control.get("pim"),control);
            }
        }
    }
}


/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function PageData() {
	return {
		"pagePath": "/",
		"prevPatient": null,
		"loading": {},
		"linkClicked":false,
		"buttonSubmit":false,
		"patientSelected":false
	};
}

function PageHandler($http, $compile, PageData) {
	var siteName = $("h2.sys-title").html();
	var siteTag = $("p.sys-tag");
	var siteTagText = "";
	if(siteTag.length>0 && siteTag.html()!="") {
		siteTagText = " [" + siteTag.html() + "]";
	}
	return {
	"cleanupPanel":function(panel) {
			$('#' + panel + '-panel div[ng-controller]').each(function() {
				var mcElement = angular.element(this);
				mcElement.scope().$destroy();
			});
		},
		"gotoPage": function (path, scope, ignoreHistory) {
			if(this.isDirtyForm(path)) {
				return;
			}
			if(PageData.linkClicked) {
			    return;
            }
            PageData.linkClicked = true;

            ADD.Record.clearTimedRefresh();
			ADD.Form.disableDirty = false;
			var mainEle = $("#main-panel");
			mainEle.fadeTo("fast", "0.5");

			PageData.pagePath = path;
			
			ADD.clearFeatures('#content-form');
			
			var promise = this.updatePanel("main",scope);
			
			if (!ignoreHistory) {
				promise.then(function () {
					var title = siteName + siteTagText + " - " + $('p.lead').html();
					if (window.onpopstate) {
						History.pushState({"url": path}, title, "");
					}
					else {
						var locHash = window.location.hash;
						if(locHash.length>0) {
							locHash = locHash.substr(1);
						}
						History.pushState({"url":path}, title, path);
					}
					document.title = title;
					//alert("push state:" + path);
				});
			}

			var that = this;
			promise.then(function() {
				that.dealWithPatientChanged(promise,scope);

				PageData.linkClicked = false;
				ADD.Notice.stopWait();
				$(".modal").modal('hide');
				$(window).scrollTop(0);
			});
		},
		"refreshPage":function(scope) {
			var path = PageData.pagePath;
			this.gotoPage(path,scope,true);
		},
		"isDirtyForm":function(path) {
			if (ADD.Form.disableDirty)
				return false;
			if (ADD.Notice.hasErrors())
				return false;
			
			if($("#main-panel .dirty").length==0) {
				return false;
			}
			$('#modal-confirm-save').modal('show');
			$.DirtyForms.redirectPath=path;
			return true;
		},
		"gotoHome":function(scope) {
			var homePath = "/";
			if(PageData.pagePath) {
				var url = new URI(PageData.pagePath);
				var query = url.query(true);
				if(query.pid) {
					homePath="/?pid=" + query.pid;
				}
			}
			scope.gotoPage(homePath,scope);
			$('#side-panel .active').removeClass('active');
		},
		"updatePanel": function (panel, scope) {
			var panelEle = $("#" + panel + "-panel");

			if(panel!="main") {
				panelEle.fadeTo("fast", "0.5");
			}
			this.cleanupPanel(panel);
			var url = new URI(PageData.pagePath);
			url.addQuery("panel", panel);
			url.addQuery("pagePath", PageData.pagePath);
			url.addQuery("ts",ADD.Util.ts());
			
			var that = this;
			PageData.loading[panel] = true;
			var promise = $http({
				url: "/client/panel-content",
				headers: {"Content-Type": "application/x-www-form-urlencoded"},
				method: "GET",
				params: url.query(true)
			});

			promise.success(function (data, textStatus) {
				that.compileContent(panel,data,scope);
			});

			promise.error(function(data) {
				that.compileContent(panel,"<div class='alert alert-error'><pre>" + data + "</pre></div>" )
			});
			return promise;
		},
		"hasPatientChanged": function () {
			var url = new URI(PageData.pagePath);
			var search = url.search(true);

			if(PageData.prevPatient && !search.pid) {
				PageData.prevPatient = search.pid;
				return true;
			}
			if (PageData.prevPatient && PageData.prevPatient === search.pid.toString()) {
				return false;
			}
				
			PageData.prevPatient = search.pid;
			return true;
		},
		"compileContent": function (panel, html, scope) {
			var ele = $("#" + panel + "-panel");
			ele.html(html);
			this.dealWithRedirect();
			
			this.compileHtml(ele.contents(),scope);
			ele.fadeTo("fast","1");

			PageData.buttonSubmit = false;
			PageData.loading[panel] = false;
			this.dealWithPatientSelected();
			this.dealWithMenuRefresh();
			this.dealWithLoggedOut();
			CLE.Client.initModal("content");
		},
		"compileHtml":function(htmlContent,scope) {
			$compile(htmlContent)(scope);
		},
		"dealWithRedirect": function () {
			var redirect = $('#ng-redirect');
			if (redirect.length > 0) {
				PageData.pagePath = redirect.val();
			}
		},
		"dealWithMenuRefresh": function () {
			var ngCurrent = $('#ng-current');
			var ngMenu = $('#ng-menu');
			if (ngMenu.length == 0) {
				return;
			}
			var menuRefresh = ngMenu.val();
			var menu = CLE.Client.element(menuRefresh + "-domain-menu");
			if (menu) {
				menu.scope().control.put("current",ngCurrent.val());
				menu.scope().refresh();
			}
		},
		"dealWithLoggedOut": function () {
			var ngLoggedout = $('#ng-loggedout');
			if (ngLoggedout.length === 0) {
				return;
			}
			jQuery(document).idleTimer.enabled=false;
			$.DirtyForms.disable();
			ADD.User.AutoLogout.autoLogout(); 
		},
		"hasError": function () {
			var ngLoggedout = $('#ng-error');
			if (ngLoggedout.length === 0) {
				return false;
			}
			return true;
		},
		"dealWithPatientSelected":function() {
			if(this.hasError()) {
				PageData.patientSelected=true;
				return;
			}
			var url = new URI(PageData.pagePath);
			var search = url.search(true);
			if(search.pid) {
				PageData.patientSelected = true;
				return;
			}
			PageData.patientSelected = false;
		},
		"dealWithPatientChanged":function(promise, scope) {
			var forceReload = false;
			var ngReload = $('#ng-reload');
			if (ngReload.length > 0) {
				forceReload = true;
			}
			if (!forceReload && !this.hasPatientChanged()) {
				return;
			}
			var that = this;

			promise.then(function () {
				that.updatePanel("side",scope);
				that.updatePanel("top",scope);
			});
		}
	}

}
function FormHandler($http, $window, PageData, PageHandler) {
	return {
		"collectFormData":function($event) {
			if(!$event) {
				var submitForm = $("#content-form");
				
			}
			else {
				var submitElement = $($event.currentTarget);
				var submitForm = submitElement.closest("form");
			}
			
			var formData = submitForm.serialize();
			if (submitForm.attr("action")) {
				PageData.pagePath = submitForm.attr("action");

				if(history.pushState) {
					history.pushState({"url": submitForm.attr("action")}, "ADD");
				}
			}

			if ($event && submitElement.attr("name")) {
				formData = formData + "&" + submitElement.attr("name") + "=" + submitElement.val();
			}

			return [formData,submitForm];
		},
		"getPostMethodPromise":function(formData,pageParams) {
			return $http({
				url: "/client/standard-form",
				headers: {"Content-Type": "application/x-www-form-urlencoded"},
				method: "POST",
				data: formData,
				params: pageParams
			});
		},
		"getGetMethodPromise":function(formData,pageParams) {
			var pagePath = PageData.pagePath + "?" + encodeURIComponent(formData);
			PageData.pagePath = pagePath;
			return $http({
				url: "/client/standard-form?pagePath="+pagePath,
				method: "GET"
			});
		},
		"submit": function ($event, scope) {
			if (!ADD.Form.isValid()) {
				PageData.buttonSubmit = false;
				return;
			}
			PageData.buttonSubmit = true;
			
			var url = new URI(PageData.pagePath);
			PageData.loading['main'] = true;
			$("#main-panel").fadeTo("fast","0.5");
            var pageParams = url.search(true);
			var submitIcon;
			if ($event) {
				var submitElement = $($event.currentTarget);
				
				var submitIcon = submitElement.find("i");
				if(submitIcon.length>0) {
					var iconClass = submitIcon.attr("class");

					var ajaxGif = "ajax-button-black.gif";

					if(iconClass.search("icon-white")!==-1) {
						ajaxGif = "ajax-button-white.gif";
					}
					submitIcon.replaceWith("<img style='width:14px;height:14px;' src='/module/clinicle/client/images/" + ajaxGif + "'/>");
				}
			}
			var temp = this.collectFormData($event);
			var formData = temp[0];
			var submitForm = temp[1];
			pageParams['ts'] = ADD.Util.ts();
			pageParams['pagePath'] = PageData.pagePath;

			var methodType = submitForm.attr("method");

			if(methodType && methodType.toUpperCase()=="GET") {
				var methodPromise = this.getGetMethodPromise(formData,pageParams);
			}
			else {
				var methodPromise = this.getPostMethodPromise(formData,pageParams);
			}
			
			methodPromise.success(function (data, textStatus) {
				if($event) {
					var submitIcon = submitElement.find("img");
					if(submitIcon) {
						submitIcon.replaceWith("<i class='" + iconClass + "'></i>");
					}
				}
				ADD.clearFeatures('#content-form');

				PageHandler.compileContent("main",data,scope);
				$("#main-panel").fadeTo("fast","1");
			});
			PageHandler.dealWithPatientChanged(methodPromise,scope);
			
			return methodPromise;
		}
	};
}
function RootController($scope, $window, $element, PageData, PageHandler, FormHandler) {
	$scope.isPanelLoading = function (panel) {
		if (PageData.loading[panel] == true) {
			return true;
		} else {
			return false;
		}
	};
	$scope.hideLoader = function (panel) {
		PageData.loading[panel] = false;
	};

	$scope.save = function ($event) {
		return FormHandler.submit($event, $scope);
	};
	$scope.gotoPage = function(path, ignoreHistory) {
		return PageHandler.gotoPage(path, $scope, ignoreHistory);
	};
	
	$scope.gotoHome = function() {
		return PageHandler.gotoHome($scope);
	};
	$scope.refreshPage = function() {
		return PageHandler.refreshPage($scope);
	};
	$scope.isPatientSelected = function () {
		return PageData.patientSelected;
	};
	$scope.hasBeenSubmitted = function() {
		return PageData.buttonSubmit;
	};
	$scope.hasBeenClicked = function() {
		return PageData.linkClicked;
	};
	$scope.getPageData = function() {
		return PageData;
	};
	$scope.compileHtml = function(html) {
		PageHandler.compileHtml(html,$scope);
	}
	var path = "";
	$scope.gotoPage(path);
}
function EmbeddedFormController($scope, $http, $element,ModalEntry) {
	$scope.control = new CLE.Control($element,function() {
		var params = $scope.control.all();
		params['ts'] = ADD.Util.ts();

		$http({
			url: "/client/modal-content",
			method: "GET",
			headers: {"Content-Type": "text/html"},
			params: params
		}).success(function (data, status, headers, config) {
			$element.find(".form-content").html(data);
			ModalEntry.initModal($scope.control.get("pim"));
			var refreshIds = $scope.control.get("refresh");

			if (!refreshIds)
				return;

			for (var i = 0; i < refreshIds.length; i++) {
				CLE.Client.refresh(refreshIds[i]);
			}
		});
	});
	
	$scope.refresh = function () {
		$scope.control.refresh();
	};

	$scope.refresh();
}

function MenuController($scope, $http, $element, $compile, PageData, ModalEntry) {
	$scope.control = new CLE.Control($element);

	$scope.refresh = function () {
		$element.find(".accordion-inner").append("<li><img src='/module/clinicle/client/images/ajax-loader.gif'/></li>");
		$('#options .active').removeClass('active');
		var params = $scope.control.all();
		var url = new URI(PageData.pagePath);
		var urlQuery = url.query(true);
		params['pagePath'] = PageData.pagePath;

		params['ts'] = ADD.Util.ts();
		$http({url: "/client/menu-content", method: "GET", headers: {"Content-Type": "text/html"}, params: params}).
		success(function (data, status, headers, config) {
			$element.html(data);
			$compile($element.contents())($scope);
			ModalEntry.initModal($scope.control.get("pagePath"));
		});
	};
}


function FieldController($scope, $http, $element) {
	$scope.control = new CLE.Control($element,function () {
		var params = $scope.control.all();
		params['ts'] = ADD.Util.ts();
		$http({url: "/client/field-content", method: "GET", headers: {"Content-Type": "text/html"}, params: params}).
		success(function (data, status, headers, config) {
			$element.html(data);
		});
	});

	$scope.refresh = function () {
		$scope.control.refresh();
	};
}

(function(window,undefined){

	// Bind to StateChange Event
	History.Adapter.bind(window, 'statechange',function(){ // Note: We are using statechange instead of popstate
		var state = History.getState(); // Note: We are using History.getState() instead of event.state
		if(!state) {
			return;
		}
		document.title = state.title;
        if(!state.data.url) {
            return;
        }
		angular.element($('body')).scope().gotoPage(state.data.url, true);
	});

})(window);

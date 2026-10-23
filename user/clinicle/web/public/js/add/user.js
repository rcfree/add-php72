ADD.User.AutoLogout = function() {
	function timedLogout() {
			jQuery(document).idleTimer.enabled=false;
			
			if($(".modal").is(":visible")) {
				$('modal').modal('hide');
			}
			var encForm = jQuery("#content-form");
			var isDirty = encForm.dirtyForms('isDirty');
				
				
			if (!isDirty) {
				autoLogout();
				return;
			}

			set('__autosave',1);
			$('#content-form :input').blur();

			if ($('.error').length) {
				set('__invalid',1);
			}
			jQuery(document).idleTimer.enabled=false;

			$.DirtyForms.disable();

			angular.element("body").scope().save().then(
				function() {
					autoLogout();
				}
			);

	};
	function autoLogout() {
		ADD.User.AutoLogout.autoLogout();
	};
	function showLogoutBox() {
		var message = "Sorry you have been logged out because you have been inactive for over 30 minutes. <br/>Click 'Log In' to log back in or close this window";
		ADD.Notice.showAlert("Auto Logout",message,"Log In", "/auth/login?dest=" + encodeURIComponent(window.location.href));
		$.DirtyForms.disable();

		//prevent users moving out of modal
		$('.modal-backdrop').unbind('click');
	}
	function checkLoggedIn() {
		jQuery.ajax({
			url:"/auth/loggedin",
			error: function (xhr, textStatus, errorThrown) {
				if(xhr.status==0) return;
				ADD.Notice.showAlert("Unexpected HTTP error","HTTP error " + xhr.status + "<br/>" + errorThrown);
			},
			success: function(data, textStatus) {
				if(!data['logged_in']) {
					showLogoutBox();
				}
			}
		});
	}
	return {
		"setup":function(secondsToWait) {
			var milliSecs = secondsToWait * 1000;
			$.idleTimer(milliSecs);
			$(document).on('idle.idleTimer', function(){ timedLogout() });
			var date = new Date();
			window.autoLogoutTime = date.getTime() + milliSecs + 1000;
		},
		"autoLogout":function() {
			$(".modal").modal('hide');
			var that = this;
			jQuery.ajax({
					url:"/auth/logout?" + ADD.Util.ts(),
					method:"GET",
					error: function (XMLHttpRequest, textStatus, errorThrown) {
							ADD.Notice.showAlert("Unexpected error","Error " + errorThrown);
					},
					success: function(data, textStatus) {
						$(document).off('idle.idleTimer');
					}
			}).then(function() {
				showLogoutBox();
			});
		}
	}
}();
ADD.Record = function() {
	return {
		"refreshPage": function() {
			angular.element("body").scope().refreshPage();
		},
		"setupTimedRefresh": function (seconds) {
			var that = this;
			this.timeoutId = setTimeout(function() {
				that.refreshPage();
			}, seconds * 1000);
		},
		"clearTimedRefresh":function() {
			if(!this.timeoutId) {
				return;
			}
			clearTimeout(this.timeoutId);
		}
	}
}();
function selectAll(jQuerySel) {
    $(jQuerySel).prop("checked",true);
}
function unselectAll(jQuerySel) {
    $(jQuerySel).prop("checked", false);
}
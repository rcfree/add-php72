ADD.Feature.ModalEntry = function() {
	return {
		"action": function(action) {
			if (!this.attrs.ajax)
				this.attrs.ajax = {"path": "/api/enc", "query": {}};
			screenHeight = jQuery(window).height();
			width = 800;
			height = 400;
			if (screenHeight <= 600)
				height = screenHeight - 150

			jQuery('.modal').css('margin-left', (width / 2) * -1);
			jQuery('.modal').css('margin-top', (screenHeight - height - 100) / 2);
			jQuery('.modal-body').css('height', height - 100);
			ADD.Notice.messageElement = "modal-messages-" + this.fieldName;

			elementId = "modal-" + this.fieldName;

			var capitalisedAction = action.substring(0, 1).toUpperCase() + action.substring(1);
			if (capitalisedAction == "Create")
				capitalisedAction = "Add";
			var heading = capitalisedAction + " " + this.attrs.description;
			ADD.Notice.removeAllErrors();
			jQuery('#' + elementId + ' .modal-header h3').html(heading);
			this.attrs.saveButtonId = this.fieldName + '_save_btn';
			this.attrs.closeButtonId = this.fieldName + '_close_btn';
			jQuery('#' + this.fieldName + '_save_btn').click(function() {
				modalSave(options)
			});
			jQuery('#' + this.fieldName + '_close_btn').click(function() {
				jQuery("#" + elementId).modal("hide");
			});

			if (action == 'delete') {
				var yn = confirm("Are you sure you wish to delete the  " + this.attrs.description + "?");
				if (!yn)
					return false;
			}
			set('__action', action)
			query = this.attrs.ajax.query;
			query.action = action;
			query.id = this.attrs.id;
			query.lid = this.attrs.lid;
			query.fid = this.attrs.fid;
			query.pid = this.attrs.pid;
			jQuery.ajax({
				url: this.attrs.ajax.path,
				data: query,
				dataType: "json",
				error: function(XMLHttpRequest, textStatus, errorThrown) {
					alert("There was an error attempting to get " + this.attrs.ajax.path);
				},
				success: function(data, textStatus) {
					ADD.Form.ignoreClearContent = true;
					if (data.error) {
						alert(data.error);
						return;
					}
					jQuery.each(data, function(index, value) {

						currField = field(index);

						set(index, value);
						currField.change();
						if (ADD.has(index)) {
							var ops = ADD.get(index);
							for (var o = 0; o < ops.length; o++) {
								var currOp = ops[o];
								if (currOp.ajaxGet) {
									currOp.value = value;
									currOp['ajaxGet'].call(currOp, value);
								}
							}
						}
					});


					jQuery('[no-view="1"]').each(function(index) {
						var ele = jQuery(this);
						if (action == 'view')
							ele.hide();
						else
							ele.show();
					});

					jQuery('.disable-on-update :input').each(function(index) {
						var ele = jQuery(this);
						if (action == 'view' || action == 'edit') {
							disable(ele.attr("name"));
						}
						else {
							enable(ele.attr("name"));
						}
					});

					if (action == 'view') {
						jQuery('#' + elementId + ' :input').each(function(index) {
							var ele = jQuery(this);
							//ele.attr('disabled','disabled');
							ele.attr('view-disabled', '1');
						});
						jQuery('#' + this.fieldName + '_save_btn').hide();
						jQuery('.clear-link').hide();
					}
					else {
						jQuery('[view-disabled="1"]').each(function(index) {
							var ele = jQuery(this);
							ele.removeAttr('disabled');
						});
						jQuery('#' + this.fieldName + '_save_btn').show();
						jQuery('.clear-link').show();
					}

					if (action != 'delete') {
						showEntry.call(null, data);
					}
					else {
						window.location.reload();
					}
					ADD.Form.ignoreClearContent = false;
				}
			});
			var that = this;
			var showEntry = function(data) {
				if (that.attrs.beforeFunctionName)
					that.attrs.beforeFunctionName();
				jQuery('#modal-' + that.fieldName).modal({'backdrop': 'static', 'keyboard': false});
				jQuery('#modal-' + that.fieldName).modal('show');
			}
		}
	};
}
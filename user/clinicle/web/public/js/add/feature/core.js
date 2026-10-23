
ADD.Feature.Condition = function() {
	function _conditionTrue(op, obs, exp) {
		if (op == "eq") {
			if (_.isObject(obs)) {

				for (var e = 0; e < exp.length; e++) {
					if (obs[exp[e]])
						return true;
				}
				return false;
			}

			if (jQuery.inArray(obs, exp) >= 0)
				return true;
		}
		if (op == "any") {
			if (obs && obs.length > 0)
				return true;
		}
		obs = parseFloat(obs);
		if (op == "gt") {
			if (obs > exp[0])
				return true;
		}
		if (op == "lt") {
			if (obs < exp[0])
				return true;
		}
		if (op == "bt") {
			if (obs >= exp[0] && obs <= exp[1])
				return true;
		}

		return false;
	}
	;
	return {
		"change": function() {
			if (this.disabled)
				return;
			this._runCondition();
			this.oldVal = get(this.fieldName);
		},
		"init": function() {
			this.oldVal = get(this.fieldName);

			var fld = field(this.fieldName);
			if (!fld.length) {
				fld = jQuery('[name^="' + this.fieldName + '["]');
			}

			this._runCondition(true);
		},
		"_runCondition": function(atStart) {
			var fieldVal = get(this.fieldName);
			this.FieldConfirm = new ADD.Feature.Condition.FieldConfirm(this);

			for (var i = 0; i < this.attrs.length; i++) {
				option = this.attrs[i];
				targets = option.target;
				conditionTrue = _conditionTrue(option.op, fieldVal, option.test);
				if (conditionTrue) {
					if (option.action == "hide") {
						this.FieldConfirm._confirmFieldHide(targets, atStart, option.ignoreClearContent);
						continue;
					}
					for (var t = 0; t < targets.length; t++) {
						if (option.action == "show") {
							jQuery(".content-" + targets[t]).show();
						}
						else if (option.action == "enable") {
							enable(targets[t]);
						}
						else if (option.action == "disable") {
							disable(targets[t]);
						}
						else if (option.action == "update") {
							set_if_empty(targets[t], evaluate(option.value))
						}
						else if (option.action == "clear") {
							if (atStart)
								continue;
							clear(targets[t]);
						}
						else if (option.action == "set") {
							set(targets[t], evaluate(option.value));
						}
						else if (option.action == "set_if_empty") {
							set_if_empty(targets[t], evaluate(option.value));
						}
						else {
							throw option.action + " is not a recognised condition action";
						}
					}
				}
				else {
					if (option.action == "show") {
						this.FieldConfirm._confirmFieldHide(targets, atStart, option.ignoreClearContent);
						continue;
					}

					for (var t = 0; t < targets.length; t++) {
						if (option.action == "hide") {
							jQuery(".content-" + targets[t]).show();
						}
						else if (option.action == "enable") {
							disable(targets[t]);
						}
						else if (option.action == "disable") {
							enable(targets[t]);
						}
					}
				}
			}
		}

	}
};


ADD.Feature.Condition.FieldConfirm = function(_DefineCondition) {
	var DC = _DefineCondition;
	return {
		"_confirmFieldHide": function(targets, atStart, ignoreClearContent) {
			var confirm = true;
			if (ADD.Form.ignoreClearContent || ignoreClearContent) {
				confirm = true;
			}
			else {
				confirm = this._confirmContentClear(targets, atStart);
			}

			if (confirm) {
				for (var t = 0; t < targets.length; t++) {
					jQuery(".content-" + targets[t]).hide();
				}
			}
			else {
				for (var t = 0; t < targets.length; t++) {
					jQuery(".content-" + targets[t]).show();
				}
			}
		},
		"_confirmContentClear": function(targetNames, atStart) {
			var fieldList = [];

			for (var t = 0; t < targetNames.length; t++) {
				var inputsInContent = jQuery(".content-" + targetNames[t] + " :input");
				var visibleInputs = inputsInContent.filter(":visible");

				for (var f = 0; f < visibleInputs.length; f++) {
					var vinput = jQuery(visibleInputs[f]);
					var inputName = vinput.attr("name");
					if (inputName == null)
						continue;
					var vval = get(inputName);

					if (vval && vval.length > 0 && !vinput.attr("readonly")) {
						fieldList.push(inputName);
					}
				}
			}
			if (fieldList.length == 0)
				return true;

			var uniqueNameMap = {};
			for (var c = 0; c < fieldList.length; c++) {
				uniqueNameMap[fieldList[c]] = true;
			}
			var text = "fields";
			var valText = "values";
			var uniqueNames = _.keys(uniqueNameMap);
			if (uniqueNames.length == 1) {
				text = "field";
				valText = "value";
			}
			var uniqueLabels = [];
			for (var c = 0; c < uniqueNames.length; c++) {
				uniqueLabels.push(label(uniqueNames[c]));
			}

			var fieldName = DC.fieldName;

			if (atStart) {
				ADD.Notice.addFieldWarning(fieldName, "Please change the value of '" + label(fieldName) + "'. It is causing '" + uniqueLabels.join("','") + "' to be hidden.", fieldName);
				content(fieldName).addClass('control-group warning');

				for (var t = 0; t < uniqueNames.length; t++) {
					var cont = content(uniqueNames[t]);
					cont.show();
					cont.addClass('control-group warning');
				}

				field(fieldName).on('change', function() {
					content(fieldName).removeClass('warning');

					for (var t = 0; t < uniqueNames.length; t++) {
						content(uniqueNames[t]).removeClass('warning');
					}
				});
				return false;
			}
			else {
				var yn = confirm("Changing the value of this field will hide and clear '" + uniqueLabels.join("','") + "' " + text + ". \nAre you sure you want to do this?");
				if (yn) {
					for (var f = 0; f < uniqueNames.length; f++) {
						clear(uniqueNames[f]);
					}
				}
				else {
					DC.disabled = true;
					set(fieldName, DC.oldVal);
					DC.disabled = false;
					return false;
				}
			}
			return true;
		},
	}
}

ADD.Feature.ConfirmAction = function() {
	return {
		"click": function() {
			ADD.Notice.showConfirm(
					this.attrs.heading,
					this.attrs.text,
					this.attrs.uri
					);
		}
	}
}

ADD.Feature.Calculate = function() {
	return {
		"validateAndEvaluateCalc": function() {
			var options = this.attrs;
			var dp = options.decimalPlaces;
			var exp = options.expression;

			var fields = options.fields;
			var ajax = options.ajax;
			var asString = options.asString;
			
			for (var i = 0; i < fields.length; i++) {
				var fieldName = fields[i];
				var fieldValue = get(fieldName);
				if (fieldValue == null || fieldValue.length == 0) {
					return null;
				}
			}
			try {
				var evalValue = evaluate(exp);
			}
			catch (ex) {
				alert("An unexpected error occurred in expression:" + exp + "\n" + ex);
				return null;
			}
			if (dp)
				evalValue = new Number(evalValue).toFixed(dp);
			if (isNaN(evalValue) && !asString)
				return null;
			return evalValue;
		},
		"init": function() {

			var fields = this.attrs.fields;

			for (var f = 0; f < fields.length; f++) {
				field(fields[f]).on('change', this, function(event) {
					if(ADD.Util.isEnabled(event.data) || event.data.attrs.overridable) return;
					var calcValue = event.data.validateAndEvaluateCalc();
					if (calcValue == null) {
						set(event.data.fieldName, null);
						return;
					}

					set(event.data.fieldName, calcValue);
				});
			}
			
			if(ADD.Util.isEnabled(this) || this.attrs.overridable) return;
					
			var calcValue = this.validateAndEvaluateCalc();

			if (calcValue == null) {
				set(this.fieldName, null);
				return;
			}
			set(this.fieldName, calcValue);
		}
	}
};
ADD.Feature.ToggleLock = function() {
	return {
		"init": function() {
			var sel = this.fieldName;
			var scroller = function(event) {
				var offsetValue = $(".navbar").height();
				if($(window).width()<980) {
					offsetValue = 0;
				}
				
				var original = $('#' + sel);
				if(!original) {
					return;
				}
				if(!original.position()) {
					return;
				}
				var originalTop = original.position().top;
				var scrollTop = $(window).scrollTop();
				var actualTop = originalTop - scrollTop;
				var copy = $('#copy-'+sel);
				if(actualTop>offsetValue) {
					if (copy.length>0) {
						copy.remove();
					}
					return;
				}
				
				if (copy.length==0) {
					copy = original.clone();
					copy.attr('id','copy-'+sel);
					copy.css('position','absolute');
					copy.width(original.width());
					copy.height(original.height());
					copy.css('top',original.offset().top);
					copy.css('left',original.offset().left);
					if(angular.element("body").scope) {
						angular.element("body").scope().compileHtml(copy.contents());
					}
					original.after(copy);
				}

				copy.css('top',scrollTop + offsetValue);
			};

			$(window).bind('scroll',this,scroller);
		},
		"deinit":function() {
			$(window).unbind('scroll');
		}
	}
}

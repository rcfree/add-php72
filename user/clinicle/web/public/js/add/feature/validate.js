ADD.Feature.ValidateNumber = function() {
	return {
		"_checkInvalidNumber": function(title, number) {
			if (empty(number))
				return {"doReturn":true};
			
			var firstChar = "";
			
			if (this.attrs['allow_op']) {
				if($.isNumeric(number)) {
					this.invalid=false;
					return {"doReturn":true,"number":number};
				}
				firstChar = number.substring(0, 1);
				if ((firstChar != "<" && firstChar != ">" && firstChar != "-" && firstChar != "." && isNaN(firstChar))) {
					ADD.Notice.addFieldErrorWithLabel(title, "is only allowed to have '&gt;' or '&lt;' in front of a number", this.fieldName);
					this.invalid = true;
					return {"doReturn":true};
				}
				return {"number":number.substring(1),"firstChar":firstChar,"doReturn":false};
			}

			if (!$.isNumeric(number)) {
				ADD.Notice.addFieldErrorWithLabel(title, "is not a number", this.fieldName);
				this.invalid = true;
				return {"doReturn":true};
			}
			
			return {"doReturn":false,"number":number,"firstChar":""};
		},
		"_checkMinMax": function(number, title) {
			var minField = "minimum";
			if (this.attrs.min != undefined)
				minField = "min";
			var min = evaluate(this.attrs[minField]);
			if (min != null && min.length > 0)
				min = new Number(min);

			var maxField = "maximum";
			if (this.attrs.max != undefined)
				maxField = "max";

			var max = evaluate(this.attrs[maxField]);
			if (max != null && max.length > 0)
				max = new Number(max);

			if (min && max) {
				if (number < min || number > max) {
					ADD.Notice.addFieldErrorWithLabel(title, "should be between '" + min + "' and '" + max + "'", this.fieldName);
					this.invalid = true;
				}
			}
			else if (!min && max) {
				if (number > max) {
					ADD.Notice.addFieldErrorWithLabel(title, "should be less than '" + max + "'", this.fieldName);
					this.invalid = true;
				}
			}
			else if (min && !max) {
				if (number < min) {
					ADD.Notice.addFieldErrorWithLabel(title, "should be greater than '" + min + "'", this.fieldName);
					this.invalid = true;
				}
			}
		},
		"_checkNormalMinMax": function(number,title) {
			if (this.attrs.normalMin && number < parseFloat(this.attrs.normalMin)) {
				ADD.Notice.addFieldWarning(this.fieldName, "'" + title + "' is less than the normal minimum value (" + this.attrs.normalMin + ")");
			}

			if (this.attrs.normalMax && number > parseFloat(this.attrs.normalMax)) {
				ADD.Notice.addFieldWarning(this.fieldName, "'" + title + "' is greater than the normal maximum value (" + this.attrs.normalMax + ")");
			}
		},
		"change": function() {
			ADD.Notice.removeFieldErrors(this.fieldName);
			ADD.Notice.removeFieldWarnings(this.fieldName);
			content(this.fieldName).removeClass('warning');
			this.invalid = false;

			var title = ADD.Util.getTitleFromAssign(this);

			var number = get(this.fieldName);
			var results = this._checkInvalidNumber(title, number);
			if(results.doReturn) return;
			
			number = results.number;
			
			this._checkMinMax(number, title);
			this._checkNormalMinMax(number, title);

			var floatNum = parseFloat(number);

			var validatedNumber = this.attrs.fixed ? floatNum.toFixed(this.attrs.fixed) : floatNum;

			if (number == validatedNumber)
				return;

			set(this.fieldName, results.firstChar + validatedNumber);
		}
	};
}
ADD.Feature.ValidateYear = function() {
	return {
		"init": function() {
			ADD.assign("ValidateNumber", this.fieldName, this.attrs);
			field(this.fieldName).mask('9999');
		}
	}
}
ADD.Feature.ValidateDate = function() {
	return {
		"init": function() {
			var date = field(this.fieldName);
			date.mask('99/99/9999');
			if (this.attrs.datepicker) {
				date.datepicker({
					"dateFormat": "dd/mm/yy",
					"constrainInput": true,
					"showOn": "button",
					"onSelect": function(dateText, inst) {
						$(this).change();
					}
				});
			}
		},
		"change": function() {
			ADD.Notice.removeFieldErrors(this.fieldName);
			this.invalid = false;

			var dateInField = null;
			var title = ADD.Util.getTitleFromAssign(this);

			dateInField = get(this.fieldName);
			if (dateInField && !this.isValidDate(dateInField)) {
				ADD.Notice.addFieldErrorWithLabel(title, "is not a valid date", this.fieldName);
				this.invalid = true;
				return;
			}

			var date = field(this.fieldName).datepicker('getDate');
			if (!date)
				return;

			if (!this.isValidDate(date)) {
				ADD.Notice.addFieldErrorWithLabel(title, "is not a valid date", this.fieldName);
				this.invalid = true;
				return;
			}
			var min = null;
			var max = null;

			if (this.attrs['minimum']) {
				min = evaluate(this.attrs['minimum']);
			}
			else if (this.attrs['min']) {
				min = evaluate(this.attrs['min']);
			}

			if (this.attrs['maximum']) {
				max = evaluate(this.attrs['maximum']);
			}
			else if (this.attrs['max']) {
				max = evaluate(this.attrs['max']);
			}

			if (min) {
				minDate = dmyToDate(min);
				if (date < minDate) {
					ADD.Notice.addFieldErrorWithLabel(title, "must not be before '" + min + "'", this.fieldName);
					this.invalid = true;
				}
			}

			if (max) {

				maxDate = dmyToDate(max);

				if (date > maxDate) {
					ADD.Notice.addFieldErrorWithLabel(title, "must not be after '" + max + "'", this.fieldName);
					this.invalid = true;
				}
			}
		},
		"isValidDate":function(date) {
			try {
				if(_.isString(date)) {
					var splitDate = date.split("/");
					var Day = splitDate[0];
					var Month = splitDate[1];
					var Year = splitDate[2];
				}
				else {
					var Day = date.getDate();
					var Month = date.getMonth() + 1;
					var Year = date.getFullYear();
				}

				var LeapYear = (((Year % 4) == 0) && ((Year % 100) != 0) || ((Year % 400) == 0));   
				if (Year < 1900) return false;
				if (Month<=0 || Month > 12) return false;
				if (Day<=0) return false;
				if (Month == 2) {  
					if(LeapYear) {
						if (Day>29) return false;
					}
					else {
						if (Day>28) return false;
					}
				} 
				else {
					if ((Month == 4) || (Month == 6) || (Month == 9) || (Month == 11)) {
					   if (Day>30) return false;
					}
					else {
					   if (Day>31) return false;
					}
				}
				return true;
			}
			catch (e) {
				return false;
			} 
		}
	}
}
ADD.Feature.ValidateTime = function() {
	return {
		"init": function() {
			
		},
		"change": function() {
			ADD.Notice.removeFieldErrors(this.fieldName);
			this.invalid = false;

			var timeInField = null;
			var title = ADD.Util.getTitleFromAssign(this);

			timeInField = get(this.fieldName);
			if (timeInField && !this.isValidTime(timeInField)) {
				ADD.Notice.addFieldErrorWithLabel(title, "is not a valid time", this.fieldName);
				this.invalid = true;
				return;
			}

			
		},
		"isValidTime":function(time) {
			try {
				var splitTime = time.split(":");
				var hours = splitTime[0];
				var minutes = splitTime[1];
				
				if(hours<0 || hours>23) {
					return false;
				}
				if(minutes<0 || minutes>59) {
					return false;
				}
				return true;
			}
			catch (e) {
				return false;
			} 
		}
	}
}
ADD.Feature.SeparateDate = function() {
	return {
		"change": function() {
			ADD.Notice.removeFieldErrors(this.fieldName);
			this.invalid = false;

			if (!field(this.fieldName).is(":visible"))
				return;
			var day = get(this.fieldName + "_day");
			var month = get(this.fieldName + "_month");
			var year = get(this.fieldName + "_year");
			if (day && month == 0) {
				ADD.Notice.addFieldErrorWithLabel(label(this.fieldName), "contains a day but no month", this.fieldName);
				this.invalid = true;
				return;
			}

			if (!year && month) {
				ADD.Notice.addFieldErrorWithLabel(label(this.fieldName), "contains a month but no year", this.fieldName);
				this.invalid = true;
				return;
			}

			if (!year) {
				ADD.Notice.addFieldErrorWithLabel(label(this.fieldName), "does not have a year", this.fieldName);
				this.invalid = true;
				return;
			}
		}
	}
}

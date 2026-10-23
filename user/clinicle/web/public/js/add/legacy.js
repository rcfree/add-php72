
function set_if_empty(name,value) {
	if (get(name).length==0) {
		set(name,value);
	}
}
function group_sum(name) {
	var rows = $('.content-' + name + ' tr');
	var sum = 0;
	$('.content-' + name + ' tr').each(function(index) {
		var input = $(this).find('input:checked');
		if (input.length>0) {
			sum = sum + parseInt(input.val());
		}
	});
	return sum;
}

function get(name,text) {
	var ele = field(name);
	
	if (!ele.length) {
            
		var cboxes = jQuery('[name^="' + name + '["]');
		
		if (cboxes.length==0) {
			throw("'get' failed - no field with name '" + name + "' was defined in this form");
		}
		data = {};
		for(var c=0;c<cboxes.length;c++) {
			var cbox = jQuery(cboxes[c]);
			if (cbox.prop('checked')) data[cbox.val()]=true;
		}
                return data;
	}
	if (ele.is('select')) {
		if(text) {
			return ele.find(":selected").html();
		}
		return ele.find(":selected").val();
	}
	
	if (ele.is('input') && ele.attr('type')=='checkbox') {
		var checked = ele.is(":checked");
		if (checked) {
			return "1";
		}
		else {
			return null;
		}
	}
	
	if (ele.length>1) {
		if (ele.first().is('input') && ele.first().attr('type')=='radio') { 
			var val = ele.filter(":checked").val();
			return val;
		}
	}
	return ele.val();
}
function has(name) {
    var ele = field(name);
    if (ele.length==0) {
        ele = content(name);
        if(!ele || ele.length==0) {
            return false;
        }
    }
    return true;
}
function set(name,value) {
	var ele = field(name);
	if (isObj(value)) setCheckbox(name,value);
	if (ele.length==0) {
		var cboxes = jQuery('[name^="' + name + '["]');
		if (cboxes.length==0) return;
		jQuery(cboxes).each(function() {
			var curr = jQuery(this);
			if (value===curr.val()) {
				curr.prop('checked',true);
			}
			else {
				curr.prop('checked',false);
			}
		});
		return;
	}
		
	if (value==null || value.length==0) value="";
	
	
	jQuery(ele).each(function() {
		var curr = jQuery(this);
		if (curr.attr('type')=='radio') {
			var currVal = curr.val();
			if(currVal=='on') currVal='';
			if (currVal==value) {
				curr.attr('checked',true);
				curr.prop("checked",true);
			}
			else {
				curr.removeAttr('checked');
				curr.prop('checked',false);
			}
		}
		else if(curr.attr('type')=='checkbox') {
			if (value=='1') {
				ele.prop('checked',true);
			}
			else {
				ele.prop('checked',false);
			}
		}
		else {
			ele.val(value);
		}
		ele.change();
	});
	return true
}

function keys(o) {
    var result = []; 
    for(var name in o) { 
        if (o.hasOwnProperty(name)) 
          result.push(name); 
    } 
    return result; 
}

function field(nameOrId) {
	if (nameOrId.substring(0,1)=='#') {
		return jQuery(nameOrId); 
	}
	
	var ele = jQuery('[name="' + nameOrId + '"]');
	
	if (ele.length) return ele;
	nameOrId.replace("_","-");
	return field("#edit-" + nameOrId);
}

function content(nameOrId) {
	if (nameOrId.substring(0,1)=='#') nameOrId = nameOrId.replace("#","");
	var ele = jQuery('.content-' + nameOrId);
	if (ele.length) return ele;
}

function isObj(e) {
	if (!e) return false;
    return e.toString() === '[object Object]';
}

function setCheckbox(name,value) {
	for(var id in value) {
		if (value[id]!=null) set(name + "[" + id + "]",true);
	}
}

function enable(name) {
    inputs = jQuery('.content-' + name + ' :input');
	if(inputs.prop && inputs.prop("tagName").toLowerCase()=="select") {
		jQuery("#" + name + "-display").remove();
		inputs.show();
	}
    inputs.removeProp("readonly");
}
function disable(name) {
    inputs = jQuery('.content-' + name + ' :input');
	if(inputs.prop && inputs.prop("tagName").toLowerCase()=="select") {
		if(jQuery("#" + name + "-display").length>0) { 
			jQuery("#" + name + "-display").remove();
		}
		var html = "<div id='" + name + "-display' class='disabled-select'>" + inputs.find(":selected").text() + "</div>";
		inputs.before(html);
		inputs.hide();
	}
    inputs.prop("readonly","readonly");
}
function empty(value) {
	if (value==null) return true;
	if (value.length==0) return true;
}
function clean(nameOrId) {
	if (nameOrId.substring(0,1)=='#') {
		return nameOrId.substring(1);
	}
	else {
		return nameOrId;
	}
}

function clear(name) {
	set(name,"");
}

function removePageErrors() {
	jQuery('.messages.error').remove();
	window.addValidation={};
	return;
}
function label(name) {
	name = name.replace(/\[.+\]/,"");
	return jQuery('.content-' + name + " label").first().text().replace(/\*/,"").replace(/clear\.\./,"").replace(/\s+$/,"");
}

function sum(fieldList) {
	var fields = fieldList.split(";");
	var value = 0;
	for(var f=0;f<fields.length;f++) {
		var fieldVal = new Number(get(fields[f]));
		if (isNaN(fieldVal)) {
			//alert("Sorry you have not entered data in all the required fields and/or the data entered is not numeric");
			return "-";
		}
		value = value + fieldVal;
	}
	if (isNaN(value)) return "-";
	return value;
}
function today(name) {
	var d = new Date();
	var year = d.getFullYear();
	var month = d.getMonth()+1;
	var day = d.getDate();
	var date = zeropad(day,2) + "/" + zeropad(month,2) + "/" + year;
	if (name) set(name,date);
	return date;
}
function zeropad(number,length) {
	var numlen = number.toString().length;
	var zerolen = length-numlen;
	var output = "";
	for(var i=0;i<zerolen;i++) output+="0";
	return output + number;
}
function evaluate(expression) {
	if(expression===0) return '0';
	if (!expression || expression.length==0) return null;
	var expLength = expression.length;
	if(expLength>=2 && expression.substr(0,1)=="\"" && expression.substr(expLength-1,1)=="\"") {
		expression = expression.substr(1,expLength-2);
		return expression;
	}
	if(expression.indexOf("(")==-1) return expression;
	var value="not evaluated";
	try {
		eval("value = " + expression);
	}
	catch(ex) {
		alert("An unexpected error occurred while evaluating the expression:'" + expression + "'\n" + ex);
	}
	if(value===0) return '0';
	return value;
}
function dmyToDate(dmy) {
	if(!dmy) {
		return null;
	}
	var bits = dmy.split("/");
	if (bits.length!=3) return null;
	var d = new Number(bits[0]);
	var m = new Number(bits[1]);
	var y = new Number(bits[2]);
	if (d<1 || m>31) return null;
	if (m<1 || m>12) return null;
	if (y<1 || m>9999) return null;
	var date = new Date(y,m-1,d);
	return date;
}
function age(fromDate,toDate) {
	if (!fromDate) fromDate = date_of_birth();
	if (!toDate) toDate = today();
	if(!fromDate) {
		return;
	}
	fromDate = dmyToDate(fromDate);
	toDate = dmyToDate(toDate);
	
	var years = (toDate.getFullYear() - fromDate.getFullYear());
	ADD.log("toDate",toDate);
	ADD.log("fromDate",fromDate);
	
    if (toDate.getMonth() < fromDate.getMonth() || 
        toDate.getMonth() == fromDate.getMonth() && toDate.getDate() < fromDate.getDate()) {
        years--;
    }

    return years;
}
function date_of_birth() {
	return field('#patient-date-of-birth').html();
}
function get_year(fromDate) {
	if (!fromDate) fromDate = today();
	return dmyToDate(fromDate).getFullYear();
}
function eq(input,test) {
	if (isObj(input)) {
		vals = keys(input);
		for(val in input) {
			if (val==test) {
				return true;
			}
		}
	}
	else {
		if (input == test) {
			return true;
		}
	}
	return false;
}
/*
function validateForm(message, validateHidden) {
	if (has('__autosave') && get('__autosave') == 1)
		return true;

	if (!message)
		message = "You must fix the error(s) highlighted at the top of the page before you can save the data.";
	ADD.Form.validateHidden = validateHidden;
	if (ADD.Form.isValid()) {
		startWait();
		return true;
	}
	else {
		//jQuery('form').dirtyForms();
		stopWait();
		alert(message);
		return false;
	}
}
*/

function errorRequired(fieldName) {
	var value = get(fieldName);
	if (value.length > 0) {
		content(fieldName).removeClass('error');
	}
	else if (value instanceof Object && keys(value).length > 0) {
		content(fieldName).removeClass('error');
	}
	else {
		content(fieldName).addClass('error')
	}
}

function recalculate(fieldList) {
	for (var f = 0; f < fieldList.length; f++) {
		field(fieldList[f]).change();
	}
}
function toggleSearch(checkField) {
        if(!checkField) checkField = "search_select";
        var searchSelect = get(checkField);

        if (searchSelect=="1") {
                jQuery("#search-terms").show();
                jQuery("#search-name-dob").hide();
        }
        else {
                jQuery("#search-name-dob").show();
                jQuery("#search-terms").hide();
        }
}

function deletePatient(pid) {
	ADD.Notice.showConfirm(
		"Are you sure you want to delete this patient?",
		"All data related to this patient will be marked as deleted in ADD",
		"/patient/delete?pid=" + pid
	);
}

function purgePatient(pid) {
	ADD.Notice.showConfirm("Are you sure you want to purge this patient?",
	"All data related to this patient will be removed from ADD",
	"/patient/purge?pid=" + pid);
}

function confirmVerified() {
	jQuery('#modal-confirm-verify').modal({'backdrop':'static','keyboard':false});
	jQuery('#modal-confirm-verify').modal('show');
}
function markVerified() {
	set('__verify',1);
	jQuery('#modal-confirm-verify').modal('hide');
	angular.element("body").scope().save();
}
function markUnverified() {
	set('__verify',"<unverify>");
	jQuery('#modal-confirm-verify').modal('hide');
	angular.element("body").scope().save();
}
function showHistory(fieldName,encId) {
	var inputs = {id:encId, name:fieldName};
	$.getJSON("/api/hist",inputs,function(data,textStatus,jqXHR) {
		var html=""
		var data = data.data;
		if (data.length==0) {
			html = "<p class='info'>No history available</p>";
		}
		else {
			html = "<table class='table table-condensed table-striped'><thead><tr><th>Record Revision</th><th>Timestamp</th><th>User</th><th>Change Type</th><th>Value</th></thead><tbody>";
			for(var d=0;d<data.length;d++) {
				var item = data[d];
				html = html + "<tr><td>" + item['revision_no'] + "</td><td>" + item['timestamp'] + "</td><td>" + item['user_id'] + "</td><td>" + item['change_type'] + "</td><td>" + item['value'] + "</td></tr>";
			}
			html = html + "</tbody></table>";
		}
		ADD.Notice.showAlert("Field history for '" + label(fieldName) + "'",html,"Close");
	});
	
}

function modalEntry(options) {
	if (!options.ajax) options.ajax={"path":"/api/enc","query":{}};
	screenHeight = jQuery(window).height();
	width = 800;
	height = 400;
	if (screenHeight<=600) height=screenHeight-150
	
	jQuery('.modal').css('margin-left',(width/2)*-1);
	jQuery('.modal').css('margin-top',(screenHeight-height-100)/2);
	jQuery('.modal-body').css('height',height-100);
	ADD.Notice.setMessageElement("modal-messages-" + options.elementId);
	
	elementId = "modal-" + options.elementId;
	
	var capitalisedAction = options.action.substring(0, 1).toUpperCase() + options.action.substring(1);
    if(capitalisedAction=="Create") capitalisedAction="Add";
	var heading = capitalisedAction + " " + options.description;
	ADD.Notice.removeAllErrors();
	jQuery('#' + elementId + ' .modal-header h3').html(heading);
	options.saveButtonId = options.elementId + '_save_btn';
	options.closeButtonId = options.elementId + '_close_btn';
	jQuery('#' + options.elementId + '_save_btn').click(function() { modalSave(options) });
	jQuery('#' + options.elementId + '_close_btn').click(function() { jQuery("#" + elementId).modal("hide"); });
	
	if (options.action=='delete') {
		var yn = confirm("Are you sure you wish to delete the  " + options.description + "?");
		if (!yn) return false;
	}
	set('__action',options.action)
	query = options.ajax.query;
	query.action=options.action;
	query.id = options.id;
	query.lid = options.lid;
	query.fid = options.fid;
    query.pid = options.pid;
	jQuery.ajax({
			url:options.ajax.path,
			data:query,
			dataType: "json",
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				alert("There was an error attempting to get " + options.ajax.path);
			},
			success: function(ajaxData, textStatus) {
				ADD.Form.ignoreClearContent=true;
                                if (ajaxData.error) {
                                    alert(ajaxData.error);
                                    return;
                                }
                var data = ajaxData.data;
				jQuery.each(data, function(index, value) {
					
					currField = field(index);
					
					set(index,value);
					currField.change();
					if (ADD.has(index)) {
						var ops = ADD.get(index);
						for(var o=0;o<ops.length;o++) {
							var currOp = ops[o];
							if (currOp.ajaxGet) {
								currOp.value=value;
								currOp['ajaxGet'].call(currOp,value);
							}
						}
					}
					
				});
				
				jQuery('[no-view="1"]').each(function(index) {
					var ele = jQuery(this);
					if (options.action=='view') ele.hide(); else ele.show();
				});
				
				
				if (options.action=='view') {
					jQuery('#' + elementId + ' :input').each(function(index) {
					var ele = jQuery(this);
						ele.attr('disabled','disabled');
						ele.attr('view-disabled','1');
					});
					jQuery('#' + options.elementId + '_save_btn').hide();
					jQuery('.clear-link').hide();
				}
				else {
					jQuery('[view-disabled="1"]').each(function(index) {
					var ele = jQuery(this);
						ele.removeAttr('disabled');
					});
					jQuery('#' + options.elementId + '_save_btn').show();
					jQuery('.clear-link').show();
				}
				
				
				jQuery('.disable-on-update :input').each(function(index) {
					var ele = jQuery(this);
					if (options.action=='view' || options.action=='edit') {	
						disable(ele.attr("name"));
					}
					else {
						enable(ele.attr("name"));
					}
				});
				
				if (options.action!='delete') { 
					showEntry.call(null,data);
				}
				else {
					angular.element("body").scope().refreshPage();
				}
				ADD.Form.ignoreClearContent=false;
			}
	});
	var showEntry = function(data) {
		if (options.beforeFunctionName) window[options.beforeFunctionName]();
		jQuery('#' + elementId).modal({'backdrop':'static','keyboard':false});
		jQuery('#' + elementId).modal('show');
	}
}
function modalSave(options) {
	var message = "Unable to save data, please correct the field error(s).";
	jQuery("#modal-" + options.elementId + " .modal-footer").hide();

	if (options.closeButtonId) jQuery('#' + options.closeButtonId).hide();
	
	var valid = ADD.Form.isValid();

	if (valid) {
		if (options.afterFunctionName) window[options.afterFunctionName]();
		jQuery('.disable-on-update :input').each(function(index) {
			var ele = jQuery(this);
			enable(ele.attr("name"));
		});
		jQuery("form#content-form").submit();
	}
	else {
		jQuery("#modal-" + options.elementId + " .modal-footer").show();	
	}
}

function afterDrugInactive() {
	set('end_date',get('update_end_date'));
}

function beforeDrugInactive() {
	set('current_status','2');
}

function beforeDictionary(data) {
	var title = field('value');
	if (has('test_msg')) {
		field('test_msg').remove();
	}
	if (data && data.__used) {
		if (!has('test_msg')) {
			title.after('<div name="test_msg" style="color:darkorange; border:solid 1px darkorange;margin-top:0.5em;padding:0.5em;">Warning - this test type has been used in one or more results.<br/>Do not change the title unless it is spelt incorrectly.</div>');
		}
		return;
	}

}
function removeDictGroup(id) {
	var yn = confirm("Are you sure you want to remove the tests from the group and then delete the group?\nN.B. tests will not be deleted.");
	if (yn==false) return;
	set('__action','removegroup');
	set('__id',id);
	jQuery('#content-form').submit();
}

function updatePanel(fileName) {
	screenWidth = jQuery(".span9").width()
	jQuery('#panel-title').html(fileName);
	jQuery('iframe').attr('width',screenWidth);
	jQuery('iframe').attr('src','/file?file=' + fileName);
}
function toggleAc(eleName) {
	var ele = jQuery('[name="__' + eleName + '_ac"]');
	var prevMinLength = ele.autocomplete( "option", "minLength" );
	
	if (jQuery(".ui-menu").is(":visible")) {
		jQuery(".ui-menu").hide();
	}
	else {
		ele.autocomplete( "option", "minLength", 0 );
		ele.autocomplete( "search", "" );
		ele.autocomplete( "option", "minLength", prevMinLength );
	}
}
function updateAc(eleName, item, dataFields, clearText) {
	var ac = field('__' + eleName + '_ac');
	if (item) {
		set(eleName,item.value);
	}
	else {
		clear(eleName);
	}
	if (item) {
		if (clearText) { 
			ac.val(null);
		}
		else {
			ac.val(item.title);
		}
	}
	if (dataFields) {
		for(var i=0;i<dataFields.length;i++) {
			var tgt = dataFields[i].target;
			var src = dataFields[i].source;
			set(tgt,item.data[src]);
		}
	}
}
function autocomplete(eleName,dictName, minLength, dataFields, clearText, mustExist, createFunctionName, words, fromStart) {
	var ac = field('__' + eleName + '_ac');
	var noMessage = 0;
	if (createFunctionName) noMessage=1;
	var params = {
        dict_name: dictName,
        no_message: noMessage,
        min_length:minLength,
        words:words,
        from_start:fromStart,
    }
    if(has('__parent')) {
		params['eid']=get('__parent')
	}

	ac.autocomplete({
		source: function( request, response ) {
			params['term'] = request.term;

			jQuery.ajax({
	            url: "/api/dict",
	            dataType: "json",
	            data: params,
	            success: function(data) {
                        if(data.error) alert(data.error_text);
	                response(data.data);
	            }
			});
		},

		select: function( event, ui ) { 
			jQuery(this).selected=true; 
			ac.autocomplete('close');
			updateAc(eleName,ui.item,dataFields,clearText);
			return false;
		},
		focus: function(e, ui) {
    		return false;
		}
	}).keydown(function(e){
	if (e.keyCode === 13){
		jQuery("#edit-single-add-test").click();
	}
	});
	
	jQuery.ui.autocomplete.prototype._renderItem = function( ul, item ) {
		itemContent = null;
		if (item.message) {
			itemContent = item.html;
		}
		else {
			itemContent = '<a>' + item.html + '</a>';
		}
		return jQuery( '<li>' )
		.data( 'item.autocomplete', item )
		.append( itemContent )
		.appendTo( ul );
	}
	
}

function autocompleteList(eleName,dictName, minLength, createFunctionName) {
	var ac = field('__' + eleName + '_ac');
	var noMessage = 0;
	if (createFunctionName) noMessage=1;
	ac.autocomplete({
		source: function( request, response ) {
			jQuery.ajax({
	            url: "/add/api/dict.json",
	            dataType: "json",
	            data: {
	                term: request.term,
	                dict_name: dictName,
	                no_message: noMessage,
	                min_length:minLength
	            },
	            success: function(data) {
	                response(data.data);
	            }
			});
		},
		select: function( event, ui ) { 
			var ac = field('__' + eleName + '_ac');
            var item = ui.item;
            if (item) {
                    set(eleName,item.value);
                    ac.val(item.title);
            }
            ac.autocomplete('close');
            return false;
		},
		focus: function(e, ui) {
    		return false;
		}
	});
	ac.data('autocomplete')._renderItem = function( ul, item ) {
		itemContent = null;
		if (item.message) {
			itemContent = item.html;
		}
		else {
			itemContent = '<a>' + item.html + '</a>';
		}
		return jQuery( '<li>' )
		.data( 'item.autocomplete', item )
		.append( itemContent )
		.appendTo( ul );
	}
}

function checkAutocomplete(eleName,dictName) {
	var text = get('__' + eleName + '_ac');
	var matches = null;
	jQuery.ajax({
        url: "/api/dict",
        dataType: "json",
        data: {
            term: text,
            dict_name: dictName
        },
        success: function(fullData) {
        	if(!fullData) return;
        	var data = fullData.data;
        	if (data[0].title && data[0].title.toLowerCase()==text.toLowerCase()) {
            	for(var i=0;i<data.length;i++) {
					if (data[i].title.toLowerCase()==text.toLowerCase()) {
						matches = data[i];
						set("__" + eleName + "_ac",data[i].title);
						set(eleName,data[i].value);
					}
				}
            }
            if (!matches) {
		        if (get(eleName)=="") {
					if (get("__" + eleName + "_ac")=="") return;
					var yn=confirm("Test type was not found. Do you wish to create it?");
					
					if (yn==false) { clear('__' + eleName + '_ac'); return; }
				}
			}

			field('single_test').after('<input type="hidden" value="submit" name="single_add_test"/>');
			ADD.Notice.startWait();
			jQuery('#content-form').submit();
		}	   
	});
}

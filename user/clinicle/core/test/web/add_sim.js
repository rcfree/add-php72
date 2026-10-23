/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

ADDtest={};
ADDtest.fields={};
ADDtest.errors={};
ADDtest.warnings={};

function get(fieldName) {
	ADD.log("get " + fieldName,ADDtest.fields[fieldName]);
	return ADDtest.fields[fieldName];
}
function set(fieldName,value) {
	ADDtest.fields[fieldName]=value;
}
function label(fieldName) {
	return fieldName;
}
function field(fieldName) {
	return {
		mask: function() {},
		datepicker: function(action) {
			if(action=="getDate") return dmyToDate(get(fieldName));
		}
	};
}
function content(fieldName) {
	return {
		"removeClass":function() {}
	};
}
ADD.Notice.addFieldErrorWithLabel=function(title, message, name) {
	if(!ADDtest.errors[name]) ADDtest.errors[name]=[];
	ADDtest.errors[name].push({"title":title,"message":message});
}
ADD.Notice.addFieldWarning = function(name, message) {
	if(!ADDtest.warnings[name]) ADDtest.warnings[name]=[];
	ADDtest.warnings[name].push({"message":message});
}

ADD.Notice.removeFieldErrors = function(name) {
	if (!name) {
		ADDtest.errors={};
		return;
	}
	ADDtest.errors[name]=[];
}
ADD.Notice.hasErrors = function(name) {
	return ADDtest.errors[name];
}
ADD.Notice.removeFieldWarnings = function(name) {
	if (!name) {
		ADDtest.warnings={};
		return;
	}
	ADDtest.warnings[name]=[];
}
function date_of_birth() {
	return "03/04/1956";
}
function today() {
	return "02/09/2014";
}
function dmyToDate(dateString) {
	var dates = dateString.split("/");
	ADD.log("dates:",dates);
	return new Date(dates[2],dates[1]-1,dates[0]);
}
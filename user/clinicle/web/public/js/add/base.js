ADD = function() {
	var features = {};
	return {
		"fieldList": function() {
			return _.values(features);
		},
		"log": function(text, object) {
			if (window.console)
				console.log(text, object);
		},
		"assign": function(featType, fieldNameList, attrs) {
			if(_.isString(fieldNameList)) {
				fieldNameList=[fieldNameList]
			}
				
			for(var e=0;e<fieldNameList.length;e++) {
				var fieldName = fieldNameList[e];
				if (!features[fieldName]) {
					features[fieldName]=[];
				}
				if(!ADD.Feature[featType]) {
					ADD.err("Feature type " + featType + " is not defined/loaded");
				}

				thisFeat = new ADD.Feature[featType]();
				thisFeat.fieldName=fieldName;
				if(!attrs) attrs={};
				thisFeat.attrs=attrs;
				thisFeat.formId=ADD.Form.currentFormId;
				if(thisFeat.init) thisFeat.init();

				if(thisFeat.change) {
					fld = field(fieldName);
					if (!fld.length) fld = jQuery('[name^="' + fieldName + '["]');
					fld.on("change",thisFeat, function(event) {
						if(ADD.Form.currentFormId!=event.data.formId) return;
						event.data.change.call(event.data);
					});
				}
				if(thisFeat.click) {
					fld = field(fieldName);
					if (!fld.length) fld = jQuery('[name^="' + fieldName + '["]');
					fld.on("click",thisFeat, function(event) {
						if(ADD.Form.currentFormId!=event.data.formId) return;
						event.data.click.call(event.data);
					});
				}
				if(thisFeat.blur) {
					
					fld = field(fieldName);
					if (!fld.length) fld = jQuery('[name^="' + fieldName + '["]');
					fld.on("blur",thisFeat, function(event) {
						if(ADD.Form.currentFormId!=event.data.formId) return;
						event.data.blur.call(event.data);
					});
				}
				features[fieldName].push(thisFeat);
			}
		},
		"get": function(fieldName) {
			if (!features[fieldName])
				throw("Field '" + fieldName + "' has not been assigned any features");
			return features[fieldName];
		},
		"call": function(fieldName,funcName,funcArgs) {
			if (!features[fieldName]) return;
			for(var f=0;f<features[fieldName].length;f++) {
				var feat = features[fieldName][f];
				if(feat.isLockable && feat.lock===true) {
					continue;
				}
					
				if(feat[funcName]) {	
					if(feat.isLockable) {
						ADD.lock(feat);
					}
					if(!funcArgs) funcArgs=[];
					feat[funcName].apply(feat,funcArgs);
				}
				else {
					throw("Feature '" + fieldName + "' does not have '" + funcName + "' function");
				}
			}
		},
		"callAfter":function(millisecs,fieldName,funcName,funcArgs) {
			var refreshFunc = function() {
				ADD.call(fieldName,funcName,funcArgs);
			};
			setTimeout(refreshFunc,millisecs);
		},
		"lock":function(feature) {
			feature.lock=true;
		},
		"unlock":function(feature) {
			feature.lock=false;
		},
		"has": function(fieldName) {
			return features[fieldName]!=null;
		},
		"Feature":{},
		"Notice":{},
		"Form":{},
		"Util":{},
		"User":{},
		"unassign": function(fieldName) {
			features[fieldName]=[];
		},
		"err":function(message) {
			ADD.Notice.addError(message);
			throw message;
		},
		"sideInit":function() {
			$('.collapse').off('show');
			$('.collapse').off('hide');
			$('.collapse').on('show', function () {
				$(this).parent('.accordion-group').find('h5 span.right-caret').attr('class', 'down-caret');
			});
			$('.collapse').on('hide', function () {
				$(this).parent('.accordion-group').find('h5 span.down-caret').attr('class', 'right-caret');
			});
		},

		"mainInit":function() {
			$('#content-form').keydown(function(event){
				if(event.keyCode == 13) {
					var target = $(event.target);
					if(target.length>0 && target[0].tagName=='TEXTAREA') return true;

					event.preventDefault();
					return false;
				}
			});
			
			
			$(function(){
				/*
				var hash = window.location.hash;
				if(hash) {
					$('ul.nav a[href="' + hash + '"]').tab('show');
				}
*/
				$('.nav-tabs a').click(function (e) {
				  //$(this).tab('show');
				  //var scrollmem = $('body').scrollTop();
				  window.location.hash = this.hash;
				  $(window).scrollTop(0);
				  //$('html,body').scrollTop(scrollmem);
				});
			});

		},
		"init":function() {
			this.mainInit();
		},
		"clearFeatures":function(formId) {
			var fieldNames = _.keys(features);
			var clearFields=[];
				
			for(var k=0;k<fieldNames.length;k++) {
				var fieldName = fieldNames[k];
				if(!features[fieldName]) continue;
				var ignoreFeature = false;
				for(var f=0;f<features[fieldName].length;f++) {
					var feat = features[fieldName][f];
					if(feat.formId!==ADD.Form.currentFormId) {
						ignoreFeature=true;
						break;
					}
					if(feat.deinit) {
						feat.deinit();
					}
					var fld = field(feat.fieldName);
					fld.remove();
				}
				if(!ignoreFeature) {
					delete features[fieldName];	
				}
			}
			$(formId).children("input,select").off();
		}
	}
}();


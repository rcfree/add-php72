/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

ADD.Feature.ListAdd = function() {
	return {
		"getContainer":function() {
			return $(".multi-" + this.fieldName + "-list");
		},
		"getFieldValue":function() {
			return get("__" + this.fieldName + "_list");
		},
		"getFieldText":function() {
			return get("__" + this.fieldName + "_list",true);
		},
		"init":function() {
			var that = this;
			$("ul.multi-" + this.fieldName + "-list li button").each(function() {
				$(this).on("click",that,function(event) {
					$(this).parent("li").remove();
					if($("ul.multi-" + event.data.fieldName + "-list li").length==0) {
						$(".empty-" + event.data.fieldName + "-list").removeClass("hidden");
					}
				});
			});
		},
		"add":function() {
			var fieldName = this.fieldName;
			$(".empty-" + fieldName + "-list").addClass("hidden");
			var fieldVal = this.getFieldValue();
			var fieldText = this.getFieldText();
			if(fieldVal.length==0) {
				alert("You must select an item from the list");
				return;
			}
			if($("#" + fieldName + "-" + fieldVal).length>0) {
				alert("Cannot add '" + fieldText + "', it has already been added to the list");
				return;
			}
			var html = "<li id='" + fieldName + "-" + fieldVal + "'><button type='button' class='btn btn-mini btn-warning'><i class='icon-remove'></i></button>&nbsp;";
			html = html + fieldText + "<input type='hidden' value='" + fieldVal + "' name='" + fieldName + "[" + fieldVal + "]'/>";
			html = html + "</li>";
			this.getContainer().append(html);
			this.init();
		}
	}
}

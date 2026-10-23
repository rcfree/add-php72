ADD.Feature.AjaxPopulate = function() {
    return {
        "init": function() {
            var noticePanel = new ADD.NoticePanel();
            noticePanel.setMessageElement(ADD.Notice.getMessageElement());

            this.setNoticePanel(noticePanel);
            var fields = this.attrs.fields;
            for (var f = 0; f < fields.length; f++) {
                field(fields[f]).on('change', this, function(event) {


                    event.data.refresh();
                });
            }

            this.refresh();
        },
        "getNoticePanel":function() {
            return this.noticePanel;
        },
        "setNoticePanel":function(noticePanel) {
            this.noticePanel = noticePanel;
        },
        "populateFromAjax": function(ajaxData) {
        	var outputs = ajaxData.outputs;
            var messages = ajaxData.messages;
            var fields = ajaxData.fields;
            this.invalid = false;
            var noticePanel = this.getNoticePanel();
            noticePanel.removeFieldWarnings(this.attrs.funcName);
            if (messages) {
                if (messages.warnings) {
                    for (var e = 0; e < messages.warnings.length; e++) {
                        noticePanel.addFieldWarning(this.attrs.funcName, messages.warnings[e]);
                    }
                }
                if (messages.errors) {
                    for (var e = 0; e < messages.errors.length; e++) {
                        var error = messages.errors[e];
                        noticePanel.removeFieldErrors(error.fieldName);
                        noticePanel.addFieldErrorWithLabel(error.fieldTitle, error.message, error.fieldName);
                        this.invalid = true;
                    }
                }
                if(messages.debug) {
                    $('.debug').append(messages.debug);
                }
            }

            if (!outputs)
                return ajaxData;

            var flds = _.keys(outputs);

            for (var f = 0; f < flds.length; f++) {
                var fld = flds[f];

                if(!has(fld)) {
                    continue;
                }
                var foutput = outputs[fld];

                if (!_.isObject(foutput)) {
                    var currValue = get(fld);
                    if(currValue == "" && foutput==null) {
                        continue;
                    }
                    if(currValue == foutput) {
                        continue;
                    }
                    set(fld, foutput);
                    continue;
                }

                if (foutput.content) {
                    var contentFld = content(fld);

                    contentFld.html(foutput.content);
                }
                if(foutput.script) {
                    var fscript = new Function(foutput.script);
                    fscript();
                }
                if (foutput.options) {

                    opts = foutput.options;
                    prevValue = get(fld);

                    var selectFld = field(fld);
                    selectFld.empty();

                    for (var c = 0; c < opts.length; c++) {
                        var optEle = '<option value=\"' + opts[c].value + '\"';
                        if (field(fld).attr('selvalue') == opts[c].value) {
                            optEle = optEle + ' selected=\"true\"';
                        }
                        field(fld).append(optEle + '>' + opts[c].label + '</option>');
                    }
                    set(fld, prevValue);
                    selectFld.dirtyForms("setClean");
                }

                if (foutput.value) {
                    set(fld, foutput.value);

                    var selectFld = field(fld);
                    selectFld.dirtyForms("setClean");
                }
            }
        },
        "refresh": function() {
            var inputs = jQuery("#" + ADD.Form.currentFormId).serializeArray();
            inputs.push({name: 'm', value: this.attrs.modName});
            inputs.push({name: 'p', value: this.attrs.pageName});
            inputs.push({name: 'f', value: this.attrs.funcName});
            inputs.push({name: 'field', value: this.fieldName});
            var eid = ADD.Util.getUrlVars('eid');
            if (eid) {
                inputs.push({name: 'eid', value: eid});
            }
            var pid = ADD.Util.getUrlVars('pid');
            inputs.push({name: 'pid', value: pid});
            that = this;

            var ajaxLoader = "<i class='fa fa-spinner fa-pulse fa-fw ajax-load fa-2x'></i>"
            field(that.fieldName).before(ajaxLoader)
            var async = true;
            if(that.attrs.sync) {
                async = false;
            }
            $.ajax({
                dataType: "json",
                url: "/ajax",
                data: inputs,
                async:async,
                success:function(data, textStatus, jqXHR) {
                    that.populateFromAjax(data);
                    $('.ajax-load').remove();
                }
            });
        }
    }
};
ADD.Feature.AjaxFieldUpdate = function() {
    return {
        "ajaxGet": function(value) {
            set(this.attrs['update_field'], value);
        },
        "validate": function() {
            if (field(this.fieldName).is(":visible"))
                return;
            var value = get(this.attrs['update_field']);
            set(this.fieldName, value);
        }
    }
}


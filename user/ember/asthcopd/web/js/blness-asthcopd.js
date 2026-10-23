$(document).on("primaryCondition",function(evt,data){
	if(data.icd10_code.substring(0,3)!='J44') {
		return;
	}
	set('primary_condition','COP');
	content('asthma_severity').hide();
        set('asthma_severity','');
});

$(document).on("primaryCondition",function(evt,data){
	if(data.icd10_code.substring(0,3)!='J45') {
		return;
	}
	set('primary_condition','AST');
	content('asthma_severity').show();
        set('asthma_severity',data.qualifier);
});

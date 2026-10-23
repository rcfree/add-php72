$(document).on("primaryCondition",function(evt,data){
	if(data.icd10_code.substring(0,3)!='J41') {
		return;
	}
	set('primary_condition','CAP');
	content('asthma_severity').hide();
	set('asthma_severity','');
});
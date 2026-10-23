function checkDateSubmit(fieldName) {
    addValidateFunction(function() { 
        removeErrors(fieldName);
        if(!field(fieldName).is(":visible")) return;
        
        var day = get(fieldName + "_day");
        var month = get(fieldName + "_month");
        var year = get(fieldName + "_year");
        
        if(day && month==0) {
            addError(label(fieldName),"contains a day but no month",fieldName);
            return true;
        }
        
        if(!year && month) {
            addError(label(fieldName),"contains a month but no year",fieldName);
            return true
        }
        
        if(!year) {
            addError(label(fieldName),"does not have a year",fieldName);
            return true;
        } 
    })
}

function beforeComorbInactive() {
	set('current_status','2');
}
function afterComorbInactive() {
	set('end_date_day',get('update_end_date_day'));
        set('end_date_month',get('update_end_date_month'));
        set('end_date_year',get('update_end_date_year'));
}
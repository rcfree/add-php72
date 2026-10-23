
function getPatientLookupPath() {
	return "/lookup/pmi"
}
function getBirthDate() {
	return document.getElementById('edit-lookup-date-of-birth').value;
}

function getIdentifier() {
	return document.getElementById('edit-lookup-identifier').value;
}

function getFirstName() {
	return document.getElementById('edit-lookup-first-name').value
}

function getLastName() {
	return document.getElementById('edit-lookup-last-name').value
}

function getFieldMap() {
	var genderMap = {
			'M': 'edit-gender-m',
			'F': 'edit-gender-f'
	};
	return {
			main_pat_id: 'edit-identifier',
			nhs_number: 'edit-nhs-no',
			title: 'edit-title',
			last_name:'edit-last-name',
			first_name:'edit-first-name',
			dob:'edit-date-of-birth',
			gender:function(value) {
				set('gender',value);
			},
			ethnicity:function(value) {
				set('ethnic_group',value);
			},
			marital_status:function(value) {
				set('marital_status',value);
			},
			pat_addr1:'edit-address-line-1',
			pat_addr2:'edit-address-line-2',
			pat_addr3:'edit-address-line-3',
			postcode:'edit-postcode',
			homephone:'edit-home-phone',
			workphone:'edit-work-phone',
			date_of_death: 'edit-date-of-death',
			death_indicator:function(value) {
				set('deceased',value);
			},
			gp_local_code:'edit-local-code',
			gp_doh_code:'edit-doh-code',
			gp_practicecode:'edit-practice-code',
			gp_title:'edit-gp-title',
			gp_forename:'edit-gp-forename',
			gp_surname:'edit-gp-surname',
			gp_addr1:'edit-gp-address1',
			gp_addr2:'edit-gp-address2',
			gp_addr3:'edit-gp-address3',
			gp_addr4:'edit-gp-address4',
			gp_postcode:'edit-gp-postcode',
			gp_workphone:'edit-gp-workphone'
	};
}
function prePopulateCheck(identifier,populateFunction) {
	jQuery.ajax({
			url: "/api/patient",
			data:{'identifier':identifier},
			dataType: "json",
			error: function (XMLHttpRequest, textStatus, errorThrown) {
				alert("error"+textStatus);
			},
			success: function(data, textStatus) {
				ADD.Notice.removeAllErrors();
				if (data.length==0 || data.data.length==0) {
					populateFunction.call();
				}
				else {
					pat = data.data;
					jQuery("#patient-new-form").removeClass();
					
					if (pat['id']==get('__patient_id')) {
						populateFunction.call();
					}
					else {
						patLink = "/patient/demographics?pid=" + pat['id'];
						ADD.Notice.addFieldErrorWithLabel("","Sorry the patient cannot be selected as one with this S-number '" + pat['identifier'] + "'  (" + pat['first_name'] + " " + pat['last_name'] + ") already exists in ADD.<br/><br/> " +
								"<a href=\"javascript:;\" onclick=\"CLE.Client.gotoNgPage('" + patLink + "')\">View " + pat['first_name'] + " " + pat['last_name'] + "'s demographics in ADD</a>","patient_exists");
					}
				}
				jQuery("#patient_dialog").modal("hide");
				
			},
			timeout: ajaxTimeout,
			type: "get"
	});
}

function postPopulateCheck() {
	$('#tabs-tabs a[href="#personal"]').tab('show');
}



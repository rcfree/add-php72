function getBirthDate() {
	return document.getElementById('lookup_date_of_birth').value;
}

function getIdentifier() {
	return document.getElementById('lookup_identifier').value;
}

function getFirstName() {
	return document.getElementById('lookup_first_name').value
}

function getLastName() {
	return document.getElementById('lookup_last_name').value
}

function getFieldMap() {
	var genderMap = {
			'M': 'civicrm_gender_Male_2',
			'F': 'civicrm_gender_Female_1'
	};
	return {
			main_pat_id: 'custom_35_-1',
			nhs_number: 'custom_36_-1',
			last_name:'last_name',
			first_name:'first_name',
			middle_name:'middle_name',
			dob:function(value) {
				document.getElementById("birth_date_display").value=value;
				document.getElementById("birth_date").value=value;
			},
			gender:function(value) {
				var genderField = genderMap[value];
				document.getElementById(genderField).checked=true;
			},
			pat_addr1:'address_1_street_address',
			pat_addr2:'address_1_supplemental_address_1',
			pat_addr3:'address_1_supplemental_address_2',
			pat_addr4:'address_1_city',
			postcode:'address_1_postal_code',
			homephone:'phone_1_phone',
			gp_doh_code: 'gp_link_id'
	};
}

function postPopulateCheck() {
	cj('#last_name').blur();
}



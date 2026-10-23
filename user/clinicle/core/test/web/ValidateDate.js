
QUnit.module("ValidateDate", {
	setup:function(assert) {
		set("max_value",10);
		ADD.assign("ValidateDate", "test_date",{"min":"date_of_birth()","max":"today()","title":"Test date"});
	},
	teardown:function(assert) {
		ADD.unassign("test_date");
		set("test_date",null);
	}
});

QUnit.test( "Empty is valid", function( assert ) {
	set("test_date","");
	ADD.call("test_date","change");
	assert.equal(ADDtest.errors.test_date.length,0);
	var assigns = ADD.get('test_date');
	assert.equal(assigns[0].invalid,false);
});

QUnit.test( "Greater than 02/09/2014 is invalid if range is dob (03/04/1956) - today (02/09/2014)", function( assert ) {
	set("test_date","04/10/2014");
	ADD.call("test_date","change");
	assert.deepEqual(ADDtest.errors.test_date,[{"message":"must not be after '02/09/2014'","title":"Test date"}]);
	var assigns = ADD.get('test_date');
	assert.equal(assigns[0].invalid,true);
});

QUnit.test( "Less than 03/04/1956 is invalid if range is dob (03/04/1956) - today (02/09/2014)", function( assert ) {
	set("test_date","01/02/1945");
	ADD.call("test_date","change");
	assert.deepEqual(ADDtest.errors.test_date,[{"message":"must not be before '03/04/1956'","title":"Test date"}]);
	var assigns = ADD.get('test_date');
	assert.equal(assigns[0].invalid,true);
});

QUnit.test( "Less than 03/04/1956 is valid if range is to today (02/09/2014)", function( assert ) {
	var assigns = ADD.get('test_date');
	assigns[0].attrs.min=null;
	set("test_date","01/02/1945");
	ADD.call("test_date","change");
	assert.equal(ADDtest.errors.test_date.length,0);
	var assigns = ADD.get('test_date');
	assert.equal(assigns[0].invalid,false);
});

QUnit.test( "Greater than 03/04/1956 is valid if range is from dob (03/04/1956) onward", function( assert ) {
	var assigns = ADD.get('test_date');
	assigns[0].attrs.max=null;
	ADD.log("assigns",assigns);
	
	set("test_date","02/09/2014");
	ADD.call("test_date","change");
	assert.equal(ADDtest.errors.test_date.length,0);
	var assigns = ADD.get('test_date');
	assert.equal(assigns[0].invalid,false);
});


QUnit.module("ValidateNumber", {
	setup:function(assert) {
		set("max_value",10);
		ADD.assign("ValidateNumber", "test_number",{"min":0,"max":"get('max_value')","title":"Test number"});
	},
	teardown:function(assert) {
		ADD.unassign("test_number");
		ADDtest={};
		ADDtest.fields={};
		ADDtest.errors={};
		ADDtest.warnings={};
	}
});
QUnit.test( "Empty is valid", function( assert ) {
	set("test_number","");
	ADD.call("test_number","change");
	assert.equal(ADDtest.errors.test_number.length,0);
	var assigns = ADD.get('test_number');
	assert.equal(assigns[0].invalid,false);
});

QUnit.test( "Greater than 10 is invalid if range is 0-10", function( assert ) {
	set("test_number",15);
	ADD.call("test_number","change");
	assert.deepEqual(ADDtest.errors.test_number,[{"message":"should be between '0' and '10'","title":"Test number"}]);
	var assigns = ADD.get('test_number');
	assert.equal(assigns[0].invalid,true);
});

QUnit.test( "10 is valid if range is 0-10", function( assert ) {
	set("test_number",10);
	ADD.call("test_number","change");
	assert.equal(ADDtest.errors.test_number.length,0);
	var assigns = ADD.get('test_number');
	assert.equal(assigns[0].invalid,false);
});

QUnit.test( "Zero is valid if range is 0-10", function( assert ) {
	set("test_number",0);
	ADD.call("test_number","change");
	assert.equal(ADDtest.errors.test_number.length,0);
	var assigns = ADD.get('test_number');
	assert.equal(assigns[0].invalid,false);
});

QUnit.test( "Less than 0 is invalid if range is 0-10", function( assert ) {
	set("test_number",-1);
	ADD.call("test_number","change");
	assert.deepEqual(ADDtest.errors.test_number,[{"message":"should be between '0' and '10'","title":"Test number"}]);
	var assigns = ADD.get('test_number');
	assert.equal(assigns[0].invalid,true);
});

QUnit.test( "Between zero and 10 is valid if range is 0-10", function( assert ) {
	set("test_number",7);
	ADD.call("test_number","change");
	assert.equal(ADDtest.errors.test_number.length,0);
	var assigns = ADD.get('test_number');
	assert.equal(assigns[0].invalid,false);
});

QUnit.test( "Text entry is invalid", function( assert ) {
	set("test_number","X");
	ADD.call("test_number","change");
	assert.deepEqual(ADDtest.errors.test_number,[{"message":"is not a number","title":"Test number"}]);
	var assigns = ADD.get('test_number');
	assert.equal(assigns[0].invalid,true);
});

QUnit.test( "Greater than entry is invalid if no allow_op flag if range is 0-10", function( assert ) {
	set("test_number",">10");
	ADD.call("test_number","change");
	assert.deepEqual(ADDtest.errors.test_number,[{"message":"is not a number","title":"Test number"}]);
	var assigns = ADD.get('test_number');
	assert.equal(assigns[0].invalid,true);
});

QUnit.test( "Greater than entry is valid if allow_op flag if range is 0-10", function( assert ) {
	var assigns = ADD.get('test_number');
	assigns[0].attrs.allow_op=1;
	set("test_number",">10");
	ADD.call("test_number","change");
	assert.deepEqual(ADDtest.errors.test_number,[]);
	assert.equal(assigns[0].invalid,false);
});

QUnit.test( "Greater than entry still invalid if allow_op flag but is out of range of 0-10", function( assert ) {
	var assigns = ADD.get('test_number');
	assigns[0].attrs['allow_op']=true;
	set("test_number",">15");
	ADD.call("test_number","change");
	assert.deepEqual(ADDtest.errors.test_number,[{"message":"should be between '0' and '10'","title":"Test number"}]);
	assert.equal(assigns[0].invalid,true);
});

QUnit.test( "Greater than entry invalid if allow_op flag but has other symbol", function( assert ) {
	var assigns = ADD.get('test_number');
	assigns[0].attrs['allow_op']=true;
	set("test_number","+9");
	ADD.call("test_number","change");
	assert.deepEqual(ADDtest.errors.test_number,[{"message":"is only allowed to have '&gt;' or '&lt;' in front of a number","title":"Test number"}]);
	assert.equal(assigns[0].invalid,true);
});

QUnit.test( "Less than 0 is invalid if range is >0", function( assert ) {
	var assigns = ADD.get('test_number');
	assigns[0].attrs.max=null;
	assigns[0].attrs.allow_op=false;
	
	set("test_number",-1);
	ADD.call("test_number","change");
	assert.deepEqual(ADDtest.errors.test_number,[{"message":"should be greater than '0'","title":"Test number"}]);
	var assigns = ADD.get('test_number');
	assert.equal(assigns[0].invalid,true);
});

QUnit.test( "Greater than 10 is invalid if range is <10", function( assert ) {
	var assigns = ADD.get('test_number');
	assigns[0].attrs.min=null;
	assigns[0].attrs.max=10;
	assigns[0].attrs.allow_op=false;
	
	set("test_number",11);
	ADD.call("test_number","change");
	assert.deepEqual(ADDtest.errors.test_number,[{"message":"should be less than '10'","title":"Test number"}]);
	var assigns = ADD.get('test_number');
	assert.equal(assigns[0].invalid,true);
});

QUnit.test( "Warning if value is 8 and normal min is 10", function( assert ) {
	var assigns = ADD.get('test_number');
	assigns[0].attrs.min=5;
	assigns[0].attrs.normalMin=10;
	assigns[0].attrs.max=30;
	assigns[0].attrs.allow_op=false;
	
	set("test_number",8);
	ADD.call("test_number","change");
	assert.deepEqual(ADDtest.warnings.test_number,[{"message":"'Test number' is less than the normal minimum value (10)"}]);
	assert.equal(ADDtest.errors.test_number.length,0);
	var assigns = ADD.get('test_number');
	assert.equal(assigns[0].invalid,false);
});

QUnit.test( "Warning if value is 25 and normal max is 20", function( assert ) {
	var assigns = ADD.get('test_number');
	assigns[0].attrs.min=5;
	assigns[0].attrs.normalMax=20;
	assigns[0].attrs.max=30;
	assigns[0].attrs.allow_op=false;
	
	set("test_number",25);
	ADD.call("test_number","change");
	assert.deepEqual(ADDtest.warnings.test_number,[{"message":"'Test number' is greater than the normal maximum value (20)"}]);
	assert.equal(ADDtest.errors.test_number.length,0);
	var assigns = ADD.get('test_number');
	assert.equal(assigns[0].invalid,false);
});

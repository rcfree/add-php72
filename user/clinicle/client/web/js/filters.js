/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function RejoinFilter($sce) {
  return function(input) {
  	if(input==null) return "";
  	var splitted = input.split(";");
  	return $sce.trustAsHtml(splitted.join('<br/>'));
  };
}
function TimeToDateFilter() {
  return function(input) {
	  var dateTime = input.split(" ");
	  var bits = dateTime[0].split("-");
	  return bits[2] + "/" + bits[1] + "/" + bits[0];
  };
}
function TrustHtmlFilter($sce) {
	return function(input) {
		return $sce.trustAsHtml(input);
	};
}

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

ADD.Feature.SwitchFilter = function() {
	return {
		"change": function() {
			var switchTarget = CLE.Client.element(this.attrs["target_grid"]);
			var targetScope = switchTarget.scope().control;
			
			if(get(this.fieldName)==1) {
				targetScope.put("filter",this.attrs['on_filter']);
			}
			else {
				targetScope.put("filter",this.attrs['off_filter']);
			}
			CLE.Client.refresh(this.attrs["target_grid"]);
		}
	}
}
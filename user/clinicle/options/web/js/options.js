ADD.Feature.SetupSlider = function() {
    return {
        "init": function() {
            ADD.assign("ValidateNumber", this.fieldName, this.attrs);
            var fieldVal =  get(this.fieldName);
            if(!fieldVal) {
                fieldVal = 0;
            }

            var disabled = this.attrs.readonly ? 1 : 0;

            var fieldName = this.fieldName;
            var sliderField = field(this.fieldName + "-slider");
            sliderField.slider({
                min: parseInt(this.attrs.min),
                max: parseInt(this.attrs.max),
                step: parseInt(this.attrs.step),
                disabled:disabled,
                value: fieldVal,
                slide: function (event, ui) {
                    set(fieldName,ui.value);
                }
            });
            field(this.fieldName).change(function() {
                sliderField.slider("value",parseInt(get(fieldName)));
            })
        }
    }
}
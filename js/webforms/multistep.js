function JsWebformsStepNext(el, formId){
	var current_fs = $(el).up().up();
	var next_fs = current_fs.next();
	
	var form = new VarienForm('webform_'+formId);
	if(form.validator && form.validator.validate()){
		Effect.Appear(next_fs,{duration:0.5});
		current_fs.hide();
	}
}

function JsWebformsStepPrevious(el){
	var current_fs = $(el).up().up();
	if(current_fs.className != 'form-step') current_fs = current_fs.up();
	var previous_fs = current_fs.previous();

	Effect.Appear(previous_fs,{duration:0.5});
	current_fs.hide();
}
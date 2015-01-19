(function($) {
//$.entwine('ss', function($) {

	$('div.display-logic, div.display-logic-master').entwine({

		getFormField: function() {
			return this.find('[name='+this.getFieldName()+']');
		},

		getFieldName: function() {
			return this.attr('id');
		},


		getFieldValue: function() {
			return this.getFormField().val();
		},

		evaluateEqualTo: function(val) {			
			return this.getFieldValue() === val;
		},

		evaluateNotEqualTo: function(val) {
			return this.getFieldValue() !== val;
		},

		evaluateLessThan: function(val) {
			num = parseFloat(val);
			return this.getFieldValue() < num;
		},

		evaluateGreaterThan: function(val) {
			num = parseFloat(val);
			
			return parseFloat(this.getFieldValue()) > num;
		},

		evaluateLessThan: function(val) {
			num = parseFloat(val);
			return parseFloat(this.getFieldValue()) < num;
		},

		evaluateContains: function(val) {
			return this.getFieldValue().match(val) !== null;
		},

		evaluateStartsWith: function(val) {
			return this.getFieldValue().match(new RegExp('^'+val)) !== null;
		},

		evaluateEndsWith: function(val) {
			return this.getFieldValue().match(new RegExp(val+'$')) !== null;
		},

		evaluateEmpty: function() {
			return $.trim(this.getFieldValue()).length === 0;
		},

		evaluateNotEmpty: function() {
			return !this.evaluateEmpty();
		},

		evaluateBetween: function(minmax) {
			v = parseFloat(this.getFieldValue());
			parts = minmax.split("-");
			if(parts.length === 2) {				
				return v > parseFloat(parts[0]) && v < parseFloat(parts[1]);
			}
			return false;
		},

		evaluateChecked: function() {
			return this.getFormField().is(":checked");
		},

		onmatch: function () {
			
			var allReadonly = true;
			var masters = [];
			var field = this.getFormField();

			if(field.data('display-logic-eval') && field.data('display-logic-masters')) {
				this.data('display-logic-eval', field.data('display-logic-eval'))
					.data('display-logic-masters', field.data('display-logic-masters'));
			}

			masters = this.getMasters();			

			for(m in masters) {
				var master = this.closest('form').find('#'+masters[m]);				
				if(!master.is('.readonly')) allReadonly = false;
				master.addClass("display-logic-master");				
			}

			// If all the masters are readonly fields, the field has no way of displaying.
			if(masters.length && allReadonly) {				
				this.show();
			}
		},

		getLogic: function() {
			return $.trim(this.data('display-logic-eval'));
		},

		parseLogic: function() {
			var js = this.getLogic();			
			var result = new Function("return " + js).bind(this)();	
			console.log('got result', result);	
			return result;
		},


		getMasters: function() {
			var masters = this.data('display-logic-masters');

			return (masters) ? masters.split(",") : [];
		}

	});


	$('.field.optionset').entwine({

		getFormField: function() {
			f = this._super().filter(":checked");			
			return f;
		}

	});


	$('.field.optionset.checkboxset').entwine({

		evaluateHasCheckedOption: function(val) {
			var found = false;
			this.find(':checkbox').filter(':checked').each(function() {				
				found = (found || ($(this).val() === val || $(this).getLabel().text() === val));
			})

			return found;
		},

		evaluateHasCheckedAtLeast: function(num) {
			return this.find(':checked').length >= num;
		},

		evaluateHasCheckedLessThan: function(num) {
			return this.find(':checked').length <= num;	
		}

	});


	$('.field input[type=checkbox]').entwine({
		getLabel: function() {
			return this.closest('form').find('label[for='+this.attr('id')+']');
		}
	})



	$('div.display-logic.display-logic-display').entwine({
		testLogic: function() {			
			this.toggle(this.parseLogic());
		}
	});


	$('div.display-logic.display-logic-hide').entwine({
		testLogic: function() {
			this.toggle(!this.parseLogic());
		}
	});


	$('.field.display-logic-master :input').entwine({
		onmatch: function() {
			this.closest(".field").notify();
		},

		onchange: function() {			
			this.closest(".field").notify();
		}
	});
	

	$('.field.display-logic-master :checkbox, .field.display-logic-master :radio').entwine({
		onmatch: function() {			
			this.closest(".field").notify();
		},

		onclick: function() {						
			this.closest(".field").notify();
		}
	});

	$('div.display-logic.optionset, div.display-logic-master.optionset').entwine({
		getFieldValue: function () {
			return this.find(':checked').val();
		}
	});	

	$('.field.display-logic-master').entwine({
		Listeners: null,

		notify: function() {
			console.log('notify', this.getListeners());		
			$.each(this.getListeners(), function() {
				console.log(this, 'is testing logic');				
				$(this).testLogic();
			});
		},

		getListeners: function() {
			if(l = this._super()) {
				return l;
			}
			var self = this;
			var listeners = [];
			this.closest("form").find('.display-logic').each(function() {
				masters = $(this).getMasters();
				for(m in  masters) {
					if(masters[m] == self.attr('id')) {
						listeners.push($(this));
						break;
					}
				}
			});
			this.setListeners(listeners);
			return this.getListeners();
		}
	});


	$('.field.display-logic-master.checkboxset').entwine({

	})




	$('.field.display-logic *').entwine({
		getHolder: function() {
			return this.closest('.display-logic');
		}
	});

//})
})(jQuery);

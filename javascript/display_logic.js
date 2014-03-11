(function($) {

	"use strict"

	$('.field').entwine({

		getFormField: function() {
			var field = this.closest('form').find('[name='+this.getFieldName()+']');
			return field;
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

		evaluateGreaterThan: function(val) {
			var num = parseFloat(val);
			return parseFloat(this.getFieldValue()) > num;
		},

		evaluateLessThan: function(val) {
			var num = parseFloat(val);
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
			var v = parseFloat(this.getFieldValue());
			var parts = minmax.split("-");
			if(parts.length === 2) {				
				return v > parseFloat(parts[0]) && v < parseFloat(parts[1]);
			}
			return false;
		},

		evaluateChecked: function() {
			return this.getFormField().is(":checked");
		}


	});

	$('.field.display-logic').entwine({
		onmatch: function () {
			var masters = this.getMasters();			
			for(var m in masters) {
				this.closest('form').find('#'+masters[m]).addClass("display-logic-master");				
			}
		},

		getLogic: function() {
			return $.trim(this.find('.display-logic-eval').text());
		},

		parseLogic: function() {
			var js = this.getLogic();
			var result = eval(js);	
			return result;
		},

		getMasters: function() {
			var masters = this.data('display-logic-masters');

			return (masters) ? masters.split(",") : [];
		}

	});


	$('.field.optionset').entwine({
		getFormField: function() {
			var f = this._super().filter(":checked");			
			return f;
		}
	});


	$('.field.optionset.checkboxset').entwine({

		evaluateHasCheckedOption: function(val) {
			this.find(':checkbox').filter(':checked').each(function() {
				return $(this).val() === val || $(this).getLabel() === val;
			})
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



	$('.field.display-logic.display-logic-display').entwine({
		testLogic: function() {
			this.toggle(this.parseLogic());
		}
	});


	$('.field.display-logic.display-logic-hide').entwine({
		testLogic: function() {
			this.toggle(!this.parseLogic());
		}
	});


	$('.field.display-logic-master :text, .field.display-logic-master :hidden, .field.display-logic-master select').entwine({
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


	$('.field.display-logic-master').entwine({
		Listeners: null,

		notify: function() {
			var self = this;
			$.each(this.getListeners(), function() {				
				$(this).testLogic();
			});
		},

		getListeners: function() {
			var l;
			if(l = this._super()) {
				return l;
			}
			var self = this;
			var listeners = [];
			this.closest("form").find('.display-logic').each(function() {
				var masters = $(this).getMasters();
				for(var m in masters) {
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


	$('.field.display-logic *').entwine({
		getHolder: function() {
			return this.closest('.display-logic');
		}
	});


})(jQuery);

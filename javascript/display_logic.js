(function($) {

	$('form :input').entwine({

		onmatch: function () {
			var masters = this.getMasters();				
			for(m in masters) {	
				this.closest('form').find('[name='+masters[m]+']:input').addClass("display-logic-master");				

			}
		},

		getFormField: function() {
			return this;
		},

		getFieldName: function() {
			return this.getFormField().attr('name');
		},


		getFieldValue: function() {
			return this.getFormField().val();
		},


		getHolder: function () {
			return $('#'+this.getFieldName());
		},


		getLogic: function() {
			return $.trim(this.getFormField().data('display-logic-eval'));
		},

		parseLogic: function() {
			var js = this.getLogic();			
			var result = new Function("return " + js).bind(this)();			
			
			return result;
		},

		getMasters: function() {
			var masters = this.getFormField().data('display-logic-masters');

			return (masters) ? masters.split(",") : [];
		},


		evaluateEqualTo: function(val) {			
			return this.getFieldValue() === val;		
		},

		evaluateNotEqualTo: function(val) {
			return this.getFieldValue() !== val;
		},

		evaluateLessThan: function(val) {			
			var num = parseFloat(val);
			return this.getFieldValue() < num;
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


	$('form :radio').entwine({


		getFormField: function() {
			var f = this.closest('[name='+this.attr('name')+']');
			
			return f;
		},


		getFieldValue: function () {
			return this.getHolder().find(':checked').val();
		}

	});


	$('form :checkbox').entwine({

		getFormField: function() {
			var f = this.closest('[name='+this.attr('name').split('[')[0]+']');
			
			return f;
		},


		getFieldValue: function () {
			return this.getHolder().find(':checked').val();
		},

		evaluateHasCheckedOption: function(val) {
			var found = false;
			this.getFormField().find(':checkbox').filter(':checked').each(function() {				
				found = (found || ($(this).val() === val || $(this).getLabel().text() === val));
			})

			return found;
		},

		evaluateHasCheckedAtLeast: function(num) {
			return this.getFormField().find(':checked').length >= num;
		},

		evaluateHasCheckedLessThan: function(num) {
			return this.getFormField().find(':checked').length <= num;	
		},

		getLabel: function() {
			return this.closest('form').find('label[for='+this.attr('id')+']');
		}

	});



	$('form .display-logic-display').entwine({
		testLogic: function() {
			this.getHolder().toggle(!!this.parseLogic());
		}
	});


	$('form .display-logic-hide').entwine({
		testLogic: function() {	
			this.getHolder().toggle(!this.parseLogic());
		}
	});


	$('form .display-logic-master:input').entwine({
		onmatch: function() {		
			this.notify();
		},

		onchange: function() {
			this.notify();
		},

		getIdentifier: function () {
			return this.getHolder().attr('id');
		}
	});
	

	$('form .display-logic-master').entwine({
		Listeners: null,

		notify: function() {
			$.each(this.getListeners(), function() {				
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
					if(masters[m] == self.getIdentifier()) {						
						listeners.push($(this));
						break;
					}
				}
			});
			this.setListeners(listeners);
			return this.getListeners();
		}
	});


})(jQuery);

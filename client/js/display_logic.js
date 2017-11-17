(function($) {
	$('div.display-logic, div.display-logic-master').entwine({

		escapeSelector: function(selector) {
			return selector.replace(/(\[)/g, '_').replace(/(\])/g, '');
		},

		findHolder: function(name) {
			return this.closest('form').find(
				this.escapeSelector('#'+this.nameToHolder(name))
			);
		},

		getFormField: function() {
			var name = this.getFieldName();
			if (name) {
				name = this.escapeSelector(name);
			}

			if(this.find('[name='+name+']').length) {
				return this.find('[name='+name+']');
			}

			return this.find('#'+this.getFormID()+'_'+name);
		},

		getFieldName: function() {
			var fieldID = this.attr('id');

			if(fieldID) {
				return this.attr('id')
					.replace(new RegExp('^'+this.getFormID()+'_'),'')
					.replace(/_Holder$/,'');
			}
		},

		nameToHolder: function (name) {
			name = this.escapeSelector(name);

			// SS 3.2+, Convert::raw2htmlid() logic
			name = name.replace(/[^a-zA-Z0-9\-_:.]+/g, '_').replace(/_+/g, '_');

			// Hack!
			// Remove this when OptionsetField_holder.ss uses $HolderID
			// as its div ID instead of $ID
			if(this.closest('form').find('ul.optionset li input[name='+name+']:first').length) {
				return name;
			}
			return this.getFormID()+'_'+name+'_Holder';
		},

		getFormID: function () {
			return this.closest('form').attr('id');
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
			var num = parseFloat(val);

			return this.getFieldValue() < num;
		},

		evaluateGreaterThan: function(val) {
			var num = parseFloat(val);

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

		evaluateNotChecked: function() {
			return !this.getFormField().is(":checked");
		},

		onmatch: function () {

			var allReadonly = true;
			var masters = [];
			var field = this.getFormField();

			if(field.data('display-logic-eval') && field.data('display-logic-masters')) {
				this.data('display-logic-eval', field.data('display-logic-eval'))
					.data('display-logic-masters', field.data('display-logic-masters'))
					.data('display-logic-animation', field.data('display-logic-animation'));
			}

			masters = this.getMasters();

			for(var m in masters) {
				var holderName = this.nameToHolder(this.escapeSelector(masters[m]));
				var master = this.closest('form').find(this.escapeSelector('#'+holderName));

				if(!master.is('.readonly')) allReadonly = false;

				master.addClass("display-logic-master");
				if(master.find('input[type=radio]').length) {
					master.addClass('optionset');
				}
				if(master.find("input[type=checkbox]").length > 1) {
					master.addClass('checkboxset');
				}
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

			return result;
		},


		getMasters: function() {
			var masters = this.getFormField().data('display-logic-masters');

			return (masters) ? masters.split(",") : [];
		}

	});


	$('div.optionset').entwine({

		getFormField: function() {
			f = this._super().filter(":checked");
			return f;
		}

	});


	$('div.checkboxset').entwine({

		evaluateHasCheckedOption: function(val) {
			var found = false;
			this.find(':checkbox').filter(':checked').each(function() {
				found = (found || ($(this).val() === val || $(this).getLabel().text() === val));
			});

			return found;
		},

		evaluateHasCheckedAtLeast: function(num) {
			return this.find(':checked').length >= num;
		},

		evaluateHasCheckedLessThan: function(num) {
			return this.find(':checked').length <= num;
		}

	});


	$('input[type=checkbox]').entwine({
		getLabel: function() {
			return this.closest('form').find('label[for='+this.getHolder().escapeSelector(this.attr('id'))+']');
		}
	});

	var animation = {
		
		toggle: {
			
			show: function(el) {
				el.show();
			},
			
			hide: function(el) {
				el.hide();
			}
			
		},
		
		slide: {
			
			show: function(el) {
				el.slideDown();
			},
			
			hide: function(el) {
				el.slideUp();
			}
			
		},
		
		fade: {
			
			show: function(el) {
				el.fadeIn();
			},
			
			hide: function(el) {
				el.fadeOut();
			}
			
		},
		
		perform: function(el, result, method) {
			if(typeof method == 'undefined') method = 'toggle';
			if(result) {
				this[method].show(el);
			} else {
				this[method].hide(el);
			}
		}
		
	};


	$('div.display-logic.display-logic-display').entwine({
		testLogic: function() {
			animation.perform(this, this.parseLogic(), this.data('display-logic-animation'));
		}
	});


	$('div.display-logic.display-logic-hide').entwine({
		testLogic: function() {
			animation.perform(this, !this.parseLogic(), this.data('display-logic-animation'));
		}
	});


	$('div.display-logic-master :input').entwine({
		onmatch: function() {
			this.closest(".display-logic-master").notify();
		},

		onchange: function() {
			this.closest(".display-logic-master").notify();
		}
	});


	$('div.display-logic-master :checkbox, div.display-logic-master :radio').entwine({
		onmatch: function() {
			this.closest(".display-logic-master").notify();
		},

		onclick: function() {
			this.closest(".display-logic-master").notify();
		}
	});

	$('div.display-logic.optionset, div.display-logic-master.optionset').entwine({
		getFieldValue: function () {
			return this.find(':checked').val();
		}
	});

	$('div.display-logic-master').entwine({
		Listeners: null,

		notify: function() {
			$.each(this.getListeners(), function() {
				$(this).testLogic();
			});
		},

		getListeners: function() {
			l = this._super();
			if(l) {
				return l;
			}
			var self = this;
			var listeners = [];
			this.closest("form").find('.display-logic').each(function() {
				masters = $(this).getMasters();
				for(var m in masters) {
					if(self.nameToHolder(masters[m]) == self.attr('id')) {
						listeners.push($(this)[0]);
						break;
					}
				}
			});
			this.setListeners(listeners);
			return this.getListeners();
		}
	});

	$('div.display-logic.displaylogicwrapper.display-logic-display, div.display-logic.displaylogicwrapper.display-logic-hide').entwine({
		getFormField: function () {
			return this;
		},

		getFieldName: function () {
			return '';
		}
	});

	$('div.field *').entwine({
		getHolder: function() {
			return this.parents('.field');
		}
	});

})(jQuery);

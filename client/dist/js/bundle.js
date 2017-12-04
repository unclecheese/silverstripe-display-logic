/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// identity function for calling harmony imports with the correct context
/******/ 	__webpack_require__.i = function(value) { return value; };
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 1);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _jquery = __webpack_require__(2);

var _jquery2 = _interopRequireDefault(_jquery);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

_jquery2.default.noConflict();

window.ss = window.ss || {};

_jquery2.default.entwine('ss', function ($) {
  var animation = {

    toggle: {
      show: function show(el) {
        el.show();
      },
      hide: function hide(el) {
        el.hide();
      }
    },

    slide: {
      show: function show(el) {
        el.slideDown();
      },
      hide: function hide(el) {
        el.slideUp();
      }
    },

    fade: {
      show: function show(el) {
        el.fadeIn();
      },
      hide: function hide(el) {
        el.fadeOut();
      }
    },

    perform: function perform(el, result, method) {
      if (typeof method === 'undefined') method = 'toggle';
      if (result) {
        this[method].show(el);
      } else {
        this[method].hide(el);
      }
    }
  };

  $('div.display-logic, div.display-logic-master').entwine({
    escapeSelector: function escapeSelector(selector) {
      return selector.replace(/(\[)/g, '_').replace(/(\])/g, '');
    },
    findHolder: function findHolder(name) {
      return this.closest('form').find(this.escapeSelector('#' + this.nameToHolder(name)));
    },
    getFormField: function getFormField() {
      var name = this.getFieldName();
      if (name) {
        name = this.escapeSelector(name);
      }

      if (this.find('[name=' + name + ']').length) {
        return this.find('[name=' + name + ']');
      }

      return this.find('#' + this.getFormID() + '_' + name);
    },
    getFieldName: function getFieldName() {
      var fieldID = this.attr('id');

      if (fieldID) {
        return this.attr('id').replace(new RegExp('^' + this.getFormID() + '_'), '').replace(/_Holder$/, '');
      }
      return null;
    },
    nameToHolder: function nameToHolder(name) {
      var holderName = this.escapeSelector(name);

      holderName = holderName.replace(/[^a-zA-Z0-9\-_:.]+/g, '_').replace(/_+/g, '_');

      if (this.closest('form').find('ul.optionset li input[name=' + holderName + ']:first').length) {
        return holderName;
      }
      return this.getFormID() + '_' + holderName + '_Holder';
    },
    getFormID: function getFormID() {
      return this.closest('form').attr('id');
    },
    getFieldValue: function getFieldValue() {
      return this.getFormField().val();
    },
    evaluateEqualTo: function evaluateEqualTo(val) {
      return this.getFieldValue() === val;
    },
    evaluateNotEqualTo: function evaluateNotEqualTo(val) {
      return this.getFieldValue() !== val;
    },
    evaluateGreaterThan: function evaluateGreaterThan(val) {
      var num = parseFloat(val);
      return parseFloat(this.getFieldValue()) > num;
    },
    evaluateLessThan: function evaluateLessThan(val) {
      var num = parseFloat(val);
      return parseFloat(this.getFieldValue()) < num;
    },
    evaluateContains: function evaluateContains(val) {
      return this.getFieldValue().match(val) !== null;
    },
    evaluateStartsWith: function evaluateStartsWith(val) {
      return this.getFieldValue().match(new RegExp('^' + val)) !== null;
    },
    evaluateEndsWith: function evaluateEndsWith(val) {
      return this.getFieldValue().match(new RegExp(val + '$')) !== null;
    },
    evaluateEmpty: function evaluateEmpty() {
      return $.trim(this.getFieldValue()).length === 0;
    },
    evaluateNotEmpty: function evaluateNotEmpty() {
      return !this.evaluateEmpty();
    },
    evaluateBetween: function evaluateBetween(minmax) {
      var v = parseFloat(this.getFieldValue());
      var parts = minmax.split('-');
      if (parts.length === 2) {
        return v > parseFloat(parts[0]) && v < parseFloat(parts[1]);
      }
      return false;
    },
    evaluateChecked: function evaluateChecked() {
      return this.getFormField().is(':checked');
    },
    evaluateNotChecked: function evaluateNotChecked() {
      return !this.getFormField().is(':checked');
    },
    onmatch: function onmatch() {
      var allReadonly = true;
      var masters = [];
      var field = this.getFormField();

      if (field.data('display-logic-eval') && field.data('display-logic-masters')) {
        this.data('display-logic-eval', field.data('display-logic-eval')).data('display-logic-masters', field.data('display-logic-masters')).data('display-logic-animation', field.data('display-logic-animation'));
      }

      masters = this.getMasters();

      for (var m in masters) {
        var holderName = this.nameToHolder(this.escapeSelector(masters[m]));
        var master = this.closest('form').find(this.escapeSelector('#' + holderName));
        if (!master.is('.readonly')) allReadonly = false;

        master.addClass('display-logic-master');
        if (master.find('input[type=radio]').length) {
          master.addClass('optionset');
        }
        if (master.find('input[type=checkbox]').length > 1) {
          master.addClass('checkboxset');
        }
      }

      if (masters.length && allReadonly) {
        this.show();
      }
    },
    getLogic: function getLogic() {
      return $.trim(this.data('display-logic-eval'));
    },
    parseLogic: function parseLogic() {
      var js = this.getLogic();
      var result = new Function('return ' + js).bind(this)();
      return result;
    },
    getMasters: function getMasters() {
      var masters = this.getFormField().data('display-logic-masters');
      return masters ? masters.split(',') : [];
    }
  });

  $('div.optionset').entwine({
    getFormField: function getFormField() {
      var f = this._super().filter(':checked');
      return f;
    }
  });

  $('div.checkboxset').entwine({
    evaluateHasCheckedOption: function evaluateHasCheckedOption(val) {
      var found = false;
      this.find(':checkbox').filter(':checked').each(function () {
        found = found || $(this).val() === val || $(this).getLabel().text() === val;
      });

      return found;
    },
    evaluateHasCheckedAtLeast: function evaluateHasCheckedAtLeast(num) {
      return this.find(':checked').length >= num;
    },
    evaluateHasCheckedLessThan: function evaluateHasCheckedLessThan(num) {
      return this.find(':checked').length <= num;
    }
  });

  $('input[type=checkbox]').entwine({
    getLabel: function getLabel() {
      return this.closest('form').find('label[for=' + this.getHolder().escapeSelector(this.attr('id')) + ']');
    }
  });

  $('div.display-logic.display-logic-display').entwine({
    testLogic: function testLogic() {
      animation.perform(this, this.parseLogic(), this.data('display-logic-animation'));
    }
  });

  $('div.display-logic.display-logic-hide').entwine({
    testLogic: function testLogic() {
      animation.perform(this, !this.parseLogic(), this.data('display-logic-animation'));
    }
  });

  $('div.display-logic-master input[type="text"], ' + 'div.display-logic-master input[type="email"], ' + 'div.display-logic-master input[type="numeric"]').entwine({
    onmatch: function onmatch() {
      this.closest('.display-logic-master').notify();
    },
    onkeyup: function onkeyup() {
      this.closest('.display-logic-master').notify();
    },
    onchange: function onchange() {
      this.closest('.display-logic-master').notify();
    }
  });

  $('div.display-logic-master select').entwine({
    onmatch: function onmatch() {
      this.closest('.display-logic-master').notify();
    },
    onchange: function onchange() {
      this.closest('.display-logic-master').notify();
    }
  });

  $('div.display-logic-master :checkbox, div.display-logic-master :radio').entwine({
    onmatch: function onmatch() {
      this.closest('.display-logic-master').notify();
    },
    onclick: function onclick() {
      this.closest('.display-logic-master').notify();
    }
  });

  $('div.display-logic.optionset, div.display-logic-master.optionset').entwine({
    getFieldValue: function getFieldValue() {
      return this.find(':checked').val();
    }
  });

  $('div.display-logic-master').entwine({
    Listeners: null,

    notify: function notify() {
      $.each(this.getListeners(), function () {
        $(this).testLogic();
      });
    },
    getListeners: function getListeners() {
      var l = this._super();
      if (l) {
        return l;
      }
      var self = this;
      var listeners = [];
      this.closest('form').find('.display-logic').each(function () {
        var masters = $(this).getMasters();
        for (var m in masters) {
          if (self.nameToHolder(masters[m]) === self.attr('id')) {
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
    getFormField: function getFormField() {
      return this;
    },
    getFieldName: function getFieldName() {
      return '';
    }
  });

  $('div.field *').entwine({
    getHolder: function getHolder() {
      return this.parents('.field');
    }
  });
});

/***/ }),
/* 1 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


__webpack_require__(0);

/***/ }),
/* 2 */
/***/ (function(module, exports) {

module.exports = jQuery;

/***/ })
/******/ ]);
//# sourceMappingURL=bundle.js.map
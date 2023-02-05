/* global window */
import jQuery from 'jquery';


jQuery.noConflict();

window.ss = window.ss || {};


jQuery.entwine('ss', ($) => {
  const animation = {
    toggle: {
      show(el) {
        const element = el[0];
        element.style.display = '';
        element.classList.remove('display-logic-hidden');
      },
      hide(el) {
        const element = el[0];
        element.style.display = 'none';
      }
    },
    slide: {
      show(el) {
        el.slideDown();
      },
      hide(el) {
        el.slideUp();
      }
    },
    fade: {
      show(el) {
        el.fadeIn();
      },
      hide(el) {
        el.fadeOut();
      }
    },

    perform(el, result, method = 'toggle') {
      if (result) {
        this[method].show(el);
      } else {
        this[method].hide(el);
      }
    }
  };


  $('div.display-logic, div.display-logic-master').entwine({

    escapeSelector(selector) {
      return selector.replace(/(\[)/g, '_').replace(/(\])/g, '');
    },

    findHolder(name) {
      return this.closest('form').find(
        this.escapeSelector(`#${this.nameToHolder(name)}`)
      );
    },

    getFormField() {
      let name = this.getFieldName();
      if (name) {
        name = this.escapeSelector(name);
      }

      if (this.find(`[name=${name}]`).length) {
        return this.find(`[name=${name}]`);
      }

      return this.find(`#${this.getFormID()}_${name}`);
    },

    getFieldName() {
      const fieldID = this.attr('id');

      if (fieldID) {
        return this.attr('id')
          .replace(new RegExp(`^${this.getFormID()}_`), '')
          .replace(/_Holder$/, '');
      }
      return null;
    },

    nameToHolder(name) {
      let holderName = this.escapeSelector(name);

      // SS 3.2+, Convert::raw2htmlid() logic
      holderName = holderName.replace(/[^a-zA-Z0-9\-_:.]+/g, '_').replace(/_+/g, '_');

      // Hack!
      // Remove this when OptionsetField_holder.ss uses $HolderID
      // as its div ID instead of $ID
      // if (this.closest('form').find(`ul.optionset li input[name=${holderName}]:first`).length) {
      //   return holderName;
      // }
      return `${this.getFormID()}_${holderName}_Holder`;
    },

    getFormID() {
      return this.closest('form').attr('id');
    },

    getFieldValue() {
      return this.getFormField().val();
    },

    evaluateEqualTo(val) {
      return this.getFieldValue() === val;
    },

    evaluateNotEqualTo(val) {
      return this.getFieldValue() !== val;
    },

    evaluateGreaterThan(val) {
      const num = parseFloat(val);
      return parseFloat(this.getFieldValue()) > num;
    },

    evaluateLessThan(val) {
      const num = parseFloat(val);
      return parseFloat(this.getFieldValue()) < num;
    },

    evaluateContains(val) {
      return this.getFieldValue().match(val) !== null;
    },

    evaluateStartsWith(val) {
      return this.getFieldValue().match(new RegExp(`^${val}`)) !== null;
    },

    evaluateEndsWith(val) {
      return this.getFieldValue().match(new RegExp(`${val}$`)) !== null;
    },

    evaluateEmpty() {
      return $.trim(this.getFieldValue()).length === 0;
    },

    evaluateNotEmpty() {
      return !this.evaluateEmpty();
    },

    evaluateBetween(minmax) {
      const v = parseFloat(this.getFieldValue());
      const parts = minmax.split('-');
      if (parts.length === 2) {
        return v > parseFloat(parts[0]) && v < parseFloat(parts[1]);
      }
      return false;
    },

    evaluateChecked() {
      return this.getFormField().is(':checked');
    },

    evaluateNotChecked() {
      return !this.getFormField().is(':checked');
    },

    onmatch() {
      let allReadonly = true;
      let masters = [];
      const field = this.getFormField();

      if (field.data('display-logic-eval') && field.data('display-logic-masters')) {
        this.data('display-logic-eval', field.data('display-logic-eval'))
          .data('display-logic-masters', field.data('display-logic-masters'))
          .data('display-logic-animation', field.data('display-logic-animation'));
      }

      masters = this.getMasters();
      if (masters && masters.length) {
        Object.entries(masters).forEach(entry => {
          const [, selector] = entry;
          const holderName = this.nameToHolder(this.escapeSelector(selector));
          const master = this.closest('form').find(this.escapeSelector(`#${holderName}`));
          if (!master.is('.readonly')) allReadonly = false;

          master.addClass('display-logic-master');
          if (master.find('input[type=radio]').length) {
            master.addClass('optionset');
          }
          if (master.find('input[type=checkbox]').length > 1) {
            master.addClass('checkboxset');
          }
        });
      }

      // If all the masters are readonly fields, the field has no way of displaying.
      if (masters.length && allReadonly) {
        this.show();
      }
    },

    getLogic() {
      return $.trim(this.data('display-logic-eval'));
    },

    parseLogic() {
      const js = this.getLogic();
      // eslint-disable-next-line no-new-func
      return new Function(`return ${js}`).bind(this)();
    },

    getMasters() {
      const masters = this.getFormField().data('display-logic-masters');
      return (masters) ? masters.split(',') : [];
    },

    getAnimationTargets() {
      return [this.findHolder(this.getFieldName())];
    }

  });

  $('div.checkboxset').entwine({

    evaluateHasCheckedOption(val) {
      let found = false;
      this.find(':checkbox').filter(':checked').each(function () {
        found = (found || ($(this).val() === val || $(this).getLabel().text() === val));
      });

      return found;
    },

    evaluateHasCheckedAtLeast(num) {
      return this.find(':checked').length >= num;
    },

    evaluateHasCheckedLessThan(num) {
      return this.find(':checked').length <= num;
    }

  });

  $('input[type=checkbox]').entwine({
    getLabel() {
      return this.closest('form').find(`label[for=${this.getHolder().escapeSelector(this.attr('id'))}]`);
    }
  });


  $('div.display-logic.display-logic-display').entwine({
    testLogic() {
      this.getAnimationTargets().forEach(t => {
        animation.perform(t, this.parseLogic(), this.data('display-logic-animation'));
      });
    }
  });


  $('div.display-logic.display-logic-hide').entwine({
    testLogic() {
      this.getAnimationTargets().forEach(t => {
        animation.perform(t, !this.parseLogic(), this.data('display-logic-animation'));
      });
    }
  });


  $('div.display-logic-master input[type="text"], ' +
    'div.display-logic-master input[type="email"], ' +
    'div.display-logic-master input[type="number"]').entwine({
    onmatch() {
      this.closest('.display-logic-master').notify();
    },

    onkeyup() {
      this.closest('.display-logic-master').notify();
    },

    onchange() {
      this.closest('.display-logic-master').notify();
    }
  });


  $('div.display-logic-master select').entwine({
    onmatch() {
      this.closest('.display-logic-master').notify();
    },

    onchange() {
      this.closest('.display-logic-master').notify();
    }
  });

  $('div.display-logic-master :checkbox, div.display-logic-master :radio').entwine({
    onmatch() {
      this.closest('.display-logic-master').notify();
    },

    onclick() {
      this.closest('.display-logic-master').notify();
    }
  });

  $('div.display-logic.optionset, div.display-logic-master.optionset').entwine({
    getFieldValue() {
      return this.find(':checked').val();
    },
    getAnimationTargets() {
      return this._super().concat(this.findHolder(this.getFieldName()).find('.optionset'));
    }

  });

  $('div.display-logic-master').entwine({
    Listeners: null,

    notify() {
      $.each(this.getListeners(), function () {
        $(this).testLogic();
      });
    },

    getListeners() {
      const l = this._super();
      if (l) {
        return l;
      }
      const self = this;
      const listeners = [];
      this.closest('form').find('.display-logic').each(function () {
        const masters = $(this).getMasters();
        if (masters && masters.length) {
          Object.entries(masters).forEach(entry => {
            const [, selector] = entry;
            if (self.nameToHolder(selector) === self.attr('id')) {
              listeners.push($(this)[0]);
            }
          });
        }
      });
      this.setListeners(listeners);

      return this.getListeners();
    }
  });

  $(`div.display-logic.displaylogicwrapper.display-logic-display,
     div.display-logic.displaylogicwrapper.display-logic-hide`
  ).entwine({
    getFormField() {
      return this;
    },

    getFieldName() {
      return '';
    },

    getAnimationTargets() {
      return [this];
    }
  });

  $('div.field *').entwine({
    getHolder() {
      return this.parents('.field');
    }
  });
});

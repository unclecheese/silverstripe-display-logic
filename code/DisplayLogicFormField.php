<?php

namespace UncleCheese\DisplayLogic\Extension;

use UncleCheese\DisplayLogic\DisplayLogicCriteria;
use SilverStripe\ORM\DataExtension;
use SilverStripe\View\Requirements;
use SilverStripe\Core\Config\Config;

/**
 *  Decorates a {@link FormField} object with methods for displaying/hiding
 *
 * @package  display_logic
 * @author  Uncle Cheese <unclecheese@leftandmain.com>
 */
class DisplayLogicFormField extends DataExtension {

	

	/**
	 * The {@link DisplayLogicCriteria} that is evaluated to determine whether this field should display
	 * @var DisplayLogicCriteria
	 */
	protected $displayLogicCriteria = null;




	/**
	 * If the criteria evaluate true, the field should display
	 * @param  string $master The name of the master field
	 * @return DisplayLogicCriteria
	 */
	public function displayIf($master) {
		$class ="display-logic display-logic-hidden display-logic-display";
		$this->owner->addExtraClass($class);

		if($this->owner->hasMethod('addHolderClass')) {
			$this->owner->addHolderClass($class);
		}

		return $this->displayLogicCriteria = DisplayLogicCriteria::create($this->owner, $master);
	}



	/**
	 * If the criteria evaluate true, the field should hide.
	 * The field will be hidden with CSS on page load, before the script loads.
	 * @param  string $master The name of the master field
	 * @return DisplayLogicCriteria
	 */
	public function hideIf($master) {
		$class = "display-logic display-logic-hide";
		$this->owner->addExtraClass($class);

		if($this->owner->hasMethod('addHolderClass')) {
			$this->owner->addHolderClass($class);
		}

		return $this->displayLogicCriteria = DisplayLogicCriteria::create($this->owner, $master);
	}



	/**
	 * If the criteria evaluate true, the field should hide.
	 * The field will displayed before the script loads.
	 * @param  string $master The name of the master field
	 * @return DisplayLogicCriteria
	 */
	public function displayUnless($master) {
		return $this->hideIf($master);

	}



	/**
	 * If the criteria evaluate true, the field should display.
	 * The field will be hidden with CSS on page load, before the script loads.
	 * @param  string $master The name of the master field
	 * @return DisplayLogicCriteria
	 */
	public function hideUnless($master) {
		return $this->owner->displayIf($master);
	}



	/**
	 * Sets the criteria governing the display of this field
	 * @param DisplayLogicCriteria $c
	 */
	public function setDisplayLogicCriteria(DisplayLogicCriteria $c) {
		$this->displayLogicCriteria = $c;
	}

	public function getDisplayLogicCriteria() {
		return $this->displayLogicCriteria;
	}


	/**
	 * A comma-separated list of the master form fields that control the display of this field
	 *
	 * @return  string
	 */
	public function DisplayLogicMasters() {
		if($this->displayLogicCriteria) {
			return implode(",",array_unique($this->displayLogicCriteria->getMasterList()));			
		}
	}


	/**
	 * Answers the animation method to use from the criteria object
	 *
	 * @return string
	 */
	public function DisplayLogicAnimation() {
		if($this->displayLogicCriteria) {
			return $this->displayLogicCriteria->getAnimation();
		}
	}


	/**
	 * Loads the dependencies and renders the JavaScript-readable logic to the form HTML
	 *
	 * @return  string
	 */
	public function DisplayLogic() {
		if($this->displayLogicCriteria) {
			if(!Config::inst()->get('DisplayLogic', 'jquery_included')) {
				Requirements::javascript(ADMIN_THIRDPARTY_DIR.'/jquery/jquery.js');
			}
			Requirements::javascript(ADMIN_THIRDPARTY_DIR.'/jquery-entwine/dist/jquery.entwine-dist.js');
			Requirements::javascript(DISPLAY_LOGIC_DIR.'/javascript/display_logic.js');
			Requirements::css(DISPLAY_LOGIC_DIR.'/css/display_logic.css');
			return $this->displayLogicCriteria->toScript();
		}
		
		return false;
	}
	public function onBeforeRender($field) {
		if($logic = $field->DisplayLogic()) {			
			$field->setAttribute('data-display-logic-masters', $field->DisplayLogicMasters());
			$field->setAttribute('data-display-logic-eval', $logic);
			$field->setAttribute('data-display-logic-animation', $field->DisplayLogicAnimation());
		}
	}

}

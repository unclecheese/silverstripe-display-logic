<?php

namespace Unclecheese\DisplayLogic\Extensions;

use SilverStripe\Forms\FormField;
use SilverStripe\View\Requirements;
use SilverStripe\ORM\DataExtension;
use Unclecheese\DisplayLogic\Control\DisplayLogicCriteria;

/**
 *  Decorates a {@link FormField} object with methods for displaying/hiding
 *
 * @package  display_logic
 * @author  Uncle Cheese <unclecheese@leftandmain.com>
 */
class DisplayLogicFormField extends DataExtension
{
    /**
     * The {@link DisplayLogicCriteria} that is evaluated to determine whether this field should display
     * This data must not be shared across multiple fields
     *
     * @var array
     */
    protected $displayLogicCriteria = [];

    /**
     * @param DisplayLogicCriteria $criteria
     * @return DisplayLogicCriteria
     */
    protected function storeDisplayLogicCriteria(DisplayLogicCriteria $criteria)
    {
        $id = $this->owner->getName();
        $this->displayLogicCriteria[$id] = $criteria;

        return $criteria;
    }

    /**
     * @return DisplayLogicCriteria
     */
    protected function retrieveDisplayLogicCriteria()
    {
        $id = $this->owner->getName();
        if (array_key_exists($id, $this->displayLogicCriteria)) {
            return $this->displayLogicCriteria[$id];
        }

        return null;
    }

    /**
     * If the criteria evaluate true, the field should display
     * @param  string $master The name of the master field
     * @return DisplayLogicCriteria
     */
    public function displayIf($master)
    {
        $class = 'display-logic display-logic-hidden display-logic-display';
        $this->owner->addExtraClass($class);

        if ($this->owner->hasMethod('addHolderClass')) {
            $this->owner->addHolderClass($class);
        }

        return $this->storeDisplayLogicCriteria(DisplayLogicCriteria::create($this->owner, $master));
    }

    /**
     * If the criteria evaluate true, the field should hide.
     * The field will be hidden with CSS on page load, before the script loads.
     * @param  string $master The name of the master field
     * @return DisplayLogicCriteria
     */
    public function hideIf($master)
    {
        $class = 'display-logic display-logic-hide';
        $this->owner->addExtraClass($class);

        if ($this->owner->hasMethod('addHolderClass')) {
            $this->owner->addHolderClass($class);
        }

        return $this->storeDisplayLogicCriteria(DisplayLogicCriteria::create($this->owner, $master));
    }

    /**
     * If the criteria evaluate true, the field should hide.
     * The field will displayed before the script loads.
     * @param  string $master The name of the master field
     * @return DisplayLogicCriteria
     */
    public function displayUnless($master)
    {
        return $this->hideIf($master);

    }

    /**
     * If the criteria evaluate true, the field should display.
     * The field will be hidden with CSS on page load, before the script loads.
     * @param  string $master The name of the master field
     * @return DisplayLogicCriteria
     */
    public function hideUnless($master)
    {
        return $this->owner->displayIf($master);
    }

    /**
     * Sets the criteria governing the display of this field
     * @param DisplayLogicCriteria $criteria
     */
    public function setDisplayLogicCriteria(DisplayLogicCriteria $criteria)
    {
        $this->storeDisplayLogicCriteria($criteria);
    }

    /**
     * @return DisplayLogicCriteria
     */
    public function getDisplayLogicCriteria()
    {
        return $this->retrieveDisplayLogicCriteria();
    }

    /**
     * A comma-separated list of the master form fields that control the display of this field
     *
     * @return string
     */
    public function DisplayLogicMasters()
    {
        if ($this->retrieveDisplayLogicCriteria()) {
            return implode(',', array_unique($this->retrieveDisplayLogicCriteria()->getMasterList()));
        }

        return '';
    }

    /**
     * Answers the animation method to use from the criteria object
     *
     * @return string
     */
    public function DisplayLogicAnimation()
    {
        if ($this->retrieveDisplayLogicCriteria()) {
            return $this->retrieveDisplayLogicCriteria()->getAnimation();
        }

        return '';
    }

    /**
     * Loads the dependencies and renders the JavaScript-readable logic to the form HTML
     *
     * @return  string
     */
    public function DisplayLogic()
    {
        if ($this->retrieveDisplayLogicCriteria()) {
            Requirements::javascript('silverstripe/admin: thirdparty/jquery/jquery.js');
            Requirements::javascript('silverstripe/admin: thirdparty/jquery-entwine/dist/jquery.entwine-dist.js');
            Requirements::javascript('unclecheese/display-logic: client/javascript/display_logic.js');
            Requirements::css('unclecheese/display-logic: client/css/display_logic.css');

            return $this->retrieveDisplayLogicCriteria()->toScript();
        }

        return false;
    }

    /**
     * @param FormField $field
     */
    public function onBeforeRender($field)
    {
        if ($logic = $field->DisplayLogic()) {
            $field->setAttribute('data-display-logic-masters', $field->DisplayLogicMasters());
            $field->setAttribute('data-display-logic-eval', $logic);
            $field->setAttribute('data-display-logic-animation', $field->DisplayLogicAnimation());
        }
    }
}

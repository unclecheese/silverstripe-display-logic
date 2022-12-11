<?php

/**
 *  Decorates a {@link FormField} object with methods for displaying/hiding
 *
 * @package  display_logic
 * @author  Uncle Cheese <unclecheese@leftandmain.com>
 */

namespace UncleCheese\DisplayLogic\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\View\Requirements;
use UncleCheese\DisplayLogic\Criteria;

class DisplayLogic extends Extension
{

    /**
     * @var array
     */
    protected $displayLogicCriteria = [];

    /**
     * @param Criteria $criteria
     * @return Criteria
     */
    public function setDisplayLogicCriteria(Criteria $criteria)
    {
        $id = $this->owner->getName();
        $this->displayLogicCriteria[$id] = $criteria;
        return $criteria;
    }

    /**
     * If the criteria evaluate true, the field should display
     * @param  string $dispatcher The name of the dispatcher field
     * @return Criteria
     */
    public function displayIf($dispatcher)
    {
        $class ="display-logic display-logic-hidden display-logic-display";
        $this->owner->addExtraClass($class);

        if ($this->owner->hasMethod('addHolderClass')) {
            $this->owner->addHolderClass($class);
        }

        return $this->setDisplayLogicCriteria(Criteria::create($this->owner, $dispatcher));
    }

    /**
     * If the criteria evaluate true, the field should hide.
     * The field will be hidden with CSS on page load, before the script loads.
     * @param  string $dispatcher The name of the dispatcher field
     * @return Criteria
     */
    public function hideIf($dispatcher)
    {
        $class = "display-logic display-logic-hide";
        $this->owner->addExtraClass($class);

        if ($this->owner->hasMethod('addHolderClass')) {
            $this->owner->addHolderClass($class);
        }
        return $this->setDisplayLogicCriteria(Criteria::create($this->owner, $dispatcher));
    }

    /**
     * If the criteria evaluate true, the field should hide.
     * The field will displayed before the script loads.
     * @param  string $dispatcher The name of the dispatcher field
     * @return Criteria
     */
    public function displayUnless($dispatcher)
    {
        return $this->owner->hideIf($dispatcher);
    }

    /**
     * If the criteria evaluate true, the field should display.
     * The field will be hidden with CSS on page load, before the script loads.
     * @param  string $dispatcher The name of the dispatcher field
     * @return Criteria
     */
    public function hideUnless($dispatcher)
    {
        return $this->owner->displayIf($dispatcher);
    }


    public function getDisplayLogicCriteria()
    {
        $id = $this->owner->getName();
        return isset($this->displayLogicCriteria[$id]) ? $this->displayLogicCriteria[$id] : null;
    }

    /**
     * A comma-separated list of the dispatcher form fields that control the display of this field
     *
     * @return  string
     */
    public function DisplayLogicDispatchers()
    {
        if ($criteria = $this->getDisplayLogicCriteria()) {
            return implode(",", array_unique($criteria->getDispatcherList()));
        }
    }

    /**
     * Answers the animation method to use from the criteria object
     *
     * @return string
     */
    public function DisplayLogicAnimation()
    {
        if ($criteria = $this->getDisplayLogicCriteria()) {
            return $criteria->getAnimation();
        }
    }

    /**
     * Loads the dependencies and renders the JavaScript-readable logic to the form HTML
     *
     * @return  string
     */
    public function DisplayLogic()
    {
        if ($criteria = $this->getDisplayLogicCriteria()) {
            Requirements::javascript('unclecheese/display-logic: client/dist/js/bundle.js');
            Requirements::css('unclecheese/display-logic: client/dist/styles/bundle.css');
            return $criteria->toScript();
        }

        return false;
    }

    public function onBeforeRender($field)
    {
        if ($logic = $field->DisplayLogic()) {
            $field->setAttribute('data-display-logic-dispatchers', $field->DisplayLogicDispatchers());
            $field->setAttribute('data-display-logic-eval', $logic);
            $field->setAttribute('data-display-logic-animation', $field->DisplayLogicAnimation());
        }
    }
}

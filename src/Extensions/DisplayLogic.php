<?php

namespace UncleCheese\DisplayLogic\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\Forms\FormField;
use SilverStripe\View\Requirements;
use UncleCheese\DisplayLogic\Criteria;

/**
 * Decorates a {@link FormField} object with methods for displaying/hiding
 *
 * @author Uncle Cheese <unclecheese@leftandmain.com>
 * @property FormField $owner
 */
class DisplayLogic extends Extension
{

    /**
     * @var Criteria[]
     */
    protected array $displayLogicCriteria = [];

    public function setDisplayLogicCriteria(Criteria $criteria): Criteria
    {
        $id = $this->owner->getName();
        $this->displayLogicCriteria[$id] = $criteria;

        return $criteria;
    }

    /**
     * If the criteria evaluate true, the field should display
     *
     * @param string $dispatcher The name of the form field that is controlling the display
     */
    public function displayIf(string $dispatcher): Criteria
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
     *
     * @param string $dispatcher The name of the form field that is controlling the display
     */
    public function hideIf(string $dispatcher): Criteria
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
     * The field will be displayed before the script loads.
     *
     * @param string $dispatcher The name of the form field that is controlling the display
     */
    public function displayUnless(string $dispatcher): Criteria
    {
        return $this->owner->hideIf($dispatcher);
    }

    /**
     * If the criteria evaluate true, the field should display.
     * The field will be hidden with CSS on page load, before the script loads.
     *
     * @param string $dispatcher The name of the form field that is controlling the display
     */
    public function hideUnless(string $dispatcher): Criteria
    {
        return $this->owner->displayIf($dispatcher);
    }


    public function getDisplayLogicCriteria(): ?Criteria
    {
        $id = $this->owner->getName();

        return $this->displayLogicCriteria[$id] ?? null;
    }

    /**
     * A comma-separated list of the dispatcher form fields that control the display of this field
     */
    public function DisplayLogicDispatchers(): ?string
    {
        if ($criteria = $this->getDisplayLogicCriteria()) {
            return implode(",", array_unique($criteria->getDispatcherList()));
        }

        return null;
    }

    /**
     * Answers the animation method to use from the criteria object
     */
    public function DisplayLogicAnimation(): ?string
    {
        if ($criteria = $this->getDisplayLogicCriteria()) {
            return $criteria->getAnimation();
        }

        return null;
    }

    /**
     * Loads the dependencies and renders the JavaScript-readable logic to the form HTML
     */
    public function DisplayLogic(): ?string
    {
        if ($criteria = $this->getDisplayLogicCriteria()) {
            Requirements::javascript('unclecheese/display-logic: client/dist/js/bundle.js');
            Requirements::css('unclecheese/display-logic: client/dist/styles/bundle.css');

            return $criteria->toScript();
        }

        return null;
    }

    public function onBeforeRender($field): void
    {
        if ($logic = $field->DisplayLogic()) {
            $field->setAttribute('data-display-logic-dispatchers', $field->DisplayLogicDispatchers());
            $field->setAttribute('data-display-logic-eval', $logic);
            $field->setAttribute('data-display-logic-animation', $field->DisplayLogicAnimation());
        }
    }

}

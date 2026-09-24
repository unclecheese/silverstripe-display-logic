<?php

namespace UncleCheese\DisplayLogic\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\Forms\FormField;
use SilverStripe\View\Requirements;
use UncleCheese\DisplayLogic\Criteria;
use WeakMap;

/**
 * Decorates a {@link FormField} object with methods for displaying/hiding
 *
 * @author Uncle Cheese <unclecheese@leftandmain.com>
 * @property FormField $owner
 */
class DisplayLogic extends Extension
{

    /**
     * Criteria keyed by field name. Kept as a fallback for field objects that were copied
     * from the field the rules were declared on, e.g. by a readonly transformation
     * ({@link FormField::castedCopy()} builds a new object with the same name).
     *
     * @var Criteria[]
     */
    protected array $displayLogicCriteria = [];

    /**
     * Criteria keyed by the field object itself, so a field keeps its criteria after setName().
     * Extension instances are shared by every field in the request (Injector singleton), which is
     * why per-field state has to be keyed by the owner at all. A WeakMap does not keep fields alive.
     *
     * @var WeakMap<FormField, Criteria>|null
     */
    protected ?WeakMap $displayLogicCriteriaByField = null;

    public function setDisplayLogicCriteria(Criteria $criteria): Criteria
    {
        // Created lazily: a property default cannot be an object
        $this->displayLogicCriteriaByField ??= new WeakMap();
        $this->displayLogicCriteriaByField[$this->owner] = $criteria;

        $id = $this->owner->getName();
        $this->displayLogicCriteria[$id] = $criteria;

        return $criteria;
    }

    /**
     * If the criteria evaluate true, the field should display
     *
     * @param string|FormField $dispatcher The form field that is controlling the display, or its name.
     * Pass the field to have the rule follow a later rename of that field.
     */
    public function displayIf(string|FormField $dispatcher): Criteria
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
     * @param string|FormField $dispatcher The form field that is controlling the display, or its name.
     * Pass the field to have the rule follow a later rename of that field.
     */
    public function hideIf(string|FormField $dispatcher): Criteria
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
     * @param string|FormField $dispatcher The form field that is controlling the display, or its name.
     * Pass the field to have the rule follow a later rename of that field.
     */
    public function displayUnless(string|FormField $dispatcher): Criteria
    {
        return $this->owner->hideIf($dispatcher);
    }

    /**
     * If the criteria evaluate true, the field should display.
     * The field will be hidden with CSS on page load, before the script loads.
     *
     * @param string|FormField $dispatcher The form field that is controlling the display, or its name.
     * Pass the field to have the rule follow a later rename of that field.
     */
    public function hideUnless(string|FormField $dispatcher): Criteria
    {
        return $this->owner->displayIf($dispatcher);
    }


    public function getDisplayLogicCriteria(): ?Criteria
    {
        // The field's own criteria first, whatever it is called now
        if ($this->displayLogicCriteriaByField !== null && isset($this->displayLogicCriteriaByField[$this->owner])) {
            return $this->displayLogicCriteriaByField[$this->owner];
        }

        // Otherwise a copy of a field that has criteria under this name
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

<?php

/**
 *  Defines a criterion that controls the display of a given
 *  {@link FormField} object
 *
 * @package  display_logic
 * @author  Uncle Cheese <unclecheese@leftandmain.com>
 */

namespace UncleCheese\DisplayLogic;

use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Core\Extensible;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Dev\Deprecation;

class Criterion
{

    use Extensible {
        defineMethods as extensibleDefineMethods;
    }
    use Injectable;
    use Configurable;

    /**
     * The name of the form field that is controlling the display
     * @var string
     */
    protected $dispatcher = null;

    /**
     * The comparison function to use, e.g. "EqualTo"
     * @var string
     */
    protected $operator = null;

    /**
     * The value to compare to
     * @var mixed
     */
    protected $value = null;

    /**
     * The parent {@link Criteria}
     * @var Criteria
     */
    protected $set = null;

    /**
     * Constructor
     * @param string               $dispatcher   The name of the dispatcher field
     * @param string               $operator The name of the comparison function
     * @param string               $value    The value to compare to
     * @param Criteria $set      The parent criteria set
     */
    public function __construct($dispatcher, $operator, $value, Criteria $set)
    {
        $this->dispatcher = $dispatcher;
        $this->operator = $operator;
        $this->value = $value;
        $this->set = $set;
    }

    /**
     * Accessor for the dispatcher field
     * @return string
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * @return $this
     */
    public function setDispatcher($fieldName)
    {
        $this->dispatcher = $fieldName;
        return $this;
    }

    public function getMaster()
    {
        Deprecation::notice('2.0.6', __FUNCTION__ . ' is deprecated. Please use getDispatcher() instead.');
        return $this->dispatcher;
    }

    public function setMaster($fieldName)
    {
        Deprecation::notice('2.0.6', __FUNCTION__ . ' is deprecated. Please use setDispatcher($fieldName) instead.');
        $this->dispatcher = $fieldName;

        return $this;
    }

    /**
     * Creates a JavaScript-readable representation of this criterion
     * @return string
     */
    public function toScript()
    {
        return sprintf(
            "this.findHolder('%s').evaluate%s('%s')",
            $this->dispatcher,
            $this->operator,
            addslashes($this->value ?? '')
        );
    }
}

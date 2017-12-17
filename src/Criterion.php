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
    protected $master = null;

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
     * @param string               $master   The name of the master field
     * @param string               $operator The name of the comparison function
     * @param string               $value    The value to compare to
     * @param Criteria $set      The parent criteria set
     */
    public function __construct($master, $operator, $value, Criteria $set)
    {
        $this->master = $master;
        $this->operator = $operator;
        $this->value = $value;
        $this->set = $set;
    }

    /**
     * Accessor for the master field
     * @return string
     */
    public function getMaster()
    {
        return $this->master;
    }

    /**
     * @return $this
     */
    public function setMaster($fieldName)
    {
        $this->master = $fieldName;
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
            $this->master,
            $this->operator,
            addslashes($this->value)
        );
    }
}

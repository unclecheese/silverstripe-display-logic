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
     */
    protected string $dispatcher;

    /**
     * The comparison function to use, e.g. "EqualTo"
     */
    protected string $operator;

    /**
     * The value to compare to
     */
    protected mixed $value = null;

    /**
     * The parent {@link Criteria}
     */
    protected Criteria $set;

    /**
     * Constructor
     * @param string $dispatcher The name of the form field that is controlling the display
     * @param string $operator The name of the comparison function
     * @param mixed $value The value to compare to
     * @param Criteria $set The parent criteria set
     */
    public function __construct(string $dispatcher, string $operator, mixed $value, Criteria $set)
    {
        $this->dispatcher = $dispatcher;
        $this->operator = $operator;
        $this->value = $value;
        $this->set = $set;
    }

    public function getDispatcher(): string
    {
        return $this->dispatcher;
    }

    public function setDispatcher(string $fieldName): Criterion
    {
        $this->dispatcher = $fieldName;

        return $this;
    }

    /**
     * Creates a JavaScript-readable representation of this criterion
     */
    public function toScript(): string
    {
        return sprintf(
            "this.findHolder('%s').evaluate%s('%s')",
            $this->dispatcher,
            $this->operator,
            addslashes($this->value ?? '')
        );
    }
}

<?php

/**
 *  Defines a set of criteria that control the display of a given
 *  {@link FormField} object
 *
 * @package  display_logic
 * @author  Uncle Cheese <unclecheese@leftandmain.com>
 */

namespace UncleCheese\DisplayLogic;

use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Core\Extensible;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\FormField;
use BadMethodCallException;

class Criteria
{

    use Extensible {
        defineMethods as extensibleDefineMethods;
    }
    use Injectable;
    use Configurable;

    const LOGIC_OR = 'or';

    const LOGIC_AND = 'and';

    /**
     * The name of the form field that depends on the criteria
     * @var string
     */
    protected $master = null;

    /**
     * The form field that responds to the state of {@link $master}
     * @var FormField
     */
    protected $slave = null;

    /**
     * A parent {@link Criteria}, for grouping
     * @var Criteria
     */
    protected $parent = null;

    /**
     * A list of {@link Criterion} objects
     * @var array
     */
    protected $criteria = [];

    /**
     * The animation method to use
     * @var string
     */
    protected $animation = null;

    /**
     * Either "and" or "or", determines disjunctive or conjunctive logic for the whole criteria set
     * @var string
     */
    protected $logicalOperator = null;

    /**
     * Changes the configured default animation method
     * @param string $animation
     */
    public static function set_default_animation($animation)
    {
        if (in_array($animation, Config::inst()->get(__CLASS__, 'animations'))) {
            Config::modify()->set(__CLASS__, 'default_animation', $animation);
        }
    }

    /**
     * Constructor
     * @param FormField $slave  The form field that responds to changes of another form field
     * @param [type]    $master The name of the form field to respond to
     * @param [type]    $parent The parent {@link Criteria}
     */
    public function __construct(FormField $slave, $master, $parent = null)
    {
        $this->slave = $slave;
        $this->master = $master;
        $this->parent = $parent;
        return $this;
    }

    /**
     * Wildcard method for applying all the possible conditions
     * @param  string $method The method name
     * @param  array $args The arguments
     * @return  Criteria
     */
    public function __call($method, $args)
    {
        if (in_array($method, $this->config()->comparisons)) {
            $val = isset($args[0]) ? $args[0] : null;
            if (substr($method, 0, 2) == "is") {
                $operator = substr($method, 2);
            } else {
                $operator = ucwords($method);
            }

            $this->addCriterion(Criterion::create($this->master, $operator, $val, $this));
            return $this;
        }
        if (method_exists($this, $method)) {
            return $this->$method(...$args);
        }
        $class = get_class($this);
        throw new BadMethodCallException(
            "Object->__call(): extra method $method is invalid on $class:"
        );
    }

    /**
     * Adds a {@link Criterion} for a range of values
     * @param  int  $min The minimum value
     * @param  int  $max The maxiumum value
     * @return Criteria
     */
    public function isBetween($min, $max)
    {
        $this->addCriterion(Criterion::create($this->master, "Between", "{$min}-{$max}", $this));
        return $this;
    }

    /**
     * Adds a new criterion, and makes this set use conjuctive logic
     * @param  string $master The master form field
     * @return Criteria
     */
    public function andIf($master = null)
    {
        if ($this->logicalOperator === self::LOGIC_OR) {
            user_error("Criteria: Cannot declare a logical operator more than once. (Specified andIf() after calling orIf()). Use a nested DisplayLogicCriteriaSet to combine conjunctive and disjuctive logic.", E_USER_ERROR);
        }
        if ($master) {
            $this->master = $master;
        }
        $this->logicalOperator = self::LOGIC_AND;
        return $this;
    }

    /**
     * Adds a new criterion, and makes this set use disjunctive logic
     * @param  string $master The master form field
     * @return Criteria
     */
    public function orIf($master = null)
    {
        if ($this->logicalOperator === self::LOGIC_AND) {
            user_error("Criteria: Cannot declare a logical operator more than once. (Specified orIf() after calling andIf()). Use a nested DisplayLogicCriteriaSet to combine conjunctive and disjuctive logic.", E_USER_ERROR);
        }
        if ($master) {
            $this->master = $master;
        }
        $this->logicalOperator = self::LOGIC_OR;
        return $this;
    }

    /**
     * Adds a new criterion
     * @param Criterion|Criteria $c
     */
    public function addCriterion($c)
    {
        $this->criteria[] = $c;
    }

    /**
     * Gets all the criteria
     * @return array
     */
    public function getCriteria()
    {
        return $this->criteria;
    }

    /**
     * Gets a Javascript symbol for the logical operator
     * @return string
     */
    public function getLogicalOperator()
    {
        return $this->logicalOperator === self::LOGIC_OR ? "||" : "&&";
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
        $criteria = $this->getCriteria();
        if ($criteria) {
            foreach ($criteria as $criterion) {
                $criterion->setMaster($fieldName);
            }
        }
        return $this;
    }

    /**
     * Creates a nested {@link Criteria}
     * @return Criteria
     */
    public function group()
    {
        return Criteria::create($this->slave, $this->master, $this);
    }

    /**
     *
     * Defines the animation method to use
     * @param string $animation
     * @return Criteria
     */
    public function useAnimation($animation)
    {
        if (in_array($animation, $this->config()->animations)) {
            $this->animation = $animation;
        }
        return $this;
    }

    /**
     * Answers the animation method to use
     * @return string
     */
    public function getAnimation()
    {
        if (!$this->animation) {
            return $this->config()->default_animation;
        }
        return $this->animation;
    }

    /**
     * Ends the chaining and returns the parent object, either {@link Criteria} or {@link FormField}
     * @return FormField/Criteria
     */
    public function end()
    {
        if ($this->parent) {
            $this->parent->addCriterion($this);
            return $this->parent->end();
        }
        return $this->slave;
    }

    /**
     * Creates a JavaScript readable representation of the logic
     * @return string
     */
    public function toScript()
    {
        $script = "(";
        $first = true;
        foreach ($this->getCriteria() as $c) {
            $script .= $first ? "" :  " {$this->getLogicalOperator()} ";
            $script .= $c->toScript();
            $first = false;
        }
        $script .= ")";
        return $script;
    }

    /**
     * Gets a list of all the master fields in this criteria set
     * @return string
     */
    public function getMasterList()
    {
        $list = [];
        foreach ($this->getCriteria() as $c) {
            if ($c instanceof Criteria) {
                $list = array_merge($list, $c->getMasterList());
            } else {
                $list[] = $c->getMaster();
            }
        }
        return $list;
    }
}

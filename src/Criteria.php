<?php

namespace UncleCheese\DisplayLogic;

use BadMethodCallException;
use LogicException;
use OutOfRangeException;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extensible;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Forms\FormField;

/**
 * Defines a set of criteria that control the display of a given
 * {@link FormField} object
 *
 * @author Uncle Cheese <unclecheese@leftandmain.com>
 */
class Criteria
{

    use Extensible {
        defineMethods as extensibleDefineMethods;
    }
    use Injectable;
    use Configurable;

    protected const LOGIC_OR = 'or';

    protected const LOGIC_AND = 'and';

    /**
     * The name of the form field that depends on the criteria
     */
    protected string $primary;

    /**
     * The form field that responds to the state of {@link $primary}
     */
    protected FormField $subordinate;

    /**
     * A parent {@link Criteria}, for grouping
     */
    protected ?Criteria $parent = null;

    /**
     * A list of {@link Criterion} objects
     *
     * @var Criteria[]
     */
    protected array $criteria = [];

    /**
     * The animation method to use
     */
    protected ?string $animation = null;

    /**
     * Either "and" or "or", determines disjunctive or conjunctive logic for the whole criteria set
     */
    protected ?string $logicalOperator = null;

    /**
     * Changes the configured default animation method
     *
     * @param string $animation
     */
    public static function set_default_animation(string $animation): void
    {
        if (in_array($animation, Config::inst()->get(__CLASS__, 'animations'))) {
            Config::modify()->set(__CLASS__, 'default_animation', $animation);
        }
    }

    /**
     * @param FormField $subordinate The form field that responds to changes of another form field
     * @param string $primary The name of the form field to respond to
     * @param Criteria|null $parent The parent
     */
    public function __construct(FormField $subordinate, string $primary, ?Criteria $parent = null)
    {
        $this->subordinate = $subordinate;
        $this->primary = $primary;
        $this->parent = $parent;

        return $this;
    }

    /**
     * Wildcard method for applying all the possible conditions
     *
     * @param string $method The method name
     * @param array $args The arguments
     * @return Criteria
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

            $this->addCriterion(Criterion::create($this->primary, $operator, $val, $this));

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
     *
     * @param int $min The minimum value
     * @param int $max The maxiumum value
     */
    public function isBetween(int $min, int $max): Criteria
    {
        $this->addCriterion(Criterion::create($this->primary, "Between", "{$min}-{$max}", $this));

        return $this;
    }

    /**
     * Adds a new criterion, and makes this set use conjuctive logic
     *
     * @param string|null $primary The name of the form field that depends on the criteria
     */
    public function andIf(?string $primary = null): Criteria
    {
        if ($this->logicalOperator === self::LOGIC_OR) {
            user_error("Criteria: Cannot declare a logical operator more than once. (Specified andIf() after calling orIf()). Use a nested DisplayLogicCriteriaSet to combine conjunctive and disjuctive logic.", E_USER_ERROR);
        }

        if ($primary) {
            $this->primary = $primary;
        }

        $this->logicalOperator = self::LOGIC_AND;

        return $this;
    }

    /**
     * Adds a new criterion, and makes this set use disjunctive logic
     *
     * @param string|null $primary The name of the form field that depends on the criteria
     */
    public function orIf(?string $primary = null): Criteria
    {
        if ($this->logicalOperator === self::LOGIC_AND) {
            user_error("Criteria: Cannot declare a logical operator more than once. (Specified orIf() after calling andIf()). Use a nested DisplayLogicCriteriaSet to combine conjunctive and disjuctive logic.", E_USER_ERROR);
        }

        if ($primary) {
            $this->primary = $primary;
        }

        $this->logicalOperator = self::LOGIC_OR;

        return $this;
    }

    /**
     * Adds a new criterion
     */
    public function addCriterion(Criterion|Criteria $c): void
    {
        $this->criteria[] = $c;
    }

    /**
     * Gets all the criteria
     *
     * @return Criteria[]
     */
    public function getCriteria(): array
    {
        return $this->criteria;
    }

    /**
     * Gets a Javascript symbol for the logical operator
     */
    public function getLogicalOperator(): string
    {
        return $this->logicalOperator === self::LOGIC_OR ? "||" : "&&";
    }

    public function getPrimary(): string
    {
        return $this->primary;
    }

    public function setPrimary(string $fieldName): Criteria
    {
        $this->primary = $fieldName;
        $criteria = $this->getCriteria();

        if ($criteria) {
            foreach ($criteria as $criterion) {
                $criterion->setPrimary($fieldName);
            }
        }

        return $this;
    }

    /**
     * Creates a nested {@link Criteria}
     */
    public function group(): Criteria
    {
        return Criteria::create($this->subordinate, $this->primary, $this);
    }

    /**
     * Defines the animation method to use
     */
    public function useAnimation(string $animation): Criteria
    {
        if (in_array($animation, $this->config()->animations)) {
            $this->animation = $animation;
        }

        return $this;
    }

    /**
     * Answers the animation method to use
     */
    public function getAnimation(): ?string
    {
        if (!$this->animation) {
            return $this->config()->get('default_animation');
        }

        return $this->animation;
    }

    /**
     * Ends the chaining and returns the parent {@link FormField}.
     * Works recursively if in a group {@link Criteria} context
     */
    public function end(): FormField
    {
        if ($this->parent) {
            $this->parent->addCriterion($this);

            return $this->parent->end();
        }

        return $this->subordinate;
    }

    /**
     * Escape a group {@link Criteria}
     *
     * @throws LogicException if there is no parent Criteria
     */
    public function endGroup(): Criteria
    {
        if (!$this->parent) {
            $message = __FUNCTION__ . 'called on Criteria with no parent (not in a group).';
            $message .= ' Call group() before endGroup(), or perhaps you\'re looking for end() instead.';

            throw new OutOfRangeException($message);
        }

        return $this->parent;
    }

    /**
     * Creates a JavaScript readable representation of the logic
     */
    public function toScript(): string
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
     * Gets a list of all the primary fields in this criteria set
     */
    public function getPrimaryList(): array
    {
        $list = [];

        foreach ($this->getCriteria() as $c) {
            if ($c instanceof Criteria) {
                $list = array_merge($list, $c->getPrimaryList());
            } else {
                $list[] = $c->getPrimary();
            }
        }

        return $list;
    }
}

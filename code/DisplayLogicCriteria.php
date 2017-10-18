<?php

namespace UncleCheese\DisplayLogic;

use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Object;
use SilverStripe\Forms\FormField;

/**
 *  Defines a set of criteria that control the display of a given
 *  {@link FormField} object
 *
 * @package  display_logic
 * @author  Uncle Cheese <unclecheese@leftandmain.com>
 */
class DisplayLogicCriteria extends Object {


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
	 * A parent {@link DisplayLogicCriteria}, for grouping
	 * @var DisplayLogicCriteria
	 */
	protected $parent = null;



	/**
	 * A list of {@link DisplayLogicCriterion} objects
	 * @var array
	 */
	protected $criteria = array ();



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
	public static function set_default_animation($animation) {
		if(in_array($animation, Config::inst()->get(__CLASS__, 'animations'))) {
			Config::inst()->update(__CLASS__, 'default_animation', $animation);
		}
	}



	/**
	 * Constructor
	 * @param FormField $slave  The form field that responds to changes of another form field
	 * @param [type]    $master The name of the form field to respond to
	 * @param [type]    $parent The parent {@link DisplayLogicCriteria}
	 */
	public function __construct(FormField $slave, $master, $parent = null) {
		parent::__construct();
		$this->slave = $slave;
		$this->master = $master;
		$this->parent = $parent;
		return $this;
	}




	/**
	 * Wildcard method for applying all the possible conditions
	 * @param  sting $method The method name
	 * @param  array $args The arguments
	 * @return  DisplayLogicCriteria
	 */
	public function __call($method, $args) {		
		if(in_array($method, $this->config()->comparisons)) {		
			$val = isset($args[0]) ? $args[0] : null;				
			if(substr($method, 0, 2) == "is") {
				$operator = substr($method, 2);
			}
			else {
				$operator = ucwords($method);
			}

			$this->addCriterion(DisplayLogicCriterion::create($this->master, $operator, $val, $this));
			return $this;
		}		
		return parent::__call($method, $args);
	}




	/**
	 * Adds a {@link DisplayLogicCriterion} for a range of values
	 * @param  int  $min The minimum value
	 * @param  int  $max The maxiumum value
	 * @return DisplayLogicCriteria
	 */
	public function isBetween($min, $max) {		
		$this->addCriterion(DisplayLogicCriterion::create($this->master, "Between", "{$min}-{$max}", $this));
		return $this;
	}



	/**
	 * Adds a new criterion, and makes this set use conjuctive logic
	 * @param  string $master The master form field
	 * @return DisplayLogicCriteria
	 */
	public function andIf($master = null) {
		if($this->logicalOperator == "or") {
			user_error("DisplayLogicCriteria: Cannot declare a logical operator more than once. (Specified andIf() after calling orIf()). Use a nested DisplayLogicCriteriaSet to combine conjunctive and disjuctive logic.",E_USER_ERROR);
		}
		if($master) $this->master = $master;
		$this->logicalOperator = "and";
		return $this;
	}




	/**
	 * Adds a new criterion, and makes this set use disjunctive logic
	 * @param  string $master The master form field
	 * @return DisplayLogicCriteria
	 */
	public function orIf($master = null) {
		if($this->logicalOperator == "and") {
			user_error("DisplayLogicCriteria: Cannot declare a logical operator more than once. (Specified orIf() after calling andIf()). Use a nested DisplayLogicCriteriaSet to combine conjunctive and disjuctive logic.",E_USER_ERROR);
		}
		if($master) $this->master = $master;
		$this->logicalOperator = "or";
		return $this;
	}




	/**
	 * Adds a new criterion
	 * @param DisplayLogicCriterion|DisplayLogicCriteria $c
	 */
	public function addCriterion($c) {		
		$this->criteria[] = $c;
	}



	/**
	 * Gets all the criteria
	 * @return array
	 */
	public function getCriteria() {
		return $this->criteria;
	}



	/**
	 * Gets a Javascript symbol for the logical operator
	 * @return string
	 */
	public function getLogicalOperator() {
		return $this->logicalOperator == "or" ? "||" : "&&";
	}

	/**
	 * Accessor for the master field
	 * @return string
	 */
	public function getMaster() {
		return $this->master;
	}

	/**
	 * @return $this
	 */
	public function setMaster($fieldName) {
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
	 * Creates a nested {@link DisplayLogicCriteria}
	 * @return DisplayLogicCriteria
	 */
	public function group() {
		return DisplayLogicCriteria::create($this->slave, $this->master, $this);
	}



	/**
	 *
	 * Defines the animation method to use
	 * @param string $animation
	 * @return DisplayLogicCriteria
	 */
	public function useAnimation($animation) {
		if(in_array($animation, $this->config()->animations)) {
			$this->animation = $animation;
		}
		return $this;
	}




	/**
	 * Answers the animation method to use
	 * @return string
	 */
	public function getAnimation() {
		if(!$this->animation) {
			return $this->config()->default_animation;
		}
		return $this->animation;
	}




	/**
	 * Ends the chaining and returns the parent object, either {@link DisplayLogicCriteria} or {@link FormField}
	 * @return FormField/DisplayLogicCriteria
	 */
	public function end() {
		if($this->parent) {
			$this->parent->addCriterion($this);
			return $this->parent;
		}
		return $this->slave;
	}




	/**
	 * Creates a JavaScript readable representation of the logic	 
	 * @return string
	 */
	public function toScript() {
		$script = "(";
		$first = true;
		foreach($this->getCriteria() as $c) {
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
	public function getMasterList() {
		$list = array ();
		foreach($this->getCriteria() as $c) {
			if($c instanceof DisplayLogicCriteria) {
				$list=array_merge($list, $c->getMasterList());
			}
			else {
				$list[] = $c->getMaster();
			}
		}
		return $list;
	}






}
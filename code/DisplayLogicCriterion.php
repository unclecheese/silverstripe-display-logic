<?php

/**
 *  Defines a criterion that controls the display of a given
 *  {@link FormField} object
 *
 * @package  display_logic
 * @author  Uncle Cheese <unclecheese@leftandmain.com>
 */
class DisplayLogicCriterion extends Object {


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
	 * The parent {@link DisplayLogicCriteria}
	 * @var DisplayLogicCriteria
	 */
	protected $set = null;




	/**
	 * Constructor
	 * @param string               $master   The name of the master field
	 * @param string               $operator The name of the comparison function
	 * @param string               $value    The value to compare to
	 * @param DisplayLogicCriteria $set      The parent criteria set
	 */
	public function __construct($master, $operator, $value, DisplayLogicCriteria $set) {
		parent::__construct();
		$this->master = $master;
		$this->operator = $operator;
		$this->value = $value;
		$this->set = $set;
	}




	/**
	 * Accessor for the master field
	 * @return string
	 */
	public function getMaster() {
		return $this->master;
	}




	/**
	 * Creates a JavaScript-readable representation of this criterion
	 * @return string
	 */
	public function toScript() {		
		return sprintf(
			"this.closest('form').find(this.escapeSelector('#%s')).evaluate%s('%s')",
			$this->master,
			$this->operator,
			addslashes($this->value)
		);
	}
}
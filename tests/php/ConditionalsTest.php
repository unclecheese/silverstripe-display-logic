<?php

namespace UncleCheese\DisplayLogic\Tests;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;


class ConditionalsTest extends SapphireTest
{

	public function testIsEqual()
	{
		$form = new FieldList(
			TextField::create('Field1'),
			TextField::create('Field2')->displayIf('Field1')->isEqualTo('100')->end()
		);
		$output = $form->forTemplate();
		$contains = 'data-display-logic-eval="(this.findHolder(&#039;Field1&#039;).evaluateEqualTo(&#039;100&#039;))"';
		$this->assertContains($contains, $output);
		$this->assertContains('data-display-logic-masters="Field1"', $output);
	}


	public function testIsNotEqualTo()
	{
		$form = new FieldList(
			TextField::create('Field1'),
			TextField::create('Field2')->displayIf('Field1')->isNotEqualTo('100')->end()
		);
		$output = $form->forTemplate();
		$contains = 'data-display-logic-eval="(this.findHolder(&#039;Field1&#039;).evaluateNotEqualTo(&#039;100&#039;))"';
		$this->assertContains($contains, $output);
		$this->assertContains('data-display-logic-masters="Field1"', $output);
	}

	public function testIsGreaterThan()
	{
		$form = new FieldList(
			TextField::create('Field1'),
			TextField::create('Field2')->displayIf('Field1')->isGreaterThan('100')->end(),
			TextField::create('Field3')->displayIf('Field1')->isGreaterThan('200')->end()
		);
		$output = $form->forTemplate();
		$this->assertContains('data-display-logic-eval="(this.findHolder(&#039;Field1&#039;).evaluateGreaterThan(&#039;100&#039;))"', $output);
		$this->assertContains('data-display-logic-eval="(this.findHolder(&#039;Field1&#039;).evaluateGreaterThan(&#039;200&#039;))"', $output);
		$this->assertContains('data-display-logic-masters="Field1"', $output);

		$this->assertEquals(2, substr_count($output, 'data-display-logic-masters="'));
		$this->assertEquals(2, substr_count($output, 'data-display-logic-masters="Field1"'));

	}

	public function testIsLessThan()
	{
		$form = new FieldList(
			TextField::create('Field1'),
			TextField::create('Field2')->displayIf('Field1')->isLessThan('50')->end()
		);
		$output = $form->forTemplate();
		$this->assertContains('data-display-logic-eval="(this.findHolder(&#039;Field1&#039;).evaluateLessThan(&#039;50&#039;))', $output);
		$this->assertContains('data-display-logic-masters="Field1"', $output);
	}

	public function testAggregatedRules()
	{
		$form = new FieldList(
			TextField::create('Field1'),
			TextField::create('Field2'),
			TextField::create('Field3')->displayIf('Field1')->isLessThan('50')
				->andIf('Field2')->isGreaterThan('80')->end()
		);
		$output = $form->forTemplate();
		$this->assertContains('(this.findHolder(&#039;Field1&#039;).evaluateLessThan(&#039;50&#039;) '
			. '&amp;&amp; this.findHolder(&#039;Field2&#039;).evaluateGreaterThan(&#039;80&#039;))', $output);
		$this->assertContains('data-display-logic-masters="Field1,Field2"', $output);
	}

}
<?php

namespace UncleCheese\DisplayLogic\Tests;

use OutOfRangeException;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\TextField;
use UncleCheese\DisplayLogic\Criteria;

class CriteriaTest extends SapphireTest
{
    public function testEndIsRecursiveAndReturnsTheFormField()
    {
        $field = new TextField('test');
        $displayRulesEnd = $field->displayIf('one')->isEmpty()
            ->andIf()
            ->group()
            ->orIf('two')->isEqualTo('no')
            ->orIf('two')->isEqualTo('non')
            ->orIf()
            ->group() // nested group
            ->andIf('three')->isEqualTo('yes')
            ->andIf('four')->isEqualTo('oui')
            ->end();
        $this->assertInstanceOf(TextField::class, $displayRulesEnd);
    }

    public function testEndGroupOnlyReturnsTheParentCriteria()
    {
        $field = new TextField('test');
        $displayRulesEnd = $field->displayIf('one')->isEmpty()
            ->andIf()
            ->group()
            ->orIf('two')->isEqualTo('no')
            ->orIf('two')->isEqualTo('non')
            ->endGroup()
            ->andIf()
            ->group()
            ->orIf('three')->isEqualTo('yes')
            ->orIf('three')->isEqualTo('oui')
            ->endGroup();
        $this->assertInstanceOf(Criteria::class, $displayRulesEnd);
    }

    public function testEndGroupThrowsExceptionWhenThereIsNoParentCriteria()
    {
        $this->expectException(OutOfRangeException::class);

        $field = new TextField('test');
        $displayRulesEnd = $field->displayIf('one')->isEmpty()->endGroup();
    }
}

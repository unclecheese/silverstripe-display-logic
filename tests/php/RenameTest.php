<?php

namespace UncleCheese\DisplayLogic\Tests;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\TextField;

/**
 * Display logic must survive a field being renamed after its rules were declared,
 * e.g. when a form prefixes the names of reused fields to avoid collisions.
 */
class RenameTest extends SapphireTest
{
    /**
     * Renaming the responder must not lose its criteria. They used to be stored
     * under the field's name, so after setName() the lookup came back empty and
     * no display logic was rendered at all.
     */
    public function testCriteriaSurviveResponderRename()
    {
        $responder = TextField::create('Responder');
        $responder->displayIf('Master')->isEqualTo('yes')->end();

        $responder->setName('Prefix_Responder');

        $this->assertNotNull($responder->getDisplayLogicCriteria());
        $this->assertSame("(this.findHolder('Master').evaluateEqualTo('yes'))", $responder->DisplayLogic());
        $this->assertSame('Master', $responder->DisplayLogicDispatchers());
    }

    /**
     * A dispatcher given as a field object is resolved to its name at render time,
     * so it follows a rename that happens after the rule was declared.
     */
    public function testFieldDispatcherFollowsDispatcherRename()
    {
        $master = TextField::create('Master');
        $responder = TextField::create('Responder');
        $responder->displayIf($master)->isEqualTo('yes')->end();

        $master->setName('Prefix_Master');

        $this->assertSame("(this.findHolder('Prefix_Master').evaluateEqualTo('yes'))", $responder->DisplayLogic());
        $this->assertSame('Prefix_Master', $responder->DisplayLogicDispatchers());
    }

    /**
     * The case that prompted this: both fields renamed after wiring, with a second
     * dispatcher added through andIf() and a nested group through orIf().
     */
    public function testRenamingBothFieldsWithCombinedAndNestedCriteria()
    {
        $one = TextField::create('One');
        $two = TextField::create('Two');
        $three = TextField::create('Three');
        $responder = TextField::create('Responder');
        $responder->displayIf($one)->isEmpty()
            ->andIf($two)->isEqualTo('b')
            ->andIf()
            ->group()
                ->orIf($three)->isEqualTo('c')
                ->orIf('Four')->isEqualTo('d')
            ->end();

        foreach ([$one, $two, $three, $responder] as $field) {
            $field->setName('Prefix_' . $field->getName());
        }

        $this->assertSame(
            "(this.findHolder('Prefix_One').evaluateEmpty('')"
            . " && this.findHolder('Prefix_Two').evaluateEqualTo('b')"
            . " && (this.findHolder('Prefix_Three').evaluateEqualTo('c')"
            . " || this.findHolder('Four').evaluateEqualTo('d')))",
            $responder->DisplayLogic()
        );
        $this->assertSame('Prefix_One,Prefix_Two,Prefix_Three,Four', $responder->DisplayLogicDispatchers());
    }

    /**
     * The extension instance is shared by every field in the request, so each field's
     * criteria must stay separate from every other field's.
     */
    public function testEachFieldKeepsItsOwnCriteria()
    {
        $first = TextField::create('First');
        $second = TextField::create('Second');
        $first->displayIf('A')->isEqualTo('1')->end();
        $second->hideIf('B')->isEqualTo('2')->end();

        $this->assertSame("(this.findHolder('A').evaluateEqualTo('1'))", $first->DisplayLogic());
        $this->assertSame("(this.findHolder('B').evaluateEqualTo('2'))", $second->DisplayLogic());
    }

    /**
     * A readonly transformation builds a new field object with the same name, and its
     * display logic has to come along with it.
     */
    public function testReadonlyCopyKeepsDisplayLogic()
    {
        $responder = TextField::create('Responder');
        $responder->displayIf('Master')->isEqualTo('yes')->end();

        $readonly = $responder->performReadonlyTransformation();

        $this->assertNotSame($responder, $readonly);
        $this->assertSame("(this.findHolder('Master').evaluateEqualTo('yes'))", $readonly->DisplayLogic());
    }

    /**
     * String dispatchers keep working exactly as before.
     */
    public function testStringDispatcherIsUnchanged()
    {
        $responder = TextField::create('Responder');
        $responder->displayIf('Master')->isEqualTo('yes')->orIf('Other')->isNotEmpty()->end();

        $this->assertSame(
            "(this.findHolder('Master').evaluateEqualTo('yes') || this.findHolder('Other').evaluateNotEmpty(''))",
            $responder->DisplayLogic()
        );
    }
}

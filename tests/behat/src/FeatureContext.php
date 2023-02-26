<?php

namespace UncleCheese\DisplayLogic\Tests\Behaviour;

use PHPUnit\Framework\Assert;
use SilverStripe\BehatExtension\Context\MainContextAwareTrait;
use SilverStripe\BehatExtension\Context\SilverStripeContext;

class FeatureContext extends SilverStripeContext
{
    use MainContextAwareTrait;

    /**
     * @Then /^the element "([^"]+)" should be visible$/
     * @param string $elem CSS selector
     */
    public function elementIsVisible($elem)
    {
        $node = $this->getSession()->getPage()->find('css', $elem);
        Assert::assertNotNull($node, "Element $elem was not found");
        Assert::assertTrue($node->isVisible(), "Element $elem is not visible");
    }

    /**
     * @Then /^the element "([^"]+)" should not be visible$/
     * @param string $elem CSS selector
     */
    public function elementIsNotVisible($elem)
    {
        $node = $this->getSession()->getPage()->find('css', $elem);
        Assert::assertNotNull($node, "Element $elem was not found");
        Assert::assertFalse($node->isVisible(), "Element $elem is visible");
    }

}

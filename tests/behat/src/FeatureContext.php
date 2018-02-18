<?php

namespace UncleCheese\DisplayLogic\Tests\Behaviour;

use SilverStripe\BehatExtension\Context\SilverStripeContext;
use SilverStripe\Config\Collections\CachedConfigCollection;
use SilverStripe\Core\Config\ConfigLoader;
use SilverStripe\Core\Manifest\ClassLoader;
use SilverStripe\BehatExtension\Context\MainContextAwareTrait;

class FeatureContext extends SilverStripeContext
{
    use MainContextAwareTrait;
    /**
     * @Given /^I add a test class "([^"]+)"$/
     * @param string $name
     */
    public function iAddATestClass($name)
    {
        copy(
            __DIR__ . "/../bootstrap/{$name}.php.fixture",
            __DIR__ . "/../../../src/{$name}.php"
        );
        static::flush();
    }

    /**
     * @Then /^I should not have a test class "([^"]+)"$/
     * @param string $name
     */
    public function iShouldNotHaveATestClass($name)
    {
        assertNull(
            ClassLoader::inst()->getManifest()->getItemPath(
                'UncleCheese\DisplayLogic\DisplayLogicTestPage'
            )
        );
    }

    /**
     * @Then /^I should have a test class "([^"]+)"$/
     * @param string $name
     */
    public function iShouldHaveATestClass($name)
    {
        assertEquals(
            realpath(__DIR__ . "/../../../src/{$name}.php"),
            ClassLoader::inst()->getManifest()->getItemPath(
                'UncleCheese\DisplayLogic\DisplayLogicTestPage'
            )
        );
    }

    /**
     * @Given /^I remove the test class "([^"]+)"$/
     * @param string $name
     */
    public function iRemoveTheTestClass($name)
    {
        unlink(__DIR__ . "/../../../src/{$name}.php");
        static::flush();
    }

    /**
     * @When /^(?:|I )go build the database/
     */
    public function stepIBuild()
    {
        $this->getMainContext()->visit('/dev/build?flush');
    }

    /**
     * @When /^(?:|I )go flush the website, dismissing the dialog$/
     */
    public function stepIFlushDismisingTheDialog()
    {
        $this->getMainContext()->visit('/?flush=all');
        $this->getSession()->getDriver()->getWebDriverSession()->dismiss_alert();
    }


    /**
     * @Then /^the element "([^"]+)" should be visible$/
     * @param string $elem CSS selector
     */
    public function elementIsVisible($elem)
    {
        $node = $this->getSession()->getPage()->find('css', $elem);
        assertNotNull($node, "Element $elem was not found");
        assertTrue($node->isVisible(), "Element $elem is not visible");
    }

    /**
     * @Then /^the element "([^"]+)" should not be visible$/
     * @param string $elem CSS selector
     */
    public function elementIsNotVisible($elem)
    {
        $node = $this->getSession()->getPage()->find('css', $elem);
        assertNotNull($node, "Element $elem was not found");
        assertFalse($node->isVisible(), "Element $elem is visible");
    }

    protected static function flush()
    {
        ClassLoader::inst()->getManifest()->init(false, true);
        $config = ConfigLoader::inst()->getManifest();
        if ($config instanceof CachedConfigCollection) {
            $config->setFlush(true);
        }
    }

}

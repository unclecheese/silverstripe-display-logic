<?php

namespace Unclecheese\DisplayLogic\Control;

use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extensible;
use SilverStripe\Core\Injector\Injectable;

/**
 * Class DisplayLogicMain
 * @package Unclecheese\DisplayLogic\Control
 */
class DisplayLogicMain
{
    use Extensible;
    use Injectable;
    use Configurable;

    /**
     * DisplayLogicMain constructor.
     */
    public function __construct()
    {
    }
}

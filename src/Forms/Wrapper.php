<?php

namespace UncleCheese\DisplayLogic\Forms;

use SilverStripe\Forms\CompositeField;

/**
 * Empty class definition to map to the DisplayLogicWrapper_Holder.ss template.
 *
 * @todo    Consider doing this with Injector, using calls: setHolderTemplate on CompositeField
 * @package UncleCheese\DisplayLogic\Forms
 * @method Wrapper isEqualTo(mixed $val)
 * @method Wrapper isNotEqualTo(mixed $val)
 * @method Wrapper isGreaterThan(mixed $val)
 * @method Wrapper isLessThan(mixed $val)
 * @method Wrapper contains(mixed $val)
 * @method Wrapper startsWith(mixed $val)
 * @method Wrapper endsWith(mixed $val)
 * @method Wrapper isEmpty()
 * @method Wrapper isNotEmpty()
 * @method Wrapper isBetween(int $min, int $max)
 * @method Wrapper isChecked()
 * @method Wrapper isNotChecked()
 * @method Wrapper hasCheckedOption(mixed $val)
 * @method Wrapper hasCheckedAtLeast(mixed $val)
 * @method Wrapper hasCheckedLessThan(mixed $val)
 * @method Wrapper displayIf(string $master)
 * @method Wrapper displayUnless(string $master)
 * @method Wrapper hideIf(string $master)
 * @method Wrapper hideUnless(string $master)
 * @method Wrapper andIf(string $master)
 * @method Wrapper orIf(string $master)
 */
class Wrapper extends CompositeField
{
}

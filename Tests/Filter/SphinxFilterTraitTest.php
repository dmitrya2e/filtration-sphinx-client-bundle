<?php

/*
 * This file is part of the Da2e FiltrationSphinxClientBundle package.
 *
 * (c) Dmitry Abrosimov <abrosimovs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Da2e\FiltrationSphinxClientBundle\Tests\Filter;

use Da2e\FiltrationBundle\Filter\SphinxFilterTrait;
use Da2e\FiltrationBundle\Tests\TestCase;

/**
 * Class SphinxFilterTraitTest
 *
 * @author Dmitry Abrosimov <abrosimovs@gmail.com>
 */
class SphinxFilterTraitTest extends TestCase
{
    public function testIsExclude()
    {
        /** @var SphinxFilterTrait $mock */
        $mock = $this->getMockForTrait('\Da2e\FiltrationSphinxClientBundle\Filter\SphinxFilterTrait');
        $this->assertFalse($mock->isExclude());
    }

    public function testSetExclude()
    {
        /** @var SphinxFilterTrait $mock */
        $mock = $this->getMockForTrait('\Da2e\FiltrationSphinxClientBundle\Filter\SphinxFilterTrait');

        $mock->setExclude(true);
        $this->assertTrue($mock->isExclude());

        $mock->setExclude(false);
        $this->assertFalse($mock->isExclude());
    }

    public function testGetExcludeOptionDescription()
    {
        /** @var SphinxFilterTrait $mock */
        $mock = $this->getMockForTrait('\Da2e\FiltrationSphinxClientBundle\Filter\SphinxFilterTrait');

        $this->assertSame([
            'setter' => 'setExclude',
            'empty'  => false,
            'type'   => 'bool',
        ], $this->invokeMethod($mock, 'getExcludeOptionDescription'));
    }
}

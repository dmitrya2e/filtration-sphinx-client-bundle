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

use Da2e\FiltrationSphinxClientBundle\Filter\TextFilter;
use Da2e\FiltrationBundle\Tests\Filter\Filter\AbstractFilterTestCase;

/**
 * Class TextFilterTest
 *
 * @author Dmitry Abrosimov <abrosimovs@gmail.com>
 */
class TextFilterTest extends AbstractFilterTestCase
{
    public function testGetValidOptions()
    {
        $this->assertTrue(is_array(TextFilter::getValidOptions()));
        $this->assertSame($this->getAbstractFilterValidOptions(), TextFilter::getValidOptions());
    }

    public function testApplyFilter()
    {
        $handler = $this->getMock('\SphinxClient', ['SetFilter'], [], '', false);
        $handler->expects($this->never())->method($this->anything());

        /** @var TextFilter|\PHPUnit_Framework_MockObject_MockObject $mock */
        $mock = $this->getCustomMock('Da2e\FiltrationSphinxClientBundle\Filter\TextFilter', [
            'checkSphinxHandlerInstance',
        ]);

        $mock->expects($this->never())->method('checkSphinxHandlerInstance')->with($handler);

        $mock->setValue([]);
        $mock->applyFilter($handler);
    }

    public function testApplyFilter_NotSphinxClientHandler()
    {
        $handler = $this->getMock('\SphinxClient', ['SetFilter'], [], '', false);
        $handler->expects($this->never())->method($this->anything());

        /** @var TextFilter|\PHPUnit_Framework_MockObject_MockObject $mock */
        $mock = $this->getCustomMock('Da2e\FiltrationSphinxClientBundle\Filter\TextFilter', [
            'checkSphinxHandlerInstance',
        ]);

        $mock->expects($this->never())->method('checkSphinxHandlerInstance')->with($handler);

        $mock->setValue([]);
        $mock->applyFilter('foobar');
    }

    public function testApplyFilter_HasAppliedValue()
    {
        $handler = $this->getMock('\SphinxClient', ['SetFilter'], [], '', false);
        $handler->expects($this->never())->method($this->anything());

        /** @var TextFilter|\PHPUnit_Framework_MockObject_MockObject $mock */
        $mock = $this->getCustomMock('Da2e\FiltrationSphinxClientBundle\Filter\TextFilter', [
            'checkSphinxHandlerInstance',
        ]);

        $mock->expects($this->never())->method('checkSphinxHandlerInstance')->with($handler);

        $mock->setValue('foobar');
        $mock->setFieldName('foo');
        $mock->applyFilter($handler);
    }
}

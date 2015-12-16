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

use Da2e\FiltrationSphinxClientBundle\Filter\EntityFilter;
use Da2e\FiltrationBundle\Tests\Filter\Filter\AbstractFilterTestCase;

/**
 * Class EntityFilterTest
 *
 * @author Dmitry Abrosimov <abrosimovs@gmail.com>
 */
class EntityFilterTest extends AbstractFilterTestCase
{
    public function testGetValidOptions()
    {
        $this->assertTrue(is_array(EntityFilter::getValidOptions()));
        $this->assertSame(
            array_merge($this->getAbstractFilterValidOptions(), [
                'exclude' => [
                    'setter' => 'setExclude',
                    'empty'  => false,
                    'type'   => 'bool',
                ],
            ]),
            EntityFilter::getValidOptions()
        );
    }

    public function testApplyFilter()
    {
        $handler = $this->getMock('\SphinxClient', ['SetFilter'], [], '', false);
        $handler->expects($this->never())->method($this->anything());

        /** @var EntityFilter|\PHPUnit_Framework_MockObject_MockObject $mock */
        $mock = $this->getCustomMock('Da2e\FiltrationSphinxClientBundle\Filter\EntityFilter', [
            'checkSphinxHandlerInstance',
        ]);

        $mock->expects($this->once())->method('checkSphinxHandlerInstance')->with($handler);

        $mock->setValue([]);
        $mock->applyFilter($handler);
    }

    public function testApplyFilter_HasAppliedValue()
    {
        $handler = $this->getMock('\SphinxClient', ['SetFilter'], [], '', false);
        $handler->expects($this->once())->method('SetFilter')->with('foo', [1, 2, 3], false);
        $handler->expects($this->never())->method($this->logicalNot($this->matches('SetFilter')));

        /** @var EntityFilter|\PHPUnit_Framework_MockObject_MockObject $mock */
        $mock = $this->getCustomMock('Da2e\FiltrationSphinxClientBundle\Filter\EntityFilter', [
            'checkSphinxHandlerInstance',
        ]);

        $mock->expects($this->once())->method('checkSphinxHandlerInstance')->with($handler);

        $e1 = $this->getMock('\stdClass', ['getId']);
        $e1->expects($this->any())->method('getId')->willReturn(1);

        $e2 = $this->getMock('\stdClass', ['getId']);
        $e2->expects($this->any())->method('getId')->willReturn(2);

        $e3 = $this->getMock('\stdClass', ['getId']);
        $e3->expects($this->any())->method('getId')->willReturn(3);

        $mock->setValue([$e1, $e2, $e3]);
        $mock->setFieldName('foo');
        $mock->applyFilter($handler);
    }

    public function testApplyFilter_HasAppliedValue_Exclude()
    {
        $handler = $this->getMock('\SphinxClient', ['SetFilter'], [], '', false);
        $handler->expects($this->once())->method('SetFilter')->with('foo', [1, 2, 3], true);
        $handler->expects($this->never())->method($this->logicalNot($this->matches('SetFilter')));

        /** @var EntityFilter|\PHPUnit_Framework_MockObject_MockObject $mock */
        $mock = $this->getCustomMock('Da2e\FiltrationSphinxClientBundle\Filter\EntityFilter', [
            'checkSphinxHandlerInstance',
        ]);

        $mock->expects($this->once())->method('checkSphinxHandlerInstance')->with($handler);

        $e1 = $this->getMock('\stdClass', ['getId']);
        $e1->expects($this->any())->method('getId')->willReturn(1);

        $e2 = $this->getMock('\stdClass', ['getId']);
        $e2->expects($this->any())->method('getId')->willReturn(2);

        $e3 = $this->getMock('\stdClass', ['getId']);
        $e3->expects($this->any())->method('getId')->willReturn(3);

        $mock->setValue([$e1, $e2, $e3]);
        $mock->setFieldName('foo');
        $mock->setExclude(true);
        $mock->applyFilter($handler);
    }
}

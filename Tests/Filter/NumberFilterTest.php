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

use Da2e\FiltrationBundle\Exception\Filter\Filter\InvalidArgumentException;
use Da2e\FiltrationBundle\Exception\Filter\Filter\LogicException;
use Da2e\FiltrationBundle\Filter\Filter\AbstractRangeOrSingleFilter;
use Da2e\FiltrationSphinxClientBundle\Filter\NumberFilter;

/**
 * Class NumberFilterTest
 *
 * @author Dmitry Abrosimov <abrosimovs@gmail.com>
 */
class NumberFilterTest extends SphinxAPIFilterTestCase
{
    public function testGetValidOptions()
    {
        $this->assertTrue(is_array(NumberFilter::getValidOptions()));
        $this->assertSame(
            array_merge($this->getAbstractRangeOrSingleFilterValidOptions(), [
                'float' => [
                    'setter' => 'setFloat',
                    'empty'  => false,
                    'type'   => 'bool',
                ],
                'exclude'            => [
                    'setter' => 'setExclude',
                    'empty'  => false,
                    'type'   => 'bool',
                ],
                'default_min'        => [
                    'setter' => 'setDefaultMin',
                    'empty'  => false,
                    'type'   => ['int', 'float'],
                ],
                'default_max'        => [
                    'setter' => 'setDefaultMax',
                    'empty'  => false,
                    'type'   => ['int', 'float'],
                ],
                'default_float_step' => [
                    'setter' => 'setDefaultFloatStep',
                    'empty'  => false,
                    'type'   => 'float',
                ],
            ]),
            NumberFilter::getValidOptions()
        );
    }

    public function testDefaultMinProperty()
    {
        $property = $this->getPrivateProperty(
            '\Da2e\FiltrationSphinxClientBundle\Filter\NumberFilter',
            'defaultMin'
        );

        $this->assertSame(0, $property->getValue($this->getNumberFilterMock()));
    }

    public function testDefaultMaxProperty()
    {
        $property = $this->getPrivateProperty(
            '\Da2e\FiltrationSphinxClientBundle\Filter\NumberFilter',
            'defaultMax'
        );

        $this->assertSame(PHP_INT_MAX, $property->getValue($this->getNumberFilterMock()));
    }

    public function testDefaultFloatStep()
    {
        $property = $this->getPrivateProperty(
            '\Da2e\FiltrationSphinxClientBundle\Filter\NumberFilter',
            'defaultFloatStep'
        );

        $this->assertSame(0.01, $property->getValue($this->getNumberFilterMock()));
    }

    public function testGetDefaultMin()
    {
        $filterMock = $this->getNumberFilterMock();
        $this->assertSame(0, $filterMock->getDefaultMin());
    }

    public function testSetDefaultMin()
    {
        $filterMock = $this->getNumberFilterMock();

        $filterMock->setDefaultMin(123);
        $this->assertSame(123, $filterMock->getDefaultMin());

        $filterMock->setDefaultMin(123.99);
        $this->assertSame(123.99, $filterMock->getDefaultMin());
    }

    public function testSetDefaultMin_InvalidArg()
    {
        $args = [
            '',
            'foobar',
            null,
            new \stdClass(),
            [],
            function () {
            },
            true,
            false,
        ];

        $exceptionCount = 0;

        foreach ($args as $arg) {
            $filterMock = $this->getNumberFilterMock();

            try {
                $filterMock->setDefaultMin($arg);
            } catch (InvalidArgumentException $e) {
                $exceptionCount++;
            }
        }

        $this->assertEquals(count($args), $exceptionCount);
    }

    public function testGetDefaultMax()
    {
        $filterMock = $this->getNumberFilterMock();
        $this->assertSame(PHP_INT_MAX, $filterMock->getDefaultMax());
    }

    public function testSetDefaultMax()
    {
        $filterMock = $this->getNumberFilterMock();

        $filterMock->setDefaultMax(123);
        $this->assertSame(123, $filterMock->getDefaultMax());

        $filterMock->setDefaultMax(123.99);
        $this->assertSame(123.99, $filterMock->getDefaultMax());
    }

    public function testSetDefaultMax_InvalidArg()
    {
        $args = [
            '',
            'foobar',
            null,
            new \stdClass(),
            [],
            function () {
            },
            true,
            false,
        ];

        $exceptionCount = 0;

        foreach ($args as $arg) {
            $filterMock = $this->getNumberFilterMock();

            try {
                $filterMock->setDefaultMax($arg);
            } catch (InvalidArgumentException $e) {
                $exceptionCount++;
            }
        }

        $this->assertEquals(count($args), $exceptionCount);
    }

    public function testGetDefaultFloatStep()
    {
        $filterMock = $this->getNumberFilterMock();
        $this->assertSame(0.01, $filterMock->getDefaultFloatStep());
    }

    public function testSetDefaultFloatStep()
    {
        $filterMock = $this->getNumberFilterMock();

        $filterMock->setDefaultFloatStep(123.99);
        $this->assertSame(123.99, $filterMock->getDefaultFloatStep());
    }

    public function testSetDefaultFloatStep_InvalidArg()
    {
        $args = [
            '',
            'foobar',
            null,
            new \stdClass(),
            [],
            function () {
            },
            true,
            false,
            0.0,
            -1.0,
        ];

        $exceptionCount = 0;

        foreach ($args as $arg) {
            $filterMock = $this->getNumberFilterMock();

            try {
                $filterMock->setDefaultFloatStep($arg);
            } catch (InvalidArgumentException $e) {
                $exceptionCount++;
            }
        }

        $this->assertEquals(count($args), $exceptionCount);
    }

    public function testGetValueMinStep()
    {
        $filterMock = $this->getNumberFilterMock();
        $this->assertSame(1, $this->invokeMethod($filterMock, 'getValueMinStep'));

        $filterMock->setFloat(true);
        $filterMock->setDefaultFloatStep(100.0);
        $this->assertSame(100.0, $this->invokeMethod($filterMock, 'getValueMinStep'));

        $filterMock->setFloat(false);
        $this->assertSame(1, $this->invokeMethod($filterMock, 'getValueMinStep'));
    }

    public function testGetConvertedDefaultMin()
    {
        $filterMock = $this->getNumberFilterMock();
        $this->assertSame(0, $this->invokeMethod($filterMock, 'getConvertedDefaultMin'));

        $filterMock->setDefaultMin(1);
        $this->assertSame(1, $this->invokeMethod($filterMock, 'getConvertedDefaultMin'));

        $filterMock->setFloat(true);
        $filterMock->setDefaultMin(5.0);
        $this->assertSame(5.0, $this->invokeMethod($filterMock, 'getConvertedDefaultMin'));

        $filterMock->setFloat(false);
        $filterMock->setDefaultMin(50.00);
        $this->assertSame(50, $this->invokeMethod($filterMock, 'getConvertedDefaultMin'));

        $filterMock->setFloat(true);
        $filterMock->setDefaultMin(100);
        $this->assertSame(100.00, $this->invokeMethod($filterMock, 'getConvertedDefaultMin'));
    }

    public function testGetConvertedDefaultMax()
    {
        $filterMock = $this->getNumberFilterMock();
        $this->assertSame(PHP_INT_MAX, $this->invokeMethod($filterMock, 'getConvertedDefaultMax'));

        $filterMock->setDefaultMax(1);
        $this->assertSame(1, $this->invokeMethod($filterMock, 'getConvertedDefaultMax'));

        $filterMock->setFloat(true);
        $filterMock->setDefaultMax(5.0);
        $this->assertSame(5.0, $this->invokeMethod($filterMock, 'getConvertedDefaultMax'));

        $filterMock->setFloat(false);
        $filterMock->setDefaultMax(50.00);
        $this->assertSame(50, $this->invokeMethod($filterMock, 'getConvertedDefaultMax'));

        $filterMock->setFloat(true);
        $filterMock->setDefaultMax(100);
        $this->assertSame(100.00, $this->invokeMethod($filterMock, 'getConvertedDefaultMax'));
    }

    public function testApplyFilter_Ranged()
    {
        $handler = $this->getSphinxClientMock();
        $handler->expects($this->never())->method($this->anything());

        $filterMock = $this->getNumberFilterMock([
            'checkSphinxHandlerInstance',
            'applySingleFilter',
            'applyRangedFilter'
        ]);

        $filterMock->expects($this->once())->method('checkSphinxHandlerInstance')->with($handler);
        $filterMock->expects($this->never())->method('applySingleFilter')->with($handler);
        $filterMock->expects($this->never())->method('applyRangedFilter')->with($handler);

        $filterMock->setFromValue(null);
        $filterMock->setToValue(null);
        $filterMock->applyFilter($handler);
    }

    public function testApplyFilter_Ranged_HasAppliedValue()
    {
        $handler = $this->getSphinxClientMock();
        $handler->expects($this->never())->method($this->anything());

        $filterMock = $this->getNumberFilterMock([
            'checkSphinxHandlerInstance',
            'applySingleFilter',
            'applyRangedFilter'
        ]);

        $filterMock->expects($this->once())->method('checkSphinxHandlerInstance')->with($handler);
        $filterMock->expects($this->once())->method('applyRangedFilter')->with($handler);
        $filterMock->expects($this->never())->method('applySingleFilter')->with($handler);

        $filterMock->setFromValue(1);
        $filterMock->setToValue(2);
        $filterMock->applyFilter($handler);
    }

    public function testApplyFilter_Single()
    {
        $handler = $this->getSphinxClientMock();
        $handler->expects($this->never())->method($this->anything());

        $filterMock = $this->getNumberFilterMock([
            'checkSphinxHandlerInstance',
            'applySingleFilter',
            'applyRangedFilter'
        ]);

        $filterMock->expects($this->once())->method('checkSphinxHandlerInstance')->with($handler);
        $filterMock->expects($this->never())->method('applySingleFilter')->with($handler);
        $filterMock->expects($this->never())->method('applyRangedFilter')->with($handler);

        $filterMock->setSingle(true);
        $filterMock->setValue(null);
        $filterMock->applyFilter($handler);
    }

    public function testApplyFilter_Single_HasAppliedValue()
    {
        $handler = $this->getSphinxClientMock();
        $handler->expects($this->never())->method($this->anything());

        $filterMock = $this->getNumberFilterMock([
            'checkSphinxHandlerInstance',
            'applySingleFilter',
            'applyRangedFilter'
        ]);

        $filterMock->expects($this->once())->method('checkSphinxHandlerInstance')->with($handler);
        $filterMock->expects($this->once())->method('applySingleFilter')->with($handler);
        $filterMock->expects($this->never())->method('applyRangedFilter')->with($handler);

        $filterMock->setSingle(true);
        $filterMock->setValue(123);
        $filterMock->applyFilter($handler);
    }

    public function testApplySingleFilter_Exact()
    {
        $handler = $this->getSphinxClientMock();
        $handler->expects($this->never())->method($this->logicalNot($this->matches('SetFilter')));
        $handler->expects($this->once())->method('SetFilter')->with('foo', 123, false);

        $filterMock = $this->getNumberFilterMock();
        $filterMock->setValue(123);
        $filterMock->setFieldName('foo');
        $filterMock->setSingle(true);
        $filterMock->setSingleType(AbstractRangeOrSingleFilter::SINGLE_TYPE_EXACT);
        $this->invokeMethod($filterMock, 'applySingleFilter', [$handler]);
    }

    public function testApplySingleFilter_Exact_Exclude()
    {
        $handler = $this->getSphinxClientMock();
        $handler->expects($this->never())->method($this->logicalNot($this->matches('SetFilter')));
        $handler->expects($this->once())->method('SetFilter')->with('foo', 123, true);

        $filterMock = $this->getNumberFilterMock();
        $filterMock->setValue(123);
        $filterMock->setFieldName('foo');
        $filterMock->setSingle(true);
        $filterMock->setSingleType(AbstractRangeOrSingleFilter::SINGLE_TYPE_EXACT);
        $filterMock->setExclude(true);
        $this->invokeMethod($filterMock, 'applySingleFilter', [$handler]);
    }

    public function testApplySingleFilter_Greater_ValueMustNotBeGreaterThanDefaultMax()
    {
        $handler = $this->getSphinxClientMock();
        $handler->expects($this->never())->method($this->anything());

        $filterMock = $this->getNumberFilterMock();
        $filterMock->setFieldName('foo');
        $filterMock->setSingle(true);
        $filterMock->setDefaultMax(10);

        $args = [
            [11, AbstractRangeOrSingleFilter::SINGLE_TYPE_GREATER],
            [10.01, AbstractRangeOrSingleFilter::SINGLE_TYPE_GREATER_OR_EQUAL],
        ];

        $exceptionCount = 0;

        foreach ($args as $arg) {
            try {
                $filterMock->setValue($arg[0]);
                $filterMock->setSingleType($arg[1]);
                $this->invokeMethod($filterMock, 'applySingleFilter', [$handler]);
            } catch (LogicException $e) {
                $exceptionCount++;
            }
        }

        $this->assertSame(count($args), $exceptionCount);
    }

    public function testApplySingleFilter_Less_ValueMustNotBeLessThanDefaultMin()
    {
        $handler = $this->getSphinxClientMock();
        $handler->expects($this->never())->method($this->anything());

        $filterMock = $this->getNumberFilterMock();
        $filterMock->setFieldName('foo');
        $filterMock->setSingle(true);
        $filterMock->setDefaultMin(10);

        $args = [
            [9, AbstractRangeOrSingleFilter::SINGLE_TYPE_LESS],
            [9.99, AbstractRangeOrSingleFilter::SINGLE_TYPE_LESS_OR_EQUAL],
        ];

        $exceptionCount = 0;

        foreach ($args as $arg) {
            try {
                $filterMock->setValue($arg[0]);
                $filterMock->setSingleType($arg[1]);
                $this->invokeMethod($filterMock, 'applySingleFilter', [$handler]);
            } catch (LogicException $e) {
                $exceptionCount++;
            }
        }

        $this->assertSame(count($args), $exceptionCount);
    }

    public function testApplySingleFilter_NotExact()
    {
        $args = [
            AbstractRangeOrSingleFilter::SINGLE_TYPE_GREATER,
            AbstractRangeOrSingleFilter::SINGLE_TYPE_GREATER_OR_EQUAL,
            AbstractRangeOrSingleFilter::SINGLE_TYPE_LESS,
            AbstractRangeOrSingleFilter::SINGLE_TYPE_LESS_OR_EQUAL,
        ];

        foreach ($args as $arg) {
            $handler = $this->getSphinxClientMock();
            $handler->expects($this->never())->method($this->logicalNot($this->matches('SetFilterRange')));
            $handler->expects($this->once())->method('SetFilterRange')->with('foo', 333, 666, false);

            $filterMock = $this->getNumberFilterMock(['getBoundsForSingleNonExactFilter']);
            $filterMock->expects($this->atLeastOnce())
                ->method('getBoundsForSingleNonExactFilter')
                ->willReturn([333, 666]);

            $filterMock->setFieldName('foo');
            $filterMock->setSingle(true);
            $filterMock->setValue(333);
            $filterMock->setSingleType($arg);
            $this->invokeMethod($filterMock, 'applySingleFilter', [$handler]);
        }
    }

    public function testApplySingleFilter_NotExact_Exclude()
    {
        $args = [
            AbstractRangeOrSingleFilter::SINGLE_TYPE_GREATER,
            AbstractRangeOrSingleFilter::SINGLE_TYPE_GREATER_OR_EQUAL,
            AbstractRangeOrSingleFilter::SINGLE_TYPE_LESS,
            AbstractRangeOrSingleFilter::SINGLE_TYPE_LESS_OR_EQUAL,
        ];

        foreach ($args as $arg) {
            $handler = $this->getSphinxClientMock();
            $handler->expects($this->never())->method($this->logicalNot($this->matches('SetFilterRange')));
            $handler->expects($this->once())->method('SetFilterRange')->with('foo', 333, 666, true);

            $filterMock = $this->getNumberFilterMock(['getBoundsForSingleNonExactFilter']);
            $filterMock->expects($this->atLeastOnce())
                ->method('getBoundsForSingleNonExactFilter')
                ->willReturn([333, 666]);

            $filterMock->setFieldName('foo');
            $filterMock->setSingle(true);
            $filterMock->setValue(333);
            $filterMock->setSingleType($arg);
            $filterMock->setExclude(true);
            $this->invokeMethod($filterMock, 'applySingleFilter', [$handler]);
        }
    }

    public function testApplySingleFilter_NotExact_Float()
    {
        $args = [
            AbstractRangeOrSingleFilter::SINGLE_TYPE_GREATER,
            AbstractRangeOrSingleFilter::SINGLE_TYPE_GREATER_OR_EQUAL,
            AbstractRangeOrSingleFilter::SINGLE_TYPE_LESS,
            AbstractRangeOrSingleFilter::SINGLE_TYPE_LESS_OR_EQUAL,
        ];

        foreach ($args as $arg) {
            $handler = $this->getSphinxClientMock();
            $handler->expects($this->never())->method($this->logicalNot($this->matches('SetFilterFloatRange')));
            $handler->expects($this->once())->method('SetFilterFloatRange')->with('foo', 333, 666, false);

            $filterMock = $this->getNumberFilterMock(['getBoundsForSingleNonExactFilter']);
            $filterMock->expects($this->atLeastOnce())
                ->method('getBoundsForSingleNonExactFilter')
                ->willReturn([333, 666]);

            $filterMock->setFieldName('foo');
            $filterMock->setSingle(true);
            $filterMock->setValue(333);
            $filterMock->setSingleType($arg);
            $filterMock->setFloat(true);
            $this->invokeMethod($filterMock, 'applySingleFilter', [$handler]);
        }
    }

    public function testApplySingleFilter_NotExact_Float_Exclude()
    {
        $args = [
            AbstractRangeOrSingleFilter::SINGLE_TYPE_GREATER,
            AbstractRangeOrSingleFilter::SINGLE_TYPE_GREATER_OR_EQUAL,
            AbstractRangeOrSingleFilter::SINGLE_TYPE_LESS,
            AbstractRangeOrSingleFilter::SINGLE_TYPE_LESS_OR_EQUAL,
        ];

        foreach ($args as $arg) {
            $handler = $this->getSphinxClientMock();
            $handler->expects($this->never())->method($this->logicalNot($this->matches('SetFilterFloatRange')));
            $handler->expects($this->once())->method('SetFilterFloatRange')->with('foo', 333.33, 666.66, true);

            $filterMock = $this->getNumberFilterMock(['getBoundsForSingleNonExactFilter']);
            $filterMock->expects($this->atLeastOnce())
                ->method('getBoundsForSingleNonExactFilter')
                ->willReturn([333.33, 666.66]);

            $filterMock->setFieldName('foo');
            $filterMock->setSingle(true);
            $filterMock->setValue(333.33);
            $filterMock->setSingleType($arg);
            $filterMock->setExclude(true);
            $filterMock->setFloat(true);
            $this->invokeMethod($filterMock, 'applySingleFilter', [$handler]);
        }
    }

    /**
     * @expectedException \Da2e\FiltrationBundle\Exception\Filter\Filter\LogicException
     */
    public function testApplyRangedFilter_DefaultMinMustNotBeGreaterThanDefaultMax()
    {
        $handler = $this->getSphinxClientMock();
        $handler->expects($this->never())->method($this->anything());

        $filterMock = $this->getNumberFilterMock();
        $filterMock->setDefaultMin(10);
        $filterMock->setDefaultMax(9);
        $filterMock->setFromValue(10);
        $filterMock->setToValue(11);

        $this->invokeMethod($filterMock, 'applyRangedFilter', [$handler]);
    }

    public function testApplyRangedFilter()
    {
        $handler = $this->getSphinxClientMock();
        $handler->expects($this->never())->method($this->logicalNot($this->matches('SetFilterRange')));
        $handler->expects($this->once())->method('SetFilterRange')->with('foo', 333, 666, false);

        $filterMock = $this->getNumberFilterMock(['getBoundsForRangedFilter']);
        $filterMock->expects($this->atLeastOnce())->method('getBoundsForRangedFilter')->willReturn([333, 666]);

        $filterMock->setSingle(false);
        $filterMock->setFieldName('foo');
        $filterMock->setFromValue(10);
        $filterMock->setToValue(11);

        $this->invokeMethod($filterMock, 'applyRangedFilter', [$handler]);
    }

    public function testApplyRangedFilter_Exclude()
    {
        $handler = $this->getSphinxClientMock();
        $handler->expects($this->never())->method($this->logicalNot($this->matches('SetFilterRange')));
        $handler->expects($this->once())->method('SetFilterRange')->with('foo', 333, 666, true);

        $filterMock = $this->getNumberFilterMock(['getBoundsForRangedFilter']);
        $filterMock->expects($this->atLeastOnce())->method('getBoundsForRangedFilter')->willReturn([333, 666]);

        $filterMock->setSingle(false);
        $filterMock->setFieldName('foo');
        $filterMock->setFromValue(10);
        $filterMock->setToValue(11);
        $filterMock->setExclude(true);

        $this->invokeMethod($filterMock, 'applyRangedFilter', [$handler]);
    }

    public function testApplyRangedFilter_Float()
    {
        $handler = $this->getSphinxClientMock();
        $handler->expects($this->never())->method($this->logicalNot($this->matches('SetFilterFloatRange')));
        $handler->expects($this->once())->method('SetFilterFloatRange')->with('foo', 333.33, 666.66, false);

        $filterMock = $this->getNumberFilterMock(['getBoundsForRangedFilter']);
        $filterMock->expects($this->atLeastOnce())->method('getBoundsForRangedFilter')->willReturn([333.33, 666.66]);

        $filterMock->setSingle(false);
        $filterMock->setFieldName('foo');
        $filterMock->setFromValue(10);
        $filterMock->setToValue(11);
        $filterMock->setFloat(true);

        $this->invokeMethod($filterMock, 'applyRangedFilter', [$handler]);
    }

    public function testApplyRangedFilter_Float_Exclude()
    {
        $handler = $this->getSphinxClientMock();
        $handler->expects($this->never())->method($this->logicalNot($this->matches('SetFilterFloatRange')));
        $handler->expects($this->once())->method('SetFilterFloatRange')->with('foo', 333.33, 666.66, true);

        $filterMock = $this->getNumberFilterMock(['getBoundsForRangedFilter']);
        $filterMock->expects($this->atLeastOnce())->method('getBoundsForRangedFilter')->willReturn([333.33, 666.66]);

        $filterMock->setSingle(false);
        $filterMock->setFieldName('foo');
        $filterMock->setFromValue(10);
        $filterMock->setToValue(11);
        $filterMock->setExclude(true);
        $filterMock->setFloat(true);

        $this->invokeMethod($filterMock, 'applyRangedFilter', [$handler]);
    }

    public function testGetBoundsForSingleNonExactFilter()
    {
        $filterMock = $this->getNumberFilterMock();
        $filterMock->setFloat(false);
        $filterMock->setValue(100);
        $filterMock->setDefaultMin(0);
        $filterMock->setDefaultMax(999);
        $filterMock->setSingle(true);

        $filterMock->setSingleType(AbstractRangeOrSingleFilter::SINGLE_TYPE_GREATER);
        $result = $this->invokeMethod($filterMock, 'getBoundsForSingleNonExactFilter');
        $this->assertSame([101, 999], $result);

        $filterMock->setSingleType(AbstractRangeOrSingleFilter::SINGLE_TYPE_GREATER_OR_EQUAL);
        $result = $this->invokeMethod($filterMock, 'getBoundsForSingleNonExactFilter');
        $this->assertSame([100, 999], $result);

        $filterMock->setSingleType(AbstractRangeOrSingleFilter::SINGLE_TYPE_LESS);
        $result = $this->invokeMethod($filterMock, 'getBoundsForSingleNonExactFilter');
        $this->assertSame([0, 99], $result);

        $filterMock->setSingleType(AbstractRangeOrSingleFilter::SINGLE_TYPE_LESS_OR_EQUAL);
        $result = $this->invokeMethod($filterMock, 'getBoundsForSingleNonExactFilter');
        $this->assertSame([0, 100], $result);
    }

    public function testGetBoundsForSingleNonExactFilter_Float()
    {
        $filterMock = $this->getNumberFilterMock();
        $filterMock->setFloat(true);
        $filterMock->setValue(100.0);
        $filterMock->setDefaultMin(0.0);
        $filterMock->setDefaultMax(999.0);
        $filterMock->setSingle(true);
        $filterMock->setDefaultFloatStep(0.01);

        $filterMock->setSingleType(AbstractRangeOrSingleFilter::SINGLE_TYPE_GREATER);
        $result = $this->invokeMethod($filterMock, 'getBoundsForSingleNonExactFilter');
        $this->assertSame([100.01, 999.0], $result);

        $filterMock->setSingleType(AbstractRangeOrSingleFilter::SINGLE_TYPE_GREATER_OR_EQUAL);
        $result = $this->invokeMethod($filterMock, 'getBoundsForSingleNonExactFilter');
        $this->assertSame([100.0, 999.0], $result);

        $filterMock->setSingleType(AbstractRangeOrSingleFilter::SINGLE_TYPE_LESS);
        $result = $this->invokeMethod($filterMock, 'getBoundsForSingleNonExactFilter');
        $this->assertSame([0.0, 99.99], $result);

        $filterMock->setSingleType(AbstractRangeOrSingleFilter::SINGLE_TYPE_LESS_OR_EQUAL);
        $result = $this->invokeMethod($filterMock, 'getBoundsForSingleNonExactFilter');
        $this->assertSame([0.0, 100.0], $result);
    }

    public function testGetBoundsForRangedFilter()
    {
        $filterMock = $this->getNumberFilterMock();
        $filterMock->setFloat(false);
        $filterMock->setFromValue(100);
        $filterMock->setToValue(200);
        $filterMock->setSingle(false);

        $filterMock->setRangedFromType(AbstractRangeOrSingleFilter::RANGED_FROM_TYPE_GREATER);
        $filterMock->setRangedToType(AbstractRangeOrSingleFilter::RANGED_TO_TYPE_LESS_OR_EQUAL);
        $result = $this->invokeMethod($filterMock, 'getBoundsForRangedFilter');
        $this->assertSame([101, 200], $result);

        $filterMock->setRangedFromType(AbstractRangeOrSingleFilter::RANGED_FROM_TYPE_GREATER_OR_EQUAL);
        $filterMock->setRangedToType(AbstractRangeOrSingleFilter::RANGED_TO_TYPE_LESS);
        $result = $this->invokeMethod($filterMock, 'getBoundsForRangedFilter');
        $this->assertSame([100, 199], $result);

        $filterMock->setRangedFromType(AbstractRangeOrSingleFilter::RANGED_FROM_TYPE_GREATER);
        $filterMock->setRangedToType(AbstractRangeOrSingleFilter::RANGED_TO_TYPE_LESS);
        $result = $this->invokeMethod($filterMock, 'getBoundsForRangedFilter');
        $this->assertSame([101, 199], $result);

        $filterMock->setRangedFromType(AbstractRangeOrSingleFilter::RANGED_FROM_TYPE_GREATER_OR_EQUAL);
        $filterMock->setRangedToType(AbstractRangeOrSingleFilter::RANGED_TO_TYPE_LESS_OR_EQUAL);
        $result = $this->invokeMethod($filterMock, 'getBoundsForRangedFilter');
        $this->assertSame([100, 200], $result);
    }

    public function testGetBoundsForRangedFilter_OneOfFieldsIsAppliedOnly()
    {
        // Only "from" is applied
        $filterMock = $this->getNumberFilterMock();
        $filterMock->setFloat(false);
        $filterMock->setSingle(false);
        $filterMock->setDefaultMin(0);
        $filterMock->setDefaultMax(999);

        $filterMock->setFromValue(100);
        $filterMock->setToValue(null);
        $filterMock->setRangedFromType(AbstractRangeOrSingleFilter::RANGED_FROM_TYPE_GREATER);
        $filterMock->setRangedToType(AbstractRangeOrSingleFilter::RANGED_TO_TYPE_LESS_OR_EQUAL);
        $result = $this->invokeMethod($filterMock, 'getBoundsForRangedFilter');
        $this->assertSame([101, 999], $result);

        $filterMock->setFromValue(100);
        $filterMock->setToValue(null);
        $filterMock->setRangedFromType(AbstractRangeOrSingleFilter::RANGED_FROM_TYPE_GREATER_OR_EQUAL);
        $filterMock->setRangedToType(AbstractRangeOrSingleFilter::RANGED_TO_TYPE_LESS);
        $result = $this->invokeMethod($filterMock, 'getBoundsForRangedFilter');
        $this->assertSame([100, 998], $result);

        $filterMock->setFromValue(100);
        $filterMock->setToValue(null);
        $filterMock->setRangedFromType(AbstractRangeOrSingleFilter::RANGED_FROM_TYPE_GREATER);
        $filterMock->setRangedToType(AbstractRangeOrSingleFilter::RANGED_TO_TYPE_LESS);
        $result = $this->invokeMethod($filterMock, 'getBoundsForRangedFilter');
        $this->assertSame([101, 998], $result);

        $filterMock->setFromValue(100);
        $filterMock->setToValue(null);
        $filterMock->setRangedFromType(AbstractRangeOrSingleFilter::RANGED_FROM_TYPE_GREATER_OR_EQUAL);
        $filterMock->setRangedToType(AbstractRangeOrSingleFilter::RANGED_TO_TYPE_LESS_OR_EQUAL);
        $result = $this->invokeMethod($filterMock, 'getBoundsForRangedFilter');
        $this->assertSame([100, 999], $result);

        // Only "to" is applied
        $filterMock = $this->getNumberFilterMock();
        $filterMock->setFloat(false);
        $filterMock->setSingle(false);
        $filterMock->setDefaultMin(0);
        $filterMock->setDefaultMax(999);

        $filterMock->setFromValue(null);
        $filterMock->setToValue(200);
        $filterMock->setRangedFromType(AbstractRangeOrSingleFilter::RANGED_FROM_TYPE_GREATER);
        $filterMock->setRangedToType(AbstractRangeOrSingleFilter::RANGED_TO_TYPE_LESS_OR_EQUAL);
        $result = $this->invokeMethod($filterMock, 'getBoundsForRangedFilter');
        $this->assertSame([1, 200], $result);

        $filterMock->setFromValue(null);
        $filterMock->setToValue(200);
        $filterMock->setRangedFromType(AbstractRangeOrSingleFilter::RANGED_FROM_TYPE_GREATER_OR_EQUAL);
        $filterMock->setRangedToType(AbstractRangeOrSingleFilter::RANGED_TO_TYPE_LESS);
        $result = $this->invokeMethod($filterMock, 'getBoundsForRangedFilter');
        $this->assertSame([0, 199], $result);

        $filterMock->setFromValue(null);
        $filterMock->setToValue(200);
        $filterMock->setRangedFromType(AbstractRangeOrSingleFilter::RANGED_FROM_TYPE_GREATER);
        $filterMock->setRangedToType(AbstractRangeOrSingleFilter::RANGED_TO_TYPE_LESS);
        $result = $this->invokeMethod($filterMock, 'getBoundsForRangedFilter');
        $this->assertSame([1, 199], $result);

        $filterMock->setFromValue(null);
        $filterMock->setToValue(200);
        $filterMock->setRangedFromType(AbstractRangeOrSingleFilter::RANGED_FROM_TYPE_GREATER_OR_EQUAL);
        $filterMock->setRangedToType(AbstractRangeOrSingleFilter::RANGED_TO_TYPE_LESS_OR_EQUAL);
        $result = $this->invokeMethod($filterMock, 'getBoundsForRangedFilter');
        $this->assertSame([0, 200], $result);
    }

    public function testGetBoundsForRangedFilter_Float()
    {
        $filterMock = $this->getNumberFilterMock();
        $filterMock->setFloat(true);
        $filterMock->setFromValue(100.0);
        $filterMock->setToValue(200.0);
        $filterMock->setSingle(false);
        $filterMock->setDefaultFloatStep(0.01);

        $filterMock->setRangedFromType(AbstractRangeOrSingleFilter::RANGED_FROM_TYPE_GREATER);
        $filterMock->setRangedToType(AbstractRangeOrSingleFilter::RANGED_TO_TYPE_LESS_OR_EQUAL);
        $result = $this->invokeMethod($filterMock, 'getBoundsForRangedFilter');
        $this->assertSame([100.01, 200.0], $result);

        $filterMock->setRangedFromType(AbstractRangeOrSingleFilter::RANGED_FROM_TYPE_GREATER_OR_EQUAL);
        $filterMock->setRangedToType(AbstractRangeOrSingleFilter::RANGED_TO_TYPE_LESS);
        $result = $this->invokeMethod($filterMock, 'getBoundsForRangedFilter');
        $this->assertSame([100.0, 199.99], $result);

        $filterMock->setRangedFromType(AbstractRangeOrSingleFilter::RANGED_FROM_TYPE_GREATER);
        $filterMock->setRangedToType(AbstractRangeOrSingleFilter::RANGED_TO_TYPE_LESS);
        $result = $this->invokeMethod($filterMock, 'getBoundsForRangedFilter');
        $this->assertSame([100.01, 199.99], $result);

        $filterMock->setRangedFromType(AbstractRangeOrSingleFilter::RANGED_FROM_TYPE_GREATER_OR_EQUAL);
        $filterMock->setRangedToType(AbstractRangeOrSingleFilter::RANGED_TO_TYPE_LESS_OR_EQUAL);
        $result = $this->invokeMethod($filterMock, 'getBoundsForRangedFilter');
        $this->assertSame([100.0, 200.0], $result);
    }

    public function testGetBoundsForRangedFilter_Float_OneOfFieldsIsAppliedOnly()
    {
        // Only "from" is applied
        $filterMock = $this->getNumberFilterMock();
        $filterMock->setFloat(true);
        $filterMock->setSingle(false);
        $filterMock->setDefaultFloatStep(0.01);
        $filterMock->setDefaultMin(0.0);
        $filterMock->setDefaultMax(999.9);

        $filterMock->setFromValue(100.0);
        $filterMock->setToValue(null);
        $filterMock->setRangedFromType(AbstractRangeOrSingleFilter::RANGED_FROM_TYPE_GREATER);
        $filterMock->setRangedToType(AbstractRangeOrSingleFilter::RANGED_TO_TYPE_LESS_OR_EQUAL);
        $result = $this->invokeMethod($filterMock, 'getBoundsForRangedFilter');
        $this->assertSame([100.01, 999.9], $result);

        $filterMock->setFromValue(100.0);
        $filterMock->setToValue(null);
        $filterMock->setRangedFromType(AbstractRangeOrSingleFilter::RANGED_FROM_TYPE_GREATER_OR_EQUAL);
        $filterMock->setRangedToType(AbstractRangeOrSingleFilter::RANGED_TO_TYPE_LESS);
        $result = $this->invokeMethod($filterMock, 'getBoundsForRangedFilter');
        $this->assertSame([100.0, 999.89], $result);

        $filterMock->setFromValue(100.0);
        $filterMock->setToValue(null);
        $filterMock->setRangedFromType(AbstractRangeOrSingleFilter::RANGED_FROM_TYPE_GREATER);
        $filterMock->setRangedToType(AbstractRangeOrSingleFilter::RANGED_TO_TYPE_LESS);
        $result = $this->invokeMethod($filterMock, 'getBoundsForRangedFilter');
        $this->assertSame([100.01, 999.89], $result);

        $filterMock->setFromValue(100.0);
        $filterMock->setToValue(null);
        $filterMock->setRangedFromType(AbstractRangeOrSingleFilter::RANGED_FROM_TYPE_GREATER_OR_EQUAL);
        $filterMock->setRangedToType(AbstractRangeOrSingleFilter::RANGED_TO_TYPE_LESS_OR_EQUAL);
        $result = $this->invokeMethod($filterMock, 'getBoundsForRangedFilter');
        $this->assertSame([100.0, 999.9], $result);

        // Only "to" is applied
        $filterMock = $this->getNumberFilterMock();
        $filterMock->setFloat(true);
        $filterMock->setSingle(false);
        $filterMock->setDefaultFloatStep(0.01);
        $filterMock->setDefaultMin(0.0);
        $filterMock->setDefaultMax(999.9);

        $filterMock->setFromValue(null);
        $filterMock->setToValue(200.0);
        $filterMock->setRangedFromType(AbstractRangeOrSingleFilter::RANGED_FROM_TYPE_GREATER);
        $filterMock->setRangedToType(AbstractRangeOrSingleFilter::RANGED_TO_TYPE_LESS_OR_EQUAL);
        $result = $this->invokeMethod($filterMock, 'getBoundsForRangedFilter');
        $this->assertSame([0.01, 200.0], $result);

        $filterMock->setFromValue(null);
        $filterMock->setToValue(200.0);
        $filterMock->setRangedFromType(AbstractRangeOrSingleFilter::RANGED_FROM_TYPE_GREATER_OR_EQUAL);
        $filterMock->setRangedToType(AbstractRangeOrSingleFilter::RANGED_TO_TYPE_LESS);
        $result = $this->invokeMethod($filterMock, 'getBoundsForRangedFilter');
        $this->assertSame([0.0, 199.99], $result);

        $filterMock->setFromValue(null);
        $filterMock->setToValue(200.0);
        $filterMock->setRangedFromType(AbstractRangeOrSingleFilter::RANGED_FROM_TYPE_GREATER);
        $filterMock->setRangedToType(AbstractRangeOrSingleFilter::RANGED_TO_TYPE_LESS);
        $result = $this->invokeMethod($filterMock, 'getBoundsForRangedFilter');
        $this->assertSame([0.01, 199.99], $result);

        $filterMock->setFromValue(null);
        $filterMock->setToValue(200.0);
        $filterMock->setRangedFromType(AbstractRangeOrSingleFilter::RANGED_FROM_TYPE_GREATER_OR_EQUAL);
        $filterMock->setRangedToType(AbstractRangeOrSingleFilter::RANGED_TO_TYPE_LESS_OR_EQUAL);
        $result = $this->invokeMethod($filterMock, 'getBoundsForRangedFilter');
        $this->assertSame([0.0, 200.0], $result);
    }

    /**
     * Gets NumberFilter mock object.
     *
     * @param bool|array $methods
     * @param string     $name
     *
     * @return NumberFilter|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getNumberFilterMock($methods = null, $name = 'name')
    {
        return $this->getCustomMock(
            '\Da2e\FiltrationSphinxClientBundle\Filter\NumberFilter',
            $methods,
            [$name]
        );
    }
}

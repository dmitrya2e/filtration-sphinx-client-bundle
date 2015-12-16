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

use Da2e\FiltrationBundle\Exception\Filter\Filter\LogicException;
use Da2e\FiltrationBundle\Filter\Filter\AbstractRangeOrSingleFilter;
use Da2e\FiltrationSphinxClientBundle\Filter\DateFilter;

/**
 * Class DateFilterTest
 *
 * @author Dmitry Abrosimov <abrosimovs@gmail.com>
 */
class DateFilterTest extends SphinxAPIFilterTestCase
{
    public function testGetValidOptions()
    {
        $this->assertTrue(is_array(DateFilter::getValidOptions()));
        $this->assertSame(
            array_merge($this->getAbstractRangeOrSingleFilterValidOptions(), [
                'exclude' => [
                    'setter' => 'setExclude',
                    'empty'  => false,
                    'type'   => 'bool',
                ],
                'default_min' => [
                    'setter'      => 'setDefaultMin',
                    'empty'       => false,
                    'type'        => 'object',
                    'instance_of' => '\DateTime',
                ],
                'default_max' => [
                    'setter'      => 'setDefaultMax',
                    'empty'       => false,
                    'type'        => 'object',
                    'instance_of' => '\DateTime',
                ],
            ]),
            DateFilter::getValidOptions()
        );
    }

    public function testDefaultMinProperty()
    {
        $property = $this->getPrivateProperty(
            '\Da2e\FiltrationSphinxClientBundle\Filter\DateFilter',
            'defaultMin'
        );

        $result = $property->getValue($this->getDateFilterMock());
        $this->assertInstanceOf('\DateTime', $result);
        $this->assertSame('1970-01-01 00:00:00', $result->format('Y-m-d H:i:s'));
    }

    public function testDefaultMaxProperty()
    {
        $property = $this->getPrivateProperty(
            '\Da2e\FiltrationSphinxClientBundle\Filter\DateFilter',
            'defaultMax'
        );

        $result = $property->getValue($this->getDateFilterMock());
        $this->assertInstanceOf('\DateTime', $result);
        $this->assertSame('2038-01-19 00:00:00', $result->format('Y-m-d H:i:s'));
    }

    public function testGetDefaultMin()
    {
        $filterMock = $this->getDateFilterMock();
        $result = $filterMock->getDefaultMin();
        $this->assertInstanceOf('\DateTime', $result);
        $this->assertSame('1970-01-01 00:00:00', $result->format('Y-m-d H:i:s'));
    }

    public function testSetDefaultMin()
    {
        $filterMock = $this->getDateFilterMock();

        $filterMock->setDefaultMin(new \DateTime('2015-01-01 12:33:33'));
        $result = $filterMock->getDefaultMin();
        $this->assertInstanceOf('\DateTime', $result);
        $this->assertSame('2015-01-01 12:33:33', $result->format('Y-m-d H:i:s'));
    }

    public function testGetDefaultMax()
    {
        $filterMock = $this->getDateFilterMock();
        $result = $filterMock->getDefaultMax();
        $this->assertInstanceOf('\DateTime', $result);
        $this->assertSame('2038-01-19 00:00:00', $result->format('Y-m-d H:i:s'));
    }

    public function testSetDefaultMax()
    {
        $filterMock = $this->getDateFilterMock();

        $filterMock->setDefaultMax(new \DateTime('2015-01-01 12:33:33'));
        $result = $filterMock->getDefaultMax();
        $this->assertInstanceOf('\DateTime', $result);
        $this->assertSame('2015-01-01 12:33:33', $result->format('Y-m-d H:i:s'));
    }

    public function testGetConvertedDefaultMin()
    {
        $filterMock = $this->getDateFilterMock();

        $result = $this->invokeMethod($filterMock, 'getConvertedDefaultMin');
        $this->assertInstanceOf('\DateTime', $result);
        $this->assertSame('1970-01-01 00:00:00', $result->format('Y-m-d H:i:s'));

        $filterMock->setDefaultMin(new \DateTime('2015-01-01 12:33:33'));
        $result = $this->invokeMethod($filterMock, 'getConvertedDefaultMin');
        $this->assertInstanceOf('\DateTime', $result);
        $this->assertSame('2015-01-01 00:00:00', $result->format('Y-m-d H:i:s'));
    }

    public function testGetConvertedDefaultMax()
    {
        $filterMock = $this->getDateFilterMock();

        $result = $this->invokeMethod($filterMock, 'getConvertedDefaultMax');
        $this->assertInstanceOf('\DateTime', $result);
        $this->assertSame('2038-01-19 00:00:00', $result->format('Y-m-d H:i:s'));

        $filterMock->setDefaultMax(new \DateTime('2015-01-01 12:33:33'));
        $result = $this->invokeMethod($filterMock, 'getConvertedDefaultMax');
        $this->assertInstanceOf('\DateTime', $result);
        $this->assertSame('2015-01-01 00:00:00', $result->format('Y-m-d H:i:s'));
    }

    public function testApplyFilter_Ranged()
    {
        $handler = $this->getSphinxClientMock();
        $handler->expects($this->never())->method($this->anything());

        $filterMock = $this->getDateFilterMock([
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

        $filterMock = $this->getDateFilterMock([
            'checkSphinxHandlerInstance',
            'applySingleFilter',
            'applyRangedFilter'
        ]);

        $filterMock->expects($this->once())->method('checkSphinxHandlerInstance')->with($handler);
        $filterMock->expects($this->once())->method('applyRangedFilter')->with($handler);
        $filterMock->expects($this->never())->method('applySingleFilter')->with($handler);

        $filterMock->setFromValue(new \DateTime('yesterday'));
        $filterMock->setToValue(new \DateTime('today'));
        $filterMock->applyFilter($handler);
    }

    public function testApplyFilter_Single()
    {
        $handler = $this->getSphinxClientMock();
        $handler->expects($this->never())->method($this->anything());

        $filterMock = $this->getDateFilterMock([
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

        $filterMock = $this->getDateFilterMock([
            'checkSphinxHandlerInstance',
            'applySingleFilter',
            'applyRangedFilter'
        ]);

        $filterMock->expects($this->once())->method('checkSphinxHandlerInstance')->with($handler);
        $filterMock->expects($this->once())->method('applySingleFilter')->with($handler);
        $filterMock->expects($this->never())->method('applyRangedFilter')->with($handler);

        $filterMock->setSingle(true);
        $filterMock->setValue(new \DateTime('today'));
        $filterMock->applyFilter($handler);
    }

    public function testApplySingleFilter_Exact()
    {
        $value = new \DateTime('2015-01-01 00:00:00');
        $valueTimestamp = $value->getTimestamp();

        $handler = $this->getSphinxClientMock();
        $handler->expects($this->never())->method($this->logicalNot($this->matches('SetFilter')));
        $handler->expects($this->once())->method('SetFilter')->with('foo', $valueTimestamp, false);

        $filterMock = $this->getDateFilterMock();
        $filterMock->setValue(new \DateTime('2015-01-01 12:33:33'));
        $filterMock->setFieldName('foo');
        $filterMock->setSingle(true);
        $filterMock->setSingleType(AbstractRangeOrSingleFilter::SINGLE_TYPE_EXACT);
        $this->invokeMethod($filterMock, 'applySingleFilter', [$handler]);
    }

    public function testApplySingleFilter_Exact_Exclude()
    {
        $value = new \DateTime('2015-01-01 00:00:00');
        $valueTimestamp = $value->getTimestamp();

        $handler = $this->getSphinxClientMock();
        $handler->expects($this->never())->method($this->logicalNot($this->matches('SetFilter')));
        $handler->expects($this->once())->method('SetFilter')->with('foo', $valueTimestamp, true);

        $filterMock = $this->getDateFilterMock();
        $filterMock->setValue(new \DateTime('2015-01-01 12:33:33'));
        $filterMock->setFieldName('foo');
        $filterMock->setSingle(true);
        $filterMock->setExclude(true);
        $filterMock->setSingleType(AbstractRangeOrSingleFilter::SINGLE_TYPE_EXACT);
        $this->invokeMethod($filterMock, 'applySingleFilter', [$handler]);
    }

    public function testApplySingleFilter_Greater_ValueMustNotBeGreaterThanDefaultMax()
    {
        $handler = $this->getSphinxClientMock();
        $handler->expects($this->never())->method($this->anything());

        $filterMock = $this->getDateFilterMock();
        $filterMock->setFieldName('foo');
        $filterMock->setSingle(true);
        $filterMock->setDefaultMax(new \DateTime('2015-01-01 00:00:00'));

        $args = [
            [new \DateTime('2015-01-02'), AbstractRangeOrSingleFilter::SINGLE_TYPE_GREATER],
            [new \DateTime('2015-01-02'), AbstractRangeOrSingleFilter::SINGLE_TYPE_GREATER_OR_EQUAL],
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

        $filterMock = $this->getDateFilterMock();
        $filterMock->setFieldName('foo');
        $filterMock->setSingle(true);
        $filterMock->setDefaultMin(new \DateTime('2015-01-01 00:00:00'));

        $args = [
            [new \DateTime('2014-12-31 00:00:00'), AbstractRangeOrSingleFilter::SINGLE_TYPE_LESS],
            [new \DateTime('2014-12-31 00:00:00'), AbstractRangeOrSingleFilter::SINGLE_TYPE_LESS_OR_EQUAL],
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

            $filterMock = $this->getDateFilterMock(['getBoundsForSingleNonExactFilter']);
            $filterMock->expects($this->atLeastOnce())
                ->method('getBoundsForSingleNonExactFilter')
                ->willReturn([333, 666]);

            $filterMock->setFieldName('foo');
            $filterMock->setSingle(true);
            $filterMock->setValue(new \DateTime('2015-01-01 00:00:00'));
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

            $filterMock = $this->getDateFilterMock(['getBoundsForSingleNonExactFilter']);
            $filterMock->expects($this->atLeastOnce())
                ->method('getBoundsForSingleNonExactFilter')
                ->willReturn([333, 666]);

            $filterMock->setFieldName('foo');
            $filterMock->setSingle(true);
            $filterMock->setValue(new \DateTime('2015-01-01 00:00:00'));
            $filterMock->setSingleType($arg);
            $filterMock->setExclude(true);
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

        $filterMock = $this->getDateFilterMock();
        $filterMock->setDefaultMin(new \DateTime('2015-01-01 00:00:00'));
        $filterMock->setDefaultMax(new \DateTime('2014-12-31 23:59:59'));
        $filterMock->setFromValue(new \DateTime('2015-01-01 00:00:00'));
        $filterMock->setToValue(new \DateTime('2015-01-10 00:00:00'));

        $this->invokeMethod($filterMock, 'applyRangedFilter', [$handler]);
    }

    public function testApplyRangedFilter()
    {
        $handler = $this->getSphinxClientMock();
        $handler->expects($this->never())->method($this->logicalNot($this->matches('SetFilterRange')));
        $handler->expects($this->once())->method('SetFilterRange')->with('foo', 333, 666, false);

        $filterMock = $this->getDateFilterMock(['getBoundsForRangedFilter']);
        $filterMock->expects($this->atLeastOnce())->method('getBoundsForRangedFilter')->willReturn([333, 666]);

        $filterMock->setSingle(false);
        $filterMock->setFieldName('foo');
        $filterMock->setFromValue(new \DateTime('2015-01-01 00:00:00'));
        $filterMock->setToValue(new \DateTime('2015-01-10 00:00:00'));

        $this->invokeMethod($filterMock, 'applyRangedFilter', [$handler]);
    }

    public function testApplyRangedFilter_Exclude()
    {
        $handler = $this->getSphinxClientMock();
        $handler->expects($this->never())->method($this->logicalNot($this->matches('SetFilterRange')));
        $handler->expects($this->once())->method('SetFilterRange')->with('foo', 333, 666, true);

        $filterMock = $this->getDateFilterMock(['getBoundsForRangedFilter']);
        $filterMock->expects($this->atLeastOnce())->method('getBoundsForRangedFilter')->willReturn([333, 666]);

        $filterMock->setSingle(false);
        $filterMock->setFieldName('foo');
        $filterMock->setFromValue(new \DateTime('2015-01-01 00:00:00'));
        $filterMock->setToValue(new \DateTime('2015-01-10 00:00:00'));
        $filterMock->setExclude(true);

        $this->invokeMethod($filterMock, 'applyRangedFilter', [$handler]);
    }

    public function testGetBoundsForSingleNonExactFilter()
    {
        $value = new \DateTime('2015-05-05 12:33:33');
        $defaultMin = new \DateTime('2015-01-01 12:33:33');
        $defaultMax = new \DateTime('2015-12-31 12:33:33');

        $value->setTime(0, 0, 0);
        $defaultMin->setTime(0, 0, 0);
        $defaultMax->setTime(0, 0, 0);

        $dayInSeconds = 24 * 60 * 60;

        $filterMock = $this->getDateFilterMock();
        $filterMock->setSingle(true);
        $filterMock->setValue(new \DateTime('2015-05-05 12:33:33'));
        $filterMock->setDefaultMin(new \DateTime('2015-01-01 12:33:33'));
        $filterMock->setDefaultMax(new \DateTime('2015-12-31 12:33:33'));
        $filterMock->setSingleType(AbstractRangeOrSingleFilter::SINGLE_TYPE_GREATER);
        $result = $this->invokeMethod($filterMock, 'getBoundsForSingleNonExactFilter');
        $this->assertSame([$value->getTimestamp() + $dayInSeconds, $defaultMax->getTimestamp()], $result);

        $filterMock = $this->getDateFilterMock();
        $filterMock->setSingle(true);
        $filterMock->setValue(new \DateTime('2015-05-05 12:33:33'));
        $filterMock->setDefaultMin(new \DateTime('2015-01-01 12:33:33'));
        $filterMock->setDefaultMax(new \DateTime('2015-12-31 12:33:33'));
        $filterMock->setSingleType(AbstractRangeOrSingleFilter::SINGLE_TYPE_GREATER_OR_EQUAL);
        $result = $this->invokeMethod($filterMock, 'getBoundsForSingleNonExactFilter');
        $this->assertSame([$value->getTimestamp(), $defaultMax->getTimestamp()], $result);

        $filterMock = $this->getDateFilterMock();
        $filterMock->setSingle(true);
        $filterMock->setValue(new \DateTime('2015-05-05 12:33:33'));
        $filterMock->setDefaultMin(new \DateTime('2015-01-01 12:33:33'));
        $filterMock->setDefaultMax(new \DateTime('2015-12-31 12:33:33'));
        $filterMock->setSingleType(AbstractRangeOrSingleFilter::SINGLE_TYPE_LESS);
        $result = $this->invokeMethod($filterMock, 'getBoundsForSingleNonExactFilter');
        $this->assertSame([$defaultMin->getTimestamp(), $value->getTimestamp() - $dayInSeconds], $result);

        $filterMock = $this->getDateFilterMock();
        $filterMock->setSingle(true);
        $filterMock->setValue(new \DateTime('2015-05-05 12:33:33'));
        $filterMock->setDefaultMin(new \DateTime('2015-01-01 12:33:33'));
        $filterMock->setDefaultMax(new \DateTime('2015-12-31 12:33:33'));
        $filterMock->setSingleType(AbstractRangeOrSingleFilter::SINGLE_TYPE_LESS_OR_EQUAL);
        $result = $this->invokeMethod($filterMock, 'getBoundsForSingleNonExactFilter');
        $this->assertSame([$defaultMin->getTimestamp(), $value->getTimestamp()], $result);
    }

    public function testGetBoundsForRangedFilter()
    {
        $fromValue = new \DateTime('2015-05-05 12:33:33');
        $toValue = new \DateTime('2015-05-10 12:33:33');

        $fromValue->setTime(0, 0, 0);
        $toValue->setTime(23, 59, 59);

        $fromTimestamp = $fromValue->getTimestamp();
        $toTimestamp = $toValue->getTimestamp();

        $dayInSeconds = 24 * 60 * 60;

        $filterMock = $this->getDateFilterMock();
        $filterMock->setSingle(false);
        $filterMock->setFromValue(new \DateTime('2015-05-05 12:33:33'));
        $filterMock->setToValue(new \DateTime('2015-05-10 12:33:33'));
        $filterMock->setRangedFromType(AbstractRangeOrSingleFilter::RANGED_FROM_TYPE_GREATER);
        $filterMock->setRangedToType(AbstractRangeOrSingleFilter::RANGED_TO_TYPE_LESS_OR_EQUAL);
        $result = $this->invokeMethod($filterMock, 'getBoundsForRangedFilter');
        $this->assertSame([$fromTimestamp + $dayInSeconds, $toTimestamp], $result);

        $filterMock = $this->getDateFilterMock();
        $filterMock->setSingle(false);
        $filterMock->setFromValue(new \DateTime('2015-05-05 12:33:33'));
        $filterMock->setToValue(new \DateTime('2015-05-10 12:33:33'));
        $filterMock->setRangedFromType(AbstractRangeOrSingleFilter::RANGED_FROM_TYPE_GREATER_OR_EQUAL);
        $filterMock->setRangedToType(AbstractRangeOrSingleFilter::RANGED_TO_TYPE_LESS);
        $result = $this->invokeMethod($filterMock, 'getBoundsForRangedFilter');
        $this->assertSame([$fromTimestamp, $toTimestamp - $dayInSeconds], $result);

        $filterMock = $this->getDateFilterMock();
        $filterMock->setSingle(false);
        $filterMock->setFromValue(new \DateTime('2015-05-05 12:33:33'));
        $filterMock->setToValue(new \DateTime('2015-05-10 12:33:33'));
        $filterMock->setRangedFromType(AbstractRangeOrSingleFilter::RANGED_FROM_TYPE_GREATER);
        $filterMock->setRangedToType(AbstractRangeOrSingleFilter::RANGED_TO_TYPE_LESS);
        $result = $this->invokeMethod($filterMock, 'getBoundsForRangedFilter');
        $this->assertSame([$fromTimestamp + $dayInSeconds, $toTimestamp - $dayInSeconds], $result);

        $filterMock = $this->getDateFilterMock();
        $filterMock->setSingle(false);
        $filterMock->setFromValue(new \DateTime('2015-05-05 12:33:33'));
        $filterMock->setToValue(new \DateTime('2015-05-10 12:33:33'));
        $filterMock->setRangedFromType(AbstractRangeOrSingleFilter::RANGED_FROM_TYPE_GREATER_OR_EQUAL);
        $filterMock->setRangedToType(AbstractRangeOrSingleFilter::RANGED_TO_TYPE_LESS_OR_EQUAL);
        $result = $this->invokeMethod($filterMock, 'getBoundsForRangedFilter');
        $this->assertSame([$fromTimestamp, $toTimestamp], $result);
    }

    public function testGetBoundsForRangedFilter_OneOfFieldsIsAppliedOnly()
    {
        $fromValue = new \DateTime('2015-05-05 12:33:33');
        $fromValue->setTime(0, 0, 0);
        $fromTimestamp = $fromValue->getTimestamp();

        $defaultMin = new \DateTime('2015-01-01 12:33:33');
        $defaultMax = new \DateTime('2015-12-31 12:33:33');
        $defaultMin->setTime(0, 0, 0);
        $defaultMax->setTime(23, 59, 59);
        $defaultMinTimestamp = $defaultMin->getTimestamp();
        $defaultMaxTimestamp = $defaultMax->getTimestamp();

        $dayInSeconds = 24 * 60 * 60;

        // Only "from" value is applied
        $filterMock = $this->getDateFilterMock();
        $filterMock->setSingle(false);
        $filterMock->setFromValue(new \DateTime('2015-05-05 12:33:33'));
        $filterMock->setToValue(null);
        $filterMock->setDefaultMin(new \DateTime('2015-01-01 12:33:33'));
        $filterMock->setDefaultMax(new \DateTime('2015-12-31 12:33:33'));
        $filterMock->setRangedFromType(AbstractRangeOrSingleFilter::RANGED_FROM_TYPE_GREATER);
        $filterMock->setRangedToType(AbstractRangeOrSingleFilter::RANGED_TO_TYPE_LESS_OR_EQUAL);
        $result = $this->invokeMethod($filterMock, 'getBoundsForRangedFilter');
        $this->assertSame([$fromTimestamp + $dayInSeconds, $defaultMaxTimestamp], $result);

        $filterMock = $this->getDateFilterMock();
        $filterMock->setSingle(false);
        $filterMock->setFromValue(new \DateTime('2015-05-05 12:33:33'));
        $filterMock->setToValue(null);
        $filterMock->setDefaultMin(new \DateTime('2015-01-01 12:33:33'));
        $filterMock->setDefaultMax(new \DateTime('2015-12-31 12:33:33'));
        $filterMock->setRangedFromType(AbstractRangeOrSingleFilter::RANGED_FROM_TYPE_GREATER_OR_EQUAL);
        $filterMock->setRangedToType(AbstractRangeOrSingleFilter::RANGED_TO_TYPE_LESS_OR_EQUAL);
        $result = $this->invokeMethod($filterMock, 'getBoundsForRangedFilter');
        $this->assertSame([$fromTimestamp, $defaultMaxTimestamp], $result);

        $filterMock = $this->getDateFilterMock();
        $filterMock->setSingle(false);
        $filterMock->setFromValue(new \DateTime('2015-05-05 12:33:33'));
        $filterMock->setToValue(null);
        $filterMock->setDefaultMin(new \DateTime('2015-01-01 12:33:33'));
        $filterMock->setDefaultMax(new \DateTime('2015-12-31 12:33:33'));
        $filterMock->setRangedFromType(AbstractRangeOrSingleFilter::RANGED_FROM_TYPE_GREATER);
        $filterMock->setRangedToType(AbstractRangeOrSingleFilter::RANGED_TO_TYPE_LESS);
        $result = $this->invokeMethod($filterMock, 'getBoundsForRangedFilter');
        $this->assertSame([$fromTimestamp + $dayInSeconds, $defaultMaxTimestamp - $dayInSeconds], $result);

        $filterMock = $this->getDateFilterMock();
        $filterMock->setSingle(false);
        $filterMock->setFromValue(new \DateTime('2015-05-05 12:33:33'));
        $filterMock->setToValue(null);
        $filterMock->setDefaultMin(new \DateTime('2015-01-01 12:33:33'));
        $filterMock->setDefaultMax(new \DateTime('2015-12-31 12:33:33'));
        $filterMock->setRangedFromType(AbstractRangeOrSingleFilter::RANGED_FROM_TYPE_GREATER_OR_EQUAL);
        $filterMock->setRangedToType(AbstractRangeOrSingleFilter::RANGED_TO_TYPE_LESS);
        $result = $this->invokeMethod($filterMock, 'getBoundsForRangedFilter');
        $this->assertSame([$fromTimestamp, $defaultMaxTimestamp - $dayInSeconds], $result);

        // Only "to" value is applied
        $toValue = new \DateTime('2015-05-10 12:33:33');
        $toValue->setTime(23, 59, 59);
        $toTimestamp = $toValue->getTimestamp();

        $filterMock = $this->getDateFilterMock();
        $filterMock->setSingle(false);
        $filterMock->setFromValue(null);
        $filterMock->setToValue(new \DateTime('2015-05-10 12:33:33'));
        $filterMock->setDefaultMin(new \DateTime('2015-01-01 12:33:33'));
        $filterMock->setDefaultMax(new \DateTime('2015-12-31 12:33:33'));
        $filterMock->setRangedFromType(AbstractRangeOrSingleFilter::RANGED_FROM_TYPE_GREATER);
        $filterMock->setRangedToType(AbstractRangeOrSingleFilter::RANGED_TO_TYPE_LESS_OR_EQUAL);
        $result = $this->invokeMethod($filterMock, 'getBoundsForRangedFilter');
        $this->assertSame([$defaultMinTimestamp + $dayInSeconds, $toTimestamp], $result);

        $filterMock = $this->getDateFilterMock();
        $filterMock->setSingle(false);
        $filterMock->setFromValue(null);
        $filterMock->setToValue(new \DateTime('2015-05-10 12:33:33'));
        $filterMock->setDefaultMin(new \DateTime('2015-01-01 12:33:33'));
        $filterMock->setDefaultMax(new \DateTime('2015-12-31 12:33:33'));
        $filterMock->setRangedFromType(AbstractRangeOrSingleFilter::RANGED_FROM_TYPE_GREATER_OR_EQUAL);
        $filterMock->setRangedToType(AbstractRangeOrSingleFilter::RANGED_TO_TYPE_LESS_OR_EQUAL);
        $result = $this->invokeMethod($filterMock, 'getBoundsForRangedFilter');
        $this->assertSame([$defaultMinTimestamp, $toTimestamp], $result);

        $filterMock = $this->getDateFilterMock();
        $filterMock->setSingle(false);
        $filterMock->setFromValue(null);
        $filterMock->setToValue(new \DateTime('2015-05-10 12:33:33'));
        $filterMock->setDefaultMin(new \DateTime('2015-01-01 12:33:33'));
        $filterMock->setDefaultMax(new \DateTime('2015-12-31 12:33:33'));
        $filterMock->setRangedFromType(AbstractRangeOrSingleFilter::RANGED_FROM_TYPE_GREATER);
        $filterMock->setRangedToType(AbstractRangeOrSingleFilter::RANGED_TO_TYPE_LESS);
        $result = $this->invokeMethod($filterMock, 'getBoundsForRangedFilter');
        $this->assertSame([$defaultMinTimestamp + $dayInSeconds, $toTimestamp - $dayInSeconds], $result);

        $filterMock = $this->getDateFilterMock();
        $filterMock->setSingle(false);
        $filterMock->setFromValue(null);
        $filterMock->setToValue(new \DateTime('2015-05-10 12:33:33'));
        $filterMock->setDefaultMin(new \DateTime('2015-01-01 12:33:33'));
        $filterMock->setDefaultMax(new \DateTime('2015-12-31 12:33:33'));
        $filterMock->setRangedFromType(AbstractRangeOrSingleFilter::RANGED_FROM_TYPE_GREATER_OR_EQUAL);
        $filterMock->setRangedToType(AbstractRangeOrSingleFilter::RANGED_TO_TYPE_LESS);
        $result = $this->invokeMethod($filterMock, 'getBoundsForRangedFilter');
        $this->assertSame([$defaultMinTimestamp, $toTimestamp - $dayInSeconds], $result);
    }

    /**
     * Gets NumberFilter mock object.
     *
     * @param bool|array $methods
     * @param string     $name
     *
     * @return DateFilter|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getDateFilterMock($methods = null, $name = 'name')
    {
        return $this->getCustomMock(
            '\Da2e\FiltrationSphinxClientBundle\Filter\DateFilter',
            $methods,
            [$name]
        );
    }
}

<?php

/*
 * This file is part of the Da2e FiltrationSphinxClientBundle package.
 *
 * (c) Dmitry Abrosimov <abrosimovs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Da2e\FiltrationSphinxClientBundle\Filter;

use Da2e\FiltrationBundle\Exception\Filter\Filter\LogicException;
use Da2e\FiltrationBundle\Filter\Filter\AbstractDateFilter;
use \SphinxClient as SphinxClient;

/**
 * Sphinx API date (without time) filter.
 *
 * DateFilter has settings related to SphinxSearch API and \SphinxClient library only. These are:
 *  - default min value
 *  - default max value
 *
 * This is done, because \SphinxClient does not allow to make filter
 * like "setFilterRange('field', null, 100)" or "setFilterRange('field', 15, null)".
 *
 * In other words it can't filter by min value OR by max value only, it require both bounds to be set.
 *
 * So basically, when no min value is applied, the default min value is considered as actual min value.
 * And when no max value is applied, the default max value is considered as actual max value.
 *
 * @author Dmitry Abrosimov <abrosimovs@gmail.com>
 */
class DateFilter extends AbstractDateFilter
{
    use SphinxFilterTrait;
    use SphinxTypeTrait;

    // Unix time min/max values. Should be definitely changed to something meaningful.
    const DEFAULT_MIN = '1970-01-01';

    // Hope, that 2038 year is yet in the future...
    const DEFAULT_MAX = '2038-01-19';

    /**
     * @var \DateTime
     */
    protected $defaultMin;

    /**
     * @var \DateTime
     */
    protected $defaultMax;

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        parent::configure();

        $this->defaultMin = new \DateTime(self::DEFAULT_MIN);
        $this->defaultMax = new \DateTime(self::DEFAULT_MAX);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public static function getValidOptions()
    {
        return array_merge(parent::getValidOptions(), [
            'exclude'     => self::getExcludeOptionDescription(),
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
        ]);
    }

    /**
     * {@inheritdoc}
     *
     * @param \SphinxClient $sphinxClient
     */
    public function applyFilter($sphinxClient)
    {
        $this->checkSphinxHandlerInstance($sphinxClient);

        if ($this->hasAppliedValue() === false) {
            return $this;
        }

        if ($this->isSingle() === true) {
            return $this->applySingleFilter($sphinxClient);
        }

        return $this->applyRangedFilter($sphinxClient);
    }

    /**
     * Sets default min value. Note, that time will be reset while value conversion.
     *
     * @param \DateTime $defaultMin
     *
     * @return static
     */
    public function setDefaultMin(\DateTime $defaultMin)
    {
        $this->defaultMin = $defaultMin;

        return $this;
    }

    /**
     * Gets default min value.
     *
     * @return \DateTime
     */
    public function getDefaultMin()
    {
        return $this->defaultMin;
    }

    /**
     * Sets default max value. Note, that time will be set to 23:59:59 while value conversion.
     *
     * @param \DateTime $defaultMax
     *
     * @return static
     */
    public function setDefaultMax(\DateTime $defaultMax)
    {
        $this->defaultMax = $defaultMax;

        return $this;
    }

    /**
     * Gets default max value.
     *
     * @return \DateTime
     */
    public function getDefaultMax()
    {
        return $this->defaultMax;
    }

    /**
     * Applies single filter.
     *
     * @param \SphinxClient $sphinxClient
     *
     * @return static
     * @throws LogicException
     */
    protected function applySingleFilter(\SphinxClient $sphinxClient)
    {
        /** @var \DateTime $value */
        $value = $this->getConvertedValue();

        if ($this->getSingleType() === self::SINGLE_TYPE_EXACT) {
            $sphinxClient->SetFilter($this->getFieldName(), $value->getTimestamp(), $this->isExclude());
        } else {
            if (in_array($this->getSingleType(), [self::SINGLE_TYPE_GREATER, self::SINGLE_TYPE_GREATER_OR_EQUAL])) {
                if ($value > $this->getConvertedDefaultMax()) {
                    throw new LogicException(sprintf(
                        'Single value can not be greater, than %s.',
                        $this->getConvertedDefaultMax()->format('Y-m-d H:i:s')
                    ));
                }
            } else {
                if ($value < $this->getConvertedDefaultMin()) {
                    throw new LogicException(sprintf(
                        'Single value can not be less, than %s.',
                        $this->getConvertedDefaultMin()->format('Y-m-d H:i:s')
                    ));
                }
            }

            if ($this->getConvertedDefaultMin() > $this->getConvertedDefaultMax()) {
                throw new LogicException('Default min value must not be greater than default max value.');
            }

            $bounds = $this->getBoundsForSingleNonExactFilter();
            $sphinxClient->SetFilterRange($this->getFieldName(), $bounds[0], $bounds[1], $this->isExclude());
        }

        return $this;
    }

    /**
     * Applies ranged filter.
     *
     * @param \SphinxClient $sphinxClient
     *
     * @return static
     * @throws LogicException
     */
    protected function applyRangedFilter(\SphinxClient $sphinxClient)
    {
        if ($this->getConvertedDefaultMin() > $this->getConvertedDefaultMax()) {
            throw new LogicException('Default min value must not be greater than default max value.');
        }

        $bounds = $this->getBoundsForRangedFilter();
        $sphinxClient->SetFilterRange($this->getFieldName(), $bounds[0], $bounds[1], $this->isExclude());

        return $this;
    }

    /**
     * Gets min/max bounds for single filter (applySingleFilter()).
     *
     * @return array|\int[] [ min_timestamp|int, max_timestamp|int ]
     */
    protected function getBoundsForSingleNonExactFilter()
    {
        /**
         * @var \DateTime $value
         * @var \DateTime $minValue
         * @var \DateTime $maxValue
         */
        $value = $this->getConvertedValue();
        $minValue = $this->getConvertedDefaultMin();
        $maxValue = $this->getConvertedDefaultMax();

        switch ($this->getSingleType()) {
            case self::SINGLE_TYPE_GREATER:
                if ($value < $maxValue) {
                    $value->modify('+1 day');
                }

                $minValue = $value;
                break;

            case self::SINGLE_TYPE_GREATER_OR_EQUAL:
                $minValue = $value;
                break;

            case self::SINGLE_TYPE_LESS:
                if ($value > $minValue) {
                    $value->modify('-1 day');
                }

                $maxValue = $value;
                break;

            case self::SINGLE_TYPE_LESS_OR_EQUAL:
                $maxValue = $value;
                break;
        }

        return [$minValue->getTimestamp(), $maxValue->getTimestamp()];
    }

    /**
     * Gets min/max bounds for ranged filter (applyRangedFilter()).
     *
     * @return array|\int[] [ min_timestamp|int, max_timestamp|int ]
     */
    protected function getBoundsForRangedFilter()
    {
        /**
         * @var \DateTime $fromValue
         * @var \DateTime $toValue
         * @var \DateTime $minValue
         * @var \DateTime $maxValue
         */
        $fromValue = $this->getConvertedFromValue();
        $toValue = $this->getConvertedToValue();
        $hasMin = $fromValue instanceof \DateTime;
        $hasMax = $toValue instanceof \DateTime;
        $minValue = $this->getConvertedDefaultMin();
        $maxValue = $this->getConvertedDefaultMax();

        if ($hasMin && $hasMax && ($toValue >= $fromValue)) {
            $minValue = $fromValue;
            $maxValue = $toValue;
        } elseif (!$hasMin && $hasMax) {
            $maxValue = $toValue;
        } elseif ($hasMin && !$hasMax) {
            $minValue = $fromValue;
        }

        if ($minValue !== $maxValue) {
            if ($this->getRangedFromType() === self::RANGED_FROM_TYPE_GREATER && $minValue < $maxValue) {
                $minValue->modify('+1 day');
            }

            if ($this->getRangedToType() === self::RANGED_TO_TYPE_LESS && $maxValue > $minValue) {
                $maxValue->modify('-1 day');
            }
        }

        $maxValue->setTime(23, 59, 59);

        return [$minValue->getTimestamp(), $maxValue->getTimestamp()];
    }

    /**
     * Gets converted default min value.
     *
     * @return \DateTime
     */
    protected function getConvertedDefaultMin()
    {
        if ($this->defaultMin instanceof \DateTime) {
            $min = $this->defaultMin;
        } else {
            $min = new \DateTime(self::DEFAULT_MIN);
        }

        $min->setTime(0, 0, 0);

        return $min;
    }

    /**
     * Gets converted default max value.
     *
     * @return \DateTime
     */
    protected function getConvertedDefaultMax()
    {
        if ($this->defaultMax instanceof \DateTime) {
            $max = $this->defaultMax;
        } else {
            $max = new \DateTime(self::DEFAULT_MAX);
        }

        $max->setTime(0, 0, 0);

        return $max;
    }
}

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

/**
 * Trait with helpful methods for all SphinxSearch filters.
 *
 * @author Dmitry Abrosimov <abrosimovs@gmail.com>
 */
trait SphinxFilterTrait
{
    /**
     * @var bool
     */
    protected $exclude = false;

    /**
     * Checks if the filter must exclude values.
     *
     * @return boolean
     */
    public function isExclude()
    {
        return $this->exclude;
    }

    /**
     * Sets exclude flag for filter to exclude (or not) values.
     *
     * @param boolean $exclude
     *
     * @return static
     */
    public function setExclude($exclude)
    {
        $this->exclude = $exclude;

        return $this;
    }

    /**
     * Gets "exclude" option description.
     *
     * @see FilterOptionInterface::getValidOptions()
     *
     * @return array
     */
    protected static function getExcludeOptionDescription()
    {
        return [
            'setter' => 'setExclude',
            'empty'  => false,
            'type'   => 'bool',
        ];
    }
}

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

use Da2e\FiltrationBundle\Filter\Filter\AbstractTextFilter;

/**
 * Sphinx API text filter.
 *
 * It is difficult to create a fully functional Sphinx API text filter,
 * because \SphinxClient library has no possibility to set query text, except Query() method.
 *
 * Because of this the applyFilter() method does nothing.
 * You can use hasAppliedValue() and getConvertedValue() methods outside of this class to filter by text manually.
 *
 * @author Dmitry Abrosimov <abrosimovs@gmail.com>
 */
class TextFilter extends AbstractTextFilter
{
    use SphinxFilterTrait;
    use SphinxTypeTrait;

    /**
     * {@inheritDoc}
     *
     * @param \SphinxClient $sphinxClient
     */
    public function applyFilter($sphinxClient)
    {
        // Use hasAppliedValue() and getConvertedValue() methods outside of this class to filter by text manually.
        return $this;
    }
}

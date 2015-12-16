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

use Da2e\FiltrationBundle\Filter\Filter\AbstractChoiceFilter;
use \SphinxClient as SphinxClient;

/**
 * Sphinx API choice filter.
 *
 * @author Dmitry Abrosimov <abrosimovs@gmail.com>
 */
class ChoiceFilter extends AbstractChoiceFilter
{
    use SphinxFilterTrait;
    use SphinxTypeTrait;

    /**
     * {@inheritDoc}
     */
    public static function getValidOptions()
    {
        return array_merge(parent::getValidOptions(), [
            'exclude' => self::getExcludeOptionDescription(),
        ]);
    }

    /**
     * {@inheritDoc}
     *
     * @param \SphinxClient $sphinxClient
     */
    public function applyFilter($sphinxClient)
    {
        $this->checkSphinxHandlerInstance($sphinxClient);

        if ($this->hasAppliedValue()) {
            $sphinxClient->SetFilter($this->getFieldName(), $this->getConvertedValue(), $this->isExclude());
        }

        return $this;
    }
}

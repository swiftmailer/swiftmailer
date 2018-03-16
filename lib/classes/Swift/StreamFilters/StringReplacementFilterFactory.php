<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swift\StreamFilters;

use Swift\ReplacementFilterFactory;

/**
 * Creates filters for replacing needles in a string buffer.
 *
 * @author Chris Corbyn
 */
class StringReplacementFilterFactory implements ReplacementFilterFactory
{
    /** Lazy-loaded filters */
    private $filters = [];

    /**
     * Create a new StreamFilter to replace $search with $replace in a string.
     *
     * @param string $search
     * @param string $replace
     *
     * @return Swift_StreamFilter
     */
    public function createFilter($search, $replace)
    {
        if (!isset($this->filters[$search][$replace])) {
            if (!isset($this->filters[$search])) {
                $this->filters[$search] = [];
            }

            if (!isset($this->filters[$search][$replace])) {
                $this->filters[$search][$replace] = [];
            }

            $this->filters[$search][$replace] = new StringReplacementFilter($search, $replace);
        }

        return $this->filters[$search][$replace];
    }
}

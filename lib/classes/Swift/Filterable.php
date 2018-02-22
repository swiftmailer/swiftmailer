<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swift;

/**
 * Allows StreamFilters to operate on a stream.
 *
 * @author Chris Corbyn
 */
interface Filterable
{
    /**
     * Add a new StreamFilter, referenced by $key.
     *
     * @param \Swift\StreamFilter $filter
     * @param string             $key
     */
    public function addFilter(StreamFilter $filter, $key);

    /**
     * Remove an existing filter using $key.
     *
     * @param string $key
     */
    public function removeFilter($key);
}

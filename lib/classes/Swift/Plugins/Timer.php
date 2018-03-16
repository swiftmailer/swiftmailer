<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swift\Plugins;

/**
 * Provides timestamp data.
 *
 * @author Chris Corbyn
 */
interface Timer
{
    /**
     * Get the current UNIX timestamp.
     *
     * @return int
     */
    public function getTimestamp();
}

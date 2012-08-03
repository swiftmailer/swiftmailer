<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Sleeps for a duration of time.
 *
 * @package    Swift
 * @subpackage Plugins
 * @author     Chris Corbyn
 */
interface Swift_Plugins_Sleeper
{
    /**
     * Sleep for $seconds.
     *
     * @param integer $seconds
     */
    public function sleep($seconds);
}

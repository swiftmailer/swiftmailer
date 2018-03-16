<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swift\Plugins\Pop;

/**
 * Pop3Connection interface for connecting and disconnecting to a POP3 host.
 *
 * @author Chris Corbyn
 */
interface Pop3Connection
{
    /**
     * Connect to the POP3 host and throw an Exception if it fails.
     *
     * @throws Pop3Exception
     */
    public function connect();

    /**
     * Disconnect from the POP3 host and throw an Exception if it fails.
     *
     * @throws Pop3Exception
     */
    public function disconnect();
}

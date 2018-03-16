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
 * RFC Compliance Exception class.
 *
 * @author Chris Corbyn
 */
class RfcComplianceException extends SwiftException
{
    /**
     * Create a new RfcComplianceException with $message.
     *
     * @param string $message
     */
    public function __construct($message)
    {
        parent::__construct($message);
    }
}

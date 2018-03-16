<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swift\Events;

use Swift\Transport;
use Swift\TransportException;

/**
 * Generated when a TransportException is thrown from the Transport system.
 *
 * @author Chris Corbyn
 */
class TransportExceptionEvent extends EventObject
{
    /**
     * The Exception thrown.
     *
     * @var \Swift\TransportException
     */
    private $exception;

    /**
     * Create a new TransportExceptionEvent for $transport.
     */
    public function __construct(Transport $transport, TransportException $ex)
    {
        parent::__construct($transport);
        $this->exception = $ex;
    }

    /**
     * Get the TransportException thrown.
     *
     * @return \Swift\TransportException
     */
    public function getException()
    {
        return $this->exception;
    }
}

<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swift\Events;

/**
 * Generated when the state of a Transport is changed (i.e. stopped/started).
 *
 * @author Chris Corbyn
 */
class TransportChangeEvent extends EventObject
{
    /**
     * Get the Transport.
     *
     * @return \Swift\Transport
     */
    public function getTransport()
    {
        return $this->getSource();
    }
}

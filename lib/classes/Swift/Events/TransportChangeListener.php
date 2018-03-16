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
 * Listens for changes within the Transport system.
 *
 * @author Chris Corbyn
 */
interface TransportChangeListener extends EventListener
{
    /**
     * Invoked just before a Transport is started.
     *
     * @param \Swift\Events\TransportChangeEvent $evt
     */
    public function beforeTransportStarted(TransportChangeEvent $evt);

    /**
     * Invoked immediately after the Transport is started.
     *
     * @param \Swift\Events\TransportChangeEvent $evt
     */
    public function transportStarted(TransportChangeEvent $evt);

    /**
     * Invoked just before a Transport is stopped.
     *
     * @param \Swift\Events\TransportChangeEvent $evt
     */
    public function beforeTransportStopped(TransportChangeEvent $evt);

    /**
     * Invoked immediately after the Transport is stopped.
     *
     * @param \Swift\Events\TransportChangeEvent $evt
     */
    public function transportStopped(TransportChangeEvent $evt);
}

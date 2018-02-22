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
 * Listens for Transports to send commands to the server.
 *
 * @author Chris Corbyn
 */
interface CommandListener extends EventListener
{
    /**
     * Invoked immediately following a command being sent.
     *
     * @param CommandEvent $evt
     */
    public function commandSent(CommandEvent $evt);
}

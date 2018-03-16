<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2009 Fabien Potencier <fabien.potencier@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swift\Transport;

use Swift\Spool;
use Swift\Events\EventDispatcher;
use Swift\Mime\SimpleMessage;
use Swift\Events\SendEvent;
use Swift\Events\EventListener;

/**
 * Stores Messages in a queue.
 *
 * @author Fabien Potencier
 */
class SpoolTransport implements Transport
{
    /** The spool instance */
    private $spool;

    /** The event dispatcher from the plugin API */
    private $eventDispatcher;

    /**
     * Constructor.
     */
    public function __construct(EventDispatcher $eventDispatcher, Spool $spool = null)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->spool = $spool;
    }

    /**
     * Sets the spool object.
     *
     * @return $this
     */
    public function setSpool(Spool $spool)
    {
        $this->spool = $spool;

        return $this;
    }

    /**
     * Get the spool object.
     *
     * @return Spool
     */
    public function getSpool()
    {
        return $this->spool;
    }

    /**
     * Tests if this Transport mechanism has started.
     *
     * @return bool
     */
    public function isStarted()
    {
        return true;
    }

    /**
     * Starts this Transport mechanism.
     */
    public function start()
    {
    }

    /**
     * Stops this Transport mechanism.
     */
    public function stop()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function ping()
    {
        return true;
    }

    /**
     * Sends the given message.
     *
     * @param string[] $failedRecipients An array of failures by-reference
     *
     * @return int The number of sent e-mail's
     */
    public function send(SimpleMessage $message, &$failedRecipients = null)
    {
        if ($evt = $this->eventDispatcher->createSendEvent($this, $message)) {
            $this->eventDispatcher->dispatchEvent($evt, 'beforeSendPerformed');
            if ($evt->bubbleCancelled()) {
                return 0;
            }
        }

        $success = $this->spool->queueMessage($message);

        if ($evt) {
            $evt->setResult($success ? SendEvent::RESULT_SPOOLED : SendEvent::RESULT_FAILED);
            $this->eventDispatcher->dispatchEvent($evt, 'sendPerformed');
        }

        return 1;
    }

    /**
     * Register a plugin.
     */
    public function registerPlugin(Swift_Events_EventListener $plugin)
    {
        $this->eventDispatcher->bindEventListener($plugin);
    }
}

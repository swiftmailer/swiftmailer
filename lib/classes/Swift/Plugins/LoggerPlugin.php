<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swift\Plugins;

use Swift\Events\CommandEvent;
use Swift\Events\TransportChangeEvent;
use Swift\Events\ResponseEvent;
use Swift\Events\TransportExceptionEvent;
use Swift\Events\CommandListener;
use Swift\Events\ResponseListener;
use Swift\Events\TransportChangeListener;
use Swift\Events\TransportExceptionListener;
use Swift\TransportException;

/**
 * Does real time logging of Transport level information.
 *
 * @author     Chris Corbyn
 */
class LoggerPlugin implements CommandListener, ResponseListener, TransportChangeListener, TransportExceptionListener, Logger
{
    /** The logger which is delegated to */
    private $logger;

    /**
     * Create a new LoggerPlugin using $logger.
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Add a log entry.
     *
     * @param string $entry
     */
    public function add($entry)
    {
        $this->logger->add($entry);
    }

    /**
     * Clear the log contents.
     */
    public function clear()
    {
        $this->logger->clear();
    }

    /**
     * Get this log as a string.
     *
     * @return string
     */
    public function dump()
    {
        return $this->logger->dump();
    }

    /**
     * Invoked immediately following a command being sent.
     */
    public function commandSent(CommandEvent $evt)
    {
        $command = $evt->getCommand();
        $this->logger->add(sprintf('>> %s', $command));
    }

    /**
     * Invoked immediately following a response coming back.
     */
    public function responseReceived(ResponseEvent $evt)
    {
        $response = $evt->getResponse();
        $this->logger->add(sprintf('<< %s', $response));
    }

    /**
     * Invoked just before a Transport is started.
     */
    public function beforeTransportStarted(TransportChangeEvent $evt)
    {
        $transportName = get_class($evt->getSource());
        $this->logger->add(sprintf('++ Starting %s', $transportName));
    }

    /**
     * Invoked immediately after the Transport is started.
     */
    public function transportStarted(TransportChangeEvent $evt)
    {
        $transportName = get_class($evt->getSource());
        $this->logger->add(sprintf('++ %s started', $transportName));
    }

    /**
     * Invoked just before a Transport is stopped.
     */
    public function beforeTransportStopped(TransportChangeEvent $evt)
    {
        $transportName = get_class($evt->getSource());
        $this->logger->add(sprintf('++ Stopping %s', $transportName));
    }

    /**
     * Invoked immediately after the Transport is stopped.
     */
    public function transportStopped(TransportChangeEvent $evt)
    {
        $transportName = get_class($evt->getSource());
        $this->logger->add(sprintf('++ %s stopped', $transportName));
    }

    /**
     * Invoked as a TransportException is thrown in the Transport system.
     */
    public function exceptionThrown(TransportExceptionEvent $evt)
    {
        $e = $evt->getException();
        $message = $e->getMessage();
        $code = $e->getCode();
        $this->logger->add(sprintf('!! %s (code: %s)', $message, $code));
        $message .= PHP_EOL;
        $message .= 'Log data:'.PHP_EOL;
        $message .= $this->logger->dump();
        $evt->cancelBubble();
        throw new TransportException($message, $code, $e->getPrevious());
    }
}

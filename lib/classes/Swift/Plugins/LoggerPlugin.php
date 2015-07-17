<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Does real time logging of Transport level information.
 *
 * @author Chris Corbyn
 */
class Swift_Plugins_LoggerPlugin implements Swift_Events_CommandListener, Swift_Events_ResponseListener, Swift_Events_TransportChangeListener, Swift_Events_TransportExceptionListener, Swift_Plugins_Logger
{
    /** The logger which is delegated to */
    private $_logger;

    /** Output with timestamp */
    private $_formatTS = true;

    /**
     * Create a new LoggerPlugin using $logger.
     *
     * @param Swift_Plugins_Logger $logger
     */
    public function __construct(Swift_Plugins_Logger $logger)
    {
        $this->_logger = $logger;
    }

    /**
     * Add a log entry.
     *
     * @param string $entry
     */
    public function add($entry)
    {
        $this->_logger->add($entry);
    }

    /**
     * Clear the log contents.
     */
    public function clear()
    {
        $this->_logger->clear();
    }

    /**
     * Get this log as a string.
     *
     * @return string
     */
    public function dump()
    {
        return $this->_logger->dump();
    }

    /**
     * formatting a log entry
     * 
     * @param string $format sprintf format option
     * @param object $info1 object to be outputted
     * @param object $info2 object to be outputted
     * @return string
     */
    private function formatLogRow($format, $info1=false, $info2=false)
    {
        $output = '';
        if ($this->_formatTS) 
        {
            list($sec, $usec) = explode('.', microtime(true));
            $usec = str_replace("0.", ".", $usec);
            $output = date('H:i:s', $sec) . $usec.' - ';
        }
        if ($info1) $output .= sprintf($format, $info1);
        if ($info2) $output .= sprintf($format, $info2);
        return $output;
    }

    /**
     * Invoked immediately following a command being sent.
     *
     * @param Swift_Events_CommandEvent $evt
     */
    public function commandSent(Swift_Events_CommandEvent $evt)
    {
        $command = $evt->getCommand();
        $this->_logger->add($this->formatLogRow('>> %s', $command));
    }

    /**
     * Invoked immediately following a response coming back.
     *
     * @param Swift_Events_ResponseEvent $evt
     */
    public function responseReceived(Swift_Events_ResponseEvent $evt)
    {
        $response = $evt->getResponse();
        $this->_logger->add($this->formatLogRow('<< %s', $response));
    }

    /**
     * Invoked just before a Transport is started.
     *
     * @param Swift_Events_TransportChangeEvent $evt
     */
    public function beforeTransportStarted(Swift_Events_TransportChangeEvent $evt)
    {
        $transportName = get_class($evt->getSource());
        $this->_logger->add($this->formatLogRow('++ Starting %s', $transportName));
    }

    /**
     * Invoked immediately after the Transport is started.
     *
     * @param Swift_Events_TransportChangeEvent $evt
     */
    public function transportStarted(Swift_Events_TransportChangeEvent $evt)
    {
        $transportName = get_class($evt->getSource());
        $this->_logger->add($this->formatLogRow('++ %s started', $transportName));
    }

    /**
     * Invoked just before a Transport is stopped.
     *
     * @param Swift_Events_TransportChangeEvent $evt
     */
    public function beforeTransportStopped(Swift_Events_TransportChangeEvent $evt)
    {
        $transportName = get_class($evt->getSource());
        $this->_logger->add($this->formatLogRow('++ Stopping %s', $transportName));
    }

    /**
     * Invoked immediately after the Transport is stopped.
     *
     * @param Swift_Events_TransportChangeEvent $evt
     */
    public function transportStopped(Swift_Events_TransportChangeEvent $evt)
    {
        $transportName = get_class($evt->getSource());
        $this->_logger->add($this->formatLogRow('++ %s stopped', $transportName));
    }

    /**
     * Invoked as a TransportException is thrown in the Transport system.
     *
     * @param Swift_Events_TransportExceptionEvent $evt
     */
    public function exceptionThrown(Swift_Events_TransportExceptionEvent $evt)
    {
        $e = $evt->getException();
        $message = $e->getMessage();
        $code = $e->getCode();
        $this->_logger->add($this->formatLogRow('!! %s (code: %s)', $message, $code));
        $message .= PHP_EOL;
        $message .= 'Log data:'.PHP_EOL;
        $message .= $this->_logger->dump();
        $evt->cancelBubble();
        throw new Swift_TransportException($message, $code, $e->getPrevious());
    }
}

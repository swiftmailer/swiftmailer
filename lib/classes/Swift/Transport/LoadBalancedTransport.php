<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Redundantly and rotationally uses several Transports when sending.
 *
 * @author Chris Corbyn
 */
class Swift_Transport_LoadBalancedTransport implements Swift_Transport
{
    /**
     * Transports which are deemed useless.
     *
     * @var Swift_Transport[]
     */
    private $deadTransports = array();

    /**
     * The Transports which are used in rotation.
     *
     * @var Swift_Transport[]
     */
    protected $transports = array();

    /**
     * Creates a new LoadBalancedTransport.
     */
    public function __construct()
    {
    }

    /**
     * Set $transports to delegate to.
     *
     * @param Swift_Transport[] $transports
     */
    public function setTransports(array $transports)
    {
        $this->transports = $transports;
        $this->deadTransports = array();
    }

    /**
     * Get $transports to delegate to.
     *
     * @return Swift_Transport[]
     */
    public function getTransports()
    {
        return array_merge($this->transports, $this->deadTransports);
    }

    /**
     * Test if this Transport mechanism has started.
     *
     * @return bool
     */
    public function isStarted()
    {
        return count($this->transports) > 0;
    }

    /**
     * Start this Transport mechanism.
     */
    public function start()
    {
        $this->transports = array_merge($this->transports, $this->deadTransports);
    }

    /**
     * Stop this Transport mechanism.
     */
    public function stop()
    {
        foreach ($this->transports as $transport) {
            $transport->stop();
        }
    }

    /**
     * Send the given Message.
     *
     * Recipient/sender data will be retrieved from the Message API.
     * The return value is the number of recipients who were accepted for delivery.
     *
     * @param Swift_Mime_Message $message
     * @param string[]           $failedRecipients An array of failures by-reference
     *
     * @return int
     */
    public function send(Swift_Mime_Message $message, &$failedRecipients = null)
    {
        $maxTransports = count($this->transports);
        $sent = 0;

        for ($i = 0; $i < $maxTransports
            && $transport = $this->getNextTransport(); ++$i) {
            try {
                if (!$transport->isStarted()) {
                    $transport->start();
                }
                if ($sent = $transport->send($message, $failedRecipients)) {
                    break;
                }
            } catch (Swift_TransportException $e) {
                $this->killCurrentTransport();
            }
        }

        if (count($this->transports) == 0) {
            throw new Swift_TransportException(
                'All Transports in LoadBalancedTransport failed, or no Transports available'
                );
        }

        return $sent;
    }

    /**
     * Register a plugin.
     *
     * @param Swift_Events_EventListener $plugin
     */
    public function registerPlugin(Swift_Events_EventListener $plugin)
    {
        foreach ($this->transports as $transport) {
            $transport->registerPlugin($plugin);
        }
    }

    /**
     * Rotates the transport list around and returns the first instance.
     *
     * @return Swift_Transport
     */
    protected function getNextTransport()
    {
        if ($next = array_shift($this->transports)) {
            $this->transports[] = $next;
        }

        return $next;
    }

    /**
     * Tag the currently used (top of stack) transport as dead/useless.
     */
    protected function killCurrentTransport()
    {
        if ($transport = array_pop($this->transports)) {
            try {
                $transport->stop();
            } catch (Exception $e) {
            }
            $this->deadTransports[] = $transport;
        }
    }
}

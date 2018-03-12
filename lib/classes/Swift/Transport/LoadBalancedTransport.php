<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swift\Transport;

use Swift\Transport;
use Swift\TransportException;
use Swift\Mime\SimpleMessage;
use Swift\Events\EventListener;
use Exception;

/**
 * Redundantly and rotationally uses several Transports when sending.
 *
 * @author Chris Corbyn
 */
class LoadBalancedTransport implements Transport
{
    /**
     * Transports which are deemed useless.
     *
     * @var Transport[]
     */
    private $deadTransports = [];

    /**
     * The Transports which are used in rotation.
     *
     * @var Transport[]
     */
    protected $transports = [];

    /**
     * The Transport used in the last successful send operation.
     *
     * @var Transport
     */
    protected $lastUsedTransport = null;

    // needed as __construct is called from elsewhere explicitly
    public function __construct()
    {
    }

    /**
     * Set $transports to delegate to.
     *
     * @param Transport[] $transports
     */
    public function setTransports(array $transports)
    {
        $this->transports = $transports;
        $this->deadTransports = [];
    }

    /**
     * Get $transports to delegate to.
     *
     * @return Transport[]
     */
    public function getTransports()
    {
        return array_merge($this->transports, $this->deadTransports);
    }

    /**
     * Get the Transport used in the last successful send operation.
     *
     * @return Transport
     */
    public function getLastUsedTransport()
    {
        return $this->lastUsedTransport;
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
     * {@inheritdoc}
     */
    public function ping()
    {
        foreach ($this->transports as $transport) {
            if (!$transport->ping()) {
                $this->killCurrentTransport();
            }
        }

        return count($this->transports) > 0;
    }

    /**
     * Send the given Message.
     *
     * Recipient/sender data will be retrieved from the Message API.
     * The return value is the number of recipients who were accepted for delivery.
     *
     * @param string[] $failedRecipients An array of failures by-reference
     *
     * @return int
     */
    public function send(SimpleMessage $message, &$failedRecipients = null)
    {
        $maxTransports = count($this->transports);
        $sent = 0;
        $this->lastUsedTransport = null;

        for ($i = 0; $i < $maxTransports
            && $transport = $this->getNextTransport(); ++$i) {
            try {
                if (!$transport->isStarted()) {
                    $transport->start();
                }
                if ($sent = $transport->send($message, $failedRecipients)) {
                    $this->lastUsedTransport = $transport;
                    break;
                }
            } catch (TransportException $e) {
                $this->killCurrentTransport();
            }
        }

        if (0 == count($this->transports)) {
            throw new TransportException(
                'All Transports in LoadBalancedTransport failed, or no Transports available'
                );
        }

        return $sent;
    }

    /**
     * Register a plugin.
     */
    public function registerPlugin(EventListener $plugin)
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

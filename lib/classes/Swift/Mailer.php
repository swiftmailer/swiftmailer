<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swift;

use Swift\Mime\SimpleMessage;
use Swift\Events\EventListener;

/**
 * Swift Mailer class.
 *
 * @author Chris Corbyn
 */
class Mailer
{
    /** The Transport used to send messages */
    private $transport;

    /**
     * Create a new Mailer using $transport for delivery.
     */
    public function __construct(Transport $transport)
    {
        $this->transport = $transport;
    }

    /**
     * Create a new class instance of one of the message services.
     *
     * For example 'mimepart' would create a 'message.mimepart' instance
     *
     * @param string $service
     *
     * @return object
     */
    public function createMessage($service = 'message')
    {
        return DependencyContainer::getInstance()
            ->lookup('message.'.$service);
    }

    /**
     * Send the given Message like it would be sent in a mail client.
     *
     * All recipients (with the exception of Bcc) will be able to see the other
     * recipients this message was sent to.
     *
     * Recipient/sender data will be retrieved from the Message object.
     *
     * The return value is the number of recipients who were accepted for
     * delivery.
     *
     * @param array $failedRecipients An array of failures by-reference
     *
     * @return int The number of successful recipients. Can be 0 which indicates failure
     */
    public function send(SimpleMessage $message, &$failedRecipients = null)
    {
        $failedRecipients = (array) $failedRecipients;

        if (!$this->transport->isStarted()) {
            $this->transport->start();
        }

        $sent = 0;

        try {
            $sent = $this->transport->send($message, $failedRecipients);
        } catch (RfcComplianceException $e) {
            foreach ($message->getTo() as $address => $name) {
                $failedRecipients[] = $address;
            }
        }

        return $sent;
    }

    /**
     * Register a plugin using a known unique key (e.g. myPlugin).
     */
    public function registerPlugin(EventListener $plugin)
    {
        $this->transport->registerPlugin($plugin);
    }

    /**
     * The Transport used to send messages.
     *
     * @return \Swift\Transport
     */
    public function getTransport()
    {
        return $this->transport;
    }
}

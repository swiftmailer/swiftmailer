<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Swift Mailer class.
 *
 * @author Chris Corbyn
 */
class Swift_Mailer
{
    /** The Transport used to send messages */
    private $transport;
    private $twig;

    /**
     * Create a new Mailer using $transport for delivery.
     *
     * @param Swift_Transport $transport
     */
    public function __construct(Swift_Transport $transport, Twig_Environment $twig = null)
    {
        $this->transport = $transport;
        $this->twig = $twig;
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
        return Swift_DependencyContainer::getInstance()
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
     * @param Swift_Mime_Message $message
     * @param array              $failedRecipients An array of failures by-reference
     *
     * @return int The number of successful recipients. Can be 0 which indicates failure
     */
    public function send(Swift_Mime_Message $message, &$failedRecipients = null)
    {
        $failedRecipients = (array) $failedRecipients;

        if (!$this->transport->isStarted()) {
            $this->transport->start();
        }

        $sent = 0;

        try {
            $sent = $this->transport->send($message, $failedRecipients);
        } catch (Swift_RfcComplianceException $e) {
            foreach ($message->getTo() as $address => $name) {
                $failedRecipients[] = $address;
            }
        }

        return $sent;
    }

    public function renderAndSend($templatePath, array $params = array())
    {
        $message = new Swift_Message();
        $messageParts = array();
        $template = $this->twig->load($templatePath);

        foreach (array('from', 'to', 'cc', 'bcc', 'subject', 'body', 'body_txt') as $blockName) {
            if ($template->hasBlock($blockName)) {
                $messageParts[$blockName] = $this->twig->renderBlock($blockName, $params);
            } elseif (isset($params[$blockName])) {
                $messageParts[$blockName] = $params[$blockName];
            }
        }

        foreach ($messageParts as $key => $value) {
            if ('body' === $key) {
                $message->setBody($value, 'text/html');
            } elseif ('body_txt' === $key) {
                $message->addPart($value, 'text/plain');
            } else {
                $message->{'set'.ucfirst($blockName)}($value);
            }
        }

        return $this->send($message);
    }

    /**
     * Register a plugin using a known unique key (e.g. myPlugin).
     *
     * @param Swift_Events_EventListener $plugin
     */
    public function registerPlugin(Swift_Events_EventListener $plugin)
    {
        $this->transport->registerPlugin($plugin);
    }

    /**
     * The Transport used to send messages.
     *
     * @return Swift_Transport
     */
    public function getTransport()
    {
        return $this->transport;
    }
}

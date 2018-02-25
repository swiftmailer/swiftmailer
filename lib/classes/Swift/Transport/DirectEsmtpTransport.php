<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2018 Christian Schmidt
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Delivers messages directly to recipients incoming SMTP server.
 *
 * @author Christian Schmidt
 */
class Swift_Transport_DirectEsmtpTransport implements Swift_Transport
{
    private $eventDispatcher;

    private $transport;

    public function __construct(Swift_Events_EventDispatcher $eventDispatcher, Swift_Transport_EsmtpTransport $transport, Swift_AddressEncoder_IdnAddressEncoder $addressEncoder)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->transport = $transport;
        $this->addressEncoder = $addressEncoder;
    }

    /**
     * {@inheritdoc}
     */
    public function isStarted()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function start()
    {
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function send(Swift_Mime_SimpleMessage $message, &$failedRecipients = null)
    {
        $sent = 0;
        $failedRecipients = (array) $failedRecipients;

        if ($evt = $this->eventDispatcher->createSendEvent($this, $message)) {
            $this->eventDispatcher->dispatchEvent($evt, 'beforeSendPerformed');
            if ($evt->bubbleCancelled()) {
                return 0;
            }
        }

        if (!$reversePath = $message->getReversePath()) {
            $this->throwException(new Swift_TransportException(
                'Cannot send message without a sender address'
                )
            );
        }

        $to = (array) $message->getTo();
        $cc = (array) $message->getCc();
        $tos = array_merge($to, $cc);
        $bcc = (array) $message->getBcc();

        $message->setBcc([]);

        $groups = $this->groupRecipientsByMxHosts($tos, $bcc);
        foreach ($groups as $group) {
            try {
                $sent += $this->sendGroup($message, $reversePath, $group, $failedRecipients);
            } catch (Exception $e) {
                $message->setBcc($bcc);
                throw $e;
            }
        }

        $message->setBcc($bcc);

        if ($evt) {
            if ($sent == count($tos) + count($bcc)) {
                $evt->setResult(Swift_Events_SendEvent::RESULT_SUCCESS);
            } elseif ($sent > 0) {
                $evt->setResult(Swift_Events_SendEvent::RESULT_TENTATIVE);
            } else {
                $evt->setResult(Swift_Events_SendEvent::RESULT_FAILED);
            }
            $evt->setFailedRecipients($failedRecipients);
            $this->eventDispatcher->dispatchEvent($evt, 'sendPerformed');
        }

        $message->generateId(); //Make sure a new Message ID is used

        return $sent;
    }

    protected function sendGroup(Swift_Mime_SimpleMessage $message, string $reversePath, array $group, array &$failedRecipients)
    {
        foreach ($group['hosts'] as $host) {
            $transport = $this->getEsmtpTransport($host);
            try {
                if (!$transport->isStarted()) {
                    $transport->start();
                }
                return $transport->sendCopy($message, $reversePath, $group['tos'], $group['bcc'], $failedRecipients);
            } catch (Swift_TransportException $e) {
            } finally {
                try {
                    $transport->stop();
                } catch (Exception $e) {
                }
            }
        }

        $failedRecipients = array_merge($failedRecipients, $group['tos'], $group['bcc']);

        return 0;
    }

    /**
     * Register a plugin.
     */
    public function registerPlugin(Swift_Events_EventListener $plugin)
    {
        $this->eventDispatcher->bindEventListener($plugin);
    }

    /**
     * @return array Array of MX hostnames, sorted by priority.
     */
    protected function getMxHosts(string $domain): array
    {
        if (!$this->getmxrr($domain, $hosts, $weights)) {
            $hosts = [$domain];
            $weights = [0];
        }
        array_multisort($weights, SORT_NUMERIC, $hosts, SORT_STRING);

        return $hosts;
    }

    protected function getmxrr(string $domain, &$hosts, &$weights): bool
    {
        return getmxrr($domain, $hosts, $weights);
    }

    protected function groupRecipientsByMxHosts(array $tos, array $bcc): array
    {
        $groups = [];
        $hostsByDomain = [];
        foreach (['tos', 'bcc'] as $type) {
            $addresses = $$type;
            foreach ($addresses as $address => $name) {
                $address = $this->addressEncoder->encodeString($address);
                $i = strrpos($address, '@');
                $domain = substr($address, $i + 1);

                if (!isset($hostsByDomain[$domain])) {
                    $hostsByDomain[$domain] = $this->getMxHosts($domain);
                }
                $key = implode(' ', $hostsByDomain[$domain]);

                if (!isset($groups[$key])) {
                    $groups[$key] = [
                        'tos' => [],
                        'bcc' => [],
                        'hosts' => $hostsByDomain[$domain],
                    ];
                }

                $groups[$key][$type][$address] = $name;
            }
        }

        return $groups;
    }

    protected function getEsmtpTransport(string $host): Swift_Transport_EsmtpTransport
    {
        $this->transport->setHost($host);
        $this->transport->setPort(587);
        return $this->transport;
    }

    /** Throw a TransportException, first sending it to any listeners */
    protected function throwException(Swift_TransportException $e)
    {
        if ($evt = $this->eventDispatcher->createTransportExceptionEvent($this, $e)) {
            $this->eventDispatcher->dispatchEvent($evt, 'exceptionThrown');
            if (!$evt->bubbleCancelled()) {
                throw $e;
            }
        } else {
            throw $e;
        }
    }
}

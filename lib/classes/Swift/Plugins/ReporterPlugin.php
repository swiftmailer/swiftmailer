<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swift\Plugins;

use Swift\Events\SendListener;
use Swift\Events\SendEvent;

/**
 * Does real time reporting of pass/fail for each recipient.
 *
 * @author Chris Corbyn
 */
class ReporterPlugin implements SendListener
{
    /**
     * The reporter backend which takes notifications.
     *
     * @var Reporter
     */
    private $reporter;

    /**
     * Create a new ReporterPlugin using $reporter.
     */
    public function __construct(Reporter $reporter)
    {
        $this->reporter = $reporter;
    }

    /**
     * Not used.
     */
    public function beforeSendPerformed(SendEvent $evt)
    {
    }

    /**
     * Invoked immediately after the Message is sent.
     */
    public function sendPerformed(SendEvent $evt)
    {
        $message = $evt->getMessage();
        $failures = array_flip($evt->getFailedRecipients());
        foreach ((array) $message->getTo() as $address => $null) {
            $this->reporter->notify($message, $address, (array_key_exists($address, $failures) ? Reporter::RESULT_FAIL : Reporter::RESULT_PASS));
        }
        foreach ((array) $message->getCc() as $address => $null) {
            $this->reporter->notify($message, $address, (array_key_exists($address, $failures) ? Reporter::RESULT_FAIL : Reporter::RESULT_PASS));
        }
        foreach ((array) $message->getBcc() as $address => $null) {
            $this->reporter->notify($message, $address, (array_key_exists($address, $failures) ? Reporter::RESULT_FAIL : Reporter::RESULT_PASS));
        }
    }
}

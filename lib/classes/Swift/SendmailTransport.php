<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swift;

use Swift\Transport\SendmailTransport as BaseSendmailTransport;

/**
 * SendmailTransport for sending mail through a Sendmail/Postfix (etc..) binary.
 *
 * @author Chris Corbyn
 */
class SendmailTransport extends BaseSendmailTransport
{
    /**
     * Create a new SendmailTransport, optionally using $command for sending.
     *
     * @param string $command
     */
    public function __construct($command = '/usr/sbin/sendmail -bs')
    {
        call_user_func_array(
            [$this, '\\Swift\\Transport\\SendmailTransport::__construct'],
            DependencyContainer::getInstance()
                ->createDependenciesFor('transport.sendmail')
            );

        $this->setCommand($command);
    }
}

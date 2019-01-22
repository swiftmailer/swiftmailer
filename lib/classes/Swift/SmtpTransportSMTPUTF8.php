<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2019 André Renaut
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Sends Messages over SMTP with ESMTP & SMTPUTF8 support.
 *
 * @author     Chris Corbyn
 *
 * @method Swift_SmtpTransport_SMTPUTF8 	setUsername(string $username) Set the username to authenticate with.
 * @method string              			getUsername()                 Get the username to authenticate with.
 * @method Swift_SmtpTransport_SMTPUTF8 	setPassword(string $password) Set the password to authenticate with.
 * @method string              			getPassword()                 Get the password to authenticate with.
 * @method Swift_SmtpTransport_SMTPUTF8 	setAuthMode(string $mode)     Set the auth mode to use to authenticate.
 * @method string              			getAuthMode()                 Get the auth mode to use to authenticate.
 */
class Swift_SmtpTransportSMTPUTF8 extends Swift_Transport_EsmtpTransport
{
    /**
     * @param string $host
     * @param int    $port
     * @param string $encryption
     */
    public function __construct($host = 'localhost', $port = 25, $encryption = null)
    {
        call_user_func_array(
            [$this, 'Swift_Transport_EsmtpTransport::__construct'],
            Swift_DependencyContainer::getInstance()
                ->createDependenciesFor('transport.smtp_SMTPUTF8')
            );

        $this->setHost($host);
        $this->setPort($port);
        $this->setEncryption($encryption);
    }
}

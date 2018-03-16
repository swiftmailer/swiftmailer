<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swift\Transport\Esmtp\Auth;

use Swift\Transport\Esmtp\Authenticator;
use Swift\Transport\SmtpAgent;
use Swift\TransportException;

/**
 * Handles LOGIN authentication.
 *
 * @author Chris Corbyn
 */
class LoginAuthenticator implements Authenticator
{
    /**
     * Get the name of the AUTH mechanism this Authenticator handles.
     *
     * @return string
     */
    public function getAuthKeyword()
    {
        return 'LOGIN';
    }

    /**
     * Try to authenticate the user with $username and $password.
     *
     * @param string $username
     * @param string $password
     *
     * @return bool
     */
    public function authenticate(SmtpAgent $agent, $username, $password)
    {
        try {
            $agent->executeCommand("AUTH LOGIN\r\n", [334]);
            $agent->executeCommand(sprintf("%s\r\n", base64_encode($username)), [334]);
            $agent->executeCommand(sprintf("%s\r\n", base64_encode($password)), [235]);

            return true;
        } catch (TransportException $e) {
            $agent->executeCommand("RSET\r\n", [250]);

            return false;
        }
    }
}

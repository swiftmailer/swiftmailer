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
 * Handles XOAUTH2 authentication.
 *
 * Example:
 * <code>
 * $transport = (new Swift_SmtpTransport('smtp.gmail.com', 587, 'tls'))
 *   ->setAuthMode('XOAUTH2')
 *   ->setUsername('YOUR_EMAIL_ADDRESS')
 *   ->setPassword('YOUR_ACCESS_TOKEN');
 * </code>
 *
 * @author xu.li<AthenaLightenedMyPath@gmail.com>
 *
 * @see        https://developers.google.com/google-apps/gmail/xoauth2_protocol
 */
class XOAuth2Authenticator implements Authenticator
{
    /**
     * Get the name of the AUTH mechanism this Authenticator handles.
     *
     * @return string
     */
    public function getAuthKeyword()
    {
        return 'XOAUTH2';
    }

    /**
     * Try to authenticate the user with $email and $token.
     *
     * @param string $email
     * @param string $token
     *
     * @return bool
     */
    public function authenticate(SmtpAgent $agent, $email, $token)
    {
        try {
            $param = $this->constructXOAuth2Params($email, $token);
            $agent->executeCommand('AUTH XOAUTH2 '.$param."\r\n", [235]);

            return true;
        } catch (TransportException $e) {
            $agent->executeCommand("RSET\r\n", [250]);

            return false;
        }
    }

    /**
     * Construct the auth parameter.
     *
     * @see https://developers.google.com/google-apps/gmail/xoauth2_protocol#the_sasl_xoauth2_mechanism
     */
    protected function constructXOAuth2Params($email, $token)
    {
        return base64_encode("user=$email\1auth=Bearer $token\1\1");
    }
}

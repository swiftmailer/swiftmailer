<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swift\Transport\Esmtp;

use Swift\Transport\SmtpAgent;

/**
 * An Authentication mechanism.
 *
 * @author Chris Corbyn
 */
interface Authenticator
{
    /**
     * Get the name of the AUTH mechanism this Authenticator handles.
     *
     * @return string
     */
    public function getAuthKeyword();

    /**
     * Try to authenticate the user with $username and $password.
     *
     * @param SmtpAgent $agent
     * @param string    $username
     * @param string    $password
     *
     * @return bool
     */
    public function authenticate(SmtpAgent $agent, $username, $password);
}

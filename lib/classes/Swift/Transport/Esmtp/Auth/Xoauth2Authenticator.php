<?php

/**
 * Handles Xoauth2 authentication.
 *
 * @package    Swift
 * @subpackage Transport
 * @author     Toshev Vladimir
 */
class Swift_Transport_Esmtp_Auth_Xoauth2Authenticator implements \Swift_Transport_Esmtp_Authenticator
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
     * Try to authenticate the user with $username and $password.
     *
     * @param Swift_Transport_SmtpAgent $agent
     * @param string                    $username
     * @param string                    $password Xoauth2 access token obtained from service provider (Gmail and MS Outlook mail tested).
     *
     * @return boolean
     */
    public function authenticate(\Swift_Transport_SmtpAgent $agent, $username, $password)
    {
        try {
            $authstring = base64_encode("user=$username\1auth=Bearer $password\1\1");
            $agent->executeCommand(sprintf("AUTH XOAUTH2 %s\r\n", $authstring), array(235));

            return true;
        } catch (Swift_TransportException $e) {
            $agent->executeCommand("RSET\r\n", array(250));

            return false;
        }
    }
}

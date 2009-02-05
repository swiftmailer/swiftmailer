<?php

/*
 The AUTH PLAIN mechanism in Swift Mailer.
 
 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.
 
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
 
 */

//@require 'Swift/Transport/Esmtp/Authenticator.php';
//@require 'Swift/Transport/SmtpAgent.php';
//@require 'Swift/TransportException.php';

/**
 * Handles CRAM-MD5 authentication.
 * @package Swift
 * @subpackage Transport
 * @author Chris Corbyn
 */
class Swift_Transport_Esmtp_Auth_CramMd5Authenticator
  implements Swift_Transport_Esmtp_Authenticator
{
  
  /**
   * Get the name of the AUTH mechanism this Authenticator handles.
   * @return string
   */
  public function getAuthKeyword()
  {
    return 'CRAM-MD5';
  }
  
  /**
   * Try to authenticate the user with $username and $password.
   * @param Swift_Transport_SmtpAgent $agent
   * @param string $username
   * @param string $password
   * @return boolean
   */
  public function authenticate(Swift_Transport_SmtpAgent $agent,
    $username, $password)
  {
    try
    {
      $challenge = $agent->executeCommand("AUTH CRAM-MD5\r\n", array(334));
      $challenge = base64_decode(substr($challenge, 4));
      $message = base64_encode(
        $username . ' ' . $this->_getResponse($password, $challenge)
        );
      $agent->executeCommand(sprintf("%s\r\n", $message), array(235));
      return true;
    }
    catch (Swift_TransportException $e)
    {
      $agent->executeCommand("RSET\r\n", array(250));
      return false;
    }
  }
  
  /**
   * Generate a CRAM-MD5 response from a server challenge.
   * @param string $secret
   * @param string $challenge
   * @return string
   */
  private function _getResponse($secret, $challenge)
  {
    if (strlen($secret) > 64)
    {
      $secret = pack('H32', md5($secret));
    }
    
    if (strlen($secret) < 64)
    {
      $secret = str_pad($secret, 64, chr(0));
    }
    
    $k_ipad = substr($secret, 0, 64) ^ str_repeat(chr(0x36), 64);
    $k_opad = substr($secret, 0, 64) ^ str_repeat(chr(0x5C), 64);

    $inner  = pack('H32', md5($k_ipad . $challenge));
    $digest = md5($k_opad . $inner);

    return $digest;
  }
  
}

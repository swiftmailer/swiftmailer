<?php

/*
 The AUTH LOGIN mechanism in Swift Mailer.
 
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
 * Handles LOGIN authentication.
 * @package Swift
 * @subpackage Transport
 * @author Chris Corbyn
 */
class Swift_Transport_Esmtp_Auth_LoginAuthenticator
  implements Swift_Transport_Esmtp_Authenticator
{
  
  /**
   * Get the name of the AUTH mechanism this Authenticator handles.
   * @return string
   */
  public function getAuthKeyword()
  {
    return 'LOGIN';
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
      $agent->executeCommand("AUTH LOGIN\r\n", array(334));
      $agent->executeCommand(sprintf("%s\r\n", base64_encode($username)), array(334));
      $agent->executeCommand(sprintf("%s\r\n", base64_encode($password)), array(235));
      return true;
    }
    catch (Swift_TransportException $e)
    {
      $agent->executeCommand("RSET\r\n", array(250));
      return false;
    }
  }
  
}

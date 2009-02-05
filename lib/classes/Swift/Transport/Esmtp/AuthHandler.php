<?php

/*
 Handles the ESMTP AUTH extension in Swift Mailer.
 
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

//@require 'Swift/TransportException.php';
//@require 'Swift/Transport/EsmtpHandler.php';
//@require 'Swift/Transport/SmtpAgent.php';

/**
 * An ESMTP handler for AUTH support.
 * @package Swift
 * @subpackage Transport
 * @author Chris Corbyn
 */
class Swift_Transport_Esmtp_AuthHandler implements Swift_Transport_EsmtpHandler
{
  
  /**
   * Authenticators available to process the request.
   * @var Swift_Transport_Esmtp_Authenticator[]
   * @access private
   */
  private $_authenticators = array();
  
  /**
   * The username for authentication.
   * @var string
   * @access private
   */
  private $_username;
  
  /**
   * The password for authentication.
   * @var string
   * @access private
   */
  private $_password;
  
  /**
   * The ESMTP AUTH parameters available.
   * @var string[]
   * @access private
   */
  private $_esmtpParams = array();
  
  /**
   * Create a new AuthHandler with $authenticators for support.
   * @param Swift_Transport_Esmtp_Authenticator[] $authenticators
   */
  public function __construct(array $authenticators)
  {
    $this->setAuthenticators($authenticators);
  }
  
  /**
   * Set the Authenticators which can process a login request.
   * @param Swift_Transport_Esmtp_Authenticator[] $authenticators
   */
  public function setAuthenticators(array $authenticators)
  {
    $this->_authenticators = $authenticators;
  }
  
  /**
   * Get the Authenticators which can process a login request.
   * @return Swift_Transport_Esmtp_Authenticator[]
   */
  public function getAuthenticators()
  {
    return $this->_authenticators;
  }
  
  /**
   * Set the username to authenticate with.
   * @param string $username
   */
  public function setUsername($username)
  {
    $this->_username = $username;
  }
  
  /**
   * Get the username to authenticate with.
   * @return string
   */
  public function getUsername()
  {
    return $this->_username;
  }
  
  /**
   * Set the password to authenticate with.
   * @param string $password
   */
  public function setPassword($password)
  {
    $this->_password = $password;
  }
  
  /**
   * Get the password to authenticate with.
   * @return string
   */
  public function getPassword()
  {
    return $this->_password;
  }
  
  /**
   * Get the name of the ESMTP extension this handles.
   * @return boolean
   */
  public function getHandledKeyword()
  {
    return 'AUTH';
  }
  
  /**
   * Set the parameters which the EHLO greeting indicated.
   * @param string[] $parameters
   */
  public function setKeywordParams(array $parameters)
  {
    $this->_esmtpParams = $parameters;
  }
  
  /**
   * Runs immediately after a EHLO has been issued.
   * @param Swift_Transport_SmtpAgent $agent to read/write
   */
  public function afterEhlo(Swift_Transport_SmtpAgent $agent)
  {
    if ($this->_username)
    {
      $count = 0;
      foreach ($this->_authenticators as $authenticator)
      {
        if (in_array(strtolower($authenticator->getAuthKeyword()),
          array_map('strtolower', $this->_esmtpParams)))
        {
          $count++;
          if ($authenticator->authenticate($agent, $this->_username, $this->_password))
          {
            return;
          }
        }
      }
      throw new Swift_TransportException(
        'Failed to authenticate on SMTP server with username "' .
        $this->_username . '" using ' . $count . ' possible authenticators'
        );
    }
  }
  
  /**
   * Not used.
   */
  public function getMailParams()
  {
    return array();
  }
  
  /**
   * Not used.
   */
  public function getRcptParams()
  {
    return array();
  }
  
  /**
   * Not used.
   */
  public function onCommand(Swift_Transport_SmtpAgent $agent,
    $command, $codes = array(), &$failedRecipients = null, &$stop = false)
  {
  }
    
  /**
   * Returns +1, -1 or 0 according to the rules for usort().
   * This method is called to ensure extensions can be execute in an appropriate order.
   * @param string $esmtpKeyword to compare with
   * @return int
   */
  public function getPriorityOver($esmtpKeyword)
  {
    return 0;
  }
  
  /**
   * Returns an array of method names which are exposed to the Esmtp class.
   * @return string[]
   */
  public function exposeMixinMethods()
  {
    return array('setUsername', 'getUsername', 'setPassword', 'getPassword');
  }
  
  /**
   * Not used.
   */
  public function resetState()
  {
  }
  
}

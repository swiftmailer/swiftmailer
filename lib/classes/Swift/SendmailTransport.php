<?php

/*
 Sendmail Transport wrapper from Swift Mailer.
 
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

//@require 'Swift/Transport/SendmailTransport.php';
//@require 'Swift/DependencyContainer.php';

/**
 * SendmailTransport for sending mail through a sendmail/postfix (etc..) binary.
 * @package Swift
 * @subpackage Transport
 * @author Chris Corbyn
 */
class Swift_SendmailTransport extends Swift_Transport_SendmailTransport
{
  
  /**
   * Create a new SendmailTransport, optionally using $command for sending.
   * @param string $command
   */
  public function __construct($command = '/usr/sbin/sendmail -bs')
  {
    call_user_func_array(
      array($this, 'Swift_Transport_SendmailTransport::__construct'),
      Swift_DependencyContainer::getInstance()
        ->createDependenciesFor('transport.sendmail')
      );
    
    $this->setCommand($command);
  }
  
  /**
   * Create a new SendmailTransport instance.
   * @param string $command
   * @return Swift_SendmailTransport
   */
  public static function newInstance($command = '/usr/sbin/sendmail -bs')
  {
    return new self($command);
  }
  
}

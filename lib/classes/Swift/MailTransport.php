<?php

/*
 The basic mail() wrapper from Swift Mailer.
 
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

//@require 'Swift/Transport/MailTransport.php';
//@require 'Swift/DependencyContainer.php';

/**
 * Sends Messages using the mail() function.
 * @package Swift
 * @subpackage Transport
 * @author Chris Corbyn
 */
class Swift_MailTransport extends Swift_Transport_MailTransport
{
  
  /**
   * Create a new MailTransport, optionally specifying $extraParams.
   * @param string $extraParams
   */
  public function __construct($extraParams = '-f%s')
  {
    call_user_func_array(
      array($this, 'Swift_Transport_MailTransport::__construct'),
      Swift_DependencyContainer::getInstance()
        ->createDependenciesFor('transport.mail')
      );
    
    $this->setExtraParams($extraParams);
  }
  
  /**
   * Create a new MailTransport instance.
   * @param string $extraParams To be passed to mail()
   * @return Swift_MailTransport
   */
  public static function newInstance($extraParams = '-f%s')
  {
    return new self($extraParams);
  }
  
}

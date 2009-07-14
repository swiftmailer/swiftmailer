<?php

/*
 SMTP Transport wrapper from Swift Mailer.
 
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

//@require 'Swift/Transport/EsmtpTransport.php';
//@require 'Swift/DependencyContainer.php';

/**
 * Sends Messages over SMTP with ESMTP support.
 * @package Swift
 * @subpackage Transport
 * @author Chris Corbyn
 */
class Swift_SmtpTransport extends Swift_Transport_EsmtpTransport
{
  
  /**
   * Create a new SmtpTransport, optionally with $host, $port and $security.
   * @param string $host
   * @param int $port
   * @param int $security
   */
  public function __construct($host = 'localhost', $port = 25,
    $security = null)
  {
    call_user_func_array(
      array($this, 'Swift_Transport_EsmtpTransport::__construct'),
      Swift_DependencyContainer::getInstance()
        ->createDependenciesFor('transport.smtp')
      );
    
    $this->setHost($host);
    $this->setPort($port);
    $this->setEncryption($security);
  }
  
  /**
   * Create a new SmtpTransport instance.
   * @param string $host
   * @param int $port
   * @param int $security
   * @return Swift_SmtpTransport
   */
  public static function newInstance($host = 'localhost', $port = 25,
    $security = null)
  {
    return new self($host, $port, $security);
  }
  
}

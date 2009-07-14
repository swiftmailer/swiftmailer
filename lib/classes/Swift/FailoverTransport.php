<?php

/*
 High-availability failover Transport class from Swift Mailer.
 
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

//@require 'Swift/Transport/FailoverTransport.php';
//@require 'Swift/DependencyContainer.php';

/**
 * Contains a list of redundant Transports so when one fails, the next is used.
 * @package Swift
 * @subpackage Transport
 * @author Chris Corbyn
 */
class Swift_FailoverTransport extends Swift_Transport_FailoverTransport
{
  
  /**
   * Creates a new FailoverTransport with $transports.
   * @param array $transports
   */
  public function __construct($transports = array())
  {
    call_user_func_array(
      array($this, 'Swift_Transport_FailoverTransport::__construct'),
      Swift_DependencyContainer::getInstance()
        ->createDependenciesFor('transport.failover')
      );
    
    $this->setTransports($transports);
  }
  
  /**
   * Create a new FailoverTransport instance.
   * @param string $transports
   * @return Swift_FailoverTransport
   */
  public static function newInstance($transports = array())
  {
    return new self($transports);
  }
  
}

<?php

/*
 Load balanced Transport class from Swift Mailer.
 
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

//@require 'Swift/Transport/LoadBalancedTransport.php';
//@require 'Swift/DependencyContainer.php';

/**
 * Redudantly and rotationally uses several Transport implementations when sending.
 * @package Swift
 * @subpackage Transport
 * @author Chris Corbyn
 */
class Swift_LoadBalancedTransport extends Swift_Transport_LoadBalancedTransport
{
  
  /**
   * Creates a new LoadBalancedTransport with $transports.
   * @param array $transports
   */
  public function __construct($transports = array())
  {
    call_user_func_array(
      array($this, 'Swift_Transport_LoadBalancedTransport::__construct'),
      Swift_DependencyContainer::getInstance()
        ->createDependenciesFor('transport.loadbalanced')
      );
    
    $this->setTransports($transports);
  }
  
  /**
   * Create a new LoadBalancedTransport instance.
   * @param string $transports
   * @return Swift_LoadBalancedTransport
   */
  public static function newInstance($transports = array())
  {
    return new self($transports);
  }
  
}

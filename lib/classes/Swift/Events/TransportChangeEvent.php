<?php

/*
 TransportChangeEvent class in Swift Mailer.
 
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

//@require 'Swift/Events/EventObject.php';

/**
 * Generated when the state of a Transport is changed (i.e. stopped/started).
 * @package Swift
 * @subpackage Events
 * @author Chris Corbyn
 */
class Swift_Events_TransportChangeEvent extends Swift_Events_EventObject
{
  
  /**
   * Get the Transport.
   * @return Swift_Transport
   */
  public function getTransport()
  {
    return $this->getSource();
  }
  
}
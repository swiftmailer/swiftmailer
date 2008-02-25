<?php

/*
 Base Event class in Swift Mailer.
 
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


/**
 * A base Event which all Event classes inherit from.
 * @package Swift
 * @subpackage Events
 * @author Chris Corbyn
 */
class Swift_Events_EventObject
{
  
  /**
   * The source of this Event.
   * @var object
   * @access private
   */
  private $_source;
  
  /**
   * The state of this Event (should it bubble up the stack?).
   * @var boolean
   * @access private
   */
  private $_bubbleCancelled = false;
  
  /**
   * Get the source object of this event.
   * @return object
   */
  public function getSource()
  {
    return $this->_source;
  }
  
  /**
   * Create a new event using this one as a prototype.
   * The event source will be $source and will be immutable.
   * @param object $source
   * @return Swift_Events_EventObject
   */
  public function cloneFor($source)
  {
    $evt = clone $this;
    $evt->_source = $source;
    return $evt;
  }
  
  /**
   * Prevent this Event from bubbling any further up the stack.
   * @param boolean $cancel, optional
   */
  public function cancelBubble($cancel = true)
  {
    $this->_bubbleCancelled = $cancel;
  }
  
  /**
   * Returns true if this Event will not bubble any further up the stack.
   * @return boolean
   */
  public function bubbleCancelled()
  {
    return $this->_bubbleCancelled;
  }
  
  /**
   * Clone method to ensure the object is cleanly cloned.
   */
  public function __clone()
  {
    $this->_bubbleCancelled = false;
  }
  
}

<?php

/*
 EventDispatcher interface in Swift Mailer.
 
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

//@require 'Swift/Event/EventListener.php';
//@require 'Swift/Event.php';

/**
 * Interface for the EventDispatcher which handles the event dispatching layer.
 * @package Swift
 * @subpackage Event
 * @author Chris Corbyn
 */
interface Swift_Event_EventDispatcher
{
  
  /**
   * Create the event for the given event type.
   * @param string $eventType
   * @param object $source
   * @param string[] $properties the event will contain
   * @return Swift_Event_EventObject
   */
  public function createEvent($eventType, $source, array $properties);
  
  /**
   * Bind an event listener to this dispatcher.
   * The listener can optionally be bound only to the given event source.
   * @param Swift_Event_EventListener $listener
   * @param object $source, optional
   */
  public function bindEventListener(Swift_Event_EventListener $listener,
    $source = null);
  
  /**
   * Dispatch the given Event to all suitable listeners.
   * @param Swift_Event_EventObject $evt
   * @param string $target method
   */
  public function dispatchEvent(Swift_Event_EventObject $evt, $target);
  
}

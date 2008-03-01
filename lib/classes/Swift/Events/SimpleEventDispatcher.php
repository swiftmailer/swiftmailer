<?php

/*
 The standard EventDispatcher in Swift Mailer.
 
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

//@require 'Swift/Events/EventDispatcher.php';
//@require 'Swift/Events/EventListener.php';
//@require 'Swift/Events/EventObject.php';

/**
 * The EventDispatcher which handles the event dispatching layer.
 * @package Swift
 * @subpackage Events
 * @author Chris Corbyn
 */
class Swift_Events_SimpleEventDispatcher implements Swift_Events_EventDispatcher
{
  
  /**
   * A map of event aliases to concrete class names.
   * @var string[]
   * @access private
   */
  private $_eventMap = array();
  
  /**
   * A map of event class names to listener interface names.
   * @var string[]
   * @access private
   */
  private $_listenerMap = array();
  
  /**
   * A lazy-loaded map of event objects.
   * @var Swift_Events_EventObject[]
   * @access private
   */
  private $_prototypes = array();
  
  /**
   * Event listeners bound to this dispatcher.
   * @var array
   * @access private
   */
  private $_listeners = array();
  
  /**
   * Listeners queued to have an Event bubbled up the stack to them.
   * @var Swift_Events_EventListener[]
   * @access private
   */
  private $_bubbleQueue = array();
  
  /**
   * Create a new SimpleEventDispatcher using $descriptorMap.
   * The descriptor map is a complex array mapping event alias names to
   * concrete class names and listener interfaces:
   * array (
   *  'foo' => array('event' => 'FooEvent', 'listener' => 'FooListener')
   * )
   * @param array $descriptorMap
   */
  public function __construct(array $descriptorMap)
  {
    foreach ($descriptorMap as $eventType => $spec)
    {
      if (is_object($spec['event']))
      {
        $this->_prototypes[$eventType] = $spec['event'];
        $eventClass = get_class($spec['event']);
      }
      else
      {
        $eventClass = $spec['event'];
      }
      $this->_eventMap[$eventType] = $eventClass;
      $this->_listenerMap[$eventClass] = $spec['listener'];
    }
  }
  
  /**
   * Create the event for the given event type.
   * @param string $eventType
   * @param object $source
   * @param string[] $properties the event will contain
   */
  public function createEvent($eventType, $source, $properties = array())
  {
    if (!array_key_exists($eventType, $this->_eventMap))
    {
      $evt = null;
    }
    else
    {
      if (!array_key_exists($eventType, $this->_prototypes))
      {
        $class = $this->_eventMap[$eventType];
        $this->_prototypes[$eventType] = new $class();
      }
      $evt = $this->_prototypes[$eventType]->cloneFor($source);
      foreach ($properties as $key => $value)
      {
        $evt->$key = $value;
      }
    }
    return $evt;
  }
  
  /**
   * Bind an event listener to this dispatcher.
   * The listener can optionally be bound only to the given event source.
   * @param Swift_Events_EventListener $listener
   * @param object $source, optional
   */
  public function bindEventListener(Swift_Events_EventListener $listener,
    $source = null)
  {
    $this->_listeners[] = array('listener' => $listener, 'source' => $source);
  }
  
  /**
   * Dispatch the given Event to all suitable listeners.
   * @param Swift_Events_EventObject $evt
   * @param string $target method
   */
  public function dispatchEvent(Swift_Events_EventObject $evt, $target)
  {
    $this->_prepareBubbleQueue($evt);
    $this->_bubble($evt, $target);
  }
  
  // -- Private methods
  
  /**
   * Queue listeners on a stack ready for $evt to be bubbled up it.
   * @param Swift_Events_EventObject $evt
   * @access private
   */
  private function _prepareBubbleQueue(Swift_Events_EventObject $evt)
  {
    $this->_bubbleQueue = array();
    $evtClass = get_class($evt);
    foreach ($this->_listeners as $bindPair)
    {
      $listener = $bindPair['listener'];
      if (array_key_exists($evtClass, $this->_listenerMap)
        && ($listener instanceof $this->_listenerMap[$evtClass])
        && (!$bindPair['source'] || $bindPair['source'] === $evt->getSource()))
      {
        $this->_bubbleQueue[] = $listener;
      }
    }
  }
  
  /**
   * Bubble $evt up the stack calling $target() on each listener.
   * @param Swift_Events_EventObject $evt
   * @param string $target
   * @access private
   */
  private function _bubble(Swift_Events_EventObject $evt, $target)
  {
    if (!$evt->bubbleCancelled() && $listener = array_shift($this->_bubbleQueue))
    {
      $listener->$target($evt);
      $this->_bubble($evt, $target);
    }
  }
  
}

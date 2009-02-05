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

//@require 'Swift/Events/EventListener.php';
//@require 'Swift/Event.php';

/**
 * Interface for the EventDispatcher which handles the event dispatching layer.
 * @package Swift
 * @subpackage Events
 * @author Chris Corbyn
 */
interface Swift_Events_EventDispatcher
{
  
  /**
   * Create a new SendEvent for $source and $message.
   * @param Swift_Transport $source
   * @param Swift_Mime_Message
   * @return Swift_Events_SendEvent
   */
  public function createSendEvent(Swift_Transport $source,
    Swift_Mime_Message $message);
  
  /**
   * Create a new CommandEvent for $source and $command.
   * @param Swift_Transport $source
   * @param string $command That will be executed
   * @param array $successCodes That are needed
   * @return Swift_Events_CommandEvent
   */
  public function createCommandEvent(Swift_Transport $source,
    $command, $successCodes = array());
  
  /**
   * Create a new ResponseEvent for $source and $response.
   * @param Swift_Transport $source
   * @param string $response
   * @param boolean $valid If the response is valid
   * @return Swift_Events_ResponseEvent
   */
  public function createResponseEvent(Swift_Transport $source,
    $response, $valid);
  
  /**
   * Create a new TransportChangeEvent for $source.
   * @param Swift_Transport $source
   * @return Swift_Events_TransportChangeEvent
   */
  public function createTransportChangeEvent(Swift_Transport $source);
  
  /**
   * Create a new TransportExceptionEvent for $source.
   * @param Swift_Transport $source
   * @param Swift_TransportException $ex
   * @return Swift_Events_TransportExceptionEvent
   */
  public function createTransportExceptionEvent(Swift_Transport $source,
    Swift_TransportException $ex);
  
  /**
   * Bind an event listener to this dispatcher.
   * @param Swift_Events_EventListener $listener
   */
  public function bindEventListener(Swift_Events_EventListener $listener);
  
  /**
   * Dispatch the given Event to all suitable listeners.
   * @param Swift_Events_EventObject $evt
   * @param string $target method
   */
  public function dispatchEvent(Swift_Events_EventObject $evt, $target);
  
}

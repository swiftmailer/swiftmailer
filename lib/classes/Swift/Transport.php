<?php

/*
 The Transport interface from Swift Mailer.
 
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

//@require 'Swift/Mime/Message.php';
//@require 'Swift/Events/EventListener.php';

/**
 * Sends Messages via an abstract Transport subsystem.
 * 
 * @package Swift
 * @subpackage Transport
 * @author Chris Corbyn
 */
interface Swift_Transport
{

  /**
   * Test if this Transport mechanism has started.
   * 
   * @return boolean
   */
  public function isStarted();
  
  /**
   * Start this Transport mechanism.
   */
  public function start();
  
  /**
   * Stop this Transport mechanism.
   */
  public function stop();
  
  /**
   * Send the given Message.
   * 
   * Recipient/sender data will be retreived from the Message API.
   * The return value is the number of recipients who were accepted for delivery.
   * 
   * @param Swift_Mime_Message $message
   * @param string[] &$failedRecipients to collect failures by-reference
   * @return int
   */
  public function send(Swift_Mime_Message $message, &$failedRecipients = null);
  
  /**
   * Register a plugin in the Transport.
   * 
   * @param Swift_Events_EventListener $plugin
   */
  public function registerPlugin(Swift_Events_EventListener $plugin);
  
}

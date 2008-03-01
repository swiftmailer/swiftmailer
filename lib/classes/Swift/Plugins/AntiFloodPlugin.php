<?php

/*
 AntiFlood plugin in Swift Mailer.
 
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

//@require 'Swift/Events/SendListener.php';
//@require 'Swift/Events/SendEvent.php';

/**
 * Reduces network flooding when sending large amounts of mail.
 * @package Swift
 * @subpackage Plugins
 * @author Chris Corbyn
 */
class Swift_Plugins_AntiFloodPlugin implements Swift_Events_SendListener
{
  
  /**
   * Invoked immediately before the Message is sent.
   * @param Swift_Events_SendEvent $evt
   */
  public function beforeSendPerformed(Swift_Events_SendEvent $evt)
  {
  }
  
  /**
   * Invoked immediately after the Message is sent.
   * @param Swift_Events_SendEvent $evt
   */
  public function sendPerformed(Swift_Events_SendEvent $evt)
  {
  }
  
}

<?php

/*
 Logger plugin in Swift Mailer.
 
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

//@require 'Swift/Events/CommandListener.php';
//@require 'Swift/Events/CommandEvent.php';
//@require 'Swift/Events/ResponseListener.php';
//@require 'Swift/Events/ResponseEvent.php';
//@require 'Swift/Events/TransportChangeListener.php';
//@require 'Swift/Events/TransportChangeEvent.php';
//@require 'Swift/Events/TransportExceptionEvent.php';
//@require 'Swift/Events/TransportExceptionListener.php';
//@require 'Swift/Events/TransportException.php';
//@require 'Swift/Plugins/Logger.php';

/**
 * Does real time logging of Transport level information.
 * 
 * @package Swift
 * @subpackage Plugins
 * 
 * @author Chris Corbyn
 */
class Swift_Plugins_LoggerPlugin
  implements Swift_Events_CommandListener, Swift_Events_ResponseListener,
  Swift_Events_TransportChangeListener, Swift_Events_TransportExceptionListener,
  Swift_Plugins_Logger
{
  
  /** The logger which is delegated to */
  private $_logger;
  
  /**
   * Create a new LoggerPlugin using $logger.
   * 
   * @param Swift_Plugins_Logger $logger
   */
  public function __construct(Swift_Plugins_Logger $logger)
  {
    $this->_logger = $logger;
  }
  
  /**
   * Add a log entry.
   * 
   * @param string $entry
   */
  public function add($entry)
  {
    $this->_logger->add($entry);
  }
  
  /**
   * Clear the log contents.
   */
  public function clear()
  {
    $this->_logger->clear();
  }
  
  /**
   * Get this log as a string.
   * 
   * @return string
   */
  public function dump()
  {
    return $this->_logger->dump();
  }
  
  /**
   * Invoked immediately following a command being sent.
   * 
   * @param Swift_Events_ResponseEvent $evt
   */
  public function commandSent(Swift_Events_CommandEvent $evt)
  {
    $command = $evt->getCommand();
    $this->_logger->add(sprintf(">> %s", $command));
  }
  
  /**
   * Invoked immediately following a response coming back.
   * 
   * @param Swift_Events_ResponseEvent $evt
   */
  public function responseReceived(Swift_Events_ResponseEvent $evt)
  {
    $response = $evt->getResponse();
    $this->_logger->add(sprintf("<< %s", $response));
  }
  
  /**
   * Invoked just before a Transport is started.
   * 
   * @param Swift_Events_TransportChangeEvent $evt
   */
  public function beforeTransportStarted(Swift_Events_TransportChangeEvent $evt)
  {
    $transportName = get_class($evt->getSource());
    $this->_logger->add(sprintf("++ Starting %s", $transportName));
  }
  
  /**
   * Invoked immediately after the Transport is started.
   * 
   * @param Swift_Events_TransportChangeEvent $evt
   */
  public function transportStarted(Swift_Events_TransportChangeEvent $evt)
  {
    $transportName = get_class($evt->getSource());
    $this->_logger->add(sprintf("++ %s started", $transportName));
  }
  
  /**
   * Invoked just before a Transport is stopped.
   * 
   * @param Swift_Events_TransportChangeEvent $evt
   */
  public function beforeTransportStopped(Swift_Events_TransportChangeEvent $evt)
  {
    $transportName = get_class($evt->getSource());
    $this->_logger->add(sprintf("++ Stopping %s", $transportName));
  }
  
  /**
   * Invoked immediately after the Transport is stopped.
   * 
   * @param Swift_Events_TransportChangeEvent $evt
   */
  public function transportStopped(Swift_Events_TransportChangeEvent $evt)
  {
    $transportName = get_class($evt->getSource());
    $this->_logger->add(sprintf("++ %s stopped", $transportName));
  }
  
  /**
   * Invoked as a TransportException is thrown in the Transport system.
   * 
   * @param Swift_Events_TransportExceptionEvent $evt
   */
  public function exceptionThrown(Swift_Events_TransportExceptionEvent $evt)
  {
    $e = $evt->getException();
    $message = $e->getMessage();
    $this->_logger->add(sprintf("!! %s", $message));
    $message .= PHP_EOL;
    $message .= 'Log data:' . PHP_EOL;
    $message .= $this->_logger->dump();
    $evt->cancelBubble();
    throw new Swift_TransportException($message);
  }
  
}

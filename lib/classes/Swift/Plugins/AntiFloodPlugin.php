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
   * The number of emails to send before restarting Transport.
   * @var int
   * @access private
   */
  private $_threshold;
  
  /**
   * The number of seconds to sleep for during a restart.
   * @var int
   * @access private
   */
  private $_sleep;
  
  /**
   * The internal counter.
   * @var int
   * @access private
   */
  private $_counter = 0;
  
  /**
   * Create a new AntiFloodPlugin with $threshold and $sleep time.
   * @param int $threshold
   * @param int $sleep time
   */
  public function __construct($threshold = 99, $sleep = 0)
  {
    $this->setThreshold($threshold);
    $this->setSleepTime($sleep);
  }
  
  /**
   * Set the number of emails to send before restarting.
   * @param int $threshold
   */
  public function setThreshold($threshold)
  {
    $this->_threshold = $threshold;
  }
  
  /**
   * Get the number of emails to send before restarting.
   * @return int
   */
  public function getThreshold()
  {
    return $this->_threshold;
  }
  
  /**
   * Set the number of seconds to sleep for during a restart.
   * @param int $sleep time
   */
  public function setSleepTime($sleep)
  {
    $this->_sleep = $sleep;
  }
  
  /**
   * Get the number of seconds to sleep for during a restart.
   * @return int
   */
  public function getSleepTime()
  {
    return $this->_sleep;
  }
  
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
    ++$this->_counter;
    if ($this->_counter >= $this->_threshold)
    {
      $transport = $evt->getTransport();
      $transport->stop();
      if ($this->_sleep)
      {
        sleep($this->_sleep);
      }
      $transport->start();
      $this->_counter = 0;
    }
  }
  
}

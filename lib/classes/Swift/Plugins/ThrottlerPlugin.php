<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * Throttles the rate at which emails are sent.
 * @package Swift
 * @subpackage Plugins
 * @author Chris Corbyn
 */
class Swift_Plugins_ThrottlerPlugin
  extends Swift_Plugins_BandwidthMonitorPlugin
  implements Swift_Plugins_Sleeper, Swift_Plugins_Timer
{

  /** Flag for throttling in bytes per minute */
  const BYTES_PER_MINUTE = 0x01;

  /** Flag for throttling in emails per minute */
  const MESSAGES_PER_MINUTE = 0x10;

  /**
   * The Sleeper instance for sleeping.
   * @var Swift_Plugins_Sleeper
   * @access private
   */
  private $_sleeper;

  /**
   * The Timer instance which provides the timestamp.
   * @var Swift_Plugins_Timer
   * @access private
   */
  private $_timer;

  /**
   * The time at which the first email was sent.
   * @var int
   * @access private
   */
  private $_start;

  /**
   * The rate at which messages should be sent.
   * @var int
   * @access private
   */
  private $_rate;

  /**
   * The mode for throttling.
   * This is {@link BYTES_PER_MINUTE} or {@link MESSAGES_PER_MINUTE}
   * @var int
   * @access private
   */
  private $_mode;

  /**
   * An internal counter of the number of messages sent.
   * @var int
   * @access private
   */
  private $_messages = 0;

  /**
   * Create a new ThrottlerPlugin.
   * @param int $rate
   * @param int $mode, defaults to {@link BYTES_PER_MINUTE}
   * @param Swift_Plugins_Sleeper $sleeper (only needed in testing)
   * @param Swift_Plugins_Timer $timer (only needed in testing)
   */
  public function __construct($rate, $mode = self::BYTES_PER_MINUTE,
    Swift_Plugins_Sleeper $sleeper = null, Swift_Plugins_Timer $timer = null)
  {
    $this->_rate = $rate;
    $this->_mode = $mode;
    $this->_sleeper = $sleeper;
    $this->_timer = $timer;
  }

  /**
   * Invoked immediately before the Message is sent.
   * @param Swift_Events_SendEvent $evt
   */
  public function beforeSendPerformed(Swift_Events_SendEvent $evt)
  {
    $time = $this->getTimestamp();
    if (!isset($this->_start))
    {
      $this->_start = $time;
    }
    $duration = $time - $this->_start;

    if (self::BYTES_PER_MINUTE == $this->_mode)
    {
      $sleep = $this->_throttleBytesPerMinute($duration);
    }
    else
    {
      $sleep = $this->_throttleMessagesPerMinute($duration);
    }

    if ($sleep > 0)
    {
      $this->sleep($sleep);
    }
  }

  /**
   * Invoked when a Message is sent.
   * @param Swift_Events_SendEvent $evt
   */
  public function sendPerformed(Swift_Events_SendEvent $evt)
  {
    parent::sendPerformed($evt);
    ++$this->_messages;
  }

  /**
   * Sleep for $seconds.
   * @param int $seconds
   */
  public function sleep($seconds)
  {
    if (isset($this->_sleeper))
    {
      $this->_sleeper->sleep($seconds);
    }
    else
    {
      sleep($seconds);
    }
  }

  /**
   * Get the current UNIX timestamp
   * @return int
   */
  public function getTimestamp()
  {
    if (isset($this->_timer))
    {
      return $this->_timer->getTimestamp();
    }
    else
    {
      return time();
    }
  }

  // -- Private methods

  /**
   * Get a number of seconds to sleep for.
   * @param int $timePassed
   * @return int
   * @access private
   */
  private function _throttleBytesPerMinute($timePassed)
  {
    $expectedDuration = $this->getBytesOut() / ($this->_rate / 60);
    return (int) ceil($expectedDuration - $timePassed);
  }

  /**
   * Get a number of seconds to sleep for.
   * @param int $timePassed
   * @return int
   * @access private
   */
  private function _throttleMessagesPerMinute($timePassed)
  {
    $expectedDuration = $this->_messages / ($this->_rate / 60);
    return (int) ceil($expectedDuration - $timePassed);
  }

}

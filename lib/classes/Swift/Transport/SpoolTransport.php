<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2009 Fabien Potencier <fabien.potencier@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Stores Messages in a queue.
 * @package Swift
 * @author  Fabien Potencier
 */
class Swift_Transport_SpoolTransport implements Swift_Transport
{
  /** The spool instance */
  private $_spool;

  /** The event dispatcher from the plugin API */
  private $_eventDispatcher;

  /**
   * Constructor.
   */
  public function __construct(Swift_Spool $spool, Swift_Events_EventDispatcher $eventDispatcher)
  {
    $this->_spool = $spool;
    $this->_eventDispatcher = $eventDispatcher;
  }
  
  /**
   * Tests if this Transport mechanism has started.
   *
   * @return boolean
   */
  public function isStarted()
  {
    return true;
  }
  
  /**
   * Starts this Transport mechanism.
   */
  public function start()
  {
  }
  
  /**
   * Stops this Transport mechanism.
   */
  public function stop()
  {
  }
  
  /**
   * Sends the given message.
   *
   * @param Swift_Mime_Message $message
   * @param string[] &$failedRecipients to collect failures by-reference
   *
   * @return int The number of sent emails
   */
  public function send(Swift_Mime_Message $message, &$failedRecipients = null)
  {
    if ($evt = $this->_eventDispatcher->createSendEvent($this, $message))
    {
      $this->_eventDispatcher->dispatchEvent($evt, 'beforeSendPerformed');
      if ($evt->bubbleCancelled())
      {
        return 0;
      }
    }
    
    $success = $this->_spool->queueMessage($message);
    
    if ($evt)
    {
      $evt->setResult($success ? Swift_Events_SendEvent::RESULT_SUCCESS : Swift_Events_SendEvent::RESULT_FAILED);
      $this->_eventDispatcher->dispatchEvent($evt, 'sendPerformed');
    }
    
    return 1;
  }
  
  /**
   * Register a plugin.
   *
   * @param Swift_Events_EventListener $plugin
   */
  public function registerPlugin(Swift_Events_EventListener $plugin)
  {
    $this->_eventDispatcher->bindEventListener($plugin);
  }
}

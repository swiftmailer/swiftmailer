<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2009 Fabien Potencier <fabien.potencier@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Stores Messages on the filesystem.
 * @package Swift
 * @author  Fabien Potencier
 */
class Swift_FileSpool extends Swift_ConfigurableSpool
{
  /** The spool directory */
  private $_path;
  
  /**
   * Create a new FileSpool.
   * @param string $path
   */
  public function __construct($path)
  {
    $this->_path = $path;
    
    if (!file_exists($this->_path))
    {
      mkdir($this->_path, 0777, true);
    }
  }
  
  /**
   * Tests if this Spool mechanism has started.
   *
   * @return boolean
   */
  public function isStarted()
  {
    return true;
  }
  
  /**
   * Starts this Spool mechanism.
   */
  public function start()
  {
  }
  
  /**
   * Stops this Spool mechanism.
   */
  public function stop()
  {
  }
  
  /**
   * Queues a message.
   * @param Swift_Mime_Message $message The message to store
   */
  public function queueMessage(Swift_Mime_Message $message)
  {
    $ser = serialize($message);
    
    file_put_contents($this->_path.'/'.md5($ser.uniqid()).'.message', $ser);
  }
  
  /**
   * Sends messages using the given transport instance.
   *
   * @param Swift_Transport $transport         A transport instance
   * @param string[]        &$failedRecipients An array of failures by-reference
   *
   * @return int The number of sent emails
   */
  public function flushQueue(Swift_Transport $transport, &$failedRecipients = null)
  {
    if (!$transport->isStarted())
    {
      $transport->start();
    }

    $failedRecipients = (array) $failedRecipients;
    $count = 0;
    $time = time();
    foreach (new DirectoryIterator($this->_path) as $file)
    {
      $file = $file->getRealPath();

      if (!strpos($file, '.message'))
      {
        continue;
      }

      $message = unserialize(file_get_contents($file));

      $count += $transport->send($message, $failedRecipients);

      unlink($file);

      if ($this->getMessageLimit() && $count >= $this->getMessageLimit())
      {
        break;
      }

      if ($this->getTimeLimit() && (time() - $time) >= $this->getTimeLimit())
      {
        break;
      }
    }

    return $count;
  }
}
